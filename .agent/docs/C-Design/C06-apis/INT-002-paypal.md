# C06 API/Contract - INT-002 PayPal

**Requirement:** [B06 INT-002](../../B-Requirements/B06-integration-requirements/INT-002-paypal.md)
**Participating modules:** [lms_payment](../C05-modules/lms_payment.md), [lms_paypal](../C05-modules/lms_paypal.md)

## Purpose
Checkout qua PayPal bằng `@PaymentGateway` là `paypal`. ⚠️ Hiện có hai bản code trùng nhau (lms_payment + lms_paypal) — cần hợp nhất (R-004).

## Proposed contract
- **REST namespace:** gọi ra ngoài tới PayPal API; nhận return inbound `/payment/paypal/return`. Khuyến nghị dùng webhook `POST /api/v1/payment/paypal/webhook`.
- **Auth:** PayPal client id/secret nằm trong config (R-001) → cần chuyển sang Key/env. Chỉ gọi server-side.
- **Authorization:** chỉ order owner; capture ở phía server-side.
- **Response envelope:** `{ data: {payment_id, status}, error|null, meta: {gateway: "paypal"} }`.
- **Audit:** ghi log attempt/result + transaction id; redact các thông tin bí mật.

## Test requirements
- CI: mock PayPal client; dùng sandbox creds cho integration env.
- Auth+authz: chỉ order owner mới qua được gate.
- Failure/fallback: bị cancelled/failed → order giữ pending, cho retry/đổi gateway.
- Contract test: success + cancel; xác minh chỉ CÒN MỘT PayPal gateway hoạt động sau khi dọn dẹp theo R-004.
