# Task: Course Duplicate (Clone) Action

**ID:** TASK-122
**Unit:** U-015 (lms_course)
**Group:** G-CONTENT (extension)
**Priority:** P3
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Allow admins and instructors to clone an existing course including all chapters, lessons, and quiz structure. The cloned course is created as a draft with a new UUID and a title prefix "Copy of …". No enrollments, progress records, or certificates are copied. Useful for cohort iteration and course library management.

## Context

- Module: `lms_course`
- Clone scope: Course entity fields, Chapter entities, Lesson entities, Quiz + Question entities
- Cloned course gets fresh UUIDs for all entities; references are re-wired to cloned children

## Acceptance Criteria

- [ ] "Duplicate" action available on course admin list (per-row) and course edit page
- [ ] Cloned course title prefixed with "Copy of " and created in `draft` state
- [ ] All chapters cloned in same order with new UUIDs
- [ ] All lessons cloned per chapter in same order; text content duplicated
- [ ] Quiz structure cloned: all questions and answer options duplicated
- [ ] Video/media field references copied as-is (shared reference, not file copy)
- [ ] No enrollments, progress, or certificates copied
- [ ] Redirect to cloned course edit page after duplication
- [ ] Success message: "Course duplicated. You are now editing the copy."
- [ ] Permission: `duplicate lms course` (admin + instructor)

## Technical Notes

- New service method: `CourseDuplicatorService::duplicate(Course $source): Course`
- Traverse entity graph: course → chapters → lessons → quizzes → questions
- Use `$entity->createDuplicate()` (Drupal core) per entity to generate new UUID and clear ID
- Re-link parent references after each level is saved (chapter → course_id, lesson → chapter_id)
- Route: add `lms_course.duplicate` action route in `lms_course.routing.yml`
- Action plugin: `web/modules/custom/lms_course/src/Plugin/Action/DuplicateCourseAction.php`

## Dependencies

- TASK-117 (draft state for cloned courses)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
