---
workflow: WF-004
slug: order-checkout
mode: as-built
b04_source: B04/WF-004-order-management.md
adequacy: NOT_RUN
adequacy_run: null
---

# C12 — Workflow: WF-004 Order & Checkout

**Trigger:** Student visits `/checkout/{course_id}` (course) or `/checkout/membership/{plan_id}` (membership). Anonymous users are redirected to login first.
**Outcome:** `lms_order` entity with `field_status=completed`; `lms_user_course` (enrollment) or `lms_user_membership` created; confirmation email and in-app notification sent.
**B04 source:** [`../../B-Requirements/B04-business-workflows/WF-004-order-management.md`](../../B-Requirements/B04-business-workflows/WF-004-order-management.md)
**Mode:** as-built

---

## Actors

| Actor (C02 role) | Step participation | Permissions needed |
|---|---|---|
| Student (authenticated) | Steps 1–8 | `_user_is_logged_in` (redirected if anonymous) |
| System | Steps 2a (free), 6–8 (fulfillment) | — |
| Stripe | Step 5 (real PaymentIntent verify) | External API; «secret in config — see security risk» |
| Bank Transfer / Admin | Steps 3–5 bank path; admin approval | `administer lms_order` for admin step |
| PayPal (lms_payment plugin) | Step 5 — **STUB** | No server-side verification |
| ZaloPay | Step 5 — **STUB** | Hardcoded sandbox redirect |

## Entry Points

| URL | Page (C11) | Method | Form | Feature Flag |
|---|---|---|---|---|
| `/checkout/{course_id}` | checkout | GET+POST | `CheckoutForm` | — |
| `/checkout/membership/{plan_id}` | checkout-membership | GET+POST | `MembershipCheckoutForm` | — |
| `/checkout/bank-transfer/{lms_order}` | bank-transfer | GET+POST | `BankTransferForm` | — |
| `/manage/orders` | admin-orders | GET | OrderListBuilder | `administer lms_order` |
| `/manage/orders/{id}` | admin-order-detail | GET+POST | ContentEntityForm | `administer lms_order` |

## Entities Touched

| Entity | Operation | Fields Written | State Transition |
|---|---|---|---|
| `lms_order` | CREATE | `field_order_number`, `field_items`, `field_subtotal`, `field_total`, `field_currency`, `field_status`, `field_payment_method`, `field_payment_id`, `field_payment_receipt` (bank transfer) | `pending` → `completed` / `cancelled` |
| `lms_order_item` | CREATE | `field_course` or `field_membership_plan`, `field_price`, `field_type` | — |
| `lms_user_course` | CREATE (on fulfillment) | `uid`, `field_course`, `field_status=enrolled`, `field_progress=0`, `field_enrolled_at` | — → `enrolled` |
| `lms_user_membership` | CREATE (on membership fulfillment) | `uid`, `field_plan`, `field_start_date`, `field_end_date`, `field_status=active`, `field_auto_renew=TRUE` | — → `active` |
| `lms_notification` | CREATE | type=`enrollment` or `payment`, `field_channel=[in_app]` | — |
| Drupal managed file | CREATE (bank transfer) | Receipt image at `public://payment_receipts/YYYY-MM/` | — |

## State Machine: lms_order.field_status

```
  [student submits checkout]
       │
       ├──▶ Free course/plan:
       │       createAndFulfillFreeOrder() / createAndFulfillFreeMembershipOrder()
       │         └──▶ completed (atomic; no payment gateway)
       │
       ├──▶ Stripe card:
       │       PaymentIntent confirmed client-side
       │         │
       │         ├──▶ succeeded: StripeGateway returns status=success
       │         │       └──▶ completed
       │         └──▶ failed: error response → order NOT created
       │
       ├──▶ Bank transfer:
       │       BankTransferGateway returns status=pending
       │         └──▶ pending
       │               │ [student uploads receipt → BankTransferForm]
       │               │ [admin reviews → sets field_status=completed in /manage/orders]
       │               └──▶ completed (via hook_entity_update)
       │
       ├──▶ PayPal (STUB):
       │       Accepts any paypal_order_id, no server-side verify → completed (unsafe)
       │
       └──▶ ZaloPay (STUB):
               Hardcoded sandbox redirect → returns status=redirect

cancelled  ←── Admin manual action (terminal)
refunded   ←── Admin manual action (terminal; no automated access revocation)
processing ←── Defined in field; not set by any code path (dead state)
```

State guards:

| Transition | Trigger | Guard | Side Effects |
|---|---|---|---|
| `pending → completed` | Admin edits order in `/manage/orders` + saves | `administer lms_order` | `hook_entity_update` → `_lms_order_process_completed_order()` |
| `— → completed` | `submitForm()` Stripe success OR free order | `StripeGateway status=succeeded` OR `field_price=0` | `hook_entity_insert` → `_lms_order_process_completed_order()` |
| `completed` (fulfillment) | `_lms_order_process_completed_order()` | `field_status=completed` detected | Creates `lms_user_course` or `lms_user_membership`; sends email; creates in-app notification |
| `— → cancelled` / `refunded` | Admin manual only | `administer lms_order` | No automated access revocation — admin must handle manually |

## Fulfillment Logic: `_lms_order_process_completed_order()`

```
lms_order.module (_lms_order_process_completed_order):
  For each $order->field_items (lms_order_item):
    if field_type = 'course':
      CREATE lms_user_course (uid, field_course, field_status=enrolled, ...)
    if field_type = 'membership':
      CREATE lms_user_membership (uid, field_plan, field_start_date=now,
                                   field_end_date=now+field_duration_days, field_status=active)
  plugin.manager.mail → hook_mail key 'order_completed' → confirmation email
  CREATE lms_notification (type=enrollment or payment, channel=[in_app])
```

## Payment Gateway Reference

| Gateway | Plugin ID | Annotation | `processPayment()` | `handleWebhook()` | Webhook Route |
|---|---|---|---|---|---|
| Stripe | `stripe` | `@PaymentGateway` in `lms_stripe` | **REAL** — `\Stripe\PaymentIntent::retrieve()`, checks `status=succeeded` | **STUB** — logs, 200, no signature verify | **None** |
| Bank Transfer | `bank_transfer` | `@PaymentGateway` in `lms_payment` | **REAL** — returns `status=pending`; manual admin approval | N/A | N/A |
| PayPal (lms_payment plugin) | `paypal` | `@PaymentGateway` in `lms_payment` | **STUB** — accepts any `paypal_order_id`, logs, returns `status=success` | **STUB** | **None** |
| ZaloPay | `zalopay` | `@PaymentGateway` in `lms_payment` | **STUB** — hardcoded sandbox redirect URL | **STUB** — HMAC comment, not implemented | **None** |
| `lms_paypal` module `PayPalGateway` | — | **NOT discovered** — wrong interface (`Service\PaymentGatewayInterface`), no `@PaymentGateway` annotation | **STUB** (all methods TODO) | **STUB** | — |

> **Critical**: No webhook routes exist in any module. Stripe, PayPal, and ZaloPay cannot deliver async payment events. All confirmation is synchronous (Stripe: client confirm + server retrieve; bank transfer: manual admin; PayPal: no verification).

## Stripe Flow Detail (as-built)

```
1. GET /checkout/{course_id}
   CheckoutForm::buildForm():
     \Stripe\PaymentIntent::create(['amount' => price*100, 'currency' => 'usd'])
     → client_secret + publishable_key → drupalSettings
     [BUG: creates PaymentIntent on EVERY page load/form rebuild]

2. Browser:
   stripe_elements.js: Stripe(publishable_key).confirmCardPayment(client_secret, card_data)
   → on success: sets hidden field payment_intent_id

3. POST /checkout/{course_id}
   CheckoutForm::submitForm():
     plugin.manager.payment_gateway->createInstance('stripe')
       ->processPayment($order, ['payment_intent_id' => $payment_intent_id])
         \Stripe\PaymentIntent::retrieve($payment_intent_id)
         check status === 'succeeded'
         return ['status' => 'success', 'transaction_id' => $intent->id]
   → lms_order created with field_status=completed
   → hook_entity_insert fires fulfillment
```

## Modules Involved

| Module | Responsibility in This Workflow |
|---|---|
| `lms_order` | `CheckoutController` (route handler, free order fulfillment); `CheckoutForm` / `MembershipCheckoutForm` / `BankTransferForm`; `lms_order` + `lms_order_item` entities; `hook_entity_insert/update` fulfillment trigger; `hook_mail` email |
| `lms_payment` | `PaymentGatewayManager` (plugin host); `BankTransferGateway`; `PayPalGateway` (stub); `ZaloPayGateway` (stub); `PaymentSettingsForm` config |
| `lms_stripe` | `StripeGateway::processPayment()` (real Stripe SDK); `lms_order/stripe_elements` library |
| `lms_membership` | `lms_user_membership` entity created on fulfillment; `MembershipManager::hasActiveMembership()` for access checks |
| `lms_notification` | `lms_notification` entity created on fulfillment (in_app only) |

## Permissions

| Step | Public (anon) | Student (auth) | Manager | Admin |
|---|---|---|---|---|
| GET checkout page | Redirect to login | Access | — | Access |
| POST checkout form | — | Submit | — | Submit |
| View own orders | — | `view own lms_order` | same | `administer lms_order` |
| Admin: view all orders | — | — | — | `administer lms_order` |
| Admin: approve bank transfer | — | — | — | `administer lms_order` |
| Create order | — | `create lms_order` | same | `administer lms_order` |

## Notifications

| Trigger | Recipient | Channel | Template / Key |
|---|---|---|---|
| Order `completed` | Student (buyer) | email (`hook_mail`) | `order_completed` |
| Order `completed` | Student | in-app (`lms_notification`) | type=`enrollment` or `payment` |
| Stripe/PayPal async billing event | — | **None** — no webhook endpoint | Lost |

> Email channel via `hook_mail` is real. In-app notification created directly in `_lms_order_process_completed_order()`. Push notification stub in `NotificationManager`.

## Cache Invalidation

| Step | Cache Tag(s) | Search Index | List Views |
|---|---|---|---|
| Order created / updated | No explicit cache tag (as-built) | — | `/manage/orders` (AdminHtmlRouteProvider, dynamic) |
| `lms_user_course` created | No explicit cache tag | — | `/my-courses`, `/dashboard` |
| `lms_notification` created | `lms_notification_list` (via NotificationBellBlock) | — | `/notifications` |

## Operations Queues

| Page | Filters | Owner Role |
|---|---|---|
| `/manage/orders` | status (colour-coded), payment method | `administer lms_order` |

## Editorial / Management Surfaces

| Entity / Content | Create/Edit UI | Role / Permission | State | Translation | Visible C11 Surfaces |
|---|---|---|---|---|---|
| `lms_order` | `/manage/orders/{id}` (edit-form) — admin only; creation via checkout flow | `administer lms_order` | `field_status` list_string | — | `/manage/orders` |
| `lms_payment.settings` | `/admin/config/everlms/payment` (`PaymentSettingsForm`) | `administer site configuration` | — | — | — |

## Failure Modes

| Failure | Detection | Handling |
|---|---|---|
| Anonymous access | `CheckoutController` `$account->isAnonymous()` check | Redirect to Drupal login |
| `PaymentIntent::create()` fails on form build | PHP exception in `buildForm()` | Form render fails; every reload creates orphaned intents — **known bug** |
| Card declined (client-side Stripe.js) | Stripe.js inline error | No POST sent; student retries |
| `StripeGateway` returns `status=error` | `submitForm()` checks gateway response | Order NOT created; error message shown |
| Bank transfer: receipt never uploaded | Order stays `pending` indefinitely | Admin contacts student; manual cancel or approve |
| `createAndFulfillFreeMembershipOrder()` double-set | `$order->set('field_items',...)` called twice | Second call overwrites first — minor bug, functional |
| Webhook from Stripe/PayPal/ZaloPay | No route → 404 | Async events lost; no recovery without webhook implementation |
| Order completed but enrollment email fails | `hook_mail` error logged | Enrollment still succeeds; email is best-effort |
| Refund issued | Admin sets `field_status=refunded` | No automated access revocation; must be handled manually |
| `processing` status | No code sets this; dead state in field definition | — |

## Acceptance (B05)

- [B05 acceptance criteria](../../B-Requirements/B05-acceptance-criteria/) — `lms_order` with `field_status=completed`; `lms_order_item` linked to correct course/plan; `lms_user_course` created; confirmation email sent; in-app notification created

## Known Bugs (as-built)

| Bug | Location | Impact |
|---|---|---|
| `PaymentIntent::create()` on every `buildForm()` | `CheckoutForm.php`, `MembershipCheckoutForm.php` | Orphaned Stripe PaymentIntents; cost; security concern |
| `createAndFulfillFreeMembershipOrder()` double `set('field_items')` | `CheckoutController.php` | Wasteful; second call overwrites first |
| No webhook signature verification | `StripeGateway::handleWebhook()` | Webhook body not verified (STUB, TODO in source) |
| `lms_paypal` module dead code | `lms_paypal/src/Plugin/PaymentGateway/PayPalGateway.php` | Wrong interface, no annotation — never discovered by plugin manager |
| Two `PaymentGatewayInterface` definitions | `lms_payment\Plugin\PaymentGatewayInterface` vs `lms_payment\Service\PaymentGatewayInterface` | Confusing; `lms_paypal` uses wrong one |
