# D03 — Unit Spec: PaymentGatewayInterface + PaymentManager

**Unit ID:** U-027
**Group:** G-COMMERCE
**Title:** PaymentGatewayInterface + PaymentManager
**Status:** DONE
**Priority:** P0

---

## Scope

Implements the plugin architecture for payment gateways in the `lms_payment` module. Defines `PaymentGatewayInterface` (both as a Drupal annotation-based plugin interface and as a service interface), `PaymentGatewayBase` abstract class, `PaymentGatewayManager` (plugin manager), and a legacy `PaymentManager` service for gateway dispatch. Also provides `BankTransferGateway` and `ZaloPayGateway` as built-in plugins, plus `PayPalGateway` in the same module.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_payment/lms_payment.info.yml` | Module declaration |
| `web/modules/custom/lms_payment/lms_payment.services.yml` | Service registration |
| `web/modules/custom/lms_payment/lms_payment.routing.yml` | Payment settings route |
| `web/modules/custom/lms_payment/lms_payment.links.menu.yml` | Admin menu link |
| `web/modules/custom/lms_payment/src/Plugin/PaymentGatewayInterface.php` | Plugin interface |
| `web/modules/custom/lms_payment/src/Plugin/PaymentGatewayBase.php` | Abstract base class |
| `web/modules/custom/lms_payment/src/Plugin/PaymentGatewayManager.php` | Drupal plugin manager |
| `web/modules/custom/lms_payment/src/Annotation/PaymentGateway.php` | Plugin annotation class |
| `web/modules/custom/lms_payment/src/Service/PaymentGatewayInterface.php` | Service-level interface |
| `web/modules/custom/lms_payment/src/Service/PaymentManager.php` | Gateway dispatch service |
| `web/modules/custom/lms_payment/src/Plugin/PaymentGateway/BankTransferGateway.php` | Bank transfer gateway |
| `web/modules/custom/lms_payment/src/Plugin/PaymentGateway/ZaloPayGateway.php` | ZaloPay gateway |
| `web/modules/custom/lms_payment/src/Plugin/PaymentGateway/PayPalGateway.php` | PayPal gateway (in lms_payment) |
| `web/modules/custom/lms_payment/src/Form/PaymentSettingsForm.php` | Payment settings admin form |

---

## Entities / Services / Controllers / Templates

**`PaymentGatewayInterface` (Plugin):** `processPayment($order, array $payment_details): array`, `handleWebhook($request)`

**`PaymentManager` (Service):**
- `addGateway(PaymentGatewayInterface $gateway): void`
- `getGateway(string $id): ?PaymentGatewayInterface`
- `getAvailableGateways(): array`

**Built-in gateways:** `BankTransferGateway`, `ZaloPayGateway`, `PayPalGateway` (stub)

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Plugin system registers gateways |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Gateway dispatch routes to correct implementation |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `PaymentGatewayInterface` defines contract for all gateways
- [x] `PaymentGatewayManager` resolves plugins by annotation
- [x] `PaymentManager` service dispatches to correct gateway
- [x] `BankTransferGateway` functional as fallback
- [x] Admin can configure active gateways via settings form

---

## Known Debt / Issues

- Duplicate interface pattern: both `src/Plugin/PaymentGatewayInterface.php` and `src/Service/PaymentGatewayInterface.php` exist — consolidation tracked in G-HARDENING.
- Webhook verification not implemented (U-060, U-061).

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-025 | Checkout uses PaymentManager |
| U-028 | StripeGateway implements this interface |
| U-029 | PayPalGateway implements this interface |

---

*End of U-027-payment-gateway-interface.md*
