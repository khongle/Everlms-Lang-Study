# F-005 - Progress tracking & resume

**Status:** built
**Primary module:** [lms_progress](../../C-Design/C05-modules/lms_progress.md)
**Epic:** [E03 Learning & progress](../../D-Development/D02-epics/E03-learning-progress.md)
**Related workflows:** [WF-001 Enrollment & learning](../B04-business-workflows/WF-001-enrollment-and-learning.md)
**Acceptance:** [B05 F-005](../B05-acceptance-criteria/F-005-progress-tracking-and-resume.md)
**Source:** `web/modules/custom/lms_progress/`

## Requirement
Hệ thống ghi nhận completion theo từng lesson (`lms_user_lesson`) và tổng hợp (roll up) thành progress theo từng course (`lms_user_course`): phần trăm hoàn thành, lesson truy cập gần nhất, và timestamp hoàn thành. Learner có thể resume tại chỗ đã dừng; việc hoàn thành mở khóa điều kiện nhận certificate (F-007).

## Business value
Progress là xương sống của trải nghiệm học tập và là điều kiện tiên quyết cho certification và analytics; tính năng "resume" giúp giảm drop-off.

## Scope notes
- Hai progress entity: `lms_user_lesson` (chi tiết) và `lms_user_course` (tổng hợp).
- Hoàn thành toàn bộ lesson bắt buộc + pass quiz sẽ dẫn tới course completion → phát event mà F-007 tiêu thụ (consume).
- Được hiển thị trên learner dashboard và mobile API (F-019).

## Cross references
- Design: [C04 lms_user_lesson](../../C-Design/C04-entities/lms_user_lesson.md), [C04 lms_user_course](../../C-Design/C04-entities/lms_user_course.md)
- Drives: F-007 Certificates, F-020 Analytics
