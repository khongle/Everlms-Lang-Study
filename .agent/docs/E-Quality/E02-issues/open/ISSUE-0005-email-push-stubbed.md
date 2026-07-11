# ISSUE-0005 - Email/push notification channels partial

**State:** open
**Severity:** P1 · **Module:** [lms_notification](../../../C-Design/C05-modules/lms_notification.md) · **Story:** [E09S01-lms_notification](../../../D-Development/D03-stories/E09S01-lms_notification.md) · **Risk:** R-005 · **Tasks:** [E09S01T01](../../../D-Development/D04-tasks/todo/E09S01T01-implement-email-channel.md), [E09S01T02](../../../D-Development/D04-tasks/todo/E09S01T02-implement-push-channel.md)

## Summary
`lms_notification` records in-app notifications reliably, but the `email` and `push` channels (`field_channel`) are partial/stubbed, so out-of-band reminders (enrollment, certificate, order) don't reliably reach users outside the app.

## Steps to reproduce
1. Trigger a notification-worthy event (e.g. certificate issuance) for a test user with a valid email and a registered mobile device token.
2. Check the in-app notification list (`/notifications`) → entry present.
3. Check the test mailbox (or SMTP log) and the device's push tray → email/push did not arrive (or arrives inconsistently), confirming the channel dispatch is incomplete.

## Root cause
Channel dispatch for `email`/`push` in `lms_notification` was scaffolded (the `field_channel` value exists on the notification entity) but the actual delivery integrations — Drupal mail/SMTP for email (INT-008), FCM/APNs for push (INT-009) including a device-token registration endpoint — were never finished.

## Fix
Implement email via Drupal mail/SMTP (INT-008) with cron retry on transient failure; implement push via FCM/APNs (INT-009) with a device-token register endpoint on `lms_mobile_api`.

## Regression test
No automated regression test exists yet (tracked under [ISSUE-0004](ISSUE-0004-zero-test-coverage.md) / [X01](../../../D-Development/D04-tasks/todo/X01-establish-test-baseline.md)). Planned: an [E03](../../E03-test-definitions/_index.md) `notification-channel-delivery` Kernel test asserting each `field_channel` value dispatches to a mocked mail/FCM transport exactly once per triggering event, plus a cron-retry assertion for a simulated transient failure.
