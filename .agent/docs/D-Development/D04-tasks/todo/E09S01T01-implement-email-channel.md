---
id: E09S01T01
task_id: E09S01T01
title: Implement email notification channel
status: todo
story: E09S01
owning_modules: [lms_notification]
modules: [lms_notification]
difficulty: 2
risk: R-005
adequacy: NOT_RUN
---

# E09S01T01 - Implement email notification channel

## Goal
Gửi notification qua kênh email (email-channel) bằng Drupal mail/SMTP (INT-008); hiện tại vẫn partial/stubbed (R-005, P1).

## Steps
1. Implement gửi mail cho notification `field_channel=email` (template do editor quản lý).
2. Queue + cron retry khi thất bại; tôn trọng (respect) `user.field_receive_notifications`.
3. Test qua Drupal mail collector.

## Acceptance / gates
- Email enroll/cert/order được render + gửi; lỗi được retry; in-app fallback vẫn hoạt động.

## Links
[story](../../D03-stories/E09S01-lms_notification.md) · [INT-008](../../../C-Design/C06-apis/INT-008-smtp-email.md) · [G02 R-005](../../../G-Governance/G02-risk-register/_index.md)
