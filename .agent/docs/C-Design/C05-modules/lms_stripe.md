---
module: lms_stripe
service: Commerce
route-groups: []
status: DONE
---

# C05 — Module: LMS Stripe

**Directory:** `web/modules/custom/lms_stripe/`
**Responsibility:** Implements the `stripe` `@PaymentGateway` plugin that verifies Stripe PaymentIntent status server-side via the Stripe PHP SDK.

**Documentation mode:** as-built

---

## Route Bindings

| Route Group | Prefix | Controller | Paths |
|-------------|--------|-----------|-------|
| — | — | — | No routes defined |

> All Stripe UI (Elements widget, client-secret injection) is handled by `lms_order`'s `CheckoutForm` and the `lms_order/stripe_elements` JS library. `lms_stripe` contributes only the server-side plugin class.

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `StripeGateway` | Plugin (`stripe`) | `@PaymentGateway` plugin; `processPayment()` = REAL; `handleWebhook()` = STUB |

---

## Internal Structure

```
web/modules/custom/lms_stripe/
+-- lms_stripe.info.yml                     # deps: lms_payment (no services/routing/permissions)
+-- src/
    +-- Plugin/
        +-- PaymentGateway/
            +-- StripeGateway.php           # Only PHP file in this module
```

### `StripeGateway.php`

**Annotation:**
```
@PaymentGateway(
  id = "stripe",
  label = @Translation("Stripe"),
  description = @Translation("Process payments via Stripe Checkout.")
)
```

Extends: `PaymentGatewayBase` (from `lms_payment`)

| Method | Implementation | Status |
|--------|---------------|--------|
| `processPayment($order, array $payment_details)` | Reads `stripe_secret_key` from `lms_payment.settings` («secret in config — see security risk»); calls `\Stripe\Stripe::setApiKey()`; calls `\Stripe\PaymentIntent::retrieve($payment_details['payment_intent_id'])`; returns `status=success` if `$intent->status === 'succeeded'`, else `status=error` | REAL |
| `handleWebhook($request)` | Logs receipt; returns HTTP 200. `// TODO: Implement webhook verification.` comment present | STUB — no `Stripe\WebhookSignature::verifyHeader()` call |

### Runtime Stripe Payment Flow

```
1. CheckoutForm::buildForm()   → \Stripe\PaymentIntent::create()    [BUG: runs on every page load]
                                 → client_secret injected via drupalSettings
2. Browser (stripe_elements.js) → stripe.confirmCardPayment(clientSecret)
                                 → sets hidden field payment_intent_id on success
3. CheckoutForm::submitForm()  → plugin.manager.payment_gateway → StripeGateway::processPayment()
4. StripeGateway              → \Stripe\PaymentIntent::retrieve(payment_intent_id)
                                 → checks status === 'succeeded'
5. On success                  → order status = completed → auto-enrollment fires
```

> The PaymentIntent is created in `lms_order/CheckoutForm::buildForm()` (not here), which is a build-time bug. See [lms_order](lms_order.md) §Known Issues.

### Composer Dependency

Uses `\Stripe\Stripe` and `\Stripe\PaymentIntent` — requires `stripe/stripe-php` package in `composer.json`. Version: **v19.4.1** (confirmed in `composer.lock`).

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_payment` | Internal | `PaymentGatewayBase`, `@PaymentGateway` annotation, `PaymentGatewayManager` discovery, `lms_payment.settings` config |
| `stripe/stripe-php` | External (Composer) | Stripe PHP SDK |

---

## Known Issues

| ID | Severity | Description |
|----|----------|-------------|
| STUB-STRIPE-WEBHOOK | High | `handleWebhook()` logs and returns 200 — no Stripe webhook signature verification (`Stripe\WebhookSignature::verifyHeader()`). Stripe cannot deliver async events. |
| DEBT-SECRET-IN-CONFIG | Critical | `stripe_secret_key` read from `lms_payment.settings` YAML — committed to repository, no Key module. «secret in config — see security risk» |
| DEBT-NO-WEBHOOK-ROUTE | High | No route defined in `lms_stripe` or any other module to receive Stripe webhook callbacks |
| NOTE-BUILD-TIME-INTENT | High | PaymentIntent creation bug is in `lms_order/CheckoutForm::buildForm()`, not here — but affects Stripe directly |

---

## Cross-links

- Plugin host: [lms_payment](lms_payment.md)
- Order consumer (Stripe Elements + buildForm bug): [lms_order](lms_order.md)
