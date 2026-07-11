# Service Contract - app-drupal → ext-smtp

| Aspect | Contract |
| ------ | -------- |
| Protocol | SMTP (Drupal mail system, INT-008) |
| Direction | outbound only (fire-and-forget mail send; no inbound callback) |
| Auth | SMTP username/password (⚠️ in config, R-001/R-005 → Key/env) |
| Data | notification event (enrollment/certificate/order) → rendered email → recipient address |
| Idempotency | not currently enforced — a cron retry without a sent-flag can resend (target: dedupe key per notification) |
| Failure | SMTP error/timeout → email not delivered; **cron retry is TARGET, not yet implemented** ([ISSUE-0005](../../E-Quality/E02-issues/open/ISSUE-0005-email-push-stubbed.md), R-005) |
| Observability | log send attempt/result per notification; no email body content logged |

> Channel dispatch is partial/stubbed end-to-end — see [ISSUE-0005](../../E-Quality/E02-issues/open/ISSUE-0005-email-push-stubbed.md).
