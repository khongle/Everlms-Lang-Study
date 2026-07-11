# C04 Entity - lms_user_lesson

**Display name:** User Lesson (per-lesson progress)
**Owning module(s):** [lms_progress](../C05-modules/lms_progress.md)
**Mode:** as-built (Content Entity)

## Purpose
Progress chi tiết theo từng lesson cho một learner (completion + vị trí resume video), được tổng hợp vào `lms_user_course`.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | learner |
| field_lesson | ref → lms_lesson |
| field_status | not-started / in-progress / completed |
| field_video_position | resume position (seconds) |
| field_completed_at | completion timestamp |
| created | timestamp |

## Relationships
- N→1 `user`, N→1 `lms_lesson`; được cuộn lên (roll up) `lms_user_course`.

## Validation rules
Nguồn: `web/modules/custom/lms_progress/src/Entity/UserLesson.php`.
- `field_lesson`: bắt buộc, entity_reference tới `lms_lesson`.
- `field_status`: `list_string`, giá trị hợp lệ `not_started|in_progress|completed`, mặc định `not_started`.
- `field_video_position`: integer, mặc định 0 (giây); không có ràng buộc non-negative trong code.
- Không có ràng buộc unique (uid, field_lesson) khai báo ở entity level (gap tiềm ẩn tương tự `lms_user_course`).

## Data invariants
- `field_status = completed` là điều kiện đầu vào trực tiếp cho phép tính `field_progress` ở `lms_user_course` (đếm `completed_count`) — xem `lms_user_course.md` Calculations. Do đó tính đúng đắn của `field_status` ở đây quyết định trực tiếp độ chính xác của rollup course-level.
- `field_video_position` chỉ có ý nghĩa với lesson `field_type = video`; với các loại khác giá trị này không được cập nhật nhưng vẫn tồn tại mặc định 0 — không có ràng buộc ẩn field theo `lms_lesson.field_type`.

## Calculations & derived values
Xác nhận trong `web/modules/custom/lms_progress/src/Controller/ProgressApiController.php`:
- Khi learner mark-complete một lesson, `field_completed_at` được set = thời điểm hiện tại (`$now`) đồng thời `field_status` chuyển sang `completed`.
- Không có công thức nào tính `field_video_position` — giá trị này được client (video player) gửi trực tiếp qua API theo vị trí phát thực tế, ghi thẳng vào field (không qua tính toán server-side).

## Editorial/data management
Do player quản lý (mark-complete / video progress). Không do editor viết. Xem [F-005](../../B-Requirements/B03-features/F-005-progress-tracking-and-resume.md).
