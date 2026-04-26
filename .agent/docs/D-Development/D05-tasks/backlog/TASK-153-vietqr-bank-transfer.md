# Task: VietQR + Bank Transfer Auto-Confirm

**ID:** TASK-153
**Unit:** U-021 (lms_payment)
**Group:** G-COMMERCE
**Priority:** P0
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Triển khai tính năng thanh toán chuyển khoản ngân hàng với QR code theo chuẩn VietQR (NAPAS) và tự động xác nhận đơn hàng thông qua webhook từ nhà cung cấp bên thứ ba (Casso hoặc MB Bank API). VietQR là chuẩn quốc gia được NAPAS định nghĩa, cho phép tạo QR code chứa đủ thông tin: STK, ngân hàng, số tiền, nội dung chuyển khoản.

Luồng hoạt động: user chọn "Chuyển khoản ngân hàng" → hệ thống sinh QR VietQR với `order_id` làm nội dung CK → user quét QR và chuyển khoản → Casso/MB Bank webhook gọi về hệ thống → so khớp `order_id` trong nội dung giao dịch → auto-mark order PAID. Extend `lms_payment` thay vì tạo module mới.

## Acceptance Criteria

- [ ] `BankTransferGateway` class trong `lms_payment` implements `PaymentGatewayInterface`
- [ ] Tạo QR VietQR theo spec NAPAS EMV QR Code: encode STK, bank BIN, amount, `LMS-{order_id}` làm description
- [ ] QR code hiển thị tại checkout với countdown 15 phút và refresh tự động
- [ ] Webhook endpoint `/payment/bank-transfer/webhook` nhận notification từ Casso/MB Bank API
- [ ] Parse `description` field của giao dịch, extract `order_id` bằng regex `LMS-\d+`
- [ ] Match order: `order_id` tồn tại + amount >= order total + order status PENDING → mark PAID
- [ ] Idempotency: reject duplicate webhook nếu transaction reference đã xử lý
- [ ] Webhook signature verify (Casso HMAC / MB Bank basic auth)
- [ ] Admin config: bank account number, bank name, BIN code, webhook secret, Casso API token
- [ ] Unit test: QR payload encoding đúng spec VietQR
- [ ] Kernel test: webhook với description khớp `LMS-{id}` → order PAID

## Technical Notes

- Class: `web/modules/custom/lms_payment/src/Plugin/Payment/BankTransferGateway.php`
- VietQR spec: https://vietqr.net/danh-sach-api — EMV QRCPS Merchant Presented Mode
- Casso API docs: gửi POST JSON `{ id, tid, description, amount, bank_account_number, when }`
- QR library: `endroid/qr-code` (nếu đã có từ TASK-152) hoặc build raw EMV string + pass to Google Charts API
- Bank BIN list: file `lms_payment/data/vn_bank_bins.json` — include top 20 banks
- Config: `lms_payment.bank_transfer_settings`
- Coordinate với TASK-160 (slip upload — fallback khi webhook không đến)

## Dependencies

- TASK-029 (payment base)
- TASK-050 (idempotency)
- TASK-031 (webhook hardening)
- TASK-160 (bank slip upload — parallel)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
