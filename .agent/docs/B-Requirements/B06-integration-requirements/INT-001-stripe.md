# INT-001 — Stripe Payment Gateway

**ID:** INT-001
**Integration:** Stripe
**Owner Module:** `lms_stripe`
**Direction:** Outbound (API calls) + Inbound (Webhooks)
**Protocol:** REST API + HTTPS Webhook
**Status:** Stub implemented

---

## Scope

Xử lý thanh toán cho course purchases, bundle purchases, và membership subscriptions. Stripe là primary payment gateway.

## Connection

| Property | Value |
|----------|-------|
| API Base URL | `https://api.stripe.com/v1` |
| Auth | Secret API Key (server-side), Publishable Key (client-side) |
| Webhook Endpoint | `/lms/stripe/webhook` |
| Protocol | HTTPS REST + Webhook |

## Data Exchanged

| Direction | Data |
|-----------|------|
| Outbound | Create Checkout Session: amount, currency, line items, success/cancel URLs |
| Inbound | Webhook events: `payment_intent.succeeded`, `charge.refunded`, `checkout.session.completed` |

## Authentication & Security

- **API Key**: `STRIPE_SECRET_KEY` environment variable; không hardcode.
- **Webhook signature**: Verify `Stripe-Signature` header với `STRIPE_WEBHOOK_SECRET`.
- **Idempotency keys**: `order_id` dùng làm Stripe idempotency key để prevent duplicate charges.

## Rate Limits & Quotas

| Limit | Value |
|-------|-------|
| API rate limit | 100 read req/s; 100 write req/s (Stripe default) |
| Webhook retries | Stripe retries failed webhook up to 72h |

## Failure Modes

| Scenario | Behavior |
|----------|----------|
| API timeout | Order stays pending; student can retry |
| Invalid webhook signature | Reject with 400; log security event; alert admin |
| Duplicate webhook | Idempotency check: skip if order already processed |
| Stripe outage | Graceful degradation: show "Payment service unavailable" |

## Retry Policy

| Property | Value |
|----------|-------|
| Max retries (outbound) | 3 |
| Backoff | Exponential: 1s, 2s, 4s |
| Webhook: handled by | Stripe (72h retry window) |

## Related Docs

- [WF-004 — Order Management](../B04-business-workflows/WF-004-order-management.md)
- [AC-D01–D03](../B05-acceptance-criteria/AC-04-order-membership.md) — payment ACs
- [INT-002 — PayPal](./INT-002-paypal.md) — alternative gateway
