# C12 Workflow - WF-001 Enrollment & learning

**Object:** course

## Business source
[B04 WF-001](../../B-Requirements/B04-business-workflows/WF-001-enrollment-and-learning.md)

## Modules
[lms_progress](../C05-modules/lms_progress.md), [lms_course](../C05-modules/lms_course.md), [lms_lesson](../C05-modules/lms_lesson.md)

## Related features (B03)
[F-004](../../B-Requirements/B03-features/F-004-enrollment-and-content-gating.md), [F-005](../../B-Requirements/B03-features/F-005-progress-tracking-and-resume.md), [F-003](../../B-Requirements/B03-features/F-003-lesson-delivery-and-media.md)

## State model
`lms_user_course.field_status`:
| State | Meaning |
| ----- | ------- |
| submitted (enrolled) | enrollment created (free/paid/membership) |
| in_review (in-progress) | lessons being completed; progress accruing |
| approved_or_completed | all required lessons + final quiz passed |
| archived | course archived/unenrolled (rare) |

Bất biến của enrollment: một learner chỉ nên có một `lms_user_course` record đang active cho mỗi course. Việc rollup progress phải cập nhật nhất quán enrollment của learner/course, và các trang hướng tới learner không được hiển thị trùng course card nếu tồn tại các dòng dữ liệu duplicate cũ (legacy).

## Transitions
| From | To | Trigger |
| ---- | -- | ------- |
| submitted | in_review | learner mở lesson đầu tiên |
| in_review | approved_or_completed | hoàn tất mọi lesson bắt buộc + pass final quiz |
| approved_or_completed | archived | manager unenroll (hiếm) |

## Permission model
- Initiator: learner (tự enroll free / qua order / qua membership).
- Responsible: learner (progress).
- Admin: manager có thể enroll/unenroll một user.

## As-built enrolment & access (round-2 verified)
- **Canonical `field_status` vocabulary:** `enrolled` → `in_progress` → `completed` (hiển thị cho learner). Giá trị legacy `active` và route/controller mồ côi `lms_course.enroll_free` đã được xóa ở round-2; enrolment free giờ ghi nhận là `enrolled`.
- **Enrolment paths:** CTA "Enroll Now" trên course-detail (với learner chưa enroll) trỏ tới `lms_order.checkout` (`/checkout/{course_id}`). `CheckoutController` rút gọn (short-circuit) course **free** thành một enrolment trực tiếp (`lms_user_course` với `field_status = enrolled`, **không tạo order row**) rồi redirect về trang detail kèm thông báo thành công. Course trả phí tiếp tục qua WF-004.
- **Membership unlock:** một membership đang active (`lms_membership.manager::hasActiveMembership`) làm `is_enrolled` = true cho mọi course mà **không** tạo dòng `lms_user_course`. Learner dạng này có quyền truy cập nhưng chưa có progress cho tới khi mở một lesson.
- **CTA semantics:** đã enrolled/unlocked **và có** lesson progress → hiện "Continue Learning"; đã enrolled/unlocked nhưng **chưa có** progress → hiện "Start Learning". `has_progress` = có tồn tại bất kỳ dòng `lms_user_lesson` nào cho các lesson của course. Dashboard, catalog card, và sidebar course-detail phải đồng nhất với nhau.
- **Progress rounding:** giá trị lưu ở `lms_user_course.field_progress` và mọi nơi hiển thị (dashboard, card, lesson player) đều dùng `floor((completed / total) * 100)` để cùng một enrolment không bao giờ hiện hai con số phần trăm khác nhau.

## Notifications
`lms_notification` khi enroll + khi completion (in_app ổn; email/push mới hoàn thiện một phần theo R-005). Các link nhạy cảm trỏ tới trang có bảo vệ theo role.

## Round-2 QA evidence
Screens: `.agent/docs/E-Quality/E00-workflows/round-2/_screens/001-enrollment-and-learning/`. Các issue phát hiện + đã fix: `WF-001-001..006` (issues/done). Session thực tế: `maria.santos` (uid 61, member), `julia.nowak` (uid 71, non-member, enrol free).
