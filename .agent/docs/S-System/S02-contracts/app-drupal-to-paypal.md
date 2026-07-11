# Service Contract - app-drupal → ext-paypal

| Aspect | Contract |
| ------ | -------- |
| Protocol | HTTPS (PayPal Orders API v2) |
| Direction | outbound order create/capture; ⚠️ inbound webhook is TARGET (R-002) |
| Auth | PayPal client ID/secret (⚠️ in config, R-001 → Key/env) |
| Data | order total/currency → PayPal order; `field_payment_id` stored |
| Idempotency | PayPal `PayPal-Request-Id` header (target); no idempotent webhook handler yet |
| Failure | decline/API error → order stays pending; buyer offered other gateway |
| Audit | log attempt/result; never log full account credentials |

> Duplicate gateway plugin exists in both `lms_paypal` and `lms_payment` — see [ISSUE-0006](../../E-Quality/E02-issues/open/ISSUE-0006-duplicate-paypal-module.md). Representative payment contract family with [app-drupal-to-stripe](app-drupal-to-stripe.md); ZaloPay analogous ([C06 INT-003](../../C-Design/C06-apis/_index.md)).
