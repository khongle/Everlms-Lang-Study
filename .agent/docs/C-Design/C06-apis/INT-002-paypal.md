# C06 API/Contract - INT-002 PayPal

**Requirement:** [B06 INT-002](../../B-Requirements/B06-integration-requirements/INT-002-paypal.md)
**Participating modules:** [lms_payment](../C05-modules/lms_payment.md), [lms_paypal](../C05-modules/lms_paypal.md)

## Purpose
PayPal checkout via the `paypal` `@PaymentGateway`. ⚠️ Two code copies exist (lms_payment + lms_paypal) — consolidate (R-004).

## Proposed contract
- **REST namespace:** outbound to PayPal API; inbound return `/payment/paypal/return`. Recommended webhook `POST /api/v1/payment/paypal/webhook`.
- **Auth:** PayPal client id/secret in config (R-001) → move to Key/env. Server-side.
- **Authorization:** order owner only; server-side capture.
- **Response envelope:** `{ data: {payment_id, status}, error|null, meta: {gateway: "paypal"} }`.
- **Audit:** log attempt/result + transaction id; redact secrets.

## Test requirements
- CI: mocked PayPal client; sandbox creds for integration env.
- Auth+authz: order-owner gate.
- Failure/fallback: cancelled/failed → order pending, retry/switch.
- Contract test: success + cancel; verify only ONE PayPal gateway is active after R-004 cleanup.
