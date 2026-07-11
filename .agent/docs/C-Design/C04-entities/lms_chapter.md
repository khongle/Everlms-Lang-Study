# C04 Entity - lms_chapter

**Display name:** Chapter
**Owning module(s):** [lms_chapter](../C05-modules/lms_chapter.md)
**Mode:** as-built (Content Entity, translatable)

## Purpose
Cấp giữa trong learning hierarchy — nhóm các lesson có thứ tự trong một course.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| langcode, default_langcode | translation |
| name | chapter title |
| field_description | formatted description |
| field_lessons | **multi-ref → lms_lesson** (ordered lessons) |
| weight | ordering within the course |
| status, created, changed | publish + timestamps |

## Relationships
- Parent: được tham chiếu bởi `lms_course.field_chapters`.
- Children: 1→N `lms_lesson` qua `field_lessons`.

## Validation rules
Nguồn: `web/modules/custom/lms_chapter/src/Entity/Chapter.php`.
- `name`: bắt buộc, translatable, tối đa 255 ký tự.
- `field_lessons`: `entity_reference` tới `lms_lesson`, cardinality unlimited — không bắt buộc (chapter có thể tạo trống, chưa gán lesson).
- `weight`: integer, mặc định 0, dùng để sắp xếp thứ tự chapter trong course.
- Không có `addConstraint()` tuỳ biến; không có validate về số lượng lesson tối thiểu/tối đa.

## Data invariants
- `field_lessons` là danh sách tham chiếu one-way (chapter → lesson); không có ràng buộc "một lesson chỉ thuộc đúng 1 chapter" được enforce ở tầng entity — nếu cùng một `lms_lesson` bị gán vào nhiều chapter, hệ thống không chặn (gap as-built, phụ thuộc kỷ luật biên tập).
- `status` mặc định TRUE (published) — khác với `lms_course.status` (mặc định FALSE, draft-first); chapter con không tự động theo trạng thái draft của course cha.

## Calculations & derived values
- Không tìm thấy service/cron nào tự động tính tổng thời lượng (`field_duration`) từ các lesson con để rollup lên chapter — chapter hiện không có field duration riêng, và không có logic tổng hợp nào trong `web/modules/custom` (đã grep `recalculateDuration|sumDuration|updateDuration`, không có kết quả liên quan chapter).
- `weight` là giá trị nhập tay qua drag-drop trong course-authoring UI, không phải giá trị tính toán.

## Editorial/data management
Instructor chỉnh sửa chapter bên trong course-authoring UI; thứ tự qua `weight`. Toàn bộ nội dung nằm trong field, không có text hardcode. Cache bị invalidate khi save; cache tag của course cha được bubble lên. Xem [C16 sheet 01](../C16-item-solutions/01-entities.md).
