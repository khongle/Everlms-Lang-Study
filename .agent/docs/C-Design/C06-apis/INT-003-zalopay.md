# C06 API/Contract - INT-003 ZaloPay

**Requirement:** [B06 INT-003](../../B-Requirements/B06-integration-requirements/INT-003-zalopay.md)
**Participating modules:** [lms_payment](../C05-modules/lms_payment.md)

## Purpose
Checkout ví ZaloPay qua `@PaymentGateway` là `zalopay`, phục vụ thị trường Việt Nam. ⚠️ Mới là stub.

## Proposed contract
- **REST namespace:** gọi ra ngoài tới ZaloPay API; nhận callback inbound `/payment/zalopay/callback`.
- **Auth:** ZaloPay app id + key1/key2 nằm trong config (R-001) → cần chuyển sang Key/env.
- **Authorization:** order owner; chỉ server-side.
- **Response envelope:** `{ data: {payment_id, status}, error|null, meta: {gateway: "zalopay"} }`.
- **Audit:** ghi log attempt/result + transaction id; ZaloPay yêu cầu xác minh callback bằng HMAC (cần triển khai).

## Test requirements
- CI: mock client; dùng sandbox creds.
- Auth+authz: order-owner gate; xác minh HMAC của callback.
- Failure/fallback: thất bại → giữ pending, cho retry/đổi gateway.
- Contract test: success + chữ ký callback; ghi nhận các gap còn ở dạng stub trong [E06](../../E-Quality/E06-implementation-status/_index.md).
