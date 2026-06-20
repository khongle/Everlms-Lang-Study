# F-003 - Lesson delivery & media

**Status:** built
**Primary module:** [lms_lesson](../../C-Design/C05-modules/lms_lesson.md)
**Epic:** [E02 Course catalog](../../D-Development/D02-epics/E02-course-catalog.md)
**Related workflows:** [WF-001 Enrollment & learning](../B04-business-workflows/WF-001-enrollment-and-learning.md)
**Acceptance:** [B05 F-003](../B05-acceptance-criteria/F-003-lesson-delivery-and-media.md)
**Source:** `web/modules/custom/lms_lesson/`

## Requirement
Enrolled learners can view lesson content — rich text body, video, and downloadable attachments (`lms_lesson.field_attachments`) — within the course player, mark a lesson complete, and move to the next lesson. Lessons may carry a quiz (F-006).

## Business value
Lesson delivery is where learning actually happens; reliable media playback and a clear "next" flow drive completion rates and certificate eligibility.

## Scope notes
- `lms_lesson` has 5 module dependencies (most-connected content entity) — it ties together course, chapter, progress, and quiz.
- Media is referenced via file/attachment fields; large files referenced, not inlined.
- Access gating (must be enrolled / lesson unlocked) is enforced via F-004.

## Cross references
- Design: [C04 lms_lesson](../../C-Design/C04-entities/lms_lesson.md), [C05 lms_lesson](../../C-Design/C05-modules/lms_lesson.md)
- Player page in [C11 pages](../../C-Design/C11-pages-components/pages/_index.md)
