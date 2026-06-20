---
module: lms_payment
service: Commerce
route-groups: [Admin]
status: DONE
---

# C05 — Module: LMS Payment

**Directory:** `web/modules/custom/lms_payment/`
**Responsibility:** Defines the `PaymentGateway` Drupal plugin type (annotation `@PaymentGateway`, manager `PaymentGatewayManager`, interface `PaymentGatewayInterface`) and ships three built-in gateway plugins (BankTransfer, PayPal-stub, ZaloPay-stub) plus a settings form.

**Documentation mode:** as-built

---

## Route Bindings

| Route Group | Prefix | Controller / Form | Paths |
|-------------|--------|-------------------|-------|
| Admin | `/admin/config/everlms` | `PaymentSettingsForm` | `/admin/config/everlms/payment` |

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `PaymentGatewayManager` | Service (`plugin.manager.payment_gateway`) | Drupal `DefaultPluginManager`; discovers `@PaymentGateway` plugins in `Plugin/PaymentGateway/` namespace |
| `PaymentGatewayInterface` (Plugin) | Plugin Interface | `processPayment($order, array $payment_details)`, `handleWebhook($request)`, `getLabel()` |
| `PaymentGatewayBase` | Abstract Plugin Base | `getLabel()` is real; `processPayment()` and `handleWebhook()` are stubs — see status below |
| `@PaymentGateway` | Annotation | Properties: `$id`, `$label`, `$description`; required for plugin discovery |
| `BankTransferGateway` | Plugin (`bank_transfer`) | Manual transfer — returns `status=pending`; REAL for its use case |
| `PayPalGateway` (in this module) | Plugin (`paypal`) | Stub — accepts any `paypal_order_id` without server-side verification |
| `ZaloPayGateway` | Plugin (`zalopay`) | Stub — hardcoded sandbox redirect URL, no HMAC |
| `lms_payment.settings` | Config object | All gateway credentials and feature flags |

> **Two `PaymentGatewayInterface` definitions exist in this module — they are incompatible:**
> - `src/Plugin/PaymentGatewayInterface.php` (`Drupal\lms_payment\Plugin\PaymentGatewayInterface`) — used by the plugin system; correct interface.
> - `src/Service/PaymentGatewayInterface.php` (`Drupal\lms_payment\Service\PaymentGatewayInterface`) — a second, separate interface; only implemented by `lms_paypal\PayPalGateway` (dead code). NOT used by the plugin manager.

---

## Internal Structure

```
web/modules/custom/lms_payment/
+-- lms_payment.info.yml               # deps: lms_base
+-- lms_payment.routing.yml            # 1 route: settings admin page
+-- lms_payment.services.yml           # plugin.manager.payment_gateway
+-- lms_payment.links.menu.yml         # Admin menu link → Payment Gateway Settings
+-- src/
|   +-- Annotation/
|   |   +-- PaymentGateway.php         # @PaymentGateway annotation class (extends Plugin)
|   +-- Plugin/
|   |   +-- PaymentGatewayInterface.php    # Plugin interface (USED by plugin system)
|   |   +-- PaymentGatewayBase.php         # Abstract base; processPayment/handleWebhook = STUBS
|   |   +-- PaymentGatewayManager.php      # DefaultPluginManager; cache bin: lms_payment_payment_gateway_plugins
|   |   +-- PaymentGateway/
|   |       +-- BankTransferGateway.php    # REAL — manual flow; returns pending + MANUAL_{id}
|   |       +-- PayPalGateway.php          # STUB — no PayPal REST API call; accepts any order_id
|   |       +-- ZaloPayGateway.php         # STUB — hardcoded sandbox URL; no HMAC verification
|   +-- Service/
|   |   +-- PaymentGatewayInterface.php    # SECOND (incompatible) interface — NOT used by plugin system
|   |   +-- PaymentManager.php             # Registry class (addGateway/getGateway); NOT registered in DI
|   +-- Form/
|       +-- PaymentSettingsForm.php        # Config: enable flags + credentials for all gateways
```

### Plugin Discovery

```
PaymentGatewayManager
  → scans Plugin/PaymentGateway/ namespace for @PaymentGateway annotation
  → alter hook: lms_payment_payment_gateway_info
  → cache bin: lms_payment_payment_gateway_plugins
```

Plugins discovered at runtime:

| Plugin ID | Class (module) | Status |
|-----------|---------------|--------|
| `bank_transfer` | `BankTransferGateway` (lms_payment) | REAL — logs, returns `status=pending`, `transaction_id=MANUAL_{order_id}` |
| `paypal` | `PayPalGateway` (lms_payment) | STUB — accepts any `paypal_order_id`, no REST API call |
| `zalopay` | `ZaloPayGateway` (lms_payment) | STUB — hardcoded `https://sandbox.zalopay.vn/redirect/demo?order={id}` |
| `stripe` | `StripeGateway` (lms_stripe) | REAL — see [lms_stripe](lms_stripe.md) |

### `PaymentGatewayBase` Status

| Method | Status |
|--------|--------|
| `getLabel()` | REAL — returns `$this->pluginDefinition['label']` |
| `processPayment()` | STUB — returns `['status'=>'failed','message'=>'Payment processing not implemented.']` |
| `handleWebhook()` | STUB — returns `Response('Webhook not implemented.', 500)` |

### Settings Form — `lms_payment.settings` Config Keys

| Key | Purpose |
|-----|---------|
| `enable_stripe` | Enable Stripe gateway |
| `stripe_publishable_key` | «secret in config — see security risk» |
| `stripe_secret_key` | «secret in config — see security risk» |
| `enable_paypal` | Enable PayPal gateway |
| `paypal_client_id` | «secret in config — see security risk» |
| `paypal_secret` | «secret in config — see security risk» |
| `enable_zalopay` | Enable ZaloPay gateway |
| `zalopay_app_id`, `zalopay_key1`, `zalopay_key2` | «secret in config — see security risk» |
| `enable_bank_transfer` | Enable bank transfer |
| `bank_bin`, `bank_account_no`, `bank_account_name`, `bank_instructions` | VietQR / bank display data |

> **SECURITY RISK**: All gateway credentials are stored in `lms_payment.settings` config YAML committed to the repository. No Key module or environment variable injection is used. See the project risk register.

### Webhook Status — No Endpoints Exist

| Gateway | Webhook route | Signature verification |
|---------|--------------|----------------------|
| `stripe` | NONE | STUB — TODO comment only |
| `paypal` | NONE | STUB — returns 200 with no-op |
| `zalopay` | NONE | STUB — mentions HMAC but not implemented |
| `bank_transfer` | N/A | N/A (manual) |

**No webhook routes are defined in any module.** Payment providers cannot push async events.

### Dead Code: `PaymentManager`

`src/Service/PaymentManager.php` implements `addGateway()`, `getGateway()`, `getAvailableGateways()` as a manual registry — but is **not registered in any `services.yml`**. It exists in the codebase but is never instantiated.

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_base` | Internal | Base module dependency |
| Drupal `default_plugin_manager` | Framework | `PaymentGatewayManager` extends it |

**No circular dependencies.** `lms_payment` does not depend on `lms_order`, `lms_stripe`, or `lms_paypal`.

---

## Known Issues

| ID | Severity | Description |
|----|----------|-------------|
| BUG-TWO-INTERFACES | High | Two `PaymentGatewayInterface` definitions in this module are incompatible; `lms_paypal` implements the wrong one and is dead code |
| STUB-PAYPAL-PLUGIN | High | `PayPalGateway` in this module accepts any order ID without PayPal REST API verification |
| STUB-ZALOPAY | High | `ZaloPayGateway` uses hardcoded sandbox URL; no production path |
| DEBT-NO-WEBHOOKS | High | No webhook routes anywhere; async payment confirmation is impossible |
| DEBT-PAYMENTMANAGER-UNWIRED | Low | `PaymentManager` class exists but is not a registered DI service |
| SECURITY-CREDENTIALS-IN-CONFIG | Critical | All API keys committed in config YAML; no Key module; see security risk register |

---

## Cross-links

- Order consumer: [lms_order](lms_order.md)
- Stripe plugin: [lms_stripe](lms_stripe.md)
- PayPal dead-code module: [lms_paypal](lms_paypal.md)
- Membership consumer: [lms_membership](lms_membership.md)
