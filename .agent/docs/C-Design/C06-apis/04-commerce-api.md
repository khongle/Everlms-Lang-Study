# C06 — API: Commerce (Checkout, Order, Payment Gateway)

**Documentation mode:** as-built (checkout + order lifecycle + BankTransfer + Stripe real; PayPal plugin in lms_payment = stub; lms_paypal module = dead code; ZaloPay = stub; no webhook endpoints exist anywhere)

**Modules:** `lms_order`, `lms_membership`, `lms_payment`, `lms_stripe`, `lms_paypal`, `lms_wishlist`
**Entities:** [lms_order](../C04-entities/01-content-entities.md), [lms_order_item](../C04-entities/01-content-entities.md), [lms_membership_plan](../C04-entities/01-content-entities.md), [lms_user_membership](../C04-entities/01-content-entities.md)
**Module specs:** [lms_order](../C05-modules/lms_order.md), [lms_payment](../C05-modules/lms_payment.md), [lms_stripe](../C05-modules/lms_stripe.md)

---

## API Overview

**Route Group:** Public / Authenticated (HTML form routes + AJAX endpoints)
**Base paths:** `/checkout/*`, `/my-membership/*`, `/api/lms/wishlist/*`
**Authentication:** Mixed — route layer uses `_access: TRUE` or `access content`; auth enforced in-controller
**Consumers:** Web frontend (Twig-rendered checkout forms), admin workspace
**Note:** These are NOT REST/JSON endpoints (except wishlist toggle/status). Commerce flows use Drupal Form API over HTTP POST. There is no `/api/v1/` mobile commerce surface (mobile uses web checkout via browser redirect or in-app browser).

**No webhook endpoints exist.** `lms_stripe`, `lms_paypal` (lms_payment plugin), and `lms_zoom` have `handleWebhook()` stub methods that return 200 no-op. No routes are defined to receive Stripe, PayPal, or ZaloPay webhook events. This is a known critical risk — see Known Issues below.

---

## Checkout Routes

### GET+POST `/checkout/{course_id}`

**Route:** `lms_order.checkout`
**Module:** `lms_order`
**Controller:** `CheckoutController::checkout`
**Authentication:** `_access: TRUE` (route layer — no Drupal auth check)
**Authorization:** Controller redirects anonymous users to login; checks existing enrollment
**Path Parameters:**

| Param | Type | Constraint | Description |
|-------|------|-----------|-------------|
| `course_id` | integer | `\d+` | Raw int (NOT entity-upcasted) — course entity ID |

**Response:** HTML (Twig: `lms-checkout.html.twig`)

**Controller Logic (as-built):**
1. Loads `lms_course` entity; redirects to login if anonymous.
2. For `field_price_type = 'free'`: calls `createAndFulfillFreeOrder(course_id, uid)` immediately → creates `lms_order` + `lms_order_item` with `field_status = 'completed'` → triggers enrollment via `hook_entity_insert`.
3. For paid courses: calls `\Stripe\PaymentIntent::create()` **during `buildForm()`** (not on submit — one PaymentIntent per page load; see Known Issues) → passes `client_secret` + `publishable_key` to `drupalSettings` → renders `CheckoutForm` with payment method radios.
4. On form submit: creates `lms_order` + `lms_order_item`; dispatches to `plugin.manager.payment_gateway` via the selected gateway plugin; handles `success` / `pending` / `redirect` / error outcomes.

**Payment Method Selection:**
Payment method radios are loaded from `lms_payment.settings` config. Available gateways: `stripe`, `paypal`, `zalopay`, `bank_transfer`.

**Stripe Flow (as-built):**
1. `CheckoutForm::buildForm()` → `\Stripe\PaymentIntent::create()` server-side.
2. Client JS (`lms_order/stripe_elements` library + Stripe.js v3) collects card, confirms intent.
3. On submit: `payment_intent_id` hidden field set by JS.
4. `StripeGateway::processPayment()` → `\Stripe\PaymentIntent::retrieve()` → checks `status === 'succeeded'`.

**Error Responses (controller-level):**

| Condition | Behavior |
|-----------|----------|
| Course not found | 404 |
| Already enrolled | Redirect to `/learn/{course}/{lesson}` |
| Anonymous user | Redirect to `/user/login` |
| Payment failed | Form rebuilt with error message |

---

### GET+POST `/checkout/membership/{plan_id}`

**Route:** `lms_order.checkout_membership`
**Module:** `lms_order`
**Controller:** `CheckoutController::checkoutMembership`
**Authentication:** `_access: TRUE` (route layer)
**Path Parameters:**

| Param | Type | Constraint | Description |
|-------|------|-----------|-------------|
| `plan_id` | integer | `\d+` | `lms_membership_plan` entity ID (raw int) |

**Response:** HTML (Twig: `lms-checkout.html.twig`)

**Controller Logic (as-built):** Identical flow to course checkout but creates `lms_user_membership` on free fulfillment. Contains minor bug: `$order->set('field_items', ...)` called twice in `createAndFulfillFreeMembershipOrder()`.

---

### GET `/checkout/bank-transfer/{lms_order}`

**Route:** `lms_order.bank_transfer`
**Module:** `lms_order`
**Controller:** `CheckoutController::bankTransferInstruction`
**Authentication:** `_permission: access content`
**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lms_order` | entity:lms_order | Entity-upcasted order |

**Response:** HTML (Twig: `lms-bank-transfer.html.twig`)

**Controller Logic (as-built):**
- Verifies order `field_status = 'pending'`.
- Returns `BankTransferForm` with VietQR parameters from `lms_payment.settings` (bank_bin, bank_account_no, bank_account_name, bank_instructions).
- Receipt upload: `managed_file` widget, png/jpg/jpeg, 5 MB max, stored at `public://payment_receipts/YYYY-MM`.
- On submit: marks file permanent, attaches to `order.field_payment_receipt`, redirects to `<front>`.

---

## Membership Routes

### GET `/my-membership`

**Route:** `lms_membership.my_membership`
**Module:** `lms_membership`
**Controller:** `MembershipController::myMembership`
**Authentication:** `_user_is_logged_in: TRUE`
**Response:** HTML (Twig: `lms-my-membership.html.twig`)

**Logic:** Loads `lms_user_membership` entities for current user where `field_status` in `['active', 'trial']` and `field_end_date > now()`; renders membership data array.

---

### GET `/my-membership/{lms_user_membership}/cancel`

**Route:** `lms_membership.cancel_membership`
**Module:** `lms_membership`
**Controller:** `MembershipController::cancelMembership`
**Authentication:** `_user_is_logged_in: TRUE`
**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lms_user_membership` | integer | Membership ID (no entity upcast in routing.yml) |

**Logic (as-built):** Sets `field_auto_renew = FALSE` on the membership (cancel = disables auto-renew; does NOT expire immediately). Redirects to `lms_membership.my_membership`.

**Known Gap:** No route-layer ownership check. Controller should verify `membership->getOwnerId() === currentUser->id()` but this is enforced in-controller only.

---

## Wishlist AJAX Endpoints

These are the only JSON-returning routes in the commerce domain.

### POST `/api/lms/wishlist/toggle`

**Route:** `lms_wishlist.toggle`
**Module:** `lms_wishlist`
**Controller:** `WishlistController::toggle`
**Authentication:** `_user_is_logged_in: TRUE`

**Request:**
```json
{ "course_id": 42 }
```

**Response 200:**
```json
{ "status": "added" }
```
or
```json
{ "status": "removed" }
```

**Logic:** Queries `lms_wishlist` entity for `uid + field_course`. If exists: deletes (status=`removed`); else creates new (status=`added`). Invalidates cache tag `lms_wishlist:{uid}`.

---

### GET `/api/lms/wishlist/status`

**Route:** `lms_wishlist.status`
**Module:** `lms_wishlist`
**Controller:** `WishlistController::getStatus`
**Authentication:** `_user_is_logged_in: TRUE`

**Query Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `course_ids` | string | Comma-separated course IDs |

**Response 200:**
```json
{
  "42": true,
  "55": false,
  "total": 3
}
```

---

## Order Lifecycle

```
PENDING → PROCESSING → COMPLETED
                     ↘ CANCELLED / REFUNDED
```

| Status | Trigger |
|--------|---------|
| `pending` | Order entity created on checkout form submit |
| `processing` | Payment gateway accepts request |
| `completed` | Manual admin action OR Stripe PaymentIntent status confirmed synchronously via `StripeGateway::processPayment()` |
| `cancelled` | Admin action |
| `refunded` | Admin action |

**Auto-enrollment trigger:** `lms_order.module` implements `hook_entity_insert` and `hook_entity_update`. When `field_status` transitions to `completed`, `_lms_order_process_completed_order()` iterates order items and:
- Creates `lms_user_course` entity for course items.
- Creates `lms_user_membership` entity for membership plan items.
- Sends confirmation email via `plugin.manager.mail` (key: `order_completed`).
- Creates in-app `lms_notification` entity.

---

## Payment Gateway Plugin Contract

`lms_payment` hosts a Drupal Plugin manager (`PaymentGatewayManager`, `@PaymentGateway` annotation).

**Plugin interface** (`Drupal\lms_payment\Plugin\PaymentGatewayInterface`):

| Method | Signature | Description |
|--------|-----------|-------------|
| `processPayment` | `($order, array $payment_details = []): array` | Process payment; returns `['status' => 'success\|pending\|redirect\|failed', 'transaction_id' => ...]` |
| `handleWebhook` | `($request): Response` | Handle inbound webhook — **all implementations are stubs; no routes receive webhooks** |
| `getLabel` | `(): string` | Human-readable gateway name |

**Discovered plugins (runtime):**

| Plugin ID | Class | Module | Status |
|-----------|-------|--------|--------|
| `stripe` | `StripeGateway` | `lms_stripe` | REAL — `processPayment()` calls Stripe SDK `PaymentIntent::retrieve()`; `handleWebhook()` is stub |
| `bank_transfer` | `BankTransferGateway` | `lms_payment` | REAL — returns `status=pending, transaction_id=MANUAL_{order_id}` |
| `paypal` | `PayPalGateway` | `lms_payment` | STUB — logs and returns `status=success` without verifying with PayPal REST API |
| `zalopay` | `ZaloPayGateway` | `lms_payment` | STUB — redirects to hardcoded sandbox URL `https://sandbox.zalopay.vn/redirect/demo?...` |

**Dead code — lms_paypal module:** `Drupal\lms_paypal\Plugin\PaymentGateway\PayPalGateway` implements `Service\PaymentGatewayInterface` (wrong interface), has no `@PaymentGateway` annotation, and is never discovered by the plugin manager. All three methods are TODO stubs. This class is never instantiated at runtime.

**Note:** Two `PaymentGatewayInterface` definitions exist in `lms_payment`:
- `Plugin\PaymentGatewayInterface` — used by plugin system (runtime).
- `Service\PaymentGatewayInterface` — only implemented by `lms_paypal\PayPalGateway` (dead code).

**`PaymentManager` service:** Class exists (`src/Service/PaymentManager.php`) but is **not registered in `lms_payment.services.yml`** and is not wired into the DI container. Not used at runtime.

---

## Cron

`lms_membership.module` implements `hook_cron`. Queries active/trial memberships with `field_end_date <= now()`:
- If `field_auto_renew = FALSE`: sets status=`expired`, sends `membership_expired` email.
- If `field_auto_renew = TRUE`: logs and defers (no actual auto-renewal implemented — webhook receiver is missing).

---

## API Route Summary

| Method | Path | Route Name | Auth | Response | Status |
|--------|------|-----------|------|----------|--------|
| GET+POST | `/checkout/{course_id}` | `lms_order.checkout` | controller-enforced | HTML | as-built |
| GET+POST | `/checkout/membership/{plan_id}` | `lms_order.checkout_membership` | controller-enforced | HTML | as-built |
| GET | `/checkout/bank-transfer/{lms_order}` | `lms_order.bank_transfer` | `access content` | HTML | as-built |
| GET | `/my-membership` | `lms_membership.my_membership` | logged_in | HTML | as-built |
| GET | `/my-membership/{id}/cancel` | `lms_membership.cancel_membership` | logged_in | redirect | as-built |
| POST | `/api/lms/wishlist/toggle` | `lms_wishlist.toggle` | logged_in | JSON | as-built |
| GET | `/api/lms/wishlist/status` | `lms_wishlist.status` | logged_in | JSON | as-built |

**Note:** Admin workspace routes (`/workspace/pricing/*`, `/workspace/user-memberships/*`, `/admin/config/everlms/payment`) are documented in C07-sitemap / C11-pages-components, not here — they render HTML admin UIs, not APIs.

---

## Known Issues

| Ref | Severity | Description |
|-----|----------|-------------|
| RISK — no webhooks | Critical | No webhook routes exist for Stripe, PayPal, or ZaloPay. Payment confirmation relies entirely on synchronous client-side flow. Stripe `PaymentIntent::create()` is called on form BUILD (every page load), not on submit — wastes intents and creates race conditions. |
| lms_paypal dead code | High | `lms_paypal\Plugin\PaymentGateway\PayPalGateway` implements wrong interface, no annotation; never discovered by plugin manager. PayPal gateway that IS used at runtime is the stub `PayPalGateway` in `lms_payment`. |
| ZaloPay stub | High | `ZaloPayGateway::processPayment()` redirects to hardcoded sandbox URL; HMAC not implemented. |
| Stripe PaymentIntent on buildForm | Medium | `CheckoutForm::buildForm()` calls `\Stripe\PaymentIntent::create()` — generates a new intent on every page load, not just on submit. |
| Membership cancel — no ownership check at route layer | Medium | `cancel_membership` route has no entity access check; ownership must be enforced in-controller. |
| Free membership order double-set bug | Low | `createAndFulfillFreeMembershipOrder()` calls `$order->set('field_items', ...)` twice. |
| Secrets in config | Critical | Stripe keys, PayPal client_id/secret stored in `lms_payment.settings` YAML committed to repo. «secret in config — see security risk». |
