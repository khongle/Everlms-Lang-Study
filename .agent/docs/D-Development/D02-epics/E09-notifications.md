# E09 - Notifications

**Status:** partial (in-app done; **email/push** open — R-005)

## Goal
Thông báo đa kênh (multi-channel) (in_app/email/push) theo domain event.

## Scope
- Module: [lms_notification](../../C-Design/C05-modules/lms_notification.md)
- Entity: lms_notification
- Feature: F-017 · Integrations: INT-008 (SMTP), INT-009 (push)

## Stories
- [E09S01](../D03-stories/E09S01-lms_notification.md)

## Dependencies
E01; được nhiều epic khác sử dụng (consumed).

## Open work
- ⚠️ Email delivery (INT-008) + push (INT-009) còn partial/stubbed (R-005) — task [E09S01T01](../D04-tasks/todo/E09S01T01-implement-email-channel.md), [E09S01T02](../D04-tasks/todo/E09S01T02-implement-push-channel.md).
