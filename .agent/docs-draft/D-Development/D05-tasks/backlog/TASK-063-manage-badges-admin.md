# Task: Manage Badges — Admin CRUD + Award Rules

**ID:** TASK-063
**Unit:** — (extends U-054: Badge + UserBadge entities)
**Group:** G-UI-POLISH
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
U-054 đã tạo entity `Badge` và `UserBadge` nhưng chưa có admin UI. Task này build trang `/manage/badges` (per C07 sitemap, Manager+ role) bao gồm: CRUD cho Badge entity (create/edit/delete/list), và giao diện định nghĩa award rules (ví dụ: "hoàn thành 5 khoá học", "đạt ≥90% trên 3 quiz"). Rules được evaluate tự động khi `CourseCompletedEvent` hoặc `QuizCompletedEvent` được dispatch.

## Acceptance Criteria
- [ ] `/manage/badges` — danh sách badge với icon, name, description, award count, actions (edit/delete)
- [ ] `/manage/badges/add` — form tạo badge: name, description, image upload, criteria type, threshold value
- [ ] `/manage/badges/{id}/edit` — form chỉnh sửa badge
- [ ] `/manage/badges/{id}/delete` — confirm delete (chặn nếu có UserBadge đang dùng)
- [ ] Award rule types: `course_completed` (N courses), `quiz_passed` (N quizzes với score ≥ threshold%)
- [ ] `BadgeEvaluatorService` evaluate rules và trao badge khi event xảy ra (không duplicate)
- [ ] Notification gửi cho user khi nhận badge (via `lms_notification`)
- [ ] Trang `/badges` (Student Portal) hiển thị badges của user đã nhận (UserBadge list)
- [ ] Access: Manager+ cho `/manage/badges`; Student+ cho `/badges`

## Technical Notes
- Module: `web/modules/custom/lms_badge/` (nếu chưa có, tạo mới tách từ U-054)
- Hoặc extend trong `web/modules/custom/lms_additional/` nếu U-054 nằm ở đó
- `BadgeEvaluatorService`: subscribe `CourseCompletedEvent` và `QuizCompletedEvent` từ `lms_progress` / `lms_quiz`
- Entity form: Drupal Entity Form API + `BadgeForm.php`
- Controller: `BadgeAdminController.php` với routes trong `lms_badge.routing.yml`
- Image upload: managed file field trên Badge entity
- Template: `web/themes/custom/lms_theme/templates/badge/` — `badge-list.html.twig`, `badge-card.html.twig`
- SCSS: `_badges.scss`

## Dependencies
- U-054 (Badge + UserBadge entities — DONE)
- TASK-009 (U-042 Notification dispatch service) — cần cho badge notification

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (CRUD + award rule evaluation)
- [ ] Duplicate badge award prevented (PHPUnit test với 2x event fire)
- [ ] Mobile responsive verified
