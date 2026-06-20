# F-017 Acceptance Criteria - Notifications

> ⚠️ As-built: in-app channel works; **email/push partial/stubbed** (R-005). Email/push criteria are `target` pending INT-008/INT-009.

## Core criteria
1. **Given** a notifiable event (enroll, certificate, order paid, forum reply), **when** it occurs, **then** a `lms_notification` (`field_channel`) record is created.
2. **Given** an in-app notification, **when** the user opens their notifications, **then** they see it and can mark it read.
3. **(target)** **Given** an email-channel notification, **when** dispatched (cron), **then** an email is delivered via INT-008.
4. **(target)** **Given** a push-channel notification, **when** dispatched, **then** the Flutter app receives a push via INT-009.

## Evidence expected
- Module: [lms_notification](../../C-Design/C05-modules/lms_notification.md).
- Test: in-app create/read test (passes); email/push delivery tests tracked as gaps in [E06](../../E-Quality/E06-implementation-status/_index.md).
