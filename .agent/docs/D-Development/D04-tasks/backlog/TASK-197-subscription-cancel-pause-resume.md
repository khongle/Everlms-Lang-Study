# Task: Subscription Self-Service — Cancel, Pause, Resume

**ID:** TASK-197
**Unit:** — (cross-cutting lms_membership)
**Group:** G-COMMERCE
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Implement subscription self-service actions for users: cancel (period-end vs immediate), pause (1–3 month hold), and resume a paused subscription. All state changes must be reflected in both local `lms_membership` entity fields and the upstream Stripe / PayPal subscription objects. Includes confirmation modals to prevent accidental actions and a reactivation flow for churned users.

## Acceptance Criteria

- [ ] **Cancel flow:**
  - [ ] User sees two options: "Cancel at period end" and "Cancel immediately"
  - [ ] Period-end cancel: sets Stripe `cancel_at_period_end = true`; local membership status stays `active` until period ends, then transitions to `cancelled`
  - [ ] Immediate cancel: Stripe subscription cancelled now; local membership status → `cancelled` immediately
  - [ ] Confirmation modal displays: effective date, features lost, refund eligibility
  - [ ] Cancellation reason dropdown (voluntary/too-expensive/technical/other) stored on membership entity
- [ ] **Pause flow:**
  - [ ] User selects pause duration: 1, 2, or 3 months
  - [ ] Stripe subscription paused via `pause_collection` parameter
  - [ ] Local membership status → `paused`; `paused_until` date stored on entity
  - [ ] Paused user still has course access (read-only, no new enrolments)
  - [ ] Banner shown on dashboard: "Subscription paused until {date}"
- [ ] **Resume flow:**
  - [ ] User can resume early (before `paused_until` date)
  - [ ] Stripe `pause_collection` cleared; next billing cycle proceeds normally
  - [ ] Local membership status → `active`; `paused_until` cleared
- [ ] **Reactivation flow (cancelled → active):**
  - [ ] `/my/subscription/reactivate` page: shows plan options + pricing
  - [ ] Creates new Stripe subscription (not reuse old); prorated first invoice if mid-cycle
  - [ ] Confirmation email sent on successful reactivation
- [ ] Webhook handler (`stripe.customer.subscription.updated`, `.deleted`, `.paused`, `.resumed`) syncs local state automatically
- [ ] All actions logged to `lms_moderation` audit trail
- [ ] PHPUnit kernel tests: cancel (both modes), pause, resume state machine transitions

## Technical Notes

- Module: `lms_membership` — extend `MembershipEntity`, add UI routes under `/my/subscription/`
- New entity fields: `pause_duration` (integer), `paused_until` (datetime), `cancel_reason` (list_string), `cancel_at_period_end` (boolean)
- Stripe API calls: `\Stripe\Subscription::update()`, `::cancel()`; use `lms_payment` StripeClientFactory
- PayPal: use Billing Agreements suspend/reactivate; feature-flag PayPal pause support (H05)
- Confirmation modal: custom Drupal AJAX dialog; no Symfony Form submission without JS
- State machine: define states in `lms_membership.workflows.yml` using Drupal state_machine contrib or custom enum
- Routes: `lms_membership.routing.yml` → `/my/subscription/cancel`, `/pause`, `/resume`, `/reactivate`

## Dependencies

- TASK-001 (base membership entity)
- TASK-029 (Stripe gateway integration)
- TASK-031 (PayPal gateway integration)
- TASK-124 (prorate logic — used in reactivation preview)

## Definition of Done

- [ ] Code complete — entity fields, controllers, modal forms, webhook sync
- [ ] G1-G4 gates passed
- [ ] Stripe test mode: cancel/pause/resume round-trip verified via Stripe CLI event replay
- [ ] Webhook idempotency: replaying same event twice does not double-transition state
- [ ] Tests written + passing
- [ ] No new debt introduced
