# Service Contract - app-drupal → ext-fcm-apns

| Aspect | Contract |
| ------ | -------- |
| Protocol | HTTPS (Firebase Cloud Messaging / Apple Push Notification service, INT-009) |
| Direction | outbound push send only; device-token registration endpoint is **TARGET, not yet implemented** ([ISSUE-0005](../../E-Quality/E02-issues/open/ISSUE-0005-email-push-stubbed.md)) |
| Auth | FCM server key / APNs auth key (⚠️ in config, R-001/R-005 → Key/env) |
| Data | notification event → push payload → device token (`lms_mobile_api`/`lms_notification`) |
| Idempotency | not currently enforced — no dedupe key per notification yet |
| Failure | push API error/invalid token → push not delivered; no retry queue yet |
| Observability | log send attempt/result per notification; no payload content logged beyond notification type |

> Channel dispatch is partial/stubbed end-to-end, and the device-token register endpoint does not exist yet — see [ISSUE-0005](../../E-Quality/E02-issues/open/ISSUE-0005-email-push-stubbed.md).
