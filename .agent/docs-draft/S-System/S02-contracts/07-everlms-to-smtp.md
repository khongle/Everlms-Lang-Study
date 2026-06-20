# Contract: EverLMS → SMTP (Transactional Email)

**Caller:** `everlms-web` (modules: `lms_notification`, Drupal core mail system)
**Callee:** SMTP server (provider TBD — see status below)
**Protocol:** SMTP over TLS (STARTTLS or SMTPS port 465/587)
**Auth:** SMTP AUTH (username / password) or API-based relay (provider-dependent)
**Drupal module:** `drupal/smtp` ^1.4 (in `composer.json`)
**Status:** PARTIAL — `drupal/smtp` module is installed but email channel in `NotificationManager`
is not fully wired (queue worker for email dispatch is `// TODO`)

---

## Current State

```
NotificationManager::dispatch()
  ├── in_app  → ✅ Creates lms_notification entity
  ├── email   → ⚠️  Logs "Email notification triggered" — no actual send yet
  └── push    → ⚠️  Logs "Push notification triggered" — FCM not implemented
```

> **Status: PARTIAL** — `drupal/smtp` is installed but no SMTP provider credentials are
> configured in code. Email sending via `lms_notification` is logging-only until the
> queue worker is implemented and SMTP settings are filled in.

---

## Email Triggers (Intended)

| Trigger | Notification type | Default channels | Sender module |
|---------|------------------|-----------------|---------------|
| Quiz passed | `quiz_result` | `in_app`, `email` | `lms_notification` |
| Enrollment confirmed | `enrollment` | `in_app` | `lms_notification` |
| Course completion | *(planned)* | `in_app`, `email` | `lms_notification` |
| Certificate issued | *(planned)* | `in_app`, `email` | `lms_notification` |
| Password reset | Drupal core | `email` | Drupal core mail |
| Order confirmation | *(planned)* | `email` | `lms_payment` / core |

---

## SMTP Configuration (PROPOSED)

**Status: PROPOSED** — provider not yet chosen. Recommended options:

| Provider | Use case | Notes |
|----------|----------|-------|
| SendGrid | Production transactional | Free tier 100/day; API key auth |
| Mailgun | Production transactional | 5000/month free; SMTP or API |
| Mailtrap | Development/staging only | Catches outbound email; no real delivery |
| Server SMTP | Self-hosted | Requires SPF/DKIM/DMARC setup |

Config path (once set): `smtp.settings` → `smtp_host`, `smtp_port`, `smtp_username`, `smtp_password`
Admin UI: `/admin/config/system/smtp` (provided by `drupal/smtp` module)

---

## Intended Retry Policy (PROPOSED)

**Status: PROPOSED**

| Setting | Recommended value |
|---------|-------------------|
| Queue worker retries | 3 attempts |
| Retry backoff | Exponential: 1s → 5s → 30s |
| Max send time per message | 30 seconds |
| Bounce handling | Provider webhook → flag user email as invalid |
| Queue type | Drupal `queue` (database-backed, survives restarts) |

---

## Failure Mode (PROPOSED)

| Scenario | Intended behaviour |
|----------|--------------------|
| SMTP auth failure | Log error; message stays in retry queue |
| SMTP server unreachable | Guzzle/Symfony Mailer exception caught; retry queued |
| Hard bounce | Provider webhook marks email invalid; future sends skipped |
| Soft bounce / quota exceeded | Retry with backoff up to 3× |
| All retries exhausted | Log to `lms_notification` channel; mark delivery failed |

---

## Security Notes

- SMTP password must be stored via Drupal Key module or environment variable — **not** in Drupal
  exported config (prevents accidental commit of credentials).
- Always use STARTTLS (port 587) or SMTPS (port 465). Plain SMTP (port 25) is insecure.
- Verify SPF, DKIM, and DMARC DNS records for the sending domain to avoid spam classification.

---

## Criticality

**Tier 2** — Email notifications are user-facing but asynchronous.
Transactional email failure does not block the core learning flow.
In-app notifications continue to function when email is down.
