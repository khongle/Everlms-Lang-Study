# C06 API/Contract - INT-008 SMTP / email

**Requirement:** [B06 INT-008](../../B-Requirements/B06-integration-requirements/INT-008-smtp-email.md)
**Participating modules:** [lms_notification](../C05-modules/lms_notification.md), core mail

## Purpose
Deliver transactional emails (notifications, certificate links, onboarding) via Drupal mail / SMTP. ⚠️ Email channel partial (R-005).

## Proposed contract
- **REST namespace:** n/a (SMTP transport via Drupal MailManager).
- **Auth:** SMTP host/credentials (config/env) — SENSITIVE (R-001).
- **Authorization:** system-initiated on events; per-user opt-in (`user.field_receive_notifications`).
- **Response envelope (internal):** mail send result logged; in-app notification still recorded regardless.
- **Audit:** log send success/failure; retry on cron; track bounces.

## Test requirements
- CI: Drupal test mail collector (no real SMTP) asserting subject/body/recipient.
- Auth+authz: respect per-user notification preference.
- Failure/fallback: send failure → queue/retry; in-app fallback.
- Contract test: enroll/cert/order emails rendered + queued.
