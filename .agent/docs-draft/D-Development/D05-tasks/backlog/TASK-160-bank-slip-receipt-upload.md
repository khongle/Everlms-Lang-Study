# Task: Bank Transfer Slip / Receipt Upload & Admin Verify Queue

**ID:** TASK-160
**Unit:** U-020 (lms_order), U-021 (lms_payment)
**Group:** G-COMMERCE
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Cho phép người dùng upload ảnh/ảnh chụp màn hình biên lai chuyển khoản (bank slip) sau khi thanh toán chuyển khoản thủ công. Đây là fallback quan trọng khi TASK-153 (VietQR auto-confirm) không nhận được webhook (do bank delay hoặc user chuyển khoản sai nội dung). Admin có queue verify để duyệt slip và mark order PAID.

Giao diện admin verify tương tự pattern `manage/grading` hiện tại (TASK-065): danh sách orders PENDING BANK TRANSFER với slip đã upload, admin click xem slip, approve/reject với note. OCR tự động (đọc số tiền, mã GD) để hỗ trợ verify nhanh hơn — planned cho v2, không scope trong task này.

## Acceptance Criteria

- [ ] Order detail page (post-checkout): hiển thị upload field nếu payment method là bank transfer + order status PENDING
- [ ] Upload: accept JPG/PNG/PDF, max 5MB, store trong managed file system (`private://bank-slips/`)
- [ ] `field_bank_slip_file` trên `lms_order` entity — managed file reference
- [ ] `field_bank_slip_uploaded_at` timestamp và `field_slip_status` (pending/approved/rejected)
- [ ] Admin queue view: `/admin/lms/orders/bank-slip-queue` — chỉ hiện orders có slip chưa verify
- [ ] Admin actions: "Approve" → set order PAID + trigger `OrderPaidEvent`; "Reject" → email user lý do
- [ ] Email notification khi admin approve: "Đơn hàng #{id} đã được xác nhận thanh toán"
- [ ] Email notification khi admin reject: kèm lý do, link upload slip mới
- [ ] Private file access: chỉ admin và owner order mới download được slip
- [ ] Unit test: file upload constraint (size, type); Kernel test: approve flow → order PAID

## Technical Notes

- Module: `web/modules/custom/lms_order/` + `web/modules/custom/lms_payment/`
- Admin view: Drupal Views + custom view field cho approve/reject buttons
- File storage: `private://bank-slips/{year}/{month}/{order_id}/` — tránh public URL
- Access control: `hook_file_download()` trong `lms_order.module`
- Form: `BankSlipUploadForm` at `/order/{order_id}/upload-slip`
- Event: approve triggers `OrderPaidEvent` — TASK-154 invoice sẽ tự issue
- OCR v2: ghi nhận TODO comment, không implement trong task này
- Coordinate với TASK-153 (auto-confirm), TASK-154 (invoice trigger)

## Dependencies

- TASK-153 (VietQR bank transfer — primary flow)
- TASK-050 (order status management)
- TASK-065 (admin queue pattern reference)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
