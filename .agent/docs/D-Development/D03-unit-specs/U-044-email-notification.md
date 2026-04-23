# D03 — Unit Spec: Email Notification Integration

**Unit ID:** U-044
**Group:** G-COMMUNICATION
**Title:** Email notification integration
**Status:** SPEC
**Priority:** P1
**Assigned:** unassigned

---

## Scope

Send email notifications for key events using Drupal's mail system. Emails are queued (not sent synchronously). Supports per-user opt-out preferences. Covers: course enrollment confirmation, quiz result, certificate issued, membership activation/expiry.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Service/EmailNotificationService.php` | Create | lms_notification |
| `src/Plugin/Mail/LmsMailPlugin.php` | Create | lms_notification |
| `src/Plugin/QueueWorker/EmailQueueWorker.php` | Create | lms_notification |
| `templates/email/enrollment-confirmation.html.twig` | Create | lms_notification |
| `templates/email/quiz-result.html.twig` | Create | lms_notification |
| `templates/email/certificate-issued.html.twig` | Create | lms_notification |
| `templates/email/membership-status.html.twig` | Create | lms_notification |
| `config/install/lms_notification.settings.yml` | Create | lms_notification |

---

## Interfaces / Services

- `EmailNotificationService::queue(string $type, int $userId, array $context): void`
  - Validates user email opt-in preference before queuing
  - Adds to Drupal Queue for async processing
- `EmailQueueWorker` processes queue via cron
- Uses `\Drupal::service('plugin.manager.mail')` → `drupal_mail()`
- Email templates: HTML with plain-text fallback

---

## Acceptance Criteria

- [ ] Enrollment confirmation email sent after successful checkout
- [ ] Quiz result email sent after quiz submission (pass/fail)
- [ ] Certificate issued email includes download link
- [ ] Membership activation + expiry warning emails sent
- [ ] User can opt out of each email type in profile settings
- [ ] Emails are queued, not sent synchronously (no request delay)
- [ ] No email sent to opted-out users

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | — |
| G4 Adversarial | PENDING | — |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-039 | Notification entity (trigger source) |
| U-042 | Dispatch service calls this service |
| U-036 | Certificate issued event |
| U-033 | Membership lifecycle events |

---

**Feature Flag:** None
**Note:** SMTP configuration (host, port, auth) is site-admin responsibility. Use Drupal's SMTP module contrib or core swiftmailer if available.

*End of U-044-email-notification.md*
