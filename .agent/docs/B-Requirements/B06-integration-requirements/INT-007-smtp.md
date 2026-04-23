# INT-007 — SMTP / Mail Service

**ID:** INT-007
**Integration:** SMTP / Transactional Email
**Owner Module:** `drupal/smtp` or `symfony_mailer`
**Direction:** Outbound
**Protocol:** SMTP
**Status:** Config ready

---

## Scope

Gửi transactional emails: xác nhận đơn hàng, certificate issuance, notification events, user registration verification, membership expiry reminders.

## Connection

| Property | Value |
|----------|-------|
| Protocol | SMTP (TLS/SSL) |
| Port | 587 (TLS) hoặc 465 (SSL) |
| Auth | Username + Password hoặc OAuth (Gmail) |
| Providers | SendGrid, Mailgun, AWS SES, Gmail SMTP, custom SMTP |
| Config | Drupal SMTP module settings hoặc symfony_mailer |

## Data Exchanged

| Direction | Data |
|-----------|------|
| Outbound | From, To, Subject, HTML body, attachments (PDF certificate) |
| Inbound | Delivery status (bounce, open) — via provider webhook nếu configured |

## Email Types

| Type | Trigger | Attachment |
|------|---------|-----------|
| User registration verification | New registration | None |
| Order confirmation | Order completed | None |
| Certificate issued | Certificate generation | PDF |
| Membership expiry reminder | 7 days before expiry | None |
| Membership expired | Expiry date passed | None |
| Password reset | User request | None |
| Forum notification | New reply / topic | None |

## Authentication

- SMTP credentials stored in environment variables hoặc Drupal key module.
- OAuth 2.0 hỗ trợ cho Gmail/Microsoft 365.

## Rate Limits & Quotas

| Provider | Limit |
|----------|-------|
| SendGrid Free | 100 emails/day |
| Mailgun Free | 5,000/month |
| AWS SES | 62,000/month (from EC2) |
| Custom SMTP | Per server config |

## Failure Modes

| Scenario | Behavior |
|----------|----------|
| SMTP connection fail | Retry 3x; log error; in-app notification still created |
| Email bounce | Log bounce; admin notified nếu bounce rate cao |
| Rate limit exceeded | Queue with delay; không mất email |
| Invalid recipient | Log; continue without fail |

## Related Docs

- [WF-003 — Certificate Issuance](../B04-business-workflows/WF-003-certificate-issuance.md) — certificate email
- [WF-007 — User Registration](../B04-business-workflows/WF-007-user-registration.md) — verification email
- [WF-005 — Membership Lifecycle](../B04-business-workflows/WF-005-membership-subscription.md) — renewal reminders
