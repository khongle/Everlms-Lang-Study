---
usecase_id: UC-WF004-001
workflow: WF-004
title: Student purchases a paid course via Stripe
type: happy
status: as-built
---

# UC-WF004-001 — Stripe Checkout (Happy Path)

Covers the full synchronous Stripe card payment flow: from checkout page load through PaymentIntent creation, client-side card confirmation, server-side intent retrieval, order fulfilment, and enrollment.

---

## Actors

| Actor | Role |
|-------|------|
| Student | Authenticated user initiating purchase |
| System | Drupal backend — order creation, gateway dispatch, enrollment hook |
| Stripe (external) | Card payment processor (Stripe PHP SDK + Stripe.js v3) |

---

## Preconditions

1. Student is authenticated (session is valid).
2. `lms_course` entity exists with `field_price_type = paid` and `field_price > 0`.
3. `lms_payment.settings` has `enable_stripe = TRUE`, `stripe_publishable_key` and `stripe_secret_key` configured (`«secret in config — see security risk»`).
4. Composer package `stripe/stripe-php` is installed.
5. `lms_order/stripe_elements` library and external `https://js.stripe.com/v3/` are loaded.

---

## Trigger

Student navigates to `/checkout/{course_id}` where `course_id` is the numeric ID of a paid `lms_course` entity.

---

## Main Flow

| # | Given / When / Then | Entity / Code |
|---|---------------------|---------------|
| 1 | **Given** the student is authenticated and visits `/checkout/{course_id}` | Route: `lms_order.checkout` |
| 2 | **When** `CheckoutController::checkout()` runs | Loads `lms_course` by `course_id`; confirms `field_price_type = paid` (free courses are short-circuited here via `createAndFulfillFreeOrder()`); calls `CheckoutForm::buildForm()` |
| 3 | **When** `CheckoutForm::buildForm()` executes | Calls `\Stripe\PaymentIntent::create(['amount' => ..., 'currency' => 'usd'])` server-side using `stripe_secret_key` from config; receives `client_secret` in response; injects `client_secret` + `publishable_key` into `drupalSettings`; renders `lms_checkout` Twig template — **note: PaymentIntent is created on EVERY form render, not just initial load** |
| 4 | **When** the browser loads the checkout page | `stripe_elements.js` reads `drupalSettings.stripe.*`; initialises Stripe Elements card field; attaches `change` handler |
| 5 | **When** the student enters card details and clicks "Pay" | `stripe_elements.js` calls `stripe.confirmCardPayment(client_secret, {payment_method: {card: cardElement}})` in the browser; on success, sets hidden `payment_intent_id` field and calls `form.submit()` |
| 6 | **When** the form POST is received by `CheckoutForm::submitForm()` | Reads `payment_intent_id` from submitted data; resolves the `stripe` plugin via `plugin.manager.payment_gateway`; calls `StripeGateway::processPayment($order, ['payment_intent_id' => $id])` |
| 7 | **When** `StripeGateway::processPayment()` runs | Calls `\Stripe\Stripe::setApiKey($secret_key)`; calls `\Stripe\PaymentIntent::retrieve($payment_intent_id)`; checks `$intent->status === 'succeeded'`; returns `['status' => 'success', 'transaction_id' => $intent->id]` |
| 8 | **Then** `CheckoutForm::submitForm()` creates the order | Creates `lms_order` (field_status=`completed`, field_payment_method=`stripe`, field_payment_id=transaction_id, field_total=course_price); creates `lms_order_item` (field_course=course, field_type=`course`, field_price=course_price); links item to order via `field_items` |
| 9 | **Then** `lms_order_entity_insert()` / `lms_order_entity_update()` fires | `_lms_order_process_completed_order()` is called: iterates `field_items`; for `field_type=course` items, creates `lms_user_course` (field_course=course, uid=student); sends email via `plugin.manager.mail` with key `order_completed`; creates `lms_notification` entity (in-app) |
| 10 | **Then** student is redirected | `submitForm()` redirects to `<front>` (or dashboard); student is now enrolled |

---

## Entities Mutated

| Entity | Fields Changed |
|--------|---------------|
| `lms_order` | `uid`, `field_order_number`, `field_items`, `field_subtotal`, `field_total`, `field_currency`, `field_status=completed`, `field_payment_method=stripe`, `field_payment_id` |
| `lms_order_item` | `field_course`, `field_type=course`, `field_price` |
| `lms_user_course` | `uid`, `field_course` (created by `_lms_order_process_completed_order()`) |
| `lms_notification` | `uid`, notification payload (created by `_lms_order_process_completed_order()`) |

---

## Observability Hooks

| Signal | Where | Detail |
|--------|-------|--------|
| Drupal mail | `lms_order.module::hook_mail` key `order_completed` | Purchase/enrollment confirmation email sent to student |
| In-app notification | `_lms_order_process_completed_order()` | `lms_notification` entity created for student |
| Stripe SDK call (retrieve) | `StripeGateway::processPayment()` | `\Stripe\PaymentIntent::retrieve()` — observable in Stripe dashboard under "Payments" |
| No webhook | `StripeGateway::handleWebhook()` | Returns HTTP 200 with TODO comment; no async event can be received |

---

## Acceptance Link

| Acceptance Criterion | Test Definition |
|---------------------|----------------|
| [AC-037](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md#ac-037-stripe-payment-creates-a-paymentintent-server-side-before-form-render) — Stripe PaymentIntent created in buildForm | E03-TEST-WF004-001-A |
| [AC-038](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md#ac-038-stripe-payment-success-enrolls-the-student-and-marks-the-order-completed) — Stripe success triggers enrollment | E03-TEST-WF004-001-B |

---

## Notes

- **PaymentIntent on every page load**: `CheckoutForm::buildForm()` is called for every form render (including AJAX rebuilds). Each render issues a new `\Stripe\PaymentIntent::create()` call, potentially creating multiple orphaned PaymentIntents at Stripe before the user pays. This is a known bug — see [AC-037](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md#ac-037-stripe-payment-creates-a-paymentintent-server-side-before-form-render).
- **No webhook**: There are no route definitions for Stripe webhook delivery. Async payment events (e.g., 3D Secure redirects, delayed bank confirms) cannot be received. Order completion relies solely on the synchronous PaymentIntent retrieve in step 7.
- **Secrets in config**: Stripe keys are stored in plaintext in committed config YAML — `«secret in config — see security risk»`.
- `lms_paypal\Plugin\PaymentGateway\PayPalGateway` (in `lms_paypal` module) is dead code: it implements the wrong interface and carries no `@PaymentGateway` annotation; it is never loaded by `PaymentGatewayManager`.
