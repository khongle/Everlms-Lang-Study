# F-008 - Badges & gamification

**Status:** stub (entities + admin defined; **award logic absent** — R-003 / BLK-003)
**Primary module:** [lms_badge](../../C-Design/C05-modules/lms_badge.md)
**Epic:** [E05 Certification](../../D-Development/D02-epics/E05-certification.md)
**Related workflows:** [WF-002 Quiz attempt & grading](../B04-business-workflows/WF-002-quiz-attempt-and-grading.md)
**Acceptance:** [B05 F-008](../B05-acceptance-criteria/F-008-badges-and-gamification.md)
**Source:** `web/modules/custom/lms_badge/`

## Requirement
Define achievement badges (`lms_badge`, typed via `badge_type` vocabulary) and grant them to users (`lms_user_badge`) when they reach milestones (course completion, quiz mastery, streaks). Learners see earned badges on their profile.

## Business value
Gamification lifts engagement and completion; badges complement certificates with lighter, more frequent recognition.

## Scope notes
- ⚠️ **As-built gap:** `lms_badge`/`lms_user_badge` entities and admin exist, but **no automatic award trigger is implemented** — badges are never granted by the system. Tracked as risk R-003 / blocker BLK-003.
- Requirement here documents the intended (`target`) behavior; E06 records the gap honestly.

## Cross references
- Design: [C04 lms_badge](../../C-Design/C04-entities/lms_badge.md), [C04 lms_user_badge](../../C-Design/C04-entities/lms_user_badge.md)
- Status: [E06 implementation status](../../E-Quality/E06-implementation-status/_index.md), [G02 R-003](../../G-Governance/G02-risk-register/_index.md)
