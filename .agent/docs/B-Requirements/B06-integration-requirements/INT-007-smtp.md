---
id: INT-007
name: SMTP / Transactional Email
type: REST
direction: Outbound
status: APPROVED
---

# INT-007 — SMTP / Transactional Email

**Owner:** Drupal `smtp` contrib module (`drupal/smtp`)
**Documentation:** <https://www.drupal.org/project/smtp>

Outbound transactional email via SMTP relay. The `smtp` module is **installed** and its configuration file (`smtp.settings.yml`) is present in config sync, but **SMTP is currently disabled** — `smtp_on: false`. The system falls back to Drupal's default `php_mail` / `sendmail` transport. No SMTP host, credentials, or from-address are configured. Production enablement requires populating these settings and setting `smtp_on: true`.

## Implementation Status

| Item | Status |
|------|--------|
| `drupal/smtp` module installed | as-built |
| `smtp.settings.yml` in config sync | as-built |
| SMTP enabled (`smtp_on`) | **false — disabled** |
| SMTP host | empty (not configured) |
| SMTP credentials | empty (not configured) |
| From address | empty (not configured) |
| Fallback transport | `php_mail` / `sendmail` (Drupal core default) |
| Email notification sending | `lms_notification` — email channel is a **stub** (not implemented) |

## Connection

| Property | Value |
|----------|-------|
| Protocol | SMTP (intended) |
| Port | `25` (currently set; production should use `587` TLS or `465` SSL) |
| Encryption | `standard` (currently set; production should use `tls` or `ssl`) |
| Host | *(empty — not configured)* |
| Authentication | Username + Password — *(empty — not configured)* |
| From address | *(empty — not configured)* |
| HTML email | `false` (allow_html disabled) |
| Active mailer | `php_mail` → `sendmail` (fallback, as-built) |

Config source: `config/sync/smtp.settings.yml` + `config/sync/system.mail.yml` (default mailer: `php_mail`, DSN scheme: `sendmail`).

## SLA

| Metric | Target | Measurement |
|--------|--------|-------------|
| Latency (p95) | Provider-dependent (e.g., SendGrid < 5s, AWS SES < 3s) | APM once enabled |
| Availability | 99.9% (typical transactional provider SLA) | Health check |
| Throughput | Provider-dependent (100/day free → tens of thousands/day paid) | Per-provider quota |
| Error Rate | < 1% delivery failure target | Provider bounce reporting |

## Request/Response Contract

**Outbound email (SMTP DATA):**

```json
{
  "From": "string — configured from address",
  "To": "string — recipient email",
  "Subject": "string — email subject",
  "Body (HTML or text)": "string — message body",
  "Attachment (optional)": "binary — PDF certificate (future)"
}
```

SMTP does not use a JSON envelope; fields above describe the logical mail structure passed to the `smtp` module via Drupal's `\Drupal\Core\Mail\MailManagerInterface::mail()`.

**Response (success):**

```json
{
  "result": true
}
```

Drupal's `MailManagerInterface::mail()` returns `['result' => true]` on success.

**Response (error):**

```json
{
  "result": false
}
```

SMTP connection failures are logged via Drupal watchdog; the `smtp` module does not throw exceptions by default.

## Intended Email Types

These are the email types that SHOULD be sent when SMTP is enabled and email notification stubs in `lms_notification` are implemented:

| Type | Trigger | Attachment |
|------|---------|-----------|
| User registration | New user account created | None |
| Password reset | User-initiated reset | None |
| Order confirmation | `lms_order` status → `completed` | None |
| Certificate issued | `lms_quiz.passed` event → certificate generated | PDF (planned) |
| Membership expiry reminder | Cron — 7 days before expiry | None |
| Forum reply notification | New post in subscribed thread | None |

**Note:** `lms_notification` implements email channel as a stub — no email is actually dispatched today. Only the in-app notification channel is live. SMTP enablement must be paired with completing the `lms_notification` email channel implementation.

## Retry Policy

| Property | Value |
|----------|-------|
| Max Retries | 1 (Drupal `smtp` module retries once on SMTP connection failure) |
| Backoff | None |
| Initial Delay | N/A |
| Max Delay | N/A |
| Timeout | 30s (SMTP module default) |
| Retry On | SMTP connection failure |

## Fallback Behavior

| Scenario | Behavior | Duration |
|----------|----------|----------|
| SMTP disabled (current state) | Falls back to `php_mail`/`sendmail` — delivery depends on server MTA config | Always |
| SMTP host unreachable | Log to watchdog; `mail()` returns `['result' => false]`; no queue/retry | Per send attempt |
| Invalid credentials | Log to watchdog; mail fails silently to end user | Per send attempt |
| Rate limit exceeded | No queue/backoff implemented (as-built); mails dropped | Per send attempt |

## Circuit Breaker

| Property | Value |
|----------|-------|
| Failure Threshold | None — no circuit breaker implemented |
| Open Duration | N/A |
| Half-Open Probes | N/A |
| Recovery Criteria | N/A |

## Data Flow

- **Frequency:** Event-driven — on user/order/certificate/forum events
- **Volume:** Low-to-medium — depends on active learner count; no bulk-mail use case identified
- **Data Sensitivity:** Internal (user PII: name, email); confidential if certificate PDF attached
- **Encryption:** In-transit: TLS (when properly configured); at-rest: not applicable

## Production Enablement Checklist

To activate SMTP, the following steps are required:

1. Choose an SMTP relay provider (e.g., SendGrid, Mailgun, AWS SES, or self-hosted)
2. Configure `smtp.settings.yml`:
   - `smtp_on: true`
   - `smtp_host`: relay host
   - `smtp_port`: `587` (TLS) or `465` (SSL)
   - `smtp_protocol`: `tls` or `ssl`
   - `smtp_username` / `smtp_password`: relay credentials (**store in Key module or env var, not config YAML**)
   - `smtp_from`: sender address
   - `smtp_allowhtml: true` (recommended for formatted emails)
3. Implement email channel stubs in `lms_notification` module
4. Test with `drush smail:test` or equivalent

## Feature Flag

- **Flag ID:** Not required
- **Rationale:** Controlled by `smtp_on` setting in `smtp.settings.yml`; no separate feature flag needed.

## Cross-links

- [INT-007 is referenced by `lms_notification` module](../../C-Design/C05-modules/) —
- [WF-003 Certificate Issuance](../B04-business-workflows/WF-003-certificate-issuance.md) — certificate email trigger
