# ISSUE-0002 - No payment webhooks / signature verification

**State:** open
**Severity:** P0 · **Module:** [lms_payment](../../../C-Design/C05-modules/lms_payment.md) / [lms_stripe](../../../C-Design/C05-modules/lms_stripe.md) · **Story:** [E06S02-lms_payment](../../../D-Development/D03-stories/E06S02-lms_payment.md) · **Risk:** R-002 · **Task:** [E06S02T01](../../../D-Development/D04-tasks/todo/E06S02T01-add-payment-webhooks.md)

## Summary
Order/payment state is set only from the synchronous gateway return; there are no webhook endpoints and no signature verification for Stripe/PayPal/ZaloPay, so anything that happens asynchronously at the gateway (delayed capture, refund, chargeback) never reaches `lms_order`.

## Steps to reproduce
1. Inspect `lms_payment.routing.yml` and `lms_stripe.routing.yml` for a webhook/callback route → none found.
2. Trigger a test refund/chargeback directly in a gateway's sandbox dashboard for an order captured by this build.
3. Reload the order in `/admin/lms/orders` → `field_status`/`field_payment_id` are unchanged; the site has no way to learn the gateway-side state changed.

## Root cause
Gateway plugins (`src/Plugin/PaymentGateway/*`) were built to handle only the synchronous "return URL" leg of checkout; the asynchronous webhook leg (signature verification + idempotent state reconciliation) was never implemented, so `lms_order` truth can silently diverge from gateway truth.

## Fix
Add signed webhook endpoints per gateway (Stripe/PayPal/ZaloPay); verify signatures before trusting payload; implement idempotent handlers that update `lms_order.field_status`/`field_payment_id`; alert (H-layer) on any mismatch between webhook-reported and locally-recorded state.

## Regression test
No automated regression test exists yet (tracked under [ISSUE-0004](ISSUE-0004-zero-test-coverage.md) / [X01](../../../D-Development/D04-tasks/todo/X01-establish-test-baseline.md)). Planned: an [E03](../../E03-test-definitions/_index.md) `gateway-capture` Kernel test asserting a mocked webhook payload (valid signature) transitions `field_status` idempotently, and a second assertion that an invalid-signature payload is rejected.
