# Content Entities

> Course → Chapter → Lesson hierarchy. Core learning content structure.

## Category (`course_category`)

| Field | Type | Mô tả |
|-------|------|-------|
| `name` | string | Tên danh mục |
| `description` | text_long | Mô tả chi tiết danh mục |
| `field_category_thumbnail` | image | Ảnh đại diện của danh mục (thay thế icon mặc định) |
| `field_is_featured` | boolean | Cờ đánh dấu danh mục nổi bật hiển thị ở trang chủ |
| `weight` | integer | Thứ tự sắp xếp |

### Business Rules
- Các danh mục hiển thị ngoài frontend cần có ít nhất 1 khoá học đang active (`status = 1`).

---

## Course (`lms_course`)

| Field | Type | Mô tả |
|-------|------|-------|
| `name` | string | Tên khoá học |
| `field_description` | text_long | Mô tả chi tiết |
| `field_short_description` | text | Mô tả ngắn (cho listing card) |
| `field_thumbnail` | image | Ảnh đại diện (card thumbnail) |
| `field_banner` | image | Ảnh banner (detail page) |
| `field_chapters` | entity_reference (multi) | Danh sách chapters |
| `field_category` | entity_reference (taxonomy) | Category |
| `field_tags` | entity_reference (taxonomy, multi) | Tags |
| `field_level` | entity_reference (taxonomy) | Beginner / Intermediate / Advanced |
| `field_language` | entity_reference (taxonomy) | Ngôn ngữ giảng dạy |
| `field_instructor` | entity_reference (user, multi) | Giảng viên |
| `field_price` | decimal | Giá (0 = free) |
| `field_price_type` | list_string | free / paid / membership |
| `field_duration` | integer | Tổng thời lượng (minutes) |
| `field_prerequisites` | entity_reference (self, multi) | Khoá học prerequisite |
| `field_bestseller` | boolean | Cờ bestseller |
| `field_featured` | boolean | Hiển thị featured |
| `field_quizzes` | entity_reference (multi) | Final quizzes |
| `field_learning_path` | entity_reference | Learning path chứa course |
| `field_schedule_publish` | timestamp | Ngày tự động publish (Content Scheduling) |
| `status` | boolean | Published / Draft |

### Business Rules
- Course phải có ít nhất 1 chapter để publish
- `field_price = 0` tự động set `field_price_type = free`
- `field_duration` được tính tự động từ tổng duration lessons

---

## Chapter (`lms_chapter`)

| Field | Type | Mô tả |
|-------|------|-------|
| `name` | string | Tên chương |
| `field_description` | text_long | Mô tả |
| `field_lessons` | entity_reference (multi) | Danh sách lessons |
| `field_prerequisites` | entity_reference (self, multi) | Chapter prerequisites |
| `weight` | integer | Thứ tự sắp xếp |
| `status` | boolean | Published / Draft |

### Business Rules
- Chapter phải thuộc ít nhất 1 course
- `weight` quyết định thứ tự hiển thị trong course curriculum

---

## Lesson (`lms_lesson`)

| Field | Type | Mô tả |
|-------|------|-------|
| `name` | string | Tên bài học |
| `field_content` | text_long | Nội dung rich text |
| `field_type` | list_string | video / text / quiz / h5p / scorm / zoom / mixed |
| `field_video_url` | string | YouTube / Vimeo URL |
| `field_video_file` | file | Self-hosted video upload |
| `field_video_embed` | text_long | Custom embed code |
| `field_video_subtitles` | file (multi) | Subtitle files (.srt, .vtt) |
| `field_h5p_content` | entity_reference | H5P content |
| `field_scorm_package` | file | SCORM .zip package |
| `field_zoom_meeting_id` | string | Zoom meeting ID |
| `field_zoom_schedule` | timestamp | Zoom meeting schedule |
| `field_quiz` | entity_reference | Quiz gắn lesson |
| `field_attachments` | file (multi) | Tài liệu đính kèm |
| `field_duration` | integer | Thời lượng (minutes) |
| `field_free_preview` | boolean | Cho phép xem trước không cần enroll |
| `field_prerequisites` | entity_reference (self, multi) | Lesson prerequisites |
| `field_unlock_date` | timestamp | Ngày mở khoá (Content Scheduling) |
| `field_drip_days` | integer | Số ngày sau enroll mới mở (Drip Content) |
| `weight` | integer | Thứ tự sắp xếp |
| `status` | boolean | Published / Draft |

### Business Rules
- `field_type` quyết định fields nào được hiển thị trong form
- Video lesson: phải có `field_video_url` HOẶC `field_video_file` HOẶC `field_video_embed`
- H5P lesson: phải có `field_h5p_content`
- SCORM lesson: phải có `field_scorm_package`
- Zoom lesson: phải có `field_zoom_meeting_id`
- `field_drip_days > 0` → lesson bị lock cho tới N ngày sau enrollment
