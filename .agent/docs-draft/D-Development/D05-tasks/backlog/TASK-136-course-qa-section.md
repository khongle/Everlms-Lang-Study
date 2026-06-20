# Task: Course Q&A Section

**ID:** TASK-136
**Unit:** — (new module `lms_course_qa` or extend `lms_forum`)
**Group:** G-COMMUNICATION (extension)
**Priority:** P2
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build a per-course Q&A section separate from the general forum. Students can post questions scoped to a specific course; instructors and peers can reply. Best answers can be voted up; instructors can pin their own answer. Replying triggers a notification to the question author.

## Context
- `lms_forum` exists but is a site-wide forum; Q&A must be scoped per course entity.
- `lms_notification` dispatch service available (TASK-009).
- Vote-up mechanism is distinct from `lms_rating` (course ratings); a lightweight thumbs-up per answer is sufficient.
- Instructor-pinned answer should surface at the top regardless of vote count.

## Acceptance Criteria
- [ ] Q&A tab on each course page (`/courses/{slug}/qa`) visible to enrolled students + instructors + admins
- [ ] Student can post a question (title + body with basic rich text)
- [ ] Any enrolled user or instructor can post a reply to a question
- [ ] Vote-up button on each answer; a user may vote once per answer (toggle)
- [ ] Instructor can mark one reply as "Instructor Answer" (pinned, visually distinct badge)
- [ ] Pinned instructor answer always appears first; remaining answers sorted by vote count desc then created date
- [ ] Author of question receives in-app + email notification on new reply (respects TASK-140 preferences)
- [ ] Admin/instructor can delete or hide any question or answer
- [ ] Pagination: 20 questions per page, 10 answers per question
- [ ] Responsive layout; Bootstrap 5.3 dark theme

## Technical Notes
- New module: `web/modules/custom/lms_course_qa/`
- Entities: `CourseQuestion` (course ref, uid, title, body, created, status) and `CourseAnswer` (question ref, uid, body, created, status, is_pinned, vote_count)
- Vote tracking: separate `lms_course_qa_vote` table (answer_id, uid) or lightweight field on entity
- Route: `lms_course_qa.routing.yml` — `lms_course_qa.course_qa` at `/courses/{course}/qa`
- Controller: `CourseQaController::listPage()` + AJAX reply/vote endpoints
- Notification: call `lms_notification` dispatch service on answer create
- SCSS: `_course-qa.scss` in `lms_theme`
- Extend `lms_course` course page twig to add Q&A tab nav link

## Dependencies
- TASK-009 (U-042 Notification dispatch service)
- TASK-039 (forum polish — UX patterns to reuse)
- TASK-140 (notification preferences — respect per-user channel toggles)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (question/answer CRUD, vote toggle, pin logic)
- [ ] Notification fires on reply (integration test with mock dispatch)
- [ ] No new debt introduced
