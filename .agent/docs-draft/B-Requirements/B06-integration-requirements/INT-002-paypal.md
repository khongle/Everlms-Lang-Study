# INT-002 — PayPal Payment Gateway

**ID:** INT-002
**Integration:** PayPal
**Owner Module:** `lms_paypal`
**Direction:** Outbound (API calls) + Inbound (IPN/Webhook)
**Protocol:** REST API + IPN
**Status:** Stub implemented

---

## Scope

Alternative payment gateway cho students prefer PayPal. Hỗ trợ order creation, payment capture, và refunds.

## Connection

| Property | Value |
|----------|-------|
| API Base URL | `https://api-m.paypal.com` (live) / `https://api-m.sandbox.paypal.com` (sandbox) |
| Auth | Client ID + Client Secret → OAuth 2.0 access token |
| IPN Endpoint | `/lms/paypal/ipn` |
| Webhook Endpoint | `/lms/paypal/webhook` |
| Protocol | HTTPS REST + IPN |

## Data Exchanged

| Direction | Data |
|-----------|------|
| Outbound | Create Order: amount, currency, intent=CAPTURE, return/cancel URLs |
| Outbound | Capture Order: order_id |
| Inbound | IPN notifications: payment_status, txn_id, mc_gross |
| Inbound | Webhook: PAYMENT.CAPTURE.COMPLETED, PAYMENT.CAPTURE.REFUNDED |

## Authentication & Security

- **OAuth token**: short-lived access token; refresh trước khi expire.
- **IPN verification**: POST lại PayPal để verify IPN message.
- **Webhook signature**: Verify `PayPal-Transmission-Sig` header.
- **Idempotency**: PayPal-Request-Id header cho duplicate prevention.

## Rate Limits & Quotas

| Limit | Value |
|-------|-------|
| API rate limit | Per PayPal developer plan |
| IPN retries | PayPal retries failed IPN up to 4 days |

## Failure Modes

| Scenario | Behavior |
|----------|----------|
| OAuth token expired | Auto-refresh before expiry |
| IPN verification fail | Reject; log; do not update order |
| Payment cancelled by user | Order stays pending; redirect to checkout |
| PayPal outage | Show "PayPal unavailable; try Stripe" |

## Related Docs

- [WF-004 — Order Management](../B04-business-workflows/WF-004-order-management.md)
- [INT-001 — Stripe](./INT-001-stripe.md) — primary gateway
- [AC-D01–D03](../B05-acceptance-criteria/AC-04-order-membership.md)
