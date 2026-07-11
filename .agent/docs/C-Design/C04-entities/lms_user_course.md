# C04 Entity - lms_user_course

**Display name:** User Course (enrollment + course progress)
**Owning module(s):** [lms_progress](../C05-modules/lms_progress.md)
**Mode:** as-built (Content Entity)

## Purpose
Bản ghi enrollment + progress tổng hợp kết nối một user với một course. Đây là control point cho content gating và là nguồn trigger cho điều kiện nhận certificate.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | enrolled user |
| field_course | ref → lms_course |
| field_progress | percent complete (rollup) |
| field_enrolled_at | enrollment timestamp |
| field_completed_at | completion timestamp |
| field_status | enrolled / in-progress / completed |

## Relationships
- N→1 `user`, N→1 `lms_course`. Tổng hợp progress từ `lms_user_lesson`.

## Validation rules
Nguồn: `web/modules/custom/lms_progress/src/Entity/UserCourse.php`.
- `field_course`: bắt buộc, entity_reference tới `lms_course`.
- `field_progress`: decimal(5,2), mặc định 0.
- `field_status`: `list_string`, giá trị hợp lệ `enrolled|in_progress|completed`, mặc định `enrolled`.
- Không có ràng buộc unique (uid, field_course) khai báo ở entity level — có thể tồn tại nhiều bản ghi enrollment cho cùng 1 user+course nếu code tạo entity không tự kiểm tra trước (gap tiềm ẩn, chưa xác nhận guard ở nơi tạo).

## Data invariants
- `field_progress` phải nằm trong khoảng 0-100 theo nghiệp vụ, nhưng field-definition **không giới hạn range** (gap as-built).
- `field_status = completed` phải đi kèm `field_completed_at` có giá trị — quan hệ này **được đảm bảo bởi code** (xem Calculations), không phải bởi ràng buộc entity.

## Calculations & derived values
Xác nhận trong `web/modules/custom/lms_progress/src/Controller/ProgressApiController.php` (được gọi khi learner mark-complete một lesson):
- `$progress_percentage = $total_lessons > 0 ? floor(($completed_count / $total_lessons) * 100) : 0;` trong đó `$completed_count` = số `lms_user_lesson` có `field_status = completed` của user trong course, `$total_lessons` = tổng số lesson thuộc course (qua `field_chapters` → `field_lessons`).
- `field_progress` được set = `$progress_percentage` mỗi khi có lesson được đánh dấu hoàn thành.
- Khi `$progress_percentage >= 100` và `field_status` hiện tại khác `completed`: `field_status` chuyển thành `completed`, `field_completed_at` = thời điểm hiện tại (`$now`).
- Khi `$progress_percentage > 0` và `field_status` hiện tại là `enrolled`: `field_status` chuyển thành `in_progress`.
- Đây là công thức rollup chính xác cho invariant "Tổng hợp progress từ `lms_user_lesson`" đã ghi ở Relationships.

## Editorial/data management
Do hệ thống quản lý (được tạo khi enroll, cập nhật khi lesson hoàn thành); không do editor viết. Được đọc trên dashboard + analytics + mobile API. ~81 enrollment thực tế. Xem [F-004](../../B-Requirements/B03-features/F-004-enrollment-and-content-gating.md), [F-005](../../B-Requirements/B03-features/F-005-progress-tracking-and-resume.md).
