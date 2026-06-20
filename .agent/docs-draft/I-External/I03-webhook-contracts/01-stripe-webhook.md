# Webhook Contract: Stripe → EverLMS

**Direction:** Stripe → EverLMS (inbound)
**Endpoint:** `POST /payment/webhook/stripe`
**Handler:** `\Drupal\lms_stripe\Plugin\PaymentGateway\StripeGateway::handleWebhook()`
**Auth:** Stripe-Signature header (HMAC-SHA256)

> **WARNING — Status: NOT YET IMPLEMENTED**
> Signature verification is NOT implemented. The current `handleWebhook()` body contains only a log statement (`// TODO: Implement webhook verification`). All inbound requests are accepted without validation. This is a critical security gap that must be addressed before production.

---

## Events Handled

| Stripe Event | EverLMS Action | Implemented? |
|--------------|---------------|--------------|
| `payment_intent.succeeded` | Mark order as paid, trigger enrollment fulfillment | Partial (via front-end flow only) |
| `charge.refunded` | Mark order as refunded, revoke access | NOT YET |
| `customer.subscription.created` | Activate membership | NOT YET |
| `customer.subscription.updated` | Update membership tier | NOT YET |
| `customer.subscription.deleted` | Deactivate membership | NOT YET |

---

## Request Headers (from Stripe)

| Header | Value | Required |
|--------|-------|----------|
| `Content-Type` | `application/json` | Yes |
| `Stripe-Signature` | `t={timestamp},v1={hmac}` | Yes (NOT YET verified) |

---

## Example Payload — payment_intent.succeeded

```json
{
  "id": "evt_1ABC123",
  "object": "event",
  "type": "payment_intent.succeeded",
  "created": 1714003200,
  "data": {
    "object": {
      "id": "pi_1ABC123",
      "amount": 299000,
      "currency": "vnd",
      "status": "succeeded",
      "metadata": {
        "order_id": "42"
      }
    }
  }
}
```

---

## Signature Verification (REQUIRED — NOT YET IMPLEMENTED)

```pseudocode
// Stripe sends: Stripe-Signature: t=<timestamp>,v1=<signature>
signed_payload = timestamp + "." + raw_body
expected_sig   = hmac_sha256(stripe_webhook_secret, signed_payload)
if not constant_time_eq(expected_sig, v1_from_header):
    return HTTP 400
if abs(now - timestamp) > 300:  // 5-minute tolerance
    return HTTP 400  // replay attack protection
```

**Implementation task:** Add `\Stripe\Webhook::constructEvent()` call in `handleWebhook()`.

---

## Response Expected by Stripe

| Response | Meaning |
|----------|---------|
| HTTP 200 | Stripe considers delivery successful |
| HTTP 4xx / 5xx | Stripe retries (up to 3 days, exponential backoff) |

---

## Idempotency

Each Stripe event has a unique `id` (e.g., `evt_1ABC123`).
EverLMS must store processed event IDs to prevent double-fulfillment on Stripe retries.
**Status: NOT YET IMPLEMENTED.**
