# Task: Course Archive (Soft-Delete for Retired Courses)

**ID:** TASK-121
**Unit:** U-015 (lms_course)
**Group:** G-CONTENT (extension)
**Priority:** P2
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Introduce an "Archived" state for courses that have been retired. Archived courses are hidden from the public catalog and search but all enrollment records, learner progress, and issued certificates remain fully accessible to learners who previously enrolled. Admins can restore an archived course to published state.

## Context

- Module: `lms_course`
- Differs from unpublishing: unpublished courses may be works-in-progress; archived courses are intentionally retired
- Archived courses must still appear in learner "My Courses" lists with an "Archived" badge

## Acceptance Criteria

- [ ] `field_course_status` extended (or new `field_archived` boolean) to support `archived` state
- [ ] "Archive" action available on course admin list (bulk action + per-row action)
- [ ] Archived courses excluded from public catalog (`/courses`), search index, and sitemap
- [ ] Archived courses visible in admin list with "Archived" filter tab
- [ ] Learners with existing enrollments still see the course in "My Courses" with "Archived" badge
- [ ] Learner progress, quiz results, and certificates remain accessible for archived courses
- [ ] "Restore" action returns course to previous published state
- [ ] Admin notification when archiving: "X active learners enrolled — they will retain access to content"

## Technical Notes

- Add `archived` value to course status field; update entity access handler to exclude archived from public queries
- Override `CourseListBuilder` to add "Archived" filter tab
- Update `lms_course` entity query in catalog controller to add `field_archived != 1` condition
- "My Courses" query must NOT filter on archived status
- Search API index: add `field_archived` as exclusion condition in index config
- Restore: simply unset `field_archived`; no data migration needed

## Dependencies

- None

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
