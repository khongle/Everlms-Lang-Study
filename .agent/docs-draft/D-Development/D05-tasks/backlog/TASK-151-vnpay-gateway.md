# Task: VNPay Payment Gateway

**ID:** TASK-151
**Unit:** new (raise)
**Group:** G-COMMERCE
**Priority:** P0
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Triển khai cổng thanh toán VNPay cho thị trường Việt Nam, tạo module mới `lms_vnpay` theo Adapter pattern đã định nghĩa trong ADR-007. VNPay là cổng thanh toán phổ biến nhất tại VN, hỗ trợ ATM nội địa (Napas), Visa/Mastercard, và ví điện tử. Ưu tiên P0 vì đây là yêu cầu kinh doanh cốt lõi để thu tiền từ học viên trong nước.

Module phải xử lý cả hai luồng: redirect (người dùng bị chuyển sang trang VNPay) và webhook (VNPay gọi lại `ipn_url` để confirm giao dịch). Chữ ký HMAC-SHA512 bắt buộc verify ở cả hai hướng để chống giả mạo. Idempotency key theo pattern đã định nghĩa trong TASK-050 để tránh ghi nhận đơn hàng hai lần.

## Acceptance Criteria

- [ ] Module `lms_vnpay` tồn tại tại `web/modules/custom/lms_vnpay/` với `lms_vnpay.info.yml`
- [ ] `VnpayGateway` implements `PaymentGatewayInterface` (ADR-007 Adapter pattern)
- [ ] Build query string và verify HMAC-SHA512 signature đúng spec VNPay v2.1.0
- [ ] Redirect URL handler: `/payment/vnpay/return` parse params, verify sig, update order status
- [ ] IPN (Instant Payment Notification) handler: `/payment/vnpay/ipn` — verify sig, idempotency check, mark order PAID
- [ ] Sandbox credentials configurable qua `lms_vnpay.settings` config form (tmn_code, hash_secret, url, env)
- [ ] Lỗi signature mismatch → log security event + return HTTP 400, không cập nhật order
- [ ] Unit test: `VnpaySignatureTest` — build + verify với known test vector từ VNPay docs
- [ ] Kernel test: happy-path IPN call marks `lms_order` as paid
- [ ] Admin status page hiển thị last IPN received timestamp

## Technical Notes

- Module path: `web/modules/custom/lms_vnpay/`
- Class: `src/Plugin/Payment/VnpayGateway.php` annotated `@PaymentGateway`
- Signature: `hash_hmac('sha512', $queryString, $hashSecret)` — tất cả params sorted theo key ASC, URL-encoded
- IPN response phải trả JSON `{"RspCode":"00","Message":"Confirm Success"}` trong 5s
- Config: `web/modules/custom/lms_vnpay/config/install/lms_vnpay.settings.yml`
- Coordinate với `lms_payment` `PaymentManager::processCallback()`
- Idempotency: check `field_transaction_id` on order trước khi update — theo TASK-050 pattern

## Dependencies

- TASK-029 (payment base hardening)
- TASK-050 (idempotency pattern)
- ADR-007 (payment adapter pattern)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
