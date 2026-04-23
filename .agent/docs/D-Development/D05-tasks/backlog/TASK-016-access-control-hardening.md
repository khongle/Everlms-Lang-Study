# Task: Access Control Hardening — All Routes

**ID:** TASK-016
**Unit:** U-058
**Group:** G-HARDENING
**Priority:** P0
**Estimate:** 14h
**Assigned:** unassigned
**Status:** BACKLOG (60% — routes covered, entities partial)

## Audit Notes
- 7 custom AccessControlHandlers covering 11 entity types (Course, Chapter, Lesson, Order, Quiz*, Question*, ForumTopic, ForumReply, LmsArticle)
- 13+ entity types use permissive DEFAULT handler: UserCourse, UserLesson, UserCertificate, Notification, Wishlist, Note, Badge, MembershipPlan, UserMembership etc.
- All routes have at minimum `_user_is_logged_in` or `_permission` ✔️
- **Risk**: Sensitive entities (UserCourse, UserCertificate) lack owner-based access checks

## Description
Audit và enforce access control trên tất cả routes. Đảm bảo role matrix được enforce đồng đều.

## Acceptance Criteria
- [ ] Audit tất cả routes trong routing.yml của 26 `lms_*` modules (+ `open_switcher`)
- [ ] Entity access handlers implement đúng cho mọi entity type
- [ ] Route permissions match role matrix (C02)
- [ ] "Edit own" vs "Edit any" logic enforce đúng
- [ ] Admin-only routes blocked cho Manager/Instructor/Student
- [ ] Anonymous chỉ access public pages
- [ ] Test per-role access cho critical routes

## Technical Notes
- Check: tất cả `*.routing.yml` → verify `_permission` / `_role` / `_custom_access`
- Entity: `access()` method trong mọi entity type
- Focus: management routes, API endpoints, settings pages

## Dependencies
- None (can start immediately)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed (G4 Security)
- [ ] Access matrix test coverage
