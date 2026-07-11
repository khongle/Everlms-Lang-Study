# F-008 - Badges & gamification

**Status:** stub (entities + admin defined; **award logic absent** — R-003 / BLK-003)
**Primary module:** [lms_badge](../../C-Design/C05-modules/lms_badge.md)
**Epic:** [E05 Certification](../../D-Development/D02-epics/E05-certification.md)
**Related workflows:** [WF-002 Quiz attempt & grading](../B04-business-workflows/WF-002-quiz-attempt-and-grading.md)
**Acceptance:** [B05 F-008](../B05-acceptance-criteria/F-008-badges-and-gamification.md)
**Source:** `web/modules/custom/lms_badge/`

## Requirement
Định nghĩa achievement badge (`lms_badge`, phân loại qua vocabulary `badge_type`) và cấp (grant) cho user (`lms_user_badge`) khi họ đạt milestone (course completion, quiz mastery, streak). Learner thấy các badge đã kiếm được trên profile của mình.

## Business value
Gamification nâng cao engagement và completion; badge bổ sung cho certificate bằng hình thức ghi nhận nhẹ nhàng và thường xuyên hơn.

## Scope notes
- ⚠️ **Khoảng trống as-built:** entity `lms_badge`/`lms_user_badge` và trang admin đã tồn tại, nhưng **chưa có automatic award trigger nào được implement** — hệ thống không bao giờ tự cấp badge. Được track như risk R-003 / blocker BLK-003.
- Requirement ở đây mô tả behavior dự kiến (`target`); E06 ghi nhận khoảng trống này một cách trung thực.

## Cross references
- Design: [C04 lms_badge](../../C-Design/C04-entities/lms_badge.md), [C04 lms_user_badge](../../C-Design/C04-entities/lms_user_badge.md)
- Status: [E06 implementation status](../../E-Quality/E06-implementation-status/_index.md), [G02 R-003](../../G-Governance/G02-risk-register/_index.md)
