# Task: Bulk Enroll Users into Courses

**ID:** TASK-118
**Unit:** U-015 (lms_course)
**Group:** G-CONTENT (extension)
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Allow admins to enroll multiple users into one or more courses at once. Supports manual selection of users and courses through the admin UI, and a CSV import option for large batches (common in corporate training scenarios). Processing runs as a Drupal batch operation.

## Context

- Module: `lms_course` (admin pages)
- Target audience: platform admins, corporate training coordinators
- Users are identified by email or user ID; courses by ID or title
- On completion, each user receives a welcome/enrollment confirmation email

## Acceptance Criteria

- [ ] Admin page at `/admin/lms/bulk-enroll` with user multi-select and course multi-select
- [ ] CSV upload option: columns `email`, `course_id` (optional: `access_duration_days`)
- [ ] CSV validation: report invalid emails, unknown course IDs before processing
- [ ] Preview table shown before committing enrollments
- [ ] Batch API used for large lists (>50 rows) to avoid timeout
- [ ] Duplicate enrollments skipped (already-enrolled users logged, not errored)
- [ ] Enrollment confirmation email sent to each successfully enrolled user
- [ ] Admin sees result summary: N enrolled, M skipped, K failed
- [ ] Access controlled: `administer lms course` permission required

## Technical Notes

- New form: `web/modules/custom/lms_course/src/Form/BulkEnrollForm.php`
- Route: `web/modules/custom/lms_course/lms_course.routing.yml` → add `lms_course.bulk_enroll`
- CSV parsing: use PHP `fgetcsv`; validate row by row before batch
- Batch callback: `_lms_course_bulk_enroll_process_batch($user_ids, $course_ids, &$context)`
- Reuse `EnrollmentManager::enroll()` for individual enrollment logic
- Email: extend `lms_notification` service with `sendBulkEnrollmentEmail()` method

## Dependencies

- TASK-027 (user roles and CRUD)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
