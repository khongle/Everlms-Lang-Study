# Task: Forum Thread UI + Permissions

**ID:** TASK-010
**Unit:** U-043
**Group:** G-COMMUNICATION
**Priority:** P1
**Estimate:** 10h
**Assigned:** unassigned
**Status:** DONE (85%)
**Completed:** 2026-04-15

## Audit Notes
- ForumTopic + ForumReply entities with full fields ✅
- LmsForumController: index, viewTopic, addTopic, reply, markBestAnswer, editReply ✅
- Access handlers for both entities ✅
- 5 rich Twig templates (19.4KB topic detail) ✅
- Threaded replies, best answer, resolved status, category taxonomy ✅
- **Known gaps**: No search. Upvote field exists but no route. pinned/locked fields not wired to UI. Dead code: old ForumController with `throw \Exception`. Debug messages in active controller.

## Description
Forum UI hoàn chỉnh với threaded replies, permission checks theo role matrix, và course-scoped forums.

## Acceptance Criteria
- [ ] Forum listing page với topics
- [ ] Create topic (Student+): title, body, course reference
- [ ] Reply to topic (threaded): Student+
- [ ] Moderate: Manager+ có thể edit/delete any, Instructor moderate own course
- [ ] Permissions enforce đúng role matrix
- [ ] Pagination cho topics và replies

## Technical Notes
- Module: `lms_forum`
- Controller: `ForumController`
- Routes: `/forum`, `/forum/{id}`, `/forum/new`
- Templates: `lms-forum-list.html.twig`, `lms-forum-topic.html.twig`
- Access handlers: per-entity access check

## Dependencies
- U-040 (Forum entities) ✅ DONE

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Access control tests per role
