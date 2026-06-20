# C06 API/Contract - INT-003 ZaloPay

**Requirement:** [B06 INT-003](../../B-Requirements/B06-integration-requirements/INT-003-zalopay.md)
**Participating modules:** [lms_payment](../C05-modules/lms_payment.md)

## Purpose
ZaloPay wallet checkout via the `zalopay` `@PaymentGateway` for the Vietnamese market. ⚠️ Stub.

## Proposed contract
- **REST namespace:** outbound to ZaloPay API; inbound callback `/payment/zalopay/callback`.
- **Auth:** ZaloPay app id + key1/key2 in config (R-001) → move to Key/env.
- **Authorization:** order owner; server-side.
- **Response envelope:** `{ data: {payment_id, status}, error|null, meta: {gateway: "zalopay"} }`.
- **Audit:** log attempt/result + transaction id; ZaloPay requires HMAC callback verification (implement).

## Test requirements
- CI: mocked client; sandbox creds.
- Auth+authz: order-owner gate; verify callback HMAC.
- Failure/fallback: failed → pending, retry/switch.
- Contract test: success + callback signature; mark stub gaps in [E06](../../E-Quality/E06-implementation-status/_index.md).
