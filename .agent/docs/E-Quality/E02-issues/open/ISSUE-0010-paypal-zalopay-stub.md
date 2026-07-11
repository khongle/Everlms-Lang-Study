# ISSUE-0010 - PayPal/ZaloPay gateways stub/incomplete

**State:** open
**Severity:** P2 · **Module:** [lms_payment](../../../C-Design/C05-modules/lms_payment.md) · **Story:** [E06S02-lms_payment](../../../D-Development/D03-stories/E06S02-lms_payment.md) · **Risk:** R-008

## Summary
Of the four `@PaymentGateway` plugins, `bank_transfer` and `stripe` are functional; `paypal` and `zalopay` are stub/incomplete (recognised by the plugin manager but not production-complete — ZaloPay in particular still needs HMAC callback verification), reducing payment reach in the VN market.

## Steps to reproduce
1. At checkout (P029), select the PayPal or ZaloPay payment method.
2. Complete the flow to the point of gateway redirect/callback.
3. Observe the callback handling either short-circuits, skips signature/HMAC verification, or leaves the order in a non-terminal state — confirming the gateway is recognised but not production-complete.

## Root cause
`bank_transfer` and `stripe` were built out fully as the primary launch gateways; `paypal` (also see [ISSUE-0006](ISSUE-0006-duplicate-paypal-module.md) duplicate) and `zalopay` were scaffolded to prove the plugin architecture generalizes, but their callback/signature-verification legs were not finished before this build pass closed.

## Fix
Complete PayPal (after deduping per ISSUE-0006) and ZaloPay flows including callbacks/signatures (ZaloPay HMAC verification in particular); until complete, mark both clearly as unavailable/beta in the checkout UI so buyers are not routed into a broken flow.

## Regression test
No automated regression test exists yet (tracked under [ISSUE-0004](ISSUE-0004-zero-test-coverage.md) / [X01](../../../D-Development/D04-tasks/todo/X01-establish-test-baseline.md)). Planned: an [E03](../../E03-test-definitions/_index.md) `gateway-capture` Kernel test per gateway asserting a valid callback/HMAC payload transitions the order to `completed` and an invalid one is rejected without state change.
