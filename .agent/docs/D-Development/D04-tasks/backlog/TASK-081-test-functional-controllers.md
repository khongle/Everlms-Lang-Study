# Task: Functional/Browser Tests — Top 20 Controllers

**ID:** TASK-081
**Unit:** — (cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Xây dựng Drupal `BrowserTestBase` functional test suite cho 20 controller endpoints quan trọng nhất của EverLMS. Mỗi controller test cover: HTTP response code, page render không có PHP error, critical UI elements present, và basic access control (authenticated vs. anonymous). Suite này bổ sung cho kernel tests (TASK-075..080) ở level HTTP — verify routing + controller + template stack hoạt động end-to-end.

Scope khác với Playwright E2E (TASK-068): functional tests chạy trong PHPUnit với Drupal's built-in browser emulator, không cần browser binary. Nhanh hơn E2E, chạy trong CI gate G2.

## Acceptance Criteria
- [ ] Tất cả 20 controllers trả về 200 khi authenticated đúng role:
  - [ ] `GET /` — homepage (anonymous + student)
  - [ ] `GET /courses` — course catalog (anonymous)
  - [ ] `GET /courses/{slug}` — course detail (anonymous)
  - [ ] `GET /courses/{slug}/learn/{lesson}` — lesson view (enrolled student)
  - [ ] `GET /dashboard` — student dashboard (student)
  - [ ] `GET /my-courses` — my courses (student)
  - [ ] `GET /quiz/{id}/take` — quiz take (enrolled student)
  - [ ] `POST /quiz/{id}/submit` — quiz submit (enrolled student)
  - [ ] `GET /checkout/{order_id}` — checkout (authenticated)
  - [ ] `POST /checkout/{order_id}/confirm` — checkout confirm
  - [ ] `GET /certificate/verify/{uuid}` — public verify (anonymous)
  - [ ] `GET /blog` — blog listing (anonymous)
  - [ ] `GET /search` — search results (anonymous)
  - [ ] `GET /admin/lms/courses` — admin course list (admin)
  - [ ] `GET /admin/lms/orders` — admin order list (admin)
  - [ ] `GET /admin/lms/users` — admin user list (admin)
  - [ ] `GET /admin/lms/notifications/send` — send notification (admin)
  - [ ] `GET /admin/lms/essay-queue` — essay grading queue (instructor)
  - [ ] `GET /profile/{uid}` — user profile (authenticated)
  - [ ] `GET /faq` — FAQ page (anonymous)
- [ ] Access control: unauthenticated request tới protected routes → 302/403
- [ ] No PHP fatal/warning trong response
- [ ] Run time < 5 min toàn bộ suite trong DDEV

## Technical Notes
- Base class: `Drupal\Tests\BrowserTestBase`
- Test path: `web/modules/custom/lms_base/tests/src/Functional/ControllerSmokeTest.php` (single file hoặc split theo group)
- Dùng `$this->drupalLogin()` và `$this->drupalCreateUser()` với đúng roles
- Enrolled student: cần create enrollment entity trước khi test lesson/quiz routes
- Lesson view route: `web/modules/custom/lms_lesson/src/Controller/LessonViewController.php`
- Quiz submit: POST cần CSRF token — dùng `$this->drupalPostForm()` hoặc raw POST với token fetch
- Group annotation: `@group functional-controllers`

## Dependencies
- TASK-067 (test catalog)
- TASK-068 (Playwright E2E — complement, không thay thế)
- TASK-069 (CI gate G2)
- TASK-058 (FIND-007 sitemap routes — route list reference)

## Definition of Done
- [ ] Code complete — 20 controller smoke tests
- [ ] G1-G4 gates passed
- [ ] 0 failures trong DDEV functional test run
- [ ] Access control assertions confirmed
- [ ] Suite runtime < 5 min documented
