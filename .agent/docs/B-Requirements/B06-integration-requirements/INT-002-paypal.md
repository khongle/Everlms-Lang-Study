# INT-002 - PayPal

## Requirement
Offer PayPal as a checkout option via the `@PaymentGateway` plugin `paypal`. Redirect/checkout flow to PayPal and return to confirm the order.

## Participating modules
[lms_payment](../../C-Design/C05-modules/lms_payment.md), [lms_paypal](../../C-Design/C05-modules/lms_paypal.md)

## Contract expectations
1. **Auth/authz:** PayPal client id/secret (config YAML today — R-001); server-side calls.
2. **Failure/retry/fallback:** cancelled/failed PayPal flow leaves the order `pending`; buyer can retry or switch gateway.
3. **CI test fixture/stub:** PayPal sandbox creds + mocked client; no live calls in CI.
4. **Operational owner + alerting:** Commerce owner. ⚠️ **Code duplication (R-004):** a PayPal gateway exists in BOTH `lms_paypal` and `lms_payment` — `lms_paypal` is dead/duplicate and should be removed; gateway is stub/partial today.

## Design link
[C06 INT-002](../../C-Design/C06-apis/INT-002-paypal.md)
