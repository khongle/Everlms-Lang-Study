# I03-webhook-contracts - Webhook Contracts

## Inbound (payment) — ⚠️ TARGET (not implemented; R-002)
EverLMS should expose signed webhook endpoints to reconcile payment state:
| Endpoint (target) | Provider | Events | Verify |
| ----------------- | -------- | ------ | ------ |
| `POST /api/v1/payment/stripe/webhook` | Stripe | payment_intent.*, charge.refunded | Stripe signature |
| `POST /api/v1/payment/paypal/webhook` | PayPal | PAYMENT.CAPTURE.* | PayPal verify |
| `POST /api/v1/payment/zalopay/callback` | ZaloPay | order callback | HMAC |

Handlers must be idempotent and update `lms_order.field_status`/`field_payment_id`. Task [E06S02T01](../../D-Development/D04-tasks/todo/E06S02T01-add-payment-webhooks.md).

## Outbound
None today. Push to the mobile app (FCM/APNs) is via [INT-009](../../C-Design/C06-apis/INT-009-push-firebase-apns.md), not generic webhooks.
