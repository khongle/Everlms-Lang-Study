# Webhook Contract: PayPal → EverLMS

**Direction:** PayPal → EverLMS (inbound)
**Endpoint:** `POST /payment/webhook/paypal`
**Handler:** `\Drupal\lms_paypal\Plugin\PaymentGateway\PayPalGateway::verifyPayment()` (stub)
**Auth:** PayPal-Transmission-Sig header (HMAC-SHA256)

> **WARNING — Status: NOT YET IMPLEMENTED**
> The PayPal gateway is a stub. `verifyPayment()` returns `['status' => 'pending', 'transaction_id' => '']` and does nothing. No webhook endpoint exists in routing. No signature verification exists. This entire contract is PROPOSED.

---

## Events to Handle (PROPOSED)

| PayPal Event | EverLMS Action | Implemented? |
|--------------|---------------|--------------|
| `PAYMENT.CAPTURE.COMPLETED` | Mark order as paid, trigger enrollment | NOT YET |
| `PAYMENT.CAPTURE.REFUNDED` | Mark order refunded, revoke access | NOT YET |
| `BILLING.SUBSCRIPTION.ACTIVATED` | Activate membership | NOT YET |
| `BILLING.SUBSCRIPTION.CANCELLED` | Deactivate membership | NOT YET |
| `BILLING.SUBSCRIPTION.UPDATED` | Update membership tier | NOT YET |

---

## Request Headers (from PayPal)

| Header | Value | Required |
|--------|-------|----------|
| `Content-Type` | `application/json` | Yes |
| `PAYPAL-TRANSMISSION-ID` | UUID | Yes |
| `PAYPAL-TRANSMISSION-TIME` | ISO 8601 | Yes |
| `PAYPAL-TRANSMISSION-SIG` | Base64 HMAC | Yes (NOT YET verified) |
| `PAYPAL-CERT-URL` | Certificate URL | Yes (NOT YET verified) |

---

## Example Payload — PAYMENT.CAPTURE.COMPLETED

```json
{
  "id": "WH-1AB23456CD789012E-3FG45678HI901234J",
  "event_type": "PAYMENT.CAPTURE.COMPLETED",
  "create_time": "2026-04-16T08:00:00Z",
  "resource_type": "capture",
  "resource": {
    "id": "2GG279541U471931P",
    "amount": {
      "currency_code": "USD",
      "value": "14.99"
    },
    "status": "COMPLETED",
    "custom_id": "order_42"
  }
}
```

---

## Signature Verification (REQUIRED — NOT YET IMPLEMENTED)

PayPal sử dụng certificate-based verification:

```pseudocode
// 1. Fetch PayPal's public cert from PAYPAL-CERT-URL (cache it)
// 2. Reconstruct signed string:
//    signed_string = transmission_id + "|" + transmission_time + "|" + webhook_id + "|" + crc32(raw_body)
// 3. Verify PAYPAL-TRANSMISSION-SIG against signed_string using cert public key
// 4. If invalid → return HTTP 400
```

**Implementation task:** Use PayPal REST SDK `WebhookEvent::validateWebhookEvent()` or manual cert verification.

---

## Response Expected by PayPal

| Response | Meaning |
|----------|---------|
| HTTP 200 | PayPal considers delivery successful |
| HTTP 4xx / 5xx | PayPal retries up to 3 days |

---

## Idempotency

Each PayPal event has a unique `id` (e.g., `WH-xxx`).
EverLMS must store processed event IDs to prevent double-fulfillment on PayPal retries.
**Status: NOT YET IMPLEMENTED.**

---

## Implementation Prerequisites

1. PayPal REST SDK installed (`composer require paypal/paypal-checkout-sdk` or REST API calls).
2. Webhook ID configured in `lms_payment.settings` PayPal section.
3. Routing entry added for `/payment/webhook/paypal`.
4. CSRF protection disabled on webhook route (external POST).
