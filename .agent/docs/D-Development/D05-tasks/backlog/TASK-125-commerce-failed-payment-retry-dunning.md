# Task: Failed Payment Retry with Dunning Emails

**ID:** TASK-125
**Unit:** U-022 (lms_membership), U-021 (lms_payment)
**Group:** G-COMMERCE (hardening)
**Priority:** P2
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Implement a dunning process for failed recurring membership payments. Retries are attempted on a schedule (day 1, 3, 5, 7 after initial failure). A corresponding email cadence notifies the member at each retry and escalates to a suspension warning. If payment remains unpaid after 14 days, the membership is auto-suspended.

## Context

- Module: `lms_membership`, `lms_payment`
- Triggered by Stripe `invoice.payment_failed` or PayPal `PAYMENT.SALE.DENIED` webhook events
- Retry schedule: cron-based or Stripe Smart Retries depending on provider

## Acceptance Criteria

- [ ] Webhook handler for `invoice.payment_failed` (Stripe) and `PAYMENT.SALE.DENIED` (PayPal) sets membership status to `payment_failed`
- [ ] Dunning record created: `{membership_id, attempt_number, next_retry_at, status}`
- [ ] Cron retries payment on schedule: +1 day, +3 days, +5 days, +7 days from first failure
- [ ] Email on each failed retry: "Payment attempt N of 4 failed — please update your card"
- [ ] Email at day 10: "Suspension warning — your access will be suspended in 4 days"
- [ ] At day 14 without successful payment: membership status set to `suspended`; course access revoked
- [ ] Learner can update payment method at `/profile/billing` which triggers immediate retry
- [ ] On successful retry payment: dunning record cleared, membership restored to `active`, success email sent
- [ ] Admin dunning dashboard at `/admin/lms/membership/dunning` shows all accounts in dunning with retry timeline

## Technical Notes

- New entity or table: `lms_membership_dunning` to track retry state (or a field bundle on membership)
- Cron handler in `lms_membership.module` → `hook_cron()`; query dunning records where `next_retry_at <= REQUEST_TIME`
- Stripe retry: use `\Stripe\Invoice::pay($invoice_id)` on the open invoice
- PayPal retry: re-initiate payment via subscription agreement
- Email templates: `lms-email-dunning-retry.html.twig`, `lms-email-dunning-warning.html.twig`, `lms-email-dunning-suspended.html.twig`
- Idempotency: each retry attempt has unique attempt key (extends TASK-029/031 pattern)
- Suspension: call `MembershipManager::suspend()` which revokes all enrollment-via-membership access

## Dependencies

- TASK-029 (webhook hardening)
- TASK-030 (idempotency)
- TASK-031 (payment provider abstraction)
- TASK-050 (commerce hardening)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
