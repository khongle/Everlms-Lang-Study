# Task: Kernel Test Suite — G-COMMUNICATION (Notification, Forum, Rating)

**ID:** TASK-080
**Unit:** — (cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Xây dựng PHPUnit kernel test suite cho G-COMMUNICATION group: `lms_notification`, `lms_forum`, `lms_rating`. Suite kiểm tra notification dispatch (event → queue → delivery), forum entity rules (post/reply/report), và rating submission + aggregation logic. Tất cả external delivery (email, push) được mock.

Fix FIND-013 (notification anonymous auth bug — TASK-056) phải được cover bởi regression test trong suite này để tránh revert.

## Acceptance Criteria
- [ ] **lms_notification:**
  - [ ] Event `CourseCompletedEvent` → notification entity created cho đúng user
  - [ ] Event `QuizPassedEvent` → notification dispatched
  - [ ] Anonymous user không trigger notification creation (FIND-013 regression test)
  - [ ] Notification mark-as-read → `read_at` field populated
  - [ ] Mock mailer: không có real email sent trong test
- [ ] **lms_forum:**
  - [ ] Forum topic CRUD; field validation
  - [ ] Reply relationship (parent topic → replies)
  - [ ] Report flow: topic/reply flagged → status `REPORTED`
  - [ ] Instructor/Admin can moderate (delete/lock topic)
  - [ ] Student cannot delete other student's post
- [ ] **lms_rating:**
  - [ ] Rating submit: `1–5` range validation
  - [ ] Duplicate rating prevention (same user + course)
  - [ ] Average score recalculation after new rating
  - [ ] Null/empty handling: course với 0 ratings
- [ ] Coverage ≥75% trên service classes
- [ ] Pass trong DDEV (`ddev phpunit --group kernel-communication`)

## Technical Notes
- Modules: `lms_notification`, `lms_forum`, `lms_rating`
- Test paths:
  - `web/modules/custom/lms_notification/tests/src/Kernel/`
  - `web/modules/custom/lms_forum/tests/src/Kernel/`
  - `web/modules/custom/lms_rating/tests/src/Kernel/`
- FIND-013 fix tại TASK-056: `lms_notification/src/EventSubscriber/NotificationSubscriber.php`
- Rating aggregation: likely `lms_rating/src/RatingAggregator.php`
- Mock mailer: `Drupal\Tests\*\Mock\MockMailManager`
- Annotation `@group TEST-006` (mới nếu chưa trong E03)

## Dependencies
- TASK-056 (FIND-013 notification anonymous fix — regression test)
- TASK-067 (test catalog)
- TASK-069 (CI gate G2)

## Definition of Done
- [ ] Code complete — Notification + Forum + Rating kernel tests
- [ ] G1-G4 gates passed
- [ ] FIND-013 regression test included + passing
- [ ] 0 real email/push calls trong test run
- [ ] Coverage ≥75% confirmed
