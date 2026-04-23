# D03 — Unit Spec: Order + OrderItem Entities

**Unit ID:** U-025
**Group:** G-COMMERCE
**Title:** Order + OrderItem entities
**Status:** DONE
**Priority:** P0

---

## Scope

Implements the `lms_order` and `lms_order_item` custom content entities representing a purchase transaction and its line items. Includes access control handler, list builder, checkout controller, and checkout/bank-transfer forms. The `lms_order` module also hosts the Stripe Elements JS asset.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_order/lms_order.info.yml` | Module declaration |
| `web/modules/custom/lms_order/lms_order.install` | Schema install |
| `web/modules/custom/lms_order/lms_order.module` | Hook implementations |
| `web/modules/custom/lms_order/lms_order.permissions.yml` | Order permissions |
| `web/modules/custom/lms_order/lms_order.routing.yml` | Order and checkout routes |
| `web/modules/custom/lms_order/lms_order.libraries.yml` | Stripe Elements JS library |
| `web/modules/custom/lms_order/src/Entity/Order.php` | Order entity class |
| `web/modules/custom/lms_order/src/Entity/OrderItem.php` | OrderItem entity class |
| `web/modules/custom/lms_order/src/Access/OrderAccessControlHandler.php` | Access handler |
| `web/modules/custom/lms_order/src/OrderListBuilder.php` | Admin list builder |
| `web/modules/custom/lms_order/src/Controller/CheckoutController.php` | Checkout controller |
| `web/modules/custom/lms_order/src/Form/CheckoutForm.php` | Checkout form |
| `web/modules/custom/lms_order/src/Form/MembershipCheckoutForm.php` | Membership checkout form |
| `web/modules/custom/lms_order/src/Form/BankTransferForm.php` | Bank transfer payment form |
| `web/modules/custom/lms_order/templates/lms-checkout.html.twig` | Checkout template |
| `web/modules/custom/lms_order/templates/lms-bank-transfer.html.twig` | Bank transfer template |
| `web/modules/custom/lms_order/js/stripe_elements.js` | Stripe Elements front-end JS |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_order` (`Order extends ContentEntityBase implements EntityOwnerInterface`)
- Key fields: `uid`, `field_status` (`pending`/`processing`/`completed`/`failed`/`refunded`), `field_total`, `field_currency`, `field_payment_gateway`, `field_payment_reference`, `field_items` (ref: `lms_order_item`)

**Entity:** `lms_order_item` (`OrderItem extends ContentEntityBase`)
- Key fields: `field_order` (ref: `lms_order`), `field_item_type` (`course`/`membership`), `field_reference_id`, `field_price`, `field_quantity`

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Entity schemas install; checkout routes work |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Order create/status transition functional |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_order` entity type registered in Drupal
- [x] `lms_order_item` entity type registered in Drupal
- [x] Order status transitions: `pending` → `processing` → `completed`/`failed`
- [x] Checkout form creates Order entity on submit
- [x] Bank transfer form available as fallback payment method
- [x] Access handler restricts order access to owner and admin

---

## Known Debt / Issues

None identified.

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-005 | Course entity referenced in order items |
| U-026 | MembershipPlan referenced in order items |
| U-027 | PaymentGatewayInterface called by checkout |
| U-028 | StripeGateway processes order payment |
| U-029 | PayPalGateway processes order payment |

---

*End of U-025-order-entities.md*
