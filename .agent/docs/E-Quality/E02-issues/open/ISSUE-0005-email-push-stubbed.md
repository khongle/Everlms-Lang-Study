# ISSUE-0005 - Email/push notification channels partial

**Severity:** P1 · **Status:** open · **Risk:** R-005 · **Tasks:** [E09S01T01](../../../D-Development/D04-tasks/todo/E09S01T01-implement-email-channel.md), [E09S01T02](../../../D-Development/D04-tasks/todo/E09S01T02-implement-push-channel.md)

## Description
`lms_notification` records in-app notifications reliably, but the `email` and `push` channels (`field_channel`) are partial/stubbed.

## Impact
Out-of-band reminders (enrollment, certificate, order) don't reach users → lower re-engagement; mobile push (INT-009) inactive.

## Evidence
Channel dispatch for email/push incomplete in `lms_notification`; no device-token endpoint.

## Fix
Implement email via Drupal mail/SMTP (INT-008) with cron retry; push via FCM/APNs (INT-009) with device-token register.
