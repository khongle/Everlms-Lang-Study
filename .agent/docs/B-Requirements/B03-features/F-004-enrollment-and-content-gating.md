# F-004 - Enrollment & content gating

**Status:** built
**Primary module:** [lms_progress](../../C-Design/C05-modules/lms_progress.md)
**Epic:** [E03 Learning & progress](../../D-Development/D02-epics/E03-learning-progress.md)
**Related workflows:** [WF-001 Enrollment & learning](../B04-business-workflows/WF-001-enrollment-and-learning.md)
**Acceptance:** [B05 F-004](../B05-acceptance-criteria/F-004-enrollment-and-content-gating.md)
**Source:** `web/modules/custom/lms_progress/`, `lms_course/`

## Requirement
Một user trở thành enrolled trong course (qua free enrollment, một paid order F-010, hoặc một membership F-012). Enrollment tạo ra một record `lms_user_course`. Nội dung lesson/course bị **gate**: chỉ user đã enroll (hoặc được entitled qua membership) mới mở được nội dung lesson; user chưa enroll chỉ thấy trang catalog/landing và một call-to-action.

## Business value
Gating là thứ giúp catalog có thể thu tiền được (monetisable) và gắn payment/membership với quyền truy cập; đây là điểm kiểm soát giữa marketing và delivery.

## Scope notes
- `lms_user_course` là join entity cho enrollment/progress (user ↔ course).
- Nguồn entitlement: free enroll trực tiếp, paid order, membership plan đang active.
- Kiểm tra access nằm trong route access / controller; phải nhất quán trên cả web và mobile API (F-019).

## Cross references
- Design: [C04 lms_user_course](../../C-Design/C04-entities/lms_user_course.md), [C12 WF-001](../../C-Design/C12-workflows/WF-001-enrollment-and-learning.md)
- Related: F-010 Orders, F-012 Memberships, F-005 Progress
