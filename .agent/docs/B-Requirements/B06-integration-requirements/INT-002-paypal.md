---
id: INT-002
name: PayPal Payment Gateway
type: REST
direction: Outbound
status: DEPRECATED
---

# INT-002 — PayPal Payment Gateway

**Owner:** `lms_payment` module (plugin `paypal`); `lms_paypal` module (dead code)
**Documentation:** https://developer.paypal.com/api/rest/

---

## Purpose

Alternative payment gateway for students who prefer PayPal. **Current status: stub / dead-code.** No real PayPal REST API call is made at any point in the payment flow. The `lms_paypal` module is dead code and is never used at runtime.

**Implementation status — two separate code paths, both non-functional:**

| Code path | Location | Status |
|-----------|----------|--------|
| `paypal` plugin (used at runtime) | `lms_payment/src/Plugin/PaymentGateway/PayPalGateway.php` | **STUB** — accepts any `paypal_order_id` without server-side verification |
| `lms_paypal` module `PayPalGateway` class | `lms_paypal/src/Plugin/PaymentGateway/PayPalGateway.php` | **DEAD CODE** — wrong interface, no `@PaymentGateway` annotation, never discovered by plugin manager |

---

## Connection

| Property | Value |
|----------|-------|
| Endpoint (live) | `https://api-m.paypal.com` |
| Endpoint (sandbox) | `https://api-m.sandbox.paypal.com` |
| Protocol | HTTPS/REST |
| Authentication | Client ID + Client Secret → OAuth 2.0 bearer token |
| Rate Limit | Per PayPal developer plan |

### Credentials (config keys)

Stored in `lms_payment.settings` config YAML (`config/sync/lms_payment.settings.yml`):

| Config key | Description | Status |
|------------|-------------|--------|
| `paypal_client_id` | OAuth client ID (passed to client-side PayPal JS SDK) | «secret in config — see security risk» |
| `paypal_secret` | OAuth client secret (for server-side token exchange) | «secret in config — see security risk» |

**SECURITY RISK:** PayPal credentials are stored in plaintext in the committed config YAML. No Key module abstraction. See `config-inventory.md` R3.

---

## SLA

| Metric | Target | Measurement |
|--------|--------|------------|
| Latency (p95) | not measured — local dev only, no production telemetry; no real API calls made (stub) | N/A |
| Availability | not measured — no production deployment; stub does not contact PayPal | N/A |
| Throughput | not measured — stub makes zero actual API calls; volume is effectively zero | N/A |
| Error Rate | N/A | Stub always returns success |

---

## Intended Payment Flow (target state — not implemented)

The target flow (not yet built) would be:

1. Client-side PayPal JS SDK (loaded via `paypal_client_id` in drupalSettings) renders PayPal button.
2. User approves payment in PayPal popup.
3. JS receives `paypal_order_id` and submits form.
4. Server-side: exchange Client ID + Secret for OAuth bearer token via `POST https://api-m.paypal.com/v1/oauth2/token`.
5. Server-side: capture payment via `POST https://api-m.paypal.com/v2/checkout/orders/{paypal_order_id}/capture`.
6. Verify `status === 'COMPLETED'` in capture response.
7. Update order to `completed`; trigger enrollment.

---

## Actual Runtime Flow (as-built — stub)

1. Client-side PayPal JS SDK renders button (client_id from drupalSettings).
2. User approves; JS sets `paypal_order_id` hidden field and submits form.
3. `CheckoutForm::submitForm()` dispatches to `lms_payment`'s `PayPalGateway::processPayment()`.
4. Plugin checks that `paypal_order_id` is non-empty and logs a notice.
5. **No PayPal REST API call is made.** Returns `['status' => 'success']` unconditionally.
6. Order is set to `completed` with no actual payment verification.

---

## Request/Response Contract

### OAuth Token (target state — not implemented)

**Request:**

```json
{
  "grant_type": "client_credentials"
}
```

Header: `Authorization: Basic base64(client_id:client_secret)`

**Response (success):**

```json
{
  "access_token": "string — bearer token",
  "token_type": "Bearer",
  "expires_in": "integer — seconds"
}
```

### Capture Order (target state — not implemented)

**Request:** `POST https://api-m.paypal.com/v2/checkout/orders/{order_id}/capture`

Header: `Authorization: Bearer {access_token}`

**Response (success):**

```json
{
  "id": "string — PayPal order ID",
  "status": "string — 'COMPLETED'",
  "purchase_units": [
    {
      "payments": {
        "captures": [{ "id": "string — capture ID", "status": "string", "amount": {} }]
      }
    }
  ]
}
```

**Response (error):**

```json
{
  "error": { "code": "string", "message": "string" }
}
```

---

## Dead Code: lms_paypal Module

`lms_paypal/src/Plugin/PaymentGateway/PayPalGateway.php` implements `Drupal\lms_payment\Service\PaymentGatewayInterface` (a second, incompatible interface distinct from the plugin system's `Plugin\PaymentGatewayInterface`). It has no `@PaymentGateway` annotation and is never instantiated by the `PaymentGatewayManager`. All three methods are TODO placeholders:

| Method | Implementation |
|--------|----------------|
| `createPayment()` | Returns `['redirect_url' => '', 'order_id' => '']` — stub |
| `verifyPayment()` | Returns `['status' => 'pending', 'transaction_id' => '']` — stub |
| `refund()` | Returns `false` — stub |

---

## Webhook / IPN (not implemented)

No webhook or IPN route is defined in any EverLMS module. The `handleWebhook()` method on the `paypal` plugin returns HTTP 200 with a no-op body. PayPal `PAYMENT.CAPTURE.COMPLETED` / `PAYMENT.CAPTURE.REFUNDED` events cannot be received.

---

## Retry Policy

| Property | Value |
|----------|-------|
| Max Retries | None (stub returns immediately) |
| Backoff | N/A |
| Timeout | N/A |
| Retry On | N/A |

---

## Fallback Behavior

| Scenario | Behavior | Duration |
|----------|----------|----------|
| PayPal unavailable | Stub does not contact PayPal — always appears to succeed | N/A |
| Invalid paypal_order_id | Stub does not verify — still returns success | N/A |

---

## Circuit Breaker

Not implemented.

---

## Data Flow

- **Frequency:** Per checkout form submit when PayPal is selected
- **Volume:** Zero actual API calls (stub)
- **Data Sensitivity:** Confidential (credentials stored in config)
- **Encryption:** N/A (no real API calls)

---

## Feature Flag

- **Flag ID:** not required
- **Rationale:** Gateway is enabled/disabled via `enable_paypal` boolean in `lms_payment.settings` config.

---

## Related Docs

- [INT-001 — Stripe](./INT-001-stripe.md) — primary (real) payment gateway
- [B04 — WF-004 Order Management](../B04-business-workflows/WF-004-order-management.md)
- [B03 — F-004 Order/Membership](../B03-features/04-order-membership.md)
- Source: `web/modules/custom/lms_payment/src/Plugin/PaymentGateway/PayPalGateway.php` (runtime stub)
- Source: `web/modules/custom/lms_paypal/src/Plugin/PaymentGateway/PayPalGateway.php` (dead code)
