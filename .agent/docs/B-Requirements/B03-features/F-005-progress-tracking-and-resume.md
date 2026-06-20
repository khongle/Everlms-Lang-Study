# F-005 - Progress tracking & resume

**Status:** built
**Primary module:** [lms_progress](../../C-Design/C05-modules/lms_progress.md)
**Epic:** [E03 Learning & progress](../../D-Development/D02-epics/E03-learning-progress.md)
**Related workflows:** [WF-001 Enrollment & learning](../B04-business-workflows/WF-001-enrollment-and-learning.md)
**Acceptance:** [B05 F-005](../B05-acceptance-criteria/F-005-progress-tracking-and-resume.md)
**Source:** `web/modules/custom/lms_progress/`

## Requirement
The system records per-lesson completion (`lms_user_lesson`) and rolls it up into per-course progress (`lms_user_course`): percent complete, last-accessed lesson, and completion timestamp. Learners can resume where they left off; completion unlocks certificate eligibility (F-007).

## Business value
Progress is the spine of the learning experience and the prerequisite for certification and analytics; "resume" reduces drop-off.

## Scope notes
- Two progress entities: `lms_user_lesson` (granular) and `lms_user_course` (rollup).
- Completion of all required lessons + passing quizzes drives course completion → emits the event consumed by F-007.
- Surfaced on the learner dashboard and mobile API (F-019).

## Cross references
- Design: [C04 lms_user_lesson](../../C-Design/C04-entities/lms_user_lesson.md), [C04 lms_user_course](../../C-Design/C04-entities/lms_user_course.md)
- Drives: F-007 Certificates, F-020 Analytics
