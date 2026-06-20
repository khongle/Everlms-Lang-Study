---
id: WF-004
name: Order & Checkout
status: APPROVED
features: [F-004]
---

# WF-004 — Order & Checkout

End-to-end flow from a student initiating checkout for a course through payment processing, order fulfillment (enrollment), and confirmation. Covers Stripe (real), bank transfer (real), PayPal (stub), and ZaloPay (stub) paths.

## Trigger

Student visits `/checkout/{course_id}` (course purchase) or `/checkout/membership/{plan_id}` (membership). Anonymous users are redirected to login first.

## Actors

| Actor | Role |
|-------|------|
| Student (authenticated) | Initiates purchase |
| System | Order creation, payment dispatch, fulfillment |
| Stripe | Real card payment gateway |
| Bank Transfer | Manual payment with receipt upload |
| PayPal (lms_payment plugin) | Stub — no server-side verification |
| ZaloPay | Stub — hardcoded sandbox redirect |

## Preconditions

- Student is authenticated.
- Course or membership plan entity exists.
- `lms_payment.settings` has at least one gateway enabled.

## Happy Path — Stripe (as-built)

| Step | Actor | Action | System Response | Modules / Code | Feature |
|------|-------|--------|----------------|----------------|---------|
| 1 | Student | GET `/checkout/{course_id}` | `CheckoutController::checkout()` loads course. For **free** courses: immediately calls `createAndFulfillFreeOrder()` — skip to step 8. For paid: builds `CheckoutForm`. | `lms_order` | F-004 |
| 2 | System | `CheckoutForm::buildForm()` | Calls `\Stripe\PaymentIntent::create()` server-side with the course price. Passes `client_secret` and `publishable_key` to `drupalSettings`. **Bug: PaymentIntent is created on every page load/rebuild, not just on submit.** | `lms_order/src/Form/CheckoutForm.php` | F-004 |
| 3 | Student | Enters card details | `stripe_elements.js` (via `lms_order/stripe_elements` library + Stripe.js v3) collects card and confirms the PaymentIntent in the browser. | `lms_order/js/stripe_elements.js` | F-004 |
| 4 | System | On form submit | `CheckoutForm::submitForm()` reads `payment_intent_id` from hidden field. Dispatches to `StripeGateway::processPayment()` via `plugin.manager.payment_gateway`. | `lms_stripe/src/Plugin/PaymentGateway/StripeGateway.php` | F-004 |
| 5 | System | `StripeGateway::processPayment()` | Calls `\Stripe\PaymentIntent::retrieve($payment_intent_id)`; checks `$intent->status === 'succeeded'`; returns `['status' => 'success', 'transaction_id' => $intent->id]`. | `lms_stripe` | F-004 |
| 6 | System | `CheckoutForm::submitForm()` — success path | Creates `lms_order` (status=`completed`) + `lms_order_item`. Sets `field_payment_method=stripe`, `field_payment_id=transaction_id`. | `lms_order` | F-004 |
| 7 | System | `hook_entity_insert` / `hook_entity_update` | `_lms_order_process_completed_order()` detects `status=completed`; creates `lms_user_course` (enrollment); sends confirmation email; creates in-app `lms_notification`. | `lms_order.module` | F-004, F-003 |
| 8 | Student | Receives confirmation | Redirect to homepage or dashboard. Email arrives via `hook_mail` key `order_completed`. | `lms_order.module` | F-004 |

## Happy Path — Bank Transfer (as-built)

| Step | Actor | Action | System Response | Code | Feature |
|------|-------|--------|----------------|------|---------|
| 1 | System | `BankTransferGateway::processPayment()` | Returns `['status' => 'pending', 'transaction_id' => 'MANUAL_{order_id}']`. Order created with `field_status=pending`. | `lms_payment` `BankTransferGateway` | F-004 |
| 2 | System | Redirect to `/checkout/bank-transfer/{lms_order}` | `CheckoutController::bankTransferInstruction()` verifies order is `pending`. Renders `BankTransferForm` with `lms_bank_transfer` theme (VietQR parameters from `lms_payment.settings`). | `lms_order` | F-004 |
| 3 | Student | Uploads payment receipt | `managed_file` widget; accepts png/jpg/jpeg up to 5 MB; stored at `public://payment_receipts/YYYY-MM/`. | `lms_order/src/Form/BankTransferForm.php` | F-004 |
| 4 | Student | Submits receipt | File marked permanent; attached to `lms_order.field_payment_receipt`. Order stays `pending`. | `BankTransferForm::submitForm()` | F-004 |
| 5 | Admin | Reviews receipt in `/manage/orders` | Admin manually changes order `field_status` to `completed`. `hook_entity_update` fires `_lms_order_process_completed_order()`. | `lms_order.module` | F-004 |

## Payment Gateway Status Summary

| Gateway | Plugin ID | Discovery | `processPayment()` | `handleWebhook()` | Webhook Route |
|---------|-----------|-----------|-------------------|-------------------|---------------|
| Stripe | `stripe` | `@PaymentGateway` in `lms_stripe` | **REAL** — retrieves PaymentIntent, checks `status=succeeded` | **STUB** — logs, returns 200, no signature verification | **None** |
| Bank Transfer | `bank_transfer` | `@PaymentGateway` in `lms_payment` | **REAL** — returns `pending`; manual admin approval flow | N/A (manual) | N/A |
| PayPal (lms_payment plugin) | `paypal` | `@PaymentGateway` in `lms_payment` | **STUB** — accepts any `paypal_order_id` without server-side API call | **STUB** — returns 200, no-op | **None** |
| ZaloPay | `zalopay` | `@PaymentGateway` in `lms_payment` | **STUB** — hardcoded sandbox redirect URL | **STUB** — HMAC comment, not implemented | **None** |
| `lms_paypal` module `PayPalGateway` | — | **NOT discovered** — wrong interface, no `@PaymentGateway` annotation | **STUB** | **STUB** | — |

**Critical gap**: No webhook endpoints are registered in any module. Stripe, PayPal, and ZaloPay cannot deliver asynchronous payment notifications. All payment confirmation is synchronous (Stripe via client-side confirm + server retrieve; bank transfer via manual admin action).

## Order State Machine (as-built)

| `field_status` value | When set | Transitions |
|----------------------|----------|-------------|
| `pending` | Order created; payment not yet confirmed | → `completed` (on manual admin action or on-submit success), → `cancelled` |
| `processing` | Available in field definition; not set by any current code path | — |
| `completed` | Payment confirmed (Stripe success) or admin manual approval (bank transfer); or free order | `_lms_order_process_completed_order()` fires; enrollment granted |
| `cancelled` | Manual admin action | Terminal |
| `refunded` | Manual admin action | Terminal |

## Error Paths

| Step | Condition | System Response | Recovery |
|------|-----------|----------------|----------|
| 2-E1 | Stripe `PaymentIntent::create()` fails on form build | Form render exception; student sees error page | Student retries; each reload creates a new orphaned PaymentIntent |
| 3-E1 | Card declined client-side | Stripe.js shows inline error; no form POST sent | Student uses different card |
| 5-E1 | PaymentIntent not `succeeded` | `StripeGateway` returns `['status' => 'error']`; order not created; student sees error | Student retries |
| 4-E1 | `createAndFulfillFreeMembershipOrder()` bug | `$order->set('field_items', ...)` called twice (minor bug) | Second set overwrites first — functional but wasteful |
| Bank-E1 | Student never uploads receipt | Order stays `pending` indefinitely | Admin contacts student; manual action |
| Webhook | Stripe/PayPal/ZaloPay sends webhook | No route to receive it; 404 | **No recovery possible** — webhooks cannot be processed |

## Success Criteria

- `lms_order` entity with `field_status=completed` and correct `field_payment_id`.
- `lms_order_item` linked to the correct course or membership plan.
- `lms_user_course` entity created for the student/course pair (enrollment).
- Confirmation email sent via `hook_mail`.
- In-app `lms_notification` entity created.

## Business Rules

- **Free courses**: `createAndFulfillFreeOrder()` bypasses the payment form entirely; order is created and set to `completed` atomically.
- **Stripe PaymentIntent timing**: `PaymentIntent::create()` is called in `buildForm()` (on every page load), not in `submitForm()`. This is a security and cost concern — creates orphaned intents on partial loads. Correct implementation should call `create()` only on submit.
- **Access grant trigger**: Enrollment and membership activation happen inside `_lms_order_process_completed_order()`, called from `hook_entity_insert` and `hook_entity_update`. Any code path that sets order to `completed` and saves will trigger enrollment.
- **No coupon/discount system**: `lms_order` has `field_discount` and `field_coupon_code` fields, but no coupon management module or validation logic is implemented in the current codebase.
- **Refund**: `refunded` is a terminal status; no automated access revocation on refund — admin must manually handle.

## Related Docs

- [WF-001 — Enrollment & Learning](./WF-001-enrollment-learning.md) — downstream of this flow
- [WF-005 — Membership Subscription](./WF-005-membership-subscription.md) — same checkout controller, different entity type
- Source: `web/modules/custom/lms_order/src/Controller/CheckoutController.php`
- Source: `web/modules/custom/lms_order/src/Form/CheckoutForm.php`
- Source: `web/modules/custom/lms_order/lms_order.module`
- Source: `web/modules/custom/lms_stripe/src/Plugin/PaymentGateway/StripeGateway.php`
- Source: `web/modules/custom/lms_payment/src/Plugin/PaymentGateway/BankTransferGateway.php`
