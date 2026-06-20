# Task: GDPR Data Export (Right to Data Portability — Art. 20)

**ID:** TASK-133
**Unit:** U-010 (lms_user)
**Group:** G-COMPLIANCE
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Implement self-service GDPR data export allowing users to request a JSON archive of all their personal data held by the platform. The archive covers profile data, enrollments, progress, quiz results, certificates, orders, and any forum posts. An email link to download the archive is sent within 24 hours. This satisfies GDPR Article 20 (Right to Data Portability).

## Context

- Module: `lms_user` (or `lms_gdpr` sub-module)
- Request entry point: learner's profile page at `/profile` → "Download my data" button
- Download link must expire after 48 hours for security
- Legal requirement: data delivered in machine-readable format (JSON)

## Acceptance Criteria

- [ ] "Download My Data" button on `/profile` page; submits request
- [ ] Request acknowledgement page: "Your data export will be emailed to you within 24 hours"
- [ ] Async generation: `QueueWorker` processes export to avoid timeout (or Drupal batch if queue not available)
- [ ] JSON export contains all personal data: profile fields, enrollment records, per-lesson progress, quiz attempt results, certificates (with issue date + course), order history (amounts, dates, items)
- [ ] Export excludes system logs and internal IDs that are not meaningful to the user
- [ ] Signed temporary URL generated for download; expires in 48 hours
- [ ] Email sent to user's registered email with download link and expiry notice
- [ ] Download URL invalidated after first download (single-use) or after 48h, whichever comes first
- [ ] Admin can manually trigger data export for any user at `/admin/lms/users/{uid}/export`
- [ ] Rate limit: max 1 export request per user per 24 hours (prevent abuse)

## Technical Notes

- New queue worker: `web/modules/custom/lms_user/src/Plugin/QueueWorker/DataExportWorker.php`
- Collects data via: `EntityTypeManager` loading all entity types keyed by user ID
- JSON schema: `{ user: {...}, enrollments: [...], progress: [...], quiz_results: [...], certificates: [...], orders: [...] }`
- Signed URL: use `\Drupal\Core\TempStore\PrivateTempStore` or a `lms_data_export_token` DB table with `{token, uid, expires_at, used}`
- File stored in private files directory: `private://data-exports/{uid}-{token}.json`
- Email: extend `lms_notification` service; template `lms-email-data-export-ready.html.twig`
- Rate limit: store `last_export_requested_at` on user entity or in state

## Dependencies

- TASK-027 (user CRUD)
- TASK-062 (GDPR consent framework)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
