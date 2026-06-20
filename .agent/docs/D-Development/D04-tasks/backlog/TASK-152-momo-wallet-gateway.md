# Task: MoMo Wallet Payment Gateway

**ID:** TASK-152
**Unit:** new (raise)
**Group:** G-COMMERCE
**Priority:** P0
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Triển khai cổng thanh toán MoMo (ví điện tử lớn nhất Việt Nam) qua module mới `lms_momo`. MoMo sử dụng kết hợp AES-128 encrypt payload và RSA signature để bảo mật, khác với VNPay HMAC đơn thuần. MoMo hỗ trợ hai phương thức: QR code (quét bằng app MoMo) và deep-link (chuyển thẳng vào app trên mobile).

Webhook idempotency là yêu cầu bắt buộc theo pattern TASK-050 vì MoMo có thể gọi lại IPN nhiều lần khi timeout. Cần handle both `payUrl` (web redirect) và `deeplink` (mobile) trong cùng một gateway class. Module mới `lms_momo`, không extend `lms_vnpay`.

## Acceptance Criteria

- [ ] Module `lms_momo` tồn tại tại `web/modules/custom/lms_momo/` với `lms_momo.info.yml`
- [ ] `MomoGateway` implements `PaymentGatewayInterface` (ADR-007)
- [ ] RSA signature dùng private key của merchant để sign request gửi MoMo
- [ ] Verify signature từ MoMo IPN bằng MoMo public key
- [ ] Tạo QR code: gọi MoMo API v2 `/v2/gateway/api/create`, trả về `qrCodeUrl` — render QR img trong checkout
- [ ] Deep-link: trả về `deeplink` URL, redirect trên mobile agent
- [ ] IPN handler: `/payment/momo/ipn` — idempotency check by `orderId` + `requestId`, mark order PAID
- [ ] Config form: `partner_code`, `access_key`, `secret_key`, `public_key_pem`, `private_key_pem`, env (sandbox/prod)
- [ ] Unit test: RSA sign + verify round-trip với test keypair
- [ ] Kernel test: IPN callback → order status PAID; second identical IPN → idempotent (no duplicate)
- [ ] Lỗi signature mismatch → log + HTTP 400, không thay đổi order

## Technical Notes

- Module path: `web/modules/custom/lms_momo/`
- Class: `src/Plugin/Payment/MomoGateway.php`
- MoMo API v2 base URL: `https://payment.momo.vn` (prod), `https://test-payment.momo.vn` (sandbox)
- RSA: `openssl_sign()` / `openssl_verify()` với `OPENSSL_ALGO_SHA256`
- AES: `openssl_encrypt($data, 'AES-128-CBC', $secretKey)` cho legacy v1 compat nếu cần
- Idempotency: store `requestId` trong `lms_payment` transaction log, reject duplicates
- QR rendering: sử dụng `endroid/qr-code` library hoặc external img URL từ MoMo response
- Coordinate với TASK-050, TASK-029, TASK-030

## Dependencies

- TASK-029 (payment base hardening)
- TASK-030 (payment webhook hardening)
- TASK-050 (idempotency pattern)
- ADR-007 (payment adapter pattern)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
