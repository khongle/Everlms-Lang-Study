# ISSUE-0002 - No payment webhooks / signature verification

**Severity:** P0 · **Status:** open · **Risk:** R-002 · **Task:** [E06S02T01](../../../D-Development/D04-tasks/todo/E06S02T01-add-payment-webhooks.md)

## Description
Order/payment state is set only from the synchronous gateway return; there are no webhook endpoints and no signature verification for Stripe/PayPal/ZaloPay.

## Impact
Orders can desync from gateway truth (e.g. async capture, refund, chargeback); no reconciliation; potential lost/dangling entitlements.

## Evidence
No webhook routes in `lms_payment`/`lms_stripe` `*.routing.yml`; gateway plugins capture inline only.

## Fix
Add signed webhook endpoints per gateway; idempotent handlers updating `lms_order.field_status`/`field_payment_id`; alert on mismatch.
