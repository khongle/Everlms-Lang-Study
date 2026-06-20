# Task: Forum UI Polish

**ID:** TASK-039
**Unit:** U-080
**Group:** G-UI-POLISH
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Polish forum UI: topic listing, thread view, reply form, search, pagination, moderation indicators.

## Acceptance Criteria
- [ ] Forum listing: categories, recent topics, search
- [ ] Topic list: title, author, replies count, last activity
- [ ] Thread view: original post + replies (nested or flat)
- [ ] Reply form: rich text editor, quote, mention
- [ ] Pagination cho topics và replies
- [ ] Pinned/locked topic indicators
- [ ] Author info card (avatar, role badge)
- [ ] Responsive layout

## Technical Notes
- Module: `lms_forum` (ForumController exists, templates exist)
- Templates: `lms-forum-*.html.twig`
- SCSS: `_forum.scss`
- Existing: ForumTopic + ForumReply entities, access control

## Dependencies
- TASK-010 (Forum thread UI — DONE)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Mobile responsive verified
