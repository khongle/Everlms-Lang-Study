# ISSUE-004 — Payment Gateway chỉ là Stub

| Field | Value |
|-------|-------|
| ID | ISSUE-004 |
| Severity | **High** |
| Status | OPEN |
| Source | RISK-004 (A-Project-Information/A03-project-context.md) |
| Related Units | U-028 (StripeGateway stub), U-029 (PayPalGateway stub), U-060 (Webhook Stripe), U-061 (Webhook PayPal) |
| Found | 2026-04-15 |
| Owner | Unassigned |

## Mô tả

`StripeGateway` và `PayPalGateway` trong `lms_payment` là các stub implementation — không gọi API thật. Toàn bộ checkout flow (U-030) hiện chưa được implement. Webhook handler (U-031) chưa có signature verification.

Điều này đồng nghĩa với việc:
- Không thể nhận payment thật trên staging / production
- Không có idempotency guard cho callback (ISSUE-004 kết hợp với U-062)
- Stripe webhook có thể bị replay attack nếu signature verification thiếu

## Cách tái hiện (Reproduction)

1. Xem code: `lms_payment/src/Plugin/PaymentGateway/ZaloPayGateway.php` (tương tự Stripe/PayPal)
2. Method `processPayment()` trả về success ngay lập tức mà không gọi external API
3. Webhook endpoint `/api/lms/payment/webhook` chưa kiểm tra `Stripe-Signature` header

## Root Cause Hypothesis

Đây là thiết kế có chủ đích (stub for development). Tuy nhiên các unit U-028/U-029 đã đánh dấu `DONE | G4 pass` mà chưa có chú thích rõ ràng là stub-only. Unit U-030 (checkout workflow) và U-031 (webhook handler) vẫn ở SPEC.

## Tác động

- **Architecture** (G4): Không thể go-live với stub payment
- **Security**: Thiếu webhook signature verification → replay attack / order fraud
- **Business**: Không thu được doanh thu thực

## Phương án khắc phục đề xuất

1. Implement real Stripe SDK calls trong `StripeGateway` (U-028 → cần re-open)
2. Implement webhook signature verification dùng `Stripe\Webhook::constructEvent()` (U-060)
3. Idempotency keys cho payment callbacks (U-062)
4. Unit test với Stripe test mode + test cards

## Traceability

- Risk: RISK-004
- Units: U-028, U-029, U-030, U-031, U-060, U-061, U-062
- Test: TEST-004-payment-checkout trong E03-test-definitions/
