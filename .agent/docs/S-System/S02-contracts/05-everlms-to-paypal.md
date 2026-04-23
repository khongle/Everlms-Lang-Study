# Contract: EverLMS → PayPal

**Caller:** `everlms-web` (module: `lms_paypal`)
**Callee:** PayPal REST API v2
**Protocol:** HTTPS REST
**Auth:** OAuth2 Client Credentials (Bearer token)
**Handler class:** `\Drupal\lms_paypal\Plugin\PaymentGateway\PayPalGateway`
**Status:** STUB — implementation not started

> **Status: STUB**
> `PayPalGateway` implements `PaymentGatewayInterface` but all methods return empty/false stubs.
> Every method body is a `// TODO:` comment. No actual PayPal API calls are made.
> Do NOT enable `lms_paypal` in production until implementation is complete.
> See `RISK-004` — unimplemented payment webhook verification.

---

## Planned Endpoints (PROPOSED)

**Status: PROPOSED** — none of the following are currently called by EverLMS code.

| PayPal Endpoint | Method | Purpose |
|-----------------|--------|---------|
| `https://api-m.paypal.com/v1/oauth2/token` | POST | Obtain Bearer token (Client Credentials grant) |
| `https://api-m.paypal.com/v2/checkout/orders` | POST | Create an Order (replaces deprecated Payments v1) |
| `https://api-m.paypal.com/v2/checkout/orders/{id}/capture` | POST | Capture an approved order |
| `https://api-m.paypal.com/v2/payments/captures/{id}/refund` | POST | Issue full or partial refund |

Sandbox base URL: `https://api-m.sandbox.paypal.com`

---

## Auth Flow (PROPOSED)

```
POST https://api-m.paypal.com/v1/oauth2/token
Content-Type: application/x-www-form-urlencoded
Authorization: Basic base64({client_id}:{client_secret})

grant_type=client_credentials
```

Response: `{ "access_token": "...", "expires_in": 32400 }` (9 hours TTL).

Token should be cached in Drupal State API (mirroring `lms_zoom` pattern) with a 60-second buffer
before expiry.

---

## Sandbox vs Live

| Environment | Base URL | Config key |
|-------------|----------|-----------|
| Sandbox | `https://api-m.sandbox.paypal.com` | `lms_payment.settings` → `paypal_sandbox` = `true` |
| Live | `https://api-m.paypal.com` | `lms_payment.settings` → `paypal_sandbox` = `false` |

Switch via admin UI at `/admin/config/everlms/payment`.

---

## Idempotency (PROPOSED)

PayPal supports idempotent order creation via the `PayPal-Request-Id` header:
```
PayPal-Request-Id: {unique_uuid_per_order}
```

EverLMS should generate this from `order_id` + `user_id` to prevent duplicate charges on retry.

---

## Retry / Timeout (PROPOSED)

| Setting | Recommended value |
|---------|-------------------|
| HTTP timeout | 30 seconds |
| Retry on 5xx | 2 attempts, 1s exponential backoff |
| Retry on network error | 2 attempts |
| Retry on 4xx | No (client errors are not transient) |

---

## Risk Reference

> **RISK-004:** PayPal webhook signature verification is not yet implemented.
> Inbound webhook events (`PAYMENT.CAPTURE.COMPLETED` etc.) are accepted without HMAC validation.
> This creates risk of spoofed payment confirmations.
> See `I-External/I03-webhook-contracts/` for the inbound webhook spec.

---

## Failure Mode (intended)

When PayPal API is unreachable or returns an error:
- `createPayment()` returns `['redirect_url' => '', 'order_id' => '']` (stub).
- Order remains in `pending` state; user sees error message.
- No automatic retry queue exists in current stub.
- Manual resolution via PayPal Dashboard.

**Criticality:** Tier 2 — PayPal is an alternate payment path. Stripe remains primary.
