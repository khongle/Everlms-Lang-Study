# F-002 - Course authoring & structure

**Status:** built
**Primary module:** [lms_course](../../C-Design/C05-modules/lms_course.md)
**Epic:** [E02 Course catalog](../../D-Development/D02-epics/E02-course-catalog.md)
**Related workflows:** [WF-006 Course authoring & publishing](../B04-business-workflows/WF-006-course-authoring-and-publishing.md)
**Acceptance:** [B05 F-002](../B05-acceptance-criteria/F-002-course-authoring-and-structure.md)
**Source:** `web/modules/custom/lms_course/`, `lms_chapter/`, `lms_lesson/`

## Requirement
Instructors/editors can create and organise courses as a three-level hierarchy: **Course → Chapter → Lesson** (`lms_course.field_chapters` → `lms_chapter.field_lessons` → `lms_lesson`). They set metadata (category, level, language, tags, instructor, price), order chapters/lessons, and publish/unpublish.

## Business value
Structured authoring is the core production capability; a clean hierarchy drives the learner experience, progress tracking, and certificate eligibility.

## Scope notes
- Entity-reference fields wire the hierarchy (`field_chapters`, `field_lessons`).
- Instructor assigned via `lms_course.field_instructor`.
- Editorial-usability: all course/chapter/lesson copy lives in entity fields editable via the management UI — no hardcoded text.
- AI-assisted drafting is F-018/WF-010; media specifics are F-003.

## Cross references
- Design: [C04 lms_course](../../C-Design/C04-entities/lms_course.md), [C04 lms_chapter](../../C-Design/C04-entities/lms_chapter.md), [C04 lms_lesson](../../C-Design/C04-entities/lms_lesson.md), [C03 entity-module map](../../C-Design/C03-entity-module-map/_index.md)
