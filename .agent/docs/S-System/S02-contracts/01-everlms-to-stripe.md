# Contract: EverLMS → Stripe

**Caller:** `everlms-web` (module: `lms_stripe`)
**Callee:** Stripe API
**Protocol:** HTTPS REST
**Auth:** Bearer token — Stripe Secret Key (`stripe_secret_key` from `lms_payment.settings`)
**Handler class:** `\Drupal\lms_stripe\Plugin\PaymentGateway\StripeGateway`

---

## Endpoints Used

| Stripe Endpoint | Method | Purpose | Implemented? |
|-----------------|--------|---------|--------------|
| `https://api.stripe.com/v1/payment_intents/{id}` | GET | Retrieve PaymentIntent to confirm payment status | Yes |
| `https://api.stripe.com/v1/payment_intents` | POST | Create PaymentIntent (checkout flow) | Partial (client-side) |
| `https://api.stripe.com/v1/charges/{id}/refunds` | POST | Issue refund | NOT YET |

---

## Stripe PHP SDK

EverLMS uses `\Stripe\Stripe::setApiKey()` and `\Stripe\PaymentIntent::retrieve()` from the Stripe PHP SDK.
SDK is called directly inside `StripeGateway::processPayment()`.

---

## Retry / Timeout

| Setting | Value | Source |
|---------|-------|--------|
| HTTP timeout | Not explicitly set (Stripe SDK default: 80s) | `StripeGateway::processPayment()` |
| Retry on 5xx | Not implemented (single attempt) | Code review |
| Retry on network error | Not implemented | Code review |

> **Gap:** No retry logic exists. A failed `PaymentIntent::retrieve()` immediately returns an error to the user. Recommend wrapping in retry with exponential backoff (2 attempts, 1s base).

---

## Idempotency

- Stripe PaymentIntents are inherently idempotent by `id`.
- EverLMS passes `payment_intent_id` from client → server for verification.
- No custom `Idempotency-Key` header is set for POST calls.

---

## Error Handling

```php
// Current pattern in StripeGateway::processPayment()
try {
    $intent = \Stripe\PaymentIntent::retrieve($id);
    if ($intent->status === 'succeeded') { ... return success; }
    return error;
} catch (\Exception $e) {
    \Drupal::logger('lms_stripe')->error($e->getMessage());
    return error;
}
```

All Stripe exceptions are caught and returned as user-facing error messages.

---

## Secret Key Configuration

Config path: `lms_payment.settings` → `stripe_secret_key`
Admin UI: `/admin/config/everlms/payment`

**Security note:** Key is stored in Drupal config (database-backed). For production, consider environment variable injection or Drupal key module.

---

## Failure Mode

When Stripe API is unreachable:
- Payment verification fails → user sees error message.
- Order remains in `pending` state.
- No automatic retry or queue fallback exists.
- Manual resolution required via Stripe Dashboard + admin order management.
