# C04 Entity - lms_course_bundle

**Display name:** Course Bundle
**Owning module(s):** [lms_bundle](../C05-modules/lms_bundle.md)
**Mode:** as-built (Content Entity)

## Purpose
Một gói có thể bán gồm nhiều course với một mức giá/giảm giá bundle duy nhất.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| name | bundle title |
| field_description | formatted description |
| field_price | bundle price |
| field_discount_percent | discount vs. buying individually |
| field_thumbnail | catalog image |
| field_courses | **multi-ref → lms_course** (included courses) |
| status | publish |

## Relationships
- N→N `lms_course` qua `field_courses`.
- Có thể mua qua `lms_order_item` (type=bundle); việc mua sẽ tỏa ra thành enrollment cho từng course.

## Validation rules
Nguồn: `web/modules/custom/lms_bundle/src/Entity/CourseBundle.php`.
- `name`: bắt buộc, tối đa 255 ký tự.
- `field_price`: decimal(10,2), mặc định 0.
- `field_discount_percent`: integer, mặc định 0; không có ràng buộc khoảng giá trị 0-100 trong code (có thể lưu số âm hoặc >100 nếu form không chặn).
- `field_courses`: entity_reference tới `lms_course`, cardinality unlimited, không bắt buộc.

## Data invariants
- `field_price` của bundle là giá bán cố định do editor nhập, **không tự động = tổng giá các course thành phần trừ discount** — không có service nào tính `field_price` từ `field_courses` + `field_discount_percent` (gap as-built, tương tự gap "auto-calculated" của `lms_course.field_duration`).
- `status` mặc định TRUE (active by default).

## Calculations & derived values
- Không tìm thấy logic tính `field_price` hay `field_discount_percent` tự động từ danh sách course trong `field_courses` — cả hai là input thủ công của editor.
- Khi mua bundle (qua `lms_order_item` type=bundle), việc "tỏa ra" thành enrollment riêng cho từng course thuộc về logic checkout/order, không phải giá trị dẫn xuất lưu trên `lms_course_bundle`.

## Editorial/data management
Editor tạo bundle và chọn course qua bundle entity form. Price/discount là field. Cache bị invalidate khi save. Xem [F-009](../../B-Requirements/B03-features/F-009-course-bundles.md), [C16 sheet 01](../C16-item-solutions/01-entities.md).
