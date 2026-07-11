# C06 API/Contract - INT-008 SMTP / email

**Requirement:** [B06 INT-008](../../B-Requirements/B06-integration-requirements/INT-008-smtp-email.md)
**Participating modules:** [lms_notification](../C05-modules/lms_notification.md), core mail

## Purpose
Gửi email transactional (notification, link certificate, onboarding) qua Drupal mail / SMTP. ⚠️ Kênh email mới hoàn thiện một phần (R-005).

## Proposed contract
- **REST namespace:** n/a (transport qua SMTP dùng Drupal MailManager).
- **Auth:** SMTP host/credentials (config/env) — SENSITIVE (R-001).
- **Authorization:** hệ thống tự khởi tạo khi có event; theo opt-in của từng user (`user.field_receive_notifications`).
- **Response envelope (internal):** kết quả gửi mail được ghi log; notification in-app vẫn được ghi nhận dù kết quả gửi mail thế nào.
- **Audit:** ghi log send success/failure; retry qua cron; theo dõi bounce.

## Test requirements
- CI: dùng Drupal test mail collector (không gửi SMTP thật) để assert subject/body/recipient.
- Auth+authz: tôn trọng tuỳ chọn notification của từng user.
- Failure/fallback: gửi thất bại → đưa vào queue/retry; có fallback in-app.
- Contract test: email enroll/cert/order được render + đưa vào queue.
