# INT-008 - SMTP / email delivery

## Requirement
Gửi transactional email (notification F-017, certificate link, account/onboarding) qua mail system của Drupal / SMTP.

## Participating modules
[lms_notification](../../C-Design/C05-modules/lms_notification.md), core mail

## Contract expectations
1. **Auth/authz:** SMTP host/credential — SENSITIVE (R-001); server-side.
2. **Failure/retry/fallback:** khi gửi thất bại, queue/retry qua cron; lỗi được log; in-app notification vẫn được ghi nhận để user không bị bỏ lỡ.
3. **CI test fixture/stub:** Drupal test mail collector (không dùng SMTP thật) assert nội dung message.
4. **Operational owner + alerting:** Ops owner; alert khi bounce/failure tăng đột biến. ⚠️ As-built: email channel **chỉ partial/stubbed** (R-005).

## Design link
[C06 INT-008](../../C-Design/C06-apis/INT-008-smtp-email.md)
