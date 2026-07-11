# F-017 Acceptance Criteria - Notifications

> ⚠️ As-built: in-app channel hoạt động; **email/push chỉ partial/stubbed** (R-005). Tiêu chí email/push là `target`, chờ INT-008/INT-009.

## Core criteria
1. **Given** một event có thể notify (enroll, certificate, order paid, forum reply), **when** event xảy ra, **then** một bản ghi `lms_notification` (`field_channel`) được tạo.
2. **Given** một in-app notification, **when** user mở mục notification của mình, **then** họ thấy notification và có thể đánh dấu đã đọc.
3. **(target)** **Given** một notification qua email channel, **when** được dispatch (cron), **then** một email được gửi qua INT-008.
4. **(target)** **Given** một notification qua push channel, **when** được dispatch, **then** Flutter app nhận được push qua INT-009.

## Evidence expected
- Module: [lms_notification](../../C-Design/C05-modules/lms_notification.md).
- Test: in-app create/read test (pass); email/push delivery test được track như gap trong [E06](../../E-Quality/E06-implementation-status/_index.md).
