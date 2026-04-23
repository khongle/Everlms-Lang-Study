# Task: Email Notification Integration

**ID:** TASK-011
**Unit:** U-044
**Group:** G-COMMUNICATION
**Priority:** P1
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG (5% — stub only)

## Audit Notes
- NotificationManager has `if (in_array('email', $channels))` that only LOGS. Comment: `// TODO: Implement Drupal queue creation for mail.`
- No `lms_notification_mail()` hook implementation
- No HTML email templates
- No QueueWorker plugin for async email
- No MailManagerInterface usage
- Preference UI exists with email checkbox column — stored correctly but dispatch does nothing

## Description
Tích hợp email delivery cho notifications: welcome, quiz result, certificate, order confirmation, membership reminders.

## Acceptance Criteria
- [ ] Email templates cho 6 notification types
- [ ] Email dispatch hook vào NotificationDispatcher
- [ ] SMTP configuration qua admin settings
- [ ] HTML email formatting (responsive)
- [ ] Email queue cho bulk operations

## Technical Notes
- Module: `lms_notification`
- Library: `drupal/smtp` hoặc `symfony_mailer`
- Hook: `hook_mail()` + mail templates
- Config: `/manage/settings/email`

## Dependencies
- TASK-009 (Notification dispatch service)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Email delivery test
