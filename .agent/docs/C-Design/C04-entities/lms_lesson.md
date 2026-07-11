# C04 Entity - lms_lesson

**Display name:** Lesson
**Owning module(s):** [lms_lesson](../C05-modules/lms_lesson.md)
**Mode:** as-built (Content Entity, translatable; most-connected entity — 5 module deps)

## Purpose
Đơn vị học tập ở cấp lá (leaf): cung cấp nội dung (rich text, video, SCORM, H5P, attachment) và có thể liên kết với một quiz (tùy chọn). Hỗ trợ content drip và free preview.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid, langcode, default_langcode | keys + translation |
| name | lesson title |
| field_content | formatted lesson body |
| field_type | lesson type (video/text/scorm/h5p/zoom…) |
| field_video_url, field_video_file | external video URL / uploaded video file |
| field_zoom_meeting_id, field_zoom_schedule | live-session link (lms_zoom) |
| field_scorm_package | SCORM upload (file) |
| field_h5p_embed, field_h5p_upload | H5P interactive content (configurable `h5p` field) |
| field_pptx_attachment | slides attachment |
| field_attachments | **multi → downloadable files** |
| field_quiz | ref → lms_quiz (attached assessment) |
| field_duration | lesson length |
| field_free_preview | preview without enrollment |
| field_drip_days | days after enrollment before unlock |
| field_comments | comments toggle |
| weight, status, created, changed | order, publish, timestamps |

## Relationships
- Parent: được tham chiếu bởi `lms_chapter.field_lessons`.
- Liên kết: `field_quiz` → `lms_quiz`; được theo dõi bởi `lms_user_lesson`; `lms_note.field_lesson`.

## Validation rules
Nguồn: `web/modules/custom/lms_lesson/src/Entity/Lesson.php`.
- `name`: bắt buộc, translatable, tối đa 255 ký tự.
- `field_type`: bắt buộc, `list_string`, giá trị hợp lệ `video|text|quiz|h5p|scorm|pdf|pptx|zoom|mixed`, mặc định `text`.
- `field_video_file`: file field, giới hạn `file_extensions` = `mp4 webm ogg`.
- `field_scorm_package`: file field, giới hạn `file_extensions` = `zip`.
- `field_pptx_attachment`: file field, giới hạn `file_extensions` = `ppt pptx`.
- `field_attachments`: file field đa giá trị (unlimited), giới hạn `file_extensions` = `pdf doc docx ppt pptx xls xlsx zip`.
- `field_video_url`: string, tối đa 512 ký tự, mô tả "YouTube or Vimeo URL" nhưng không có validate định dạng URL/domain nào trong code (gap as-built).
- `field_zoom_meeting_id`: string, tối đa 64 ký tự.

## Data invariants
- `field_type` quyết định field nào thực sự có ý nghĩa (ví dụ `field_type = zoom` thì `field_zoom_meeting_id`/`field_zoom_schedule` mới hữu dụng; `field_type = scorm` thì `field_scorm_package` mới hữu dụng), nhưng **không có validate điều kiện chéo (conditional required) theo `field_type`** ở `baseFieldDefinitions()` — có thể lưu lesson `field_type = video` mà không có `field_video_url`/`field_video_file` nào (gap as-built).
- `field_drip_days = 0` nghĩa là mở khoá ngay sau khi enroll (theo mô tả field); `field_free_preview = TRUE` cho phép xem không cần enrollment — hai field này có thể xung đột logic (preview nhưng vẫn có drip) nhưng không được ràng buộc chéo.
- `status` mặc định TRUE (published), khác với `lms_course.status` (mặc định FALSE draft-first).

## Calculations & derived values
- `field_duration` (phút): tương tự `lms_course.field_duration`, đây là giá trị nhập tay ở cấp lesson; không tìm thấy service nào tự tính hoặc suy ra từ file video/SCORM đã upload (ví dụ: không đọc metadata độ dài file mp4).
- `lms_zoom` module có hook `lms_zoom_entity_presave()` (`web/modules/custom/lms_zoom/lms_zoom.module`): khi lesson có `field_type = zoom`, presave gọi `ZoomMeetingManager::syncMeeting($entity)` để đồng bộ meeting Zoom — đây là side-effect ghi ra hệ thống ngoài (Zoom API), không phải field tính toán lưu trên entity.
- Không có logic rollup nào từ lesson lên chapter/course (xem thêm gap ở `lms_course.md`, `lms_chapter.md`).

## Editorial/data management
Instructor viết lesson qua Lesson entity form; media được tham chiếu dưới dạng file (không inline). Free-preview + drip kiểm soát thời điểm truy cập (kết hợp với gating của F-004). Toàn bộ nội dung nằm trong field. Cache bị invalidate khi save; quyền truy cập thay đổi theo enrollment + drip. Xem [C16 sheet 01](../C16-item-solutions/01-entities.md).
