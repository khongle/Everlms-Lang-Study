# C04 Entity - lms_order_item

**Display name:** Order Item (line)
**Owning module(s):** [lms_order](../C05-modules/lms_order.md)
**Mode:** as-built (Content Entity)

## Purpose
Một dòng có thể mua duy nhất trên một order — một course, một bundle, hoặc một membership plan.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| field_course | ref → lms_course (when a course line) |
| field_membership_plan | ref → lms_membership_plan (when a plan line) |
| field_price | line price |
| field_type | line type (course / bundle / membership) |

## Relationships
- Được tham chiếu bởi `lms_order.field_items`. Trỏ tới sản phẩm được mua (course/bundle/plan).

## Validation rules
Nguồn: `web/modules/custom/lms_order/src/Entity/OrderItem.php`.
- `field_type`: `list_string`, giá trị hợp lệ `course|bundle|membership`, mặc định `course`.
- `field_price`: decimal(10,2), mặc định 0.
- `field_course`, `field_membership_plan`: entity_reference, **cả hai đều KHÔNG bắt buộc** (`setRequired()` không được gọi trên field nào) — khác với kỳ vọng nghiệp vụ rằng một trong hai phải có giá trị tuỳ theo `field_type`.

## Data invariants
- `field_type = course` kỳ vọng `field_course` được điền và `field_membership_plan` rỗng; `field_type = membership` kỳ vọng ngược lại; `field_type = bundle` không có field tham chiếu bundle riêng (không có `field_bundle`) — chỉ có thể suy luận qua ngữ cảnh, không lưu tường minh id của `lms_course_bundle` nào được mua. Toàn bộ ràng buộc chéo này **không được enforce trong `baseFieldDefinitions()` hay `addConstraint()`** — gap as-built, entity có thể lưu ở trạng thái không nhất quán (ví dụ `field_type = bundle` mà cả `field_course` và `field_membership_plan` đều rỗng, không có cách xác định bundle nào đã mua).

## Calculations & derived values
- `field_price` là giá dòng hàng chốt tại thời điểm checkout, sao chép từ giá course/plan hiện hành lúc đặt hàng; không tìm thấy đoạn code tạo `lms_order_item` cụ thể trong phạm vi các file đã kiểm tra (`CheckoutForm.php` build `Order` với `field_items` nhưng logic tạo từng `OrderItem` nằm ở nơi khác trong `lms_order` chưa được xác nhận chi tiết) — nếu cần công thức chính xác, cần đối chiếu thêm `lms_order` controller/service tạo item.

## Editorial/data management
Được hệ thống tạo tại checkout. `field_type` của dòng này quyết định entitlement nào được cấp khi thanh toán. Xem [lms_order](lms_order.md).
