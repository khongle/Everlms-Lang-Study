# C04 Entity - lms_course

**Display name:** Course
**Owning module(s):** [lms_course](../C05-modules/lms_course.md)
**Mode:** as-built (Content Entity, translatable)

## Purpose
Đơn vị catalog và gốc của learning hierarchy (Course → Chapter → Lesson). Chứa marketing metadata, pricing, phân loại taxonomy, instructor, và danh sách chapter.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | owner/author (instructor) |
| langcode, default_langcode | translation |
| name | course title |
| field_description, field_short_description | full + short body (formatted) |
| field_thumbnail, field_banner | catalog image + hero (image fields) |
| field_category, field_level, field_language | taxonomy refs (`course_category`/`course_level`/`course_language`) |
| field_price, field_price_type | price + free/paid type |
| field_duration | total duration |
| field_bestseller, field_featured | catalog flags |
| field_what_learn, field_prerequisites, field_who_for | formatted marketing sections |
| field_rating | cached aggregate rating |
| field_chapters | **multi-ref → lms_chapter** (curriculum) |
| field_instructor | **multi-ref → user** |
| field_tags | **multi-ref → taxonomy (`course_tags`)** |
| field_certificate_template | ref → `lms_certificate_template` (certificate issued on completion) |
| status, created, changed | publish + timestamps |

## Relationships
- 1→N `lms_chapter` (qua `field_chapters`); chapter 1→N `lms_lesson`.
- N→1 instructor (`user`), N→N tags/category taxonomy.
- Được tham chiếu bởi: `lms_user_course` (enrollment), `lms_rating`, `lms_wishlist`, `lms_order_item`, `lms_course_bundle.field_courses`, `lms_forum_topic.field_course`.

## Validation rules
Nguồn: `web/modules/custom/lms_course/src/Entity/Course.php`.
- `name`: bắt buộc, translatable, tối đa 255 ký tự.
- `field_price`: decimal(10,2), mặc định 0 (0 = free course, theo `setDescription()`).
- `field_price_type`: `list_string`, giá trị hợp lệ `free|paid|membership`, mặc định `free`.
- `field_thumbnail`/`field_banner`: image field với `alt_field` bật (`setSetting('alt_field', TRUE)`), không giới hạn `file_extensions` trong code.
- `field_category`/`field_level`/`field_language`: entity_reference tới taxonomy, giới hạn `target_bundles` tương ứng (`course_category`, `course_level`, `course_language`).
- Không field nào khác ngoài `name` bị `setRequired(TRUE)`.

## Data invariants
- `status` mặc định FALSE — draft-first authoring, ghi rõ trong code comment "Draft-first authoring (C12 WF-006): a new course starts unpublished". Course mới luôn ở trạng thái nháp cho tới khi được publish thủ công.
- `field_rating` mặc định 5.0 (thang 0-5.0, precision 3 scale 1) — course mới chưa có review nào vẫn hiển thị rating tối đa; đây là default field-level, không phải giá trị tính toán từ dữ liệu thật.
- `field_price_type = paid` kỳ vọng `field_price > 0`, và `= free` kỳ vọng `field_price = 0`, nhưng cặp field này **không được ràng buộc chéo (cross-field) trong code** — có thể lưu `field_price_type = free` với `field_price > 0` mà không bị chặn (gap as-built).

## Calculations & derived values
- `field_duration`: field-level description ghi "Total duration in minutes (auto-calculated)", nhưng grep toàn bộ `web/modules/custom` (bao gồm `recalculateDuration|sumDuration|updateDuration` và mọi nơi đọc/ghi `field_duration`) **không tìm thấy service/cron nào thực sự tổng hợp thời lượng từ chapter/lesson con** — đây là gap as-built: nhãn "auto-calculated" trong code là mô tả dự định, giá trị hiện tại do editor nhập tay.
- `field_rating`: tương tự, không có service nào tính trung bình từ các `lms_rating` entity liên quan để cập nhật `field_rating` của course — rating hiển thị trên course là giá trị tĩnh do editor set, KHÔNG tự động phản ánh review thật (gap as-built, xem thêm `lms_rating.md`).

## Editorial/data management
Instructor/content_editor chỉnh sửa qua Course entity form (management UI). Toàn bộ nội dung hiển thị cho learner nằm trong field (`name`, `field_description`, các mục marketing) — không có text hardcode. Validation: title/price bắt buộc; hỗ trợ revision/translation (translatable). Cache: bị invalidate khi save qua entity cache tag (`lms_course:<id>`). Xem [C02 editorial matrix](../C02-user-roles/_index.md), [C16 sheet 01](../C16-item-solutions/01-entities.md).
