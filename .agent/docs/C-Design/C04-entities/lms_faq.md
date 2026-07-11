# C04 Entity - lms_faq

**Display name:** FAQ
**Owning module(s):** [lms_faq](../C05-modules/lms_faq.md)
**Mode:** as-built (Content Entity, translatable)

## Purpose
Một mục frequently-asked-question, được phân loại và có thể scope theo course (tùy chọn); được render thành FAQ công khai (dạng accordion).

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, langcode, default_langcode | keys + translation |
| question | the question |
| field_answer | formatted answer |
| field_scope | global vs course-scoped |
| field_course | ref → lms_course (when scoped) |
| field_category | `faq_category` taxonomy |
| weight | display order |

## Relationships
- N→1 `lms_course` (tùy chọn); được phân loại bởi `faq_category`.

## Validation rules
Nguồn: `web/modules/custom/lms_faq/src/Entity/Faq.php`.
- `question`: bắt buộc, translatable, tối đa 512 ký tự.
- `field_answer`: bắt buộc, translatable, `text_long` (formatted).
- `field_scope`: `list_string`, giá trị hợp lệ `global|course|lesson`, mặc định `global`.
- `field_course`: entity_reference tới `lms_course`, không bắt buộc.

## Data invariants
- Khi `field_scope = course` (hoặc `lesson`), nghiệp vụ kỳ vọng `field_course` phải được điền, nhưng field này **không được set `required` có điều kiện theo bundle/scope** trong `baseFieldDefinitions()` — có thể lưu `field_scope = course` với `field_course` rỗng mà không bị chặn (gap as-built).
- `field_scope = lesson` được liệt kê trong `allowed_values` nhưng **không có field tham chiếu lesson nào** trên entity (chỉ có `field_course`) — gap giữa enum và field model, cần xác nhận thêm với đội phát triển liệu đây là scope chưa hoàn thiện.

## Calculations & derived values
- `weight` là giá trị nhập tay để sắp xếp hiển thị, không phải giá trị tính toán.
- Không có trường nào khác được dẫn xuất tự động trên entity này.

## Editorial/data management
Content editor duy trì FAQ qua FAQ form; sắp xếp theo `weight`; hỗ trợ translatable. Toàn bộ nội dung nằm trong field. Xem [F-016](../../B-Requirements/B03-features/F-016-blog-and-faq-content.md).
