# Task: Entity Access Handler Audit

**ID:** TASK-019
**Unit:** U-065
**Group:** G-HARDENING
**Priority:** P0
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG (42% — 11/24 entities have custom handlers)

## Audit Notes
- **Custom handlers (11)**: Course, Chapter, Lesson, Order, Quiz, QuizResult, QuestionResponse, Question, Answer, MatchingPair, ForumTopic, ForumReply, LmsArticle
- **Default permissive handler (13+)**: OrderItem, Wishlist, Faq, Rating, CertificateTemplate, UserCertificate, CourseBundle, Notification, UserLesson, UserCourse, Note, UserBadge, Badge, MembershipPlan, UserMembership
- Default handler grants access based on generic `administer {entity_type}` permissions — no owner-based checks

## Description
Audit và implement access handlers cho tất cả custom entities. Đảm bảo RBAC enforce đúng.

## Acceptance Criteria
- [ ] Mọi entity type có custom access handler
- [ ] `view`/`create`/`update`/`delete` operations checked per role
- [ ] "Own" vs "Any" logic cho Instructor (own courses/quizzes)
- [ ] Admin bypass present nhưng không leak
- [ ] Anonymous blocked khỏi protected entities

## Technical Notes
- Check: `access()` method hoặc `access_handler` trong entity annotation
- Pattern: `EntityAccessControlHandler` extend + checkAccess()

## Dependencies
- None

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Per-entity access tests
