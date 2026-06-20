# INT-008 - SMTP / email delivery

## Requirement
Deliver transactional emails (notifications F-017, certificate links, account/onboarding) via Drupal's mail system / SMTP.

## Participating modules
[lms_notification](../../C-Design/C05-modules/lms_notification.md), core mail

## Contract expectations
1. **Auth/authz:** SMTP host/credentials — SENSITIVE (R-001); server-side.
2. **Failure/retry/fallback:** on send failure, queue/retry via cron; failures logged; in-app notification still recorded so the user is not left unaware.
3. **CI test fixture/stub:** Drupal test mail collector (no real SMTP) asserting message contents.
4. **Operational owner + alerting:** Ops owner; alert on bounce/failure spikes. ⚠️ As-built: email channel **partial/stubbed** (R-005).

## Design link
[C06 INT-008](../../C-Design/C06-apis/INT-008-smtp-email.md)
