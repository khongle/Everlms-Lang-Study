---
module: lms_paypal
service: Commerce
route-groups: []
status: STUB
---

# C05 — Module: LMS PayPal

**Directory:** `web/modules/custom/lms_paypal/`
**Responsibility:** Intended to implement a PayPal payment gateway plugin — currently dead code; the class is never discovered or instantiated by the plugin system.

**Documentation mode:** as-built (stub / dead-code)

---

> **DEAD CODE — READ FIRST**
>
> `lms_paypal`'s sole PHP file (`PayPalGateway.php`) implements the **wrong interface** (`Drupal\lms_payment\Service\PaymentGatewayInterface`) and carries **no `@PaymentGateway` annotation**. The `PaymentGatewayManager` (which scans for `@PaymentGateway`) will never discover this class.
>
> The `paypal` gateway that actually runs at checkout is `Drupal\lms_payment\Plugin\PaymentGateway\PayPalGateway` inside the **`lms_payment`** module — a separate stub that is annotated and discoverable. See [lms_payment](lms_payment.md) §Plugin Discovery.

---

## Route Bindings

| Route Group | Prefix | Controller | Paths |
|-------------|--------|-----------|-------|
| — | — | — | No routes defined |

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `PayPalGateway` | Class (not a plugin) | Implements `Service\PaymentGatewayInterface`; all methods are TODO stubs; never instantiated at runtime |

---

## Internal Structure

```
web/modules/custom/lms_paypal/
+-- lms_paypal.info.yml                     # deps: lms_payment (no services/routing/permissions/module file)
+-- src/
    +-- Plugin/
        +-- PaymentGateway/
            +-- PayPalGateway.php           # Only PHP file; dead code
```

### `PayPalGateway.php` — Dead Code Detail

**Implements:** `Drupal\lms_payment\Service\PaymentGatewayInterface` (NOT `Plugin\PaymentGatewayInterface`)
**Annotation:** NONE — no `@PaymentGateway` annotation

| Method | Implementation |
|--------|---------------|
| `getId()` | Returns `'paypal'` |
| `getLabel()` | Returns `'PayPal'` |
| `createPayment(float $amount, string $currency, array $metadata)` | `// TODO: Implement PayPal order creation.` — returns `['redirect_url'=>'','order_id'=>'']` |
| `verifyPayment(array $data)` | `// TODO: Implement PayPal webhook/IPN verification.` — returns `['status'=>'pending','transaction_id'=>'']` |
| `refund(string $transactionId, float $amount)` | `// TODO: Implement PayPal refund.` — returns `FALSE` |

All three business methods are empty TODO stubs.

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_payment` | Internal | `Service\PaymentGatewayInterface` (wrong interface — root cause of dead-code status) |

---

## Implementation Status

**FULLY STUB / DEAD CODE.** This module should either be:
1. **Fixed** — implement `Plugin\PaymentGatewayInterface`, add `@PaymentGateway` annotation, implement PayPal REST API calls, and remove the duplicate `PayPalGateway` in `lms_payment`.
2. **Removed** — delete module; keep only the (also-stub) `PayPalGateway` plugin in `lms_payment` if a placeholder is needed.

---

## Known Issues

| ID | Severity | Description |
|----|----------|-------------|
| BUG-WRONG-INTERFACE | Critical | Implements `Service\PaymentGatewayInterface` instead of `Plugin\PaymentGatewayInterface` — not discoverable by `PaymentGatewayManager` |
| BUG-NO-ANNOTATION | Critical | Missing `@PaymentGateway` annotation — plugin manager scan finds nothing |
| BUG-DUPLICATE-PAYPAL | High | `lms_payment` also contains `Plugin\PaymentGateway\PayPalGateway` (different stub, IS annotated); two PayPal classes exist with conflicting designs |
| STUB-ALL-METHODS | High | `createPayment`, `verifyPayment`, `refund` are all TODO returning empty/false values |
| DEBT-NO-PAYPAL-SDK | High | No PayPal REST SDK dependency declared; no actual API integration possible as-is |

---

## Cross-links

- Plugin host (real runtime paypal gateway): [lms_payment](lms_payment.md)
- Order consumer: [lms_order](lms_order.md)
