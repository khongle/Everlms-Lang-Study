# Task: GDPR Right to be Forgotten — Data Deletion (Art. 17)

**ID:** TASK-134
**Unit:** U-010 (lms_user)
**Group:** G-COMPLIANCE
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Implement GDPR Article 17 (Right to Erasure / Right to be Forgotten). Users can request account deletion from their profile page. PII is anonymised (name, email, avatar removed) rather than hard-deleted to preserve referential integrity and aggregate platform statistics. A 30-day grace period allows accidental requests to be cancelled. An audit trail records all deletion actions.

## Context

- Module: `lms_user` (or `lms_gdpr` sub-module)
- Anonymisation strategy: replace name with "Deleted User {hash}", set email to null, remove avatar, set account status to blocked
- Hard data deletion: not required by GDPR if data is fully anonymised and no longer personally identifiable
- 30-day grace period: learner can cancel the request via a link in the confirmation email within 30 days

## Acceptance Criteria

- [ ] "Delete My Account" button on `/profile` page; requires password re-entry before submitting
- [ ] Deletion request created with `scheduled_at = NOW + 30 days`; stored in `lms_gdpr_deletion_request` table
- [ ] Confirmation email sent to user: "Your deletion request will be processed on {date}. To cancel, click here."
- [ ] Cancellation link (signed, expires at `scheduled_at`) cancels the request
- [ ] Cron job processes due deletions: anonymises PII fields, blocks account, removes avatar
- [ ] Anonymised fields: `name` → `"Deleted User {uid_hash}"`, `mail` → null, `field_avatar` → removed, `field_phone` → null
- [ ] Preserved data: enrollment records, progress, quiz scores, certificates (de-linked from name), order records (financial audit requirement)
- [ ] Orders reference anonymised display name; financial amounts not deleted (VN accounting law)
- [ ] Admin can approve deletion early or revoke a pending deletion request
- [ ] All deletion actions logged: `lms_gdpr_deletion_log` with `uid`, `requested_at`, `processed_at`, `admin_uid_if_manual`
- [ ] Admin list at `/admin/lms/gdpr/deletion-requests` shows pending + processed requests

## Technical Notes

- New DB table: `lms_gdpr_deletion_request` columns: `id`, `uid`, `requested_at`, `scheduled_at`, `cancelled_at`, `processed_at`, `token`
- Cron: query `scheduled_at <= NOW AND processed_at IS NULL AND cancelled_at IS NULL` → process
- Anonymisation service: `web/modules/custom/lms_user/src/GdprAnonymisationService.php`
- Signed cancellation URL: `HMAC-SHA256(secret_key, "cancel:{uid}:{scheduled_at}")` stored as `token`; validated on access
- Email templates: `lms-email-gdpr-deletion-request.html.twig`, `lms-email-gdpr-deletion-cancelled.html.twig`, `lms-email-gdpr-deletion-processed.html.twig`
- Admin actions: custom entity operations on `lms_gdpr_deletion_request` entity (or plain DB + admin form)
- VN financial retention: order records must be kept for minimum 5 years (Luật kế toán 2015)

## Dependencies

- TASK-027 (user CRUD)
- TASK-062 (GDPR consent framework)
- TASK-133 (GDPR data export — often implemented as companion feature)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
