# C06 API/Contract - INT-001 Stripe

**Requirement:** [B06 INT-001](../../B-Requirements/B06-integration-requirements/INT-001-stripe.md)
**Participating modules:** [lms_stripe](../C05-modules/lms_stripe.md), [lms_payment](../C05-modules/lms_payment.md)

## Purpose
Thực hiện thanh toán thẻ tại checkout qua Stripe PHP SDK, đứng sau `@PaymentGateway` là `stripe`. Đây là tích hợp outbound; EverLMS không lưu dữ liệu thẻ.

## Proposed contract
- **REST namespace:** gọi ra ngoài tới Stripe API (`api.stripe.com`) qua SDK; nhận return inbound tại `/payment/stripe/return`. **Recommended (gap):** webhook `POST /api/v1/payment/stripe/webhook`.
- **Auth:** Stripe secret key (server) + publishable key (client). ⚠️ đang lưu trong `lms_payment.settings.yml` (R-001) — cần chuyển sang Key/env.
- **Authorization:** chỉ buyer đã authenticated và có order đang pending mới được khởi tạo; gateway chỉ được gọi ở phía server-side.
- **Response envelope (internal normalisation):** `{ data: {payment_id, status}, error: {code, message}|null, meta: {gateway: "stripe"} }`.
- **Audit:** ghi log lần charge attempt/result + `field_payment_id` trên order; không bao giờ log đầy đủ số thẻ/PAN.

## Test requirements
- CI: mock Stripe client (không gọi live); dùng Stripe test-mode keys cho integration env.
- Auth+authz: chỉ order owner mới được thanh toán; từ chối request chưa authenticated.
- Failure/retry/fallback: thẻ bị declined → order giữ nguyên pending, hiển thị lỗi rõ ràng, cho retry/đổi gateway khác.
- Contract test: success + decline + (target) xác minh chữ ký webhook.
