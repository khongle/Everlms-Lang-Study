# Task: Bulk User Import from CSV

**ID:** TASK-131
**Unit:** U-010 (lms_user / admin tools)
**Group:** G-USER-MGMT (extension)
**Priority:** P2
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Allow platform admins to import multiple users at once by uploading a CSV file. Each row specifies at minimum an email address and display name; optional columns include role and course enrollment IDs. The import flow includes preview, field-level validation error reporting, batch processing, and a welcome email to successfully created accounts.

## Context

- Module: admin tool in `lms_base` or new `lms_user_tools` module (decide at implementation time based on scope)
- Common use case: corporate training onboarding, school/cohort setup
- New users receive a Drupal one-time login link in their welcome email

## Acceptance Criteria

- [ ] Admin form at `/admin/lms/users/import` with CSV file upload field
- [ ] CSV columns: `email` (required), `display_name` (required), `role` (optional; must be valid role), `course_ids` (optional; comma-separated IDs)
- [ ] Preview table after upload shows first 10 rows; validation errors highlighted per cell
- [ ] Full validation pass before any users are created (fail fast on critical errors)
- [ ] Batch API processes import (≥50 rows) to avoid timeout; progress bar shown
- [ ] Duplicate email: skip silently with count; user is NOT overwritten
- [ ] Invalid role name: row flagged as error, skipped
- [ ] Invalid course ID: enrollment skipped for that row; user still created
- [ ] Welcome email sent on account creation: includes one-time login link
- [ ] Result summary page: N created, M skipped (duplicate), K errors with downloadable error CSV
- [ ] Access restricted to `administer users` permission

## Technical Notes

- CSV parsing: use `League\Csv` (composer) or PHP `fgetcsv`; validate UTF-8 encoding
- New form: `web/modules/custom/lms_base/src/Form/UserCsvImportForm.php` (or `lms_user_tools`)
- New route: `lms_base.user_csv_import` → `/admin/lms/users/import`
- User creation: use `\Drupal\user\Entity\User::create()` with `status = 1`; send one-time login via `user_pass_reset_url()`
- Enrollment on import: call `EnrollmentManager::enroll()` per valid course ID
- Batch: `_lms_user_csv_import_batch_process($rows, &$context)` with `results` and `finished` callbacks
- Error CSV: generated via `League\Csv\Writer` or `fputcsv`; offered as download link in result summary

## Dependencies

- TASK-027 (user CRUD and roles)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
