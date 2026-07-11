---
id: WF-010-002
workflow: WF-010
title: AI builder saves an invalid course hierarchy and can leave partial data
severity: high
category: data-integrity
status: done
roles: instructor, content_editor
source: code review
found: 2026-06-30
---

# WF-010-002 - Invalid hierarchy and partial save risk

## Problem
The AI builder attempts to attach lessons directly to `lms_course` via
`field_lessons`, but the real EverLMS model is:

`lms_course.field_chapters -> lms_chapter.field_lessons -> lms_lesson`

The service also saves the course and lessons before the final attachment
operation, so an exception can leave a draft course and orphaned lesson entities.

## Evidence
- `Course` defines `field_chapters`, not `field_lessons`.
- `Chapter` defines `field_lessons`.
- `CourseBuilderService::generateCourseStructure()` calls
  `$course->get('field_lessons')->appendItem(...)` after saving entities.

## Impact
WF-010 can fail after partial writes, producing corrupted authoring data and a
bad editor experience.

## Expected
The AI builder must create a valid draft hierarchy:

- draft course owned by the current editor
- chapters attached to the course
- lessons attached to chapters
- optional quiz/check lesson attached consistently
- cleanup on creation failure

## Fix Plan
- Create chapters and lessons according to the current entity model.
- Set owner/instructor metadata on generated course drafts.
- Track created entities and delete them on persistence failure.
- Queue lesson-body generation only after a valid draft hierarchy is saved.

## Resolution
- AI draft save now creates `lms_course.field_chapters` and
  `lms_chapter.field_lessons` instead of a non-existent course `field_lessons`.
- Generated course drafts are owned by the current editor and include
  instructor metadata.
- Optional final quiz output creates `lms_quiz`, `lms_question`, answer
  entities, and a quiz lesson attached to the final chapter.
- Created entities are tracked and cleaned up if persistence fails.
- Lesson generation queue items are created after a valid hierarchy exists.

## Verification
- Final course `138` has 3 chapters, text lessons, quiz lesson `1810`, and quiz
  `87`.
- Screenshot `011-system-db-hierarchy-verification.png` records the hierarchy.
