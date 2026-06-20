# Task: Per-Lesson Comment Thread

**ID:** TASK-137
**Unit:** — (new module `lms_lesson_comment`)
**Group:** G-COMMUNICATION (extension)
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Add a Disqus-style threaded comment section below each lesson player. Students and instructors can post comments, reply in threads, and mark a reply as the accepted answer. Comments can include a timestamp link that jumps the video player to a specific position, following the pattern used in `lms_note`.

## Context
- `lms_note` already stores video position timestamps per lesson; reuse the timestamp format and jump-to mechanism.
- `lms_rating` handles course-level star ratings; lesson comments are distinct.
- Threaded replies: two levels deep (comment → reply) is sufficient; no infinite nesting.

## Acceptance Criteria
- [ ] Comment section visible below lesson content to enrolled users + instructors + admins
- [ ] Post a top-level comment with optional video timestamp (MM:SS picker autofills from player position)
- [ ] Timestamp links are clickable and seek the video player to that position
- [ ] Reply to any top-level comment (one level of threading)
- [ ] Mark a reply as "Accepted Answer" (instructor or comment author only); accepted reply pinned visually
- [ ] Edit/delete own comment within 15 minutes of posting; admins/instructors can delete any time
- [ ] Comments sorted: accepted answer first, then by created_at asc; replies sorted by created_at asc
- [ ] Lesson comment count displayed in lesson sidebar/nav
- [ ] Instructor receives in-app notification when a new top-level comment is posted on their lesson
- [ ] Responsive layout; Bootstrap 5.3 dark theme

## Technical Notes
- New module: `web/modules/custom/lms_lesson_comment/`
- Entities: `LessonComment` (lesson ref, uid, body, timestamp_seconds, created, updated, status, parent_id, is_accepted)
- `parent_id` references another `LessonComment`; NULL = top-level
- Route: AJAX endpoint `POST /lesson/{lesson}/comments` and `GET /lesson/{lesson}/comments`
- Twig: `lesson-comments.html.twig` included via hook_page_attachments or lesson twig embed
- JS: `lesson-comments.js` handles timestamp click → `videoElement.currentTime = seconds`; reuse player API from `lms_note`
- SCSS: `_lesson-comments.scss` in `lms_theme`
- Notification: call `lms_notification` dispatch service for instructor on new comment

## Dependencies
- TASK-009 (U-042 Notification dispatch service)
- TASK-140 (notification preferences)
- TASK-136 (Q&A — share UX patterns; implement after or in parallel)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (CRUD, threading, timestamp parse, accepted-answer toggle)
- [ ] Timestamp seek verified in browser (manual smoke test note in PR)
- [ ] No new debt introduced
