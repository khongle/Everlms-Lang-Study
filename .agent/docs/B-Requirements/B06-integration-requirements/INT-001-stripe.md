---
id: INT-001
name: Stripe Payment Gateway
type: REST
direction: Bidirectional
status: LIVE
---

# INT-001 — Stripe Payment Gateway

**Owner:** `lms_stripe` module (plugin), `lms_order` module (checkout forms), `lms_payment` module (plugin host)
**Documentation:** https://stripe.com/docs/api

---

## Purpose

Processes card payments for course purchases and membership subscriptions. Stripe is the primary payment gateway. EverLMS uses the **PaymentIntent** flow: the server creates the intent, client-side Stripe Elements confirms it, then the server retrieves the intent to verify completion.

**Implementation status:** as-built for `processPayment()` (real Stripe SDK calls). `handleWebhook()` is a **stub** — webhook events cannot be received and the webhook endpoint does not exist. See bug section below.

---

## Connection

| Property | Value |
|----------|-------|
| Endpoint (API) | `https://api.stripe.com/v1` |
| Protocol | HTTPS/REST |
| Authentication | Secret API key (server-side); Publishable key (client-side Stripe Elements) |
| Client-side SDK | `https://js.stripe.com/v3/` (loaded via `lms_order/stripe_elements` library) |
| Rate Limit | 100 read req/s, 100 write req/s (Stripe default) |

### Credentials (config keys)

Stored in `lms_payment.settings` config YAML (`config/sync/lms_payment.settings.yml`):

| Config key | Description | Status |
|------------|-------------|--------|
| `stripe_publishable_key` | Client-side key (passed to drupalSettings) | «secret in config — see security risk» |
| `stripe_secret_key` | Server-side key (SDK auth) | «secret in config — see security risk» |

**SECURITY RISK:** Both keys are stored in plaintext in the committed config YAML. Stripe is currently in **TEST mode** (`pk_test_` / `sk_test_` prefixes). These must be moved to the Key module or environment variables before production. See `config-inventory.md` R3.

---

## SLA

| Metric | Target | Measurement |
|--------|--------|------------|
| Latency (p95) | < 2000ms | APM monitoring |
| Availability | 99.99% | Stripe SLA |
| Throughput | 100 req/s | Stripe rate limit |
| Error Rate | < 1% | Error monitoring |

---

## Payment Flow (actual runtime)

1. `CheckoutForm::buildForm()` or `MembershipCheckoutForm::buildForm()` calls `\Stripe\PaymentIntent::create()` **server-side on every page load** — this creates a new PaymentIntent per page load, not per form submit. This is a known bug (one PI per page load).
2. The `client_secret` and `publishable_key` are passed to the browser via `drupalSettings`.
3. `js/stripe_elements.js` (via `lms_order/stripe_elements` library) renders the card form and calls `stripe.confirmCardPayment(client_secret)` client-side.
4. On successful confirmation, JS sets the hidden `payment_intent_id` field and submits the form.
5. `CheckoutForm::submitForm()` dispatches to `StripeGateway::processPayment()` via the `plugin.manager.payment_gateway` plugin.
6. `StripeGateway::processPayment()` calls `\Stripe\PaymentIntent::retrieve($payment_intent_id)` and checks `$intent->status === 'succeeded'`.
7. On success: order status is set to `completed`; `lms_order.module` hook triggers enrollment and sends confirmation email/notification.

---

## Request/Response Contract

### Create PaymentIntent (server-side, step 1)

**Request (PHP SDK call in `CheckoutForm::buildForm()`):**

```json
{
  "amount": "integer — amount in smallest currency unit (e.g. cents)",
  "currency": "string — e.g. 'usd'",
  "metadata": {
    "order_id": "string — internal order reference"
  }
}
```

**Response (success):**

```json
{
  "id": "string — pi_xxxx PaymentIntent ID",
  "client_secret": "string — pi_xxxx_secret_yyyy (passed to browser)",
  "status": "string — 'requires_payment_method' initially",
  "amount": "integer",
  "currency": "string"
}
```

### Retrieve PaymentIntent (server-side verification, step 6)

**Request:** `\Stripe\PaymentIntent::retrieve($payment_intent_id)` using secret key.

**Response (success — checked by StripeGateway):**

```json
{
  "id": "string — pi_xxxx",
  "status": "string — 'succeeded' (expected)",
  "amount_received": "integer",
  "currency": "string"
}
```

**Response (error):**

```json
{
  "error": { "code": "string", "message": "string", "type": "string" }
}
```

---

## Webhook (stub — not functional)

**No webhook route is defined** in any EverLMS module. `StripeGateway::handleWebhook()` exists but returns HTTP 200 with a no-op body and a `// TODO: Implement webhook verification.` comment. Stripe webhook signature verification (`\Stripe\WebhookSignature::verifyHeader()`) is not called.

This means:
- Async payment events (delayed confirmations, disputes, refunds) cannot be received.
- Membership auto-renewal via webhook is explicitly not implemented (deferred in `lms_membership` `hook_cron` comment).

**Target state (not implemented):** A webhook endpoint at `/lms/stripe/webhook` would receive `payment_intent.succeeded`, `charge.refunded`, and related events, verify `Stripe-Signature` header, and update order/membership status accordingly.

---

## Retry Policy

| Property | Value |
|----------|-------|
| Max Retries | None (no retry logic in StripeGateway — single SDK call) |
| Backoff | N/A |
| Timeout | Stripe PHP SDK default (~30s) |
| Retry On | N/A — user must retry from checkout page |

---

## Fallback Behavior

| Scenario | Behavior | Duration |
|----------|----------|----------|
| Stripe API unreachable | Exception caught; order stays `pending`; error message shown | Until user retries |
| `status !== 'succeeded'` on retrieve | Gateway returns error; order not completed | User retries |
| No webhook endpoint | Async events are silently lost | Permanent (bug) |

---

## Circuit Breaker

Not implemented. No circuit breaker logic exists in `StripeGateway` or `lms_payment`.

---

## Data Flow

- **Frequency:** Real-time, per checkout form load and per form submit
- **Volume:** One PaymentIntent per page load of checkout form (bug: should be per submit)
- **Data Sensitivity:** Confidential — card handling delegated to Stripe Elements (PCI scope reduced); only PaymentIntent ID transits the server
- **Encryption:** In-transit: TLS 1.2+

---

## Known Bugs

| Bug | Location | Severity |
|-----|----------|----------|
| PaymentIntent created on form BUILD (every page load, not per submit) | `CheckoutForm::buildForm()`, `MembershipCheckoutForm::buildForm()` | High |
| Webhook endpoint does not exist — no route, no signature verification | `StripeGateway::handleWebhook()` | High |

---

## Feature Flag

- **Flag ID:** not required
- **Rationale:** Gateway is enabled/disabled via `enable_stripe` boolean in `lms_payment.settings` config; no feature flag needed.

---

## Related Docs

- [INT-002 — PayPal](./INT-002-paypal.md) — alternative gateway
- [B04 — WF-004 Order Management](../B04-business-workflows/WF-004-order-management.md)
- [B03 — F-004 Order/Membership](../B03-features/04-order-membership.md)
- Source: `web/modules/custom/lms_stripe/src/Plugin/PaymentGateway/StripeGateway.php`
- Source: `web/modules/custom/lms_order/src/Form/CheckoutForm.php`
