# Task: Course Preview Gating

**ID:** TASK-229
**Unit:** U-040 (course)
**Group:** G-CONTENT (extension)
**Priority:** P1
**Estimate:** 8h
**Assigned:** unassarding
**Status:** BACKLOG

## Description
Allow instructors to designate a "free preview" portion of their course that anonymous or non-enrolled users can access before purchasing. Configurable per course as: first chapter, first N lessons (1–5), or a time-based teaser (up to 5 minutes of video). After the preview content, a sign-up/enrollment wall is shown. This drives conversion by letting learners sample the course.

## Context
- G-CONTENT entities done; `lms_course` and `lms_lesson` exist.
- Existing access control in `lms_lesson` enforces enrollment; preview gating is an exception carve-out.
- TASK-115–122 edge cases cover existing access scenarios.

## Acceptance Criteria
- [ ] Course edit form has a "Preview Settings" section with three radio options: (a) First chapter, (b) First N lessons (N = 1–5, configurable), (c) 5-minute video teaser (first 5 min of first video lesson)
- [ ] `field_preview_mode` (select) and `field_preview_value` (integer, nullable) added to `lms_course` entity
- [ ] Anonymous and non-enrolled users can access preview-designated lessons/chapters without login
- [ ] After preview content, a "Sign-up wall" block renders: enrollment CTA, pricing, login link
- [ ] Preview lesson cards in the course outline are marked with a "Free Preview" badge
- [ ] Non-preview lessons remain locked (403) for non-enrolled users regardless of this setting
- [ ] Instructors can set `preview_mode = none` to disable all free previews
- [ ] Preview teaser (option c): video player stops at 5-minute mark for non-enrolled users; shows enrollment CTA overlay

## Technical Notes
- Module: `lms_course` (field definitions), `lms_lesson` (access handler update)
- `CoursePreviewService::isPreviewLesson(cid, lid, uid)` — resolves whether lesson qualifies as preview content for the given user
- Access handler: `LessonAccessControlHandler::checkAccess()` calls `CoursePreviewService` before checking enrollment; if preview → grant
- Sign-up wall block: `LmsSignupWallBlock` (plugin, `@Block`) — renders after last preview lesson's content; contains enrollment form snippet
- Video teaser: JS in `lms_lesson/js/video-preview-gate.js` — receives `drupalSettings.lmsVideoGate.cutoffSeconds`; pauses player and shows overlay at cutoff
- "Free Preview" badge: CSS class `lms-lesson-card--preview` on lesson cards; styled in `lms_theme/scss/components/_lesson-card.scss`

## Dependencies
- TASK-115 (lesson access edge cases — ensure no conflict with existing access rules)
- TASK-059 (auth pages — sign-up wall links to registration)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: CoursePreviewService; functional: anonymous access to preview lessons only)
- [ ] No new debt introduced
