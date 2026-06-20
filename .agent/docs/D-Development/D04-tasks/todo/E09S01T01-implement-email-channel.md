---
id: E09S01T01
title: Implement email notification channel
status: todo
story: E09S01
owning_modules: [lms_notification]
risk: R-005
adequacy: NOT_RUN
---

# E09S01T01 - Implement email notification channel

## Goal
Deliver email-channel notifications via Drupal mail/SMTP (INT-008); currently partial/stubbed (R-005, P1).

## Steps
1. Implement mail send for `field_channel=email` notifications (templates editor-managed).
2. Queue + cron retry on failure; respect `user.field_receive_notifications`.
3. Tests via Drupal mail collector.

## Acceptance / gates
- Enroll/cert/order emails rendered + sent; failures retried; in-app fallback intact.

## Links
[story](../../D03-stories/E09S01-lms_notification.md) · [INT-008](../../../C-Design/C06-apis/INT-008-smtp-email.md) · [G02 R-005](../../../G-Governance/G02-risk-register/_index.md)
