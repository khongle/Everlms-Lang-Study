# Service Contract - app-drupal → ext-stripe

| Aspect | Contract |
| ------ | -------- |
| Protocol | HTTPS (Stripe PHP SDK) |
| Direction | outbound charge/intent; ⚠️ inbound webhook is TARGET (R-002) |
| Auth | Stripe secret key (⚠️ in config, R-001 → Key/env) |
| Data | order total/currency → PaymentIntent; `field_payment_id` stored |
| Idempotency | use Stripe idempotency keys + idempotent webhook (target) |
| Failure | decline/API error → order stays pending; retry/other gateway |
| Audit | log attempt/result; never log card/PAN |

> Representative payment contract; PayPal/ZaloPay analogous ([C06 INT-002/003](../../C-Design/C06-apis/_index.md)).
