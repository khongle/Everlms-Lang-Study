# Service Contract - app-drupal → ext-zalopay

| Aspect | Contract |
| ------ | -------- |
| Protocol | HTTPS (ZaloPay Open API) |
| Direction | outbound order create; ⚠️ inbound callback HMAC verification is stub/incomplete ([ISSUE-0010](../../E-Quality/E02-issues/open/ISSUE-0010-paypal-zalopay-stub.md)) |
| Auth | ZaloPay `app_id`/`key1`/`key2` (⚠️ in config, R-001 → Key/env) |
| Data | order total/currency → ZaloPay order; `field_payment_id` stored |
| Idempotency | ZaloPay `app_trans_id` per order (target — not yet enforced) |
| Failure | decline/API error/callback mismatch → order stays pending; buyer offered other gateway |
| Audit | log attempt/result; never log raw HMAC key material |

> Lowest-criticality, stub gateway (R-008); complete after PayPal dedup ([ISSUE-0006](../../E-Quality/E02-issues/open/ISSUE-0006-duplicate-paypal-module.md)). Representative payment contract family with [app-drupal-to-stripe](app-drupal-to-stripe.md).
