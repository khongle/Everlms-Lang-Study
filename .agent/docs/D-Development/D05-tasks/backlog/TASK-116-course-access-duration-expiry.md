# Task: Course Access Duration and Expiry

**ID:** TASK-116
**Unit:** U-015 (lms_course), U-023 (lms_progress)
**Group:** G-CONTENT (extension)
**Priority:** P2
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Add per-course access time limits (e.g., 90 days from enrollment date). When the access window expires, the learner's enrollment is revoked automatically. Reminder emails are sent before expiry. Learner can re-purchase to extend access.

## Context

- Module: `lms_course`, `lms_progress`
- Enrollment entity (`lms_enrollment` or enrollment field on `lms_progress`) needs `access_expires` timestamp.
- Cron checks expiry daily; email reminders at 14 days and 3 days before expiry.
- Expired access: course appears in "My Courses" as expired, content locked.

## Acceptance Criteria

- [ ] `field_access_duration_days` integer field added to `lms_course` (0 = unlimited)
- [ ] `access_expires` timestamp stored on enrollment record at enroll time
- [ ] Cron job (`lms_course_cron`) scans enrollments expiring within 14 days → sends reminder email
- [ ] Cron job sends second reminder at 3 days before expiry
- [ ] On expiry: enrollment status set to `expired`, content access revoked
- [ ] "My Courses" card shows expiry date when limit is set; badge "Expired" when past
- [ ] Admin can manually extend expiry from enrollment edit page
- [ ] Re-purchase of same course resets `access_expires` from new purchase date
- [ ] Email templates: `lms-email-access-expiry-reminder.html.twig` (configurable body)

## Technical Notes

- Cron handler: `web/modules/custom/lms_course/lms_course.module` → `hook_cron()`
- Use `\Drupal::entityQuery` on enrollment entities with `access_expires` condition
- Mailer: extend existing `lms_notification` service or use `MailManager`
- Progress/content gate: `ProgressApiController` or content access check should verify `access_expires > REQUEST_TIME`
- No hard delete of progress or quiz data on expiry — only access revoked

## Dependencies

- TASK-027 (user roles)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
