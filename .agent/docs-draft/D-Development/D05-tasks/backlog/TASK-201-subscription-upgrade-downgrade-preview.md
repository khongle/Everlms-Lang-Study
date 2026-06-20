# Task: Subscription Upgrade/Downgrade Preview

**ID:** TASK-201
**Unit:** — (lms_membership UI)
**Group:** G-COMMERCE
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Before a user confirms an upgrade or downgrade of their subscription plan, display a preview screen showing: the prorated charge or credit for the current billing cycle, the new billing date, and a feature diff (what access they gain or lose). The prorate calculation reuses the existing prorate logic from TASK-124.

## Acceptance Criteria

- [ ] **Preview page** (`/my/subscription/change-plan`):
  - [ ] Lists available plans (excluding current); each plan has "Switch to this plan" button
  - [ ] Clicking opens a preview modal/page before any charge
- [ ] **Preview modal contents:**
  - [ ] Prorated amount: positive = charge today, negative = credit applied to next invoice
  - [ ] New billing date (next regular cycle after switch)
  - [ ] Feature diff table: "You will gain access to: …" / "You will lose access to: …"
  - [ ] "Confirm switch" and "Cancel" actions
- [ ] **Prorate calculation:**
  - [ ] Call `ProratService::calculate($current_plan, $new_plan, $current_period_end)` from TASK-124
  - [ ] Amounts displayed in user's currency with 2 decimal places
  - [ ] If credit exceeds next invoice, remainder shown as future credit
- [ ] **Confirm switch:**
  - [ ] Stripe `Subscription::update(['items' => [...], 'proration_behavior' => 'create_prorations'])`
  - [ ] Local `MembershipEntity::plan_id` updated; next billing amount stored
  - [ ] Confirmation email sent with new plan details
- [ ] **Downgrade timing:**
  - [ ] Configurable: immediate or at period end (admin setting in `lms_membership.settings`)
  - [ ] Downgrade at period end: user retains current features until next billing date
- [ ] PHPUnit test: prorate calculation with known inputs; verify feature diff array construction

## Technical Notes

- Module: `lms_membership` — new `PlanChangeController`, `FeatureDiffService`
- `FeatureDiffService::diff($plan_a, $plan_b)` returns `['gain' => [...], 'lose' => [...]]` based on plan feature flags stored as config entities
- Stripe preview: `\Stripe\Invoice::upcoming(['customer' => ..., 'subscription_items' => [...], 'subscription_proration_behavior' => 'create_prorations'])` to fetch preview invoice
- Route: `GET /my/subscription/change-plan/{plan_id}/preview` → JSON response consumed by modal JS
- Twig modal template: `lms_membership/templates/plan-change-preview.html.twig`

## Dependencies

- TASK-124 (prorate logic — ProratService)
- TASK-029 (Stripe subscription update)
- TASK-001 (membership plan entity, feature flags)
- TASK-197 (subscription state machine — must be active to allow change)

## Definition of Done

- [ ] Code complete — preview endpoint, feature diff service, confirm controller, email
- [ ] G1-G4 gates passed
- [ ] Prorate values match Stripe upcoming invoice preview
- [ ] Tests written + passing
- [ ] No new debt introduced
