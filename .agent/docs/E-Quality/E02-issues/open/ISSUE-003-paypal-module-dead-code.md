# ISSUE-003 — lms_paypal Module is Dead Code (Wrong Interface, No @PaymentGateway Annotation)

| Field | Value |
|---|---|
| ID | `ISSUE-003` |
| Title | `lms_paypal` module implements wrong interface and lacks `@PaymentGateway` annotation — never discovered by plugin manager |
| Source | G4-Architecture / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `HIGH` |
| Affected page / route | N/A — class is never instantiated |
| Owning Epic | E-10 (G-HARDENING) |
| Owning Story | [`payment-webhooks`](../../../D-Development/D03-stories/payment-webhooks.md) |
| Owning module(s) | `lms_paypal`, `lms_payment` |

## Summary

The `lms_paypal` module ships exactly one PHP class: `Drupal\lms_paypal\Plugin\PaymentGateway\PayPalGateway`. This class has two critical defects that prevent it from being used:

1. **Wrong interface** — it implements `Drupal\lms_payment\Service\PaymentGatewayInterface` (a second, separate service interface in `lms_payment/src/Service/`), NOT `Drupal\lms_payment\Plugin\PaymentGatewayInterface` (the plugin interface required by `PaymentGatewayManager`).
2. **No `@PaymentGateway` annotation** — the class has no `@PaymentGateway(id="paypal", ...)` docblock annotation, so `PaymentGatewayManager` (which scans `Plugin/PaymentGateway/` for `@PaymentGateway` annotations) never discovers it.

As a result, `lms_paypal::PayPalGateway` is **never instantiated at runtime**. The PayPal gateway actually used by `CheckoutForm` and `MembershipCheckoutForm` is `Drupal\lms_payment\Plugin\PaymentGateway\PayPalGateway`, defined in the `lms_payment` module — a separate, correctly-annotated plugin that is itself a stub (accepts any `paypal_order_id` without server-side verification).

All three methods of `lms_paypal::PayPalGateway` (`createPayment()`, `verifyPayment()`, `refund()`) are `// TODO:` placeholders returning empty/false values.

## Reproduction

1. Open `web/modules/custom/lms_paypal/src/Plugin/PaymentGateway/PayPalGateway.php`.
2. Check the `implements` clause: `implements \Drupal\lms_payment\Service\PaymentGatewayInterface` — note `Service\` namespace prefix.
3. Check the class docblock: no `@PaymentGateway` annotation present.
4. Open `web/modules/custom/lms_payment/src/Service/PaymentGatewayManager.php` — it extends `DefaultPluginManager` scanning for `@PaymentGateway` in `Plugin/PaymentGateway/` namespace.
5. Confirm: `\Drupal::service('plugin.manager.payment_gateway')->getDefinitions()` does not include a plugin with `id='paypal'` from `lms_paypal`; the `paypal` plugin definition comes from `lms_payment`.
6. Verify all three `lms_paypal::PayPalGateway` methods contain only `// TODO: Implement ...` comments.

## Root Cause

The `lms_paypal` module appears to have been scaffolded using a **service interface** (`Service\PaymentGatewayInterface`) that predates or diverges from the final **plugin interface** (`Plugin\PaymentGatewayInterface`) adopted by `PaymentGatewayManager`. The developer who wrote `lms_paypal` likely targeted the wrong interface and never added the `@PaymentGateway` annotation. Because the module compiled and Drupal did not error (the class simply exists, it just isn't discovered), the defect went unnoticed.

The two `PaymentGatewayInterface` definitions in `lms_payment` are confusingly named and live in different sub-namespaces (`Plugin\` vs `Service\`). This architectural ambiguity is the proximate cause.

## Proposed Fix

Two viable paths:

**Option A — Fix `lms_paypal` and implement PayPal gateway (preferred):**

1. Change `implements` clause to `\Drupal\lms_payment\Plugin\PaymentGatewayInterface` (or extend `PaymentGatewayBase`).
2. Add `@PaymentGateway(id = "paypal", label = "PayPal", description = "...")` annotation.
3. Implement `processPayment()` using the PayPal REST Orders v2 API (`POST /v2/checkout/orders/{id}/capture`).
4. Remove `src/Service/PaymentGatewayInterface.php` from `lms_payment` (dead interface) to eliminate the naming confusion — or rename it to `LegacyPaymentServiceInterface` and document it as deprecated.

**Option B — Formally defer PayPal (acceptable short-term):**

1. Mark `lms_paypal` as `lifecycle: experimental` in its `info.yml`.
2. Leave dead code in place but add a docblock: `// DEAD CODE — see ISSUE-003. Do not route to this class.`
3. Document deferral in Story [`payment-webhooks`](../../../D-Development/D03-stories/payment-webhooks.md).
4. Ensure the `lms_payment` `PayPalGateway` stub is NOT presented to users as a working payment option (disable PayPal in `lms_payment.settings` until Option A is complete).

The `Service\PaymentGatewayInterface` naming ambiguity must be resolved regardless of which path is chosen, to prevent future developers from implementing the wrong interface.

## Trace links

- **Story:** `D-Development/D03-stories/payment-webhooks.md`
- **C05 modules:** `C-Design/C05-modules/lms_paypal.md`, `C-Design/C05-modules/lms_payment.md`
- **Facts base:** `modules-commerce.md` §lms_paypal, §Cross-module notes — Payment gateway architecture
- **Related issues:** [ISSUE-002](ISSUE-002-no-payment-webhook-endpoints.md) (PayPal webhook cannot be implemented until this is fixed)
