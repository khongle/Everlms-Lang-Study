---
usecase_id: UC-001-01
workflow: WF-001-enrollment-learning
type: happy
---

# UC-001-01 — Paid Course Checkout via Stripe → Auto-Enrollment

**One-line purpose:** Student pays for a paid course with Stripe Elements; on payment success the system creates an order, enrolls the student, and sends a confirmation notification.

---

## Actors

| Actor | Role |
|-------|------|
| Student | Authenticated user purchasing a course |
| Stripe | External payment processor |
| `CheckoutController` | Orchestrates checkout page and free/paid branching |
| `CheckoutForm` | Builds payment form; creates Stripe PaymentIntent on every render |
| `StripeGateway` | `lms_stripe` plugin; verifies PaymentIntent server-side |
| `lms_order.module` | `hook_entity_update` triggers enrollment on `completed` status |

---

## Preconditions

- Student is authenticated (`_user_is_logged_in: TRUE`).
- `lms_course` entity is published with `field_price_type != free`.
- Stripe is enabled in `lms_payment.settings`; `stripe_secret_key` and `stripe_publishable_key` are configured (`«secret in config — see security risk»`).
- `stripe/stripe-php` Composer package is installed.
- Student has no existing `lms_user_course` enrollment for this course (or has one with status != completed — the flow re-enrolls without preventing duplicate progress records).

---

## Trigger

Student visits `/checkout/{course_id}` (route `lms_order.checkout`, `_access: TRUE`).

---

## Main Flow

| # | Given / When / Then |
|---|---------------------|
| 1 | **Given** the student navigates to `/checkout/{course_id}` |
| 2 | **When** `CheckoutController::checkout()` runs: loads `lms_course`, detects authenticated user, detects `field_price_type != free` |
| 3 | **Then** `CheckoutForm::buildForm()` is called; it calls `\Stripe\PaymentIntent::create()` server-side with the course price and currency; the returned `client_secret` and `publishable_key` are injected into `drupalSettings` |
| 4 | **When** the `lms_checkout` theme renders `lms-checkout.html.twig`, Stripe Elements JS (loaded from `https://js.stripe.com/v3/` via `lms_order/stripe_elements` library) mounts the card input |
| 5 | **When** the student enters card details and submits the form |
| 6 | **Then** Stripe Elements JS calls `stripe.confirmCardPayment(client_secret)` browser-side; on success, JS sets the `payment_intent_id` hidden field and submits the form POST |
| 7 | **When** `CheckoutForm::submitForm()` runs with `payment_intent_id` in POST data |
| 8 | **Then** `plugin.manager.payment_gateway->createInstance('stripe')` returns `StripeGateway` |
| 9 | **When** `StripeGateway::processPayment($order, ['payment_intent_id' => ...])` is called |
| 10 | **Then** `\Stripe\PaymentIntent::retrieve($id)` is called; `status === 'succeeded'` is asserted |
| 11 | **When** assertion passes |
| 12 | **Then** a `lms_order` entity is created with `field_status=completed`, `field_payment_method=stripe`, `field_payment_id={intent_id}`, `field_total={price}` |
| 13 | **Then** a `lms_order_item` is created with `field_course={course_id}`, `field_type=course`, `field_price={price}`; attached to the order via `field_items` |
| 14 | **When** `lms_order.module::hook_entity_update()` fires on the completed order |
| 15 | **Then** `_lms_order_process_completed_order()` iterates `field_items`; for the course item, it creates a `lms_user_course` entity with `field_course={course_id}`, `uid={student_uid}`, `field_status=enrolled`, `field_progress=0`, `field_enrolled_at={now}` |
| 16 | **Then** `plugin.manager.mail` sends `order_completed` email to the student's address |
| 17 | **Then** an in-app `lms_notification` entity is created for the student with `field_type=payment`, `field_channel=[in_app]` (title: "Order #N Completed"; as-built: `_lms_order_process_completed_order()` always uses `field_type='payment'`) |
| 18 | **Then** the student is redirected to the course page or dashboard (outcome from `CheckoutForm`) |

---

## Entities Mutated

| Entity | Field | Change |
|--------|-------|--------|
| `lms_order` | `field_status` | `completed` |
| `lms_order` | `field_payment_method` | `stripe` |
| `lms_order` | `field_payment_id` | Stripe PaymentIntent id |
| `lms_order` | `field_total` | Course price (decimal) |
| `lms_order_item` | `field_course` | Course entity reference |
| `lms_order_item` | `field_type` | `course` |
| `lms_user_course` | `field_status` | `enrolled` (created) |
| `lms_user_course` | `field_progress` | `0.00` |
| `lms_user_course` | `field_enrolled_at` | current timestamp |
| `lms_notification` | all base fields | created with type=`payment` |

---

## Observability Hooks

| Signal | Mechanism | Details |
|--------|-----------|---------|
| PaymentIntent created | Stripe dashboard + PHP log | `\Stripe\PaymentIntent::create()` in `CheckoutForm::buildForm()` — one per page load |
| Stripe retrieve success | No explicit Drupal log | `StripeGateway::processPayment()` returns success silently |
| Enrollment email | `hook_mail` `order_completed` | Sent via `plugin.manager.mail` |
| In-app notification | `lms_notification` entity | Visible in notification bell block |
| Orphaned PaymentIntents | Risk — no log | Every form render/refresh creates a new Stripe intent; orphans not cleaned up |

---

## Acceptance Link

| B05 AC | E03 Test (planned) |
|--------|--------------------|
| [AC-037](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md#ac-037-stripe-payment-creates-a-paymentintent-server-side-before-form-render) — Stripe PaymentIntent created in buildForm | TEST-037 |
| [AC-038](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md#ac-038-stripe-payment-success-enrolls-the-student-and-marks-the-order-completed) — Stripe success triggers enrollment | TEST-038 |

---

## Notes

- **Known risk:** `CheckoutForm::buildForm()` creates a Stripe PaymentIntent on every page render including page refreshes. This leaves orphaned PaymentIntents at Stripe. This should be moved to the form submit handler or use idempotency keys.
- **No webhook fallback:** If Stripe's browser-side confirmation succeeds but the form submit is interrupted, there is no async webhook to recover the enrollment — no webhook route exists in the codebase.
- **Stripe credentials in committed config:** `stripe_secret_key` is stored in `lms_payment.settings` YAML — `«secret in config — see security risk»`.
