# Task: Manage Send Notifications — Broadcast / Targeted UI

**ID:** TASK-064
**Unit:** — (extends U-039 + U-042: Notification entity + dispatch service)
**Group:** G-UI-POLISH
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
U-039 (Notification entity) và U-042 (Notification dispatch service) đã có nền tảng nhưng chưa có admin UI để compose và gửi thông báo thủ công. Task này build trang `/manage/notifications/send` (per C07 sitemap, Manager+) cho phép admin compose notification, chọn audience, chọn channel, xem preview và xác nhận gửi.

## Acceptance Criteria
- [ ] `/manage/notifications/send` có form: Title, Body (rich text editor), Audience, Channel
- [ ] Audience options: All users / By role (Student, Instructor) / Specific users (autocomplete) / Course enrollees (course selector)
- [ ] Channel options: In-app only / Email only / Both
- [ ] Preview step hiển thị notification như user sẽ thấy (dựa trên template `lms_notification`)
- [ ] Confirm + Send step với recipient count estimate
- [ ] Sau khi gửi: success message + link xem lịch sử
- [ ] `/manage/notifications` — lịch sử notifications đã gửi (sender, audience, channel, sent_at, count)
- [ ] Send tạo `Notification` entities cho từng recipient (batch via queue nếu audience > 100)
- [ ] Access: Manager+ role

## Technical Notes
- Module: `web/modules/custom/lms_notification/` (U-039, U-042)
- Form: `NotificationSendForm.php` (Drupal FormBase)
- Route: thêm vào `lms_notification.routing.yml`
- Rich text: Drupal CKEditor 5 (contrib, đã có qua U-002)
- Audience query: UserStorage + Enrollment entity query
- Large audience (>100): sử dụng Drupal Queue API — `lms_notification_send_queue`
- Template preview: tái dụng `lms_notification.html.twig` trong preview step
- SCSS: `_notification-send.scss`

## Dependencies
- U-039 (Notification entity — DONE)
- TASK-009 (U-042 Notification dispatch service)
- TASK-011 (U-044 Email notification integration)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (form submit + queue dispatch)
- [ ] Large audience (>100) xử lý qua queue (PHPUnit test với mock)
- [ ] No duplicate notifications (idempotency check trong service)
