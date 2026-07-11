# F-010 Acceptance Criteria - Orders & checkout

## Core criteria
1. **Given** một buyer, **when** họ thêm một course/bundle/membership, **then** một `lms_order` + `lms_order_item` (`field_items`) được tạo ở trạng thái `pending`.
2. **Given** một order đang `pending`, **when** payment thành công (F-011), **then** order chuyển sang paid/completed và entitlement được cấp.
3. **Given** một order đang `pending`, **when** payment thất bại/bị hủy, **then** order vẫn ở trạng thái pending/cancelled và không entitlement nào được cấp.
4. **Given** một order, **when** một coupon được áp dụng (WF-009), **then** tổng tiền phản ánh discount.

## Evidence expected
- Module: [lms_order](../../C-Design/C05-modules/lms_order.md).
- Test: order state-machine test; entitlement-on-paid test; ⚠️ lưu ý chưa có webhook reconciliation (R-002) (E03).
