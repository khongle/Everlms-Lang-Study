# F-002 Acceptance Criteria - Course authoring & structure

## Core criteria
1. **Given** an instructor role, **when** they create a course and add chapters then lessons, **then** the Course→Chapter→Lesson hierarchy persists via `field_chapters`/`field_lessons`.
2. **Given** a course with chapters, **when** the instructor reorders chapters/lessons, **then** the new order is saved and reflected in the player.
3. **Given** a course, **when** the instructor sets category/level/language/tags/instructor/price, **then** all values save to entity fields (no hardcoded text).
4. **Given** a draft course, **when** the instructor publishes it, **then** it becomes visible in the catalog (F-001).

## Evidence expected
- Modules: [lms_course](../../C-Design/C05-modules/lms_course.md), [lms_chapter](../../C-Design/C05-modules/lms_chapter.md), [lms_lesson](../../C-Design/C05-modules/lms_lesson.md).
- Test: entity-create kernel test for the 3-level reference chain; editor-usability smoke (E03).
