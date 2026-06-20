# Task: Trial Period Management

**ID:** TASK-200
**Unit:** — (lms_membership)
**Group:** G-COMMERCE
**Priority:** P1
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Implement N-day free trial for paid membership plans. When a user signs up for a trial-eligible plan, their Stripe subscription starts with a trial period and no immediate charge. The subscription auto-converts to paid at trial end. Users can cancel anytime during the trial with no charge. Email reminders are sent 3 days before trial expiry.

## Acceptance Criteria

- [ ] **Trial signup:**
  - [ ] Plan config: `trial_days` field on plan entity (0 = no trial, admin-configurable)
  - [ ] Checkout page shows "Start X-day free trial" CTA; billing details collected upfront via Stripe SetupIntent
  - [ ] Stripe subscription created with `trial_end = now + trial_days`; no charge until trial ends
  - [ ] Local membership status → `trialing`; `trial_ends_at` stored on entity
  - [ ] One trial per user per plan (enforced server-side; Stripe `metadata.trial_used = true`)
- [ ] **Auto-conversion:**
  - [ ] Stripe fires `customer.subscription.trial_will_end` (3 days before) → reminder email queued
  - [ ] Stripe fires `customer.subscription.updated` (status `trialing` → `active`) → local status updated; welcome email for paid tier sent
- [ ] **Cancel during trial:**
  - [ ] User cancels via TASK-197 cancel flow (available during `trialing` status)
  - [ ] Stripe subscription cancelled immediately; no charge issued
  - [ ] Local status → `trial_cancelled`
  - [ ] `trial_used` flag remains `true` — user cannot start another trial for same plan
- [ ] **Email reminders (3 days before trial end):**
  - [ ] Queue entry created when `customer.subscription.trial_will_end` webhook received
  - [ ] Email contains: trial end date, plan price, "Manage subscription" link
  - [ ] If user already cancelled, reminder not sent (check status before send)
- [ ] Dashboard shows trial status badge: "Free trial — X days remaining"
- [ ] PHPUnit test: trial signup → reminder queued → auto-convert; trial signup → cancel → no charge

## Technical Notes

- Module: `lms_membership` — new fields on `MembershipPlanEntity`: `trial_days` (integer, default 0); on `MembershipEntity`: `trial_ends_at` (datetime), `trial_used` (boolean)
- Webhook handler: `StripeWebhookController::handleTrialWillEnd()` — creates `lms_notification` queue item
- Email template: `lms_notification/templates/email/trial-reminder.html.twig`
- Trial deduplication: check `lms_membership` table for any row with `uid = :uid AND plan_id = :plan AND trial_used = 1` before allowing new trial
- Stripe metadata: set `metadata['trial_used'] = 'true'` on Customer object on trial start
- State machine states to add: `trialing`, `trial_cancelled` (extends states from TASK-197)

## Dependencies

- TASK-197 (cancel flow — reused for trial cancellation)
- TASK-029 (Stripe webhooks, SetupIntent)
- TASK-001 (membership plan entity)
- TASK-005 (checkout integration)

## Definition of Done

- [ ] Code complete — plan config, checkout flow, webhook handlers, reminder email
- [ ] G1-G4 gates passed
- [ ] Stripe test mode: trial_end webhook replayed via Stripe CLI → conversion verified
- [ ] Cannot start second trial for same plan after first
- [ ] Tests written + passing
- [ ] No new debt introduced
