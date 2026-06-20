---
module: lms_order
service: Commerce
route-groups: [App, Manage, API]
status: DONE
---

# C05 — Module: LMS Order

**Directory:** `web/modules/custom/lms_order/`
**Responsibility:** Manages the full purchase lifecycle — checkout forms for courses and membership plans, order/order-item entity storage, auto-enrollment on completion, and bank-transfer receipt upload.

**Documentation mode:** as-built

---

## Route Bindings

| Route Group | Prefix | Controller / Form | Paths |
|-------------|--------|-------------------|-------|
| App (Public) | `/checkout` | `CheckoutController` | `/checkout/{course_id}`, `/checkout/membership/{plan_id}`, `/checkout/bank-transfer/{lms_order}` |
| Manage | `/manage/orders` | `AdminHtmlRouteProvider` (auto) | `/manage/orders`, `/manage/orders/{lms_order}` |

> `lms_order.checkout` and `lms_order.checkout_membership` have `_access: TRUE` — anonymous users hit them but are immediately redirected to login inside the controller. `lms_order.bank_transfer` requires `_permission: access content`.

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `Order` (`lms_order`) | Content Entity | Order aggregate; status lifecycle pending → processing → completed / cancelled / refunded |
| `OrderItem` (`lms_order_item`) | Content Entity | Line item for one course, bundle, or membership plan |
| `OrderAccessControlHandler` | Access Handler | Restricts view to owner; requires `administer lms_order` for all else |
| `_lms_order_process_completed_order()` | Module helper | Triggered by `hook_entity_insert/update`; creates `lms_user_course` / `lms_user_membership`, sends email and in-app notification |

---

## Internal Structure

```
web/modules/custom/lms_order/
+-- lms_order.info.yml           # deps: lms_base, lms_payment
+-- lms_order.routing.yml        # 3 routes: checkout, checkout_membership, bank_transfer
+-- lms_order.permissions.yml    # administer/create/view own lms_order
+-- lms_order.module             # hook_entity_insert, hook_entity_update, hook_theme, hook_mail
+-- lms_order.install            # update_10001: adds field_payment_receipt (image)
+-- lms_order.libraries.yml      # stripe_elements library (Stripe.js v3 + js/stripe_elements.js)
+-- js/
|   +-- stripe_elements.js       # Client-side: collects card, confirms PaymentIntent, sets hidden field
+-- src/
|   +-- Controller/
|   |   +-- CheckoutController.php   # checkout(), checkoutMembership(), bankTransferInstruction(),
|   |                                # createAndFulfillFreeOrder(), createAndFulfillFreeMembershipOrder()
|   +-- Form/
|   |   +-- CheckoutForm.php         # Course checkout; Stripe PaymentIntent created in buildForm()
|   |   +-- MembershipCheckoutForm.php  # Membership checkout; same Stripe flow
|   |   +-- BankTransferForm.php     # VietQR display + receipt upload (managed_file)
|   +-- Access/
|   |   +-- OrderAccessControlHandler.php
|   +-- OrderListBuilder.php         # Manage list: ID, order number, customer, total, method, status
|   +-- Entity/
|       +-- Order.php                # lms_order entity class
|       +-- OrderItem.php            # lms_order_item entity class
+-- templates/
|   +-- lms-checkout.html.twig
|   +-- lms-bank-transfer.html.twig
+-- config/install/                  # (none shipped; field_payment_receipt added via update hook)
```

### Key Entities

#### `lms_order` (base table: `lms_order`)

| Field | Type | Notes |
|-------|------|-------|
| id, uuid | auto | entity keys |
| uid | entity_reference (user) | owner; `EntityOwnerTrait` |
| field_order_number | string(32) | required |
| field_items | entity_reference (unlimited) | target: `lms_order_item` |
| field_subtotal | decimal(10,2) | default 0 |
| field_discount | decimal(10,2) | default 0 |
| field_total | decimal(10,2) | default 0, displayConfigurable |
| field_currency | string(3) | default `USD` |
| field_status | list_string | pending / processing / completed / cancelled / refunded; default pending |
| field_payment_method | string(32) | gateway plugin ID |
| field_payment_id | string(128) | external transaction ID |
| field_coupon_code | string(32) | — |
| field_reminder_sent | boolean | default FALSE |
| field_payment_receipt | image | added update_10001; stored `public://payment_receipts/YYYY-MM` |
| created, changed | timestamp | — |

#### `lms_order_item` (base table: `lms_order_item`)

| Field | Type | Notes |
|-------|------|-------|
| id, uuid | auto | — |
| field_course | entity_reference | target: `lms_course` |
| field_membership_plan | entity_reference | target: `lms_membership_plan` |
| field_price | decimal(10,2) | default 0 |
| field_type | list_string | course / bundle / membership; default course |

> No custom list_builder, no route_provider, no admin UI — managed exclusively via the Order.

### Hook Implementations

| Hook | Behavior |
|------|----------|
| `hook_entity_insert` | If `lms_order` with `field_status = completed` → calls `_lms_order_process_completed_order()` |
| `hook_entity_update` | Same — catches manual status changes |
| `_lms_order_process_completed_order()` | Iterates `field_items`: creates `lms_user_course` for course items; creates `lms_user_membership` for membership items; sends email via `plugin.manager.mail`; creates `lms_notification` entity |
| `hook_theme` | Registers `lms_checkout` (template: lms-checkout) and `lms_bank_transfer` (template: lms-bank-transfer) |
| `hook_mail` | Key `order_completed`: purchase/enrollment confirmation to user |

### Checkout Flow (Stripe path)

1. `CheckoutForm::buildForm()` calls `\Stripe\PaymentIntent::create()` server-side and injects `client_secret` + `publishable_key` into `drupalSettings`.
   **BUG**: PaymentIntent is created on every form BUILD — one intent per page load, not one per submit. See [E02 issues](../../E-Quality/E02-issues/) — «Stripe PaymentIntent created at form build time».
2. `js/stripe_elements.js` collects card details via Stripe Elements, calls `stripe.confirmCardPayment(clientSecret)`, then sets hidden `payment_intent_id` field.
3. `submitForm()` creates Order + OrderItem, then dispatches to `plugin.manager.payment_gateway` → `StripeGateway::processPayment()`.
4. `StripeGateway` calls `\Stripe\PaymentIntent::retrieve($payment_intent_id)` and checks `status === 'succeeded'`.
5. On success, order status set to `completed`; `hook_entity_update` fires auto-enrollment.

### Bank Transfer Flow

1. User selects `bank_transfer` → form shows VietQR params from `lms_payment.settings`.
2. User uploads payment receipt via `BankTransferForm` (png/jpg/jpeg, 5 MB, stored `public://payment_receipts/YYYY-MM`).
3. `BankTransferGateway::processPayment()` returns `status=pending`, transaction ID `MANUAL_{order_id}`.
4. Admin manually sets order to `completed` to trigger enrollment.

### Known Issues / Bugs

| ID | Severity | Description |
|----|----------|-------------|
| BUG-STRIPE-BUILD | High | `CheckoutForm::buildForm()` creates a Stripe PaymentIntent on every page load — unused intents accumulate; race-condition risk on back/refresh |
| BUG-FREE-MEMBERSHIP | Low | `createAndFulfillFreeMembershipOrder()` calls `$order->set('field_items', ...)` twice — second call overwrites first |
| DEBT-NO-IDEMPOTENCY | Medium | No idempotency key on payment submission — double-submit risk |
| DEBT-NO-STATE-MACHINE | Low | `field_status` is free-form list; no explicit state machine guards illegal transitions |

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_base` | Internal | Helper service, base entity traits |
| `lms_payment` | Internal | `plugin.manager.payment_gateway` service; `lms_payment.settings` config |
| `lms_membership` | Internal (runtime) | Creates `lms_user_membership` on completion |
| `lms_notification` | Internal (runtime) | Creates in-app notifications |
| `stripe/stripe-php` | External (Composer) | Stripe PHP SDK — `\Stripe\Stripe`, `\Stripe\PaymentIntent` |

**No circular dependencies.** `lms_payment` does not depend on `lms_order`.

---

## Cross-links

- Entities: [Order, OrderItem](../C04-entities/01-content-entities.md)
- Payment plugin host: [lms_payment](lms_payment.md)
- Stripe gateway: [lms_stripe](lms_stripe.md)
- Membership checkout: [lms_membership](lms_membership.md)
- Bundle data model: [lms_bundle](lms_bundle.md)
