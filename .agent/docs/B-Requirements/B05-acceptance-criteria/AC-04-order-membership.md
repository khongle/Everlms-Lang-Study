---
feature: F-004
status: DRAFT
---

# B05 — Acceptance Criteria for F-004 — Order & Membership

> Feature source: `../B03-features/04-order-membership.md`
> Implementing modules: `lms_order`, `lms_membership`, `lms_payment`, `lms_stripe`, `lms_paypal`, `lms_wishlist`, `lms_bundle`
> Implementation maturity: **as-built** for course checkout (Stripe + bank transfer), membership CRUD, and wishlist. Key stubs and gaps are called out per criterion.

---

## AC-036: Free course checkout immediately enrolls the student

**Priority:** Must
**Given** a `lms_course` with `field_price_type=free` exists and the requesting user is authenticated
**When** the user visits `/checkout/{course_id}`
**Then** `CheckoutController::createAndFulfillFreeOrder()` creates a `lms_order` and `lms_order_item` with `field_status=completed` and `field_total=0`
**And** `hook_entity_insert()` in `lms_order.module` detects `field_status=completed` and creates a `lms_user_course` enrollment entity for the user
**And** a confirmation email is sent via `plugin.manager.mail` (`order_completed` key) and an in-app `lms_notification` entity is created

**Status:** as-built — `createAndFulfillFreeOrder()` and `_lms_order_process_completed_order()` are fully implemented.

---

## AC-037: Stripe payment creates a PaymentIntent server-side before form render

**Priority:** Must
**Given** a paid course checkout page is loaded by an authenticated user at `/checkout/{course_id}`
**When** `CheckoutForm::buildForm()` runs
**Then** `\Stripe\PaymentIntent::create()` is called server-side using the secret key from `lms_payment.settings` config (`«secret in config — see security risk»`)
**And** the `client_secret` and `publishable_key` are injected into `drupalSettings` for the Stripe Elements JavaScript

**Status:** as-built — Stripe PaymentIntent is created in `buildForm()`. Known risk: a new PaymentIntent is created on every form page load (including refreshes), potentially leaving orphaned intents at Stripe. Stripe test credentials are stored in plaintext committed config — `«secret in config — see security risk»`.

---

## AC-038: Stripe payment success enrolls the student and marks the order completed

**Priority:** Must
**Given** the user has confirmed a payment intent via Stripe Elements in the browser and the resulting `payment_intent_id` is submitted with the checkout form
**When** `CheckoutForm::submitForm()` calls `StripeGateway::processPayment()`
**Then** `\Stripe\PaymentIntent::retrieve($payment_intent_id)` is called and the `status === 'succeeded'` check passes
**And** the `lms_order.field_status` is set to `completed`
**And** `hook_entity_update()` triggers `_lms_order_process_completed_order()` which creates the `lms_user_course` enrollment

**Status:** as-built — `StripeGateway::processPayment()` uses the real Stripe PHP SDK.

---

## AC-039: Bank transfer checkout creates a pending order and shows upload form

**Priority:** Must
**Given** bank transfer is enabled in `lms_payment.settings` and a user selects it at checkout
**When** the user submits the checkout form with payment method `bank_transfer`
**Then** a `lms_order` entity is created with `field_status=pending` and `field_payment_method=bank_transfer`
**And** `BankTransferGateway::processPayment()` returns `status=pending`
**And** the user is redirected to `/checkout/bank-transfer/{lms_order}` where they can view VietQR payment details and upload a receipt image to `field_payment_receipt`

**Status:** as-built — `CheckoutController::bankTransferInstruction()` and `BankTransferForm` are fully implemented.

---

## AC-040: Webhook-triggered order completion — blocked — not implemented

**Priority:** Must
**Given** an external payment gateway (Stripe, PayPal, ZaloPay) sends an asynchronous webhook notification to the server confirming payment success
**When** the webhook HTTP request arrives
**Then** the system verifies the webhook signature, identifies the corresponding `lms_order`, transitions `field_status` to `completed`, and triggers enrollment

**Status:** **blocked — not implemented.** No webhook endpoints (routes) exist anywhere in the codebase. `StripeGateway::handleWebhook()` is a stub returning 200 with a TODO comment; `PayPalGateway::handleWebhook()` (in `lms_payment`) is also a no-op stub; `ZaloPayGateway::handleWebhook()` has a comment mentioning HMAC but no implementation. Order completion for Stripe currently relies entirely on the synchronous server-side PaymentIntent retrieval in `processPayment()`. Async payment confirmation (e.g., for 3DS redirects) cannot be received.

---

## AC-041: PayPal payment is a stub — not verified server-side

**Priority:** Must
**Given** PayPal is enabled in `lms_payment.settings` and a user selects it at checkout
**When** the user submits the checkout form with a `paypal_order_id` value
**Then** `lms_payment`'s `PayPalGateway::processPayment()` accepts the order ID **without** making any REST API call to PayPal to verify the transaction

**Status:** **blocked — not implemented (stub).** The `paypal` plugin in `lms_payment` logs a message and returns `status=success` for any `paypal_order_id` value. No PayPal SDK calls are made. The `lms_paypal` module's `PayPalGateway` class is dead code (wrong interface, no `@PaymentGateway` annotation — never discovered by the plugin manager). Accept-as-tested criterion: do NOT mark this as passing until real PayPal API verification is implemented.

---

## AC-042: ZaloPay payment is a stub with hardcoded sandbox URL

**Priority:** Must
**Given** ZaloPay is enabled in `lms_payment.settings` and a user selects it at checkout
**When** the user submits the checkout form
**Then** `ZaloPayGateway::processPayment()` returns `status=redirect` with a hardcoded sandbox URL `https://sandbox.zalopay.vn/redirect/demo?order={id}`

**Status:** **blocked — not implemented (stub).** ZaloPay integration is not functional. The hardcoded sandbox URL is not a real order redirect. HMAC signature validation in `handleWebhook()` is a comment only.

---

## AC-043: Membership plan admin CRUD works end to end

**Priority:** Must
**Given** a user with `administer lms_course` permission is at `/workspace/pricing`
**When** the user creates a new `lms_membership_plan` entity with `name`, `field_price`, `field_duration_days`, and `field_billing_cycle`, saves it, then edits it to toggle `status`, and finally deletes it
**Then** each operation (create, update, status toggle, delete) persists to the `lms_membership_plan` table and the list at `/workspace/pricing` reflects the current state after each action

**Status:** as-built — `MembershipAdminController` (pricingList, pricingAddForm, pricingEditForm, toggleStatus) and the underlying `ContentEntityForm` are fully implemented.

---

## AC-044: User membership portal shows active subscriptions

**Priority:** Must
**Given** an authenticated user has at least one `lms_user_membership` entity with `field_status` in `[active, trial]`
**When** the user requests `/my-membership`
**Then** `MembershipController::myMembership()` loads their active/trial memberships and returns the `lms_my_membership` theme with plan name, start date, end date, and auto-renew status

**Status:** as-built — `MembershipController::myMembership()` is fully implemented.

---

## AC-045: Cancelling a membership disables auto-renew (does not expire immediately)

**Priority:** Must
**Given** a user has an active `lms_user_membership` with `field_auto_renew=true`
**When** the user requests `/my-membership/{lms_user_membership}/cancel`
**Then** `MembershipController::cancelMembership()` sets `field_auto_renew=false` on the entity and saves
**And** the membership `field_status` remains `active` until `field_end_date` is reached by the cron job
**And** the user is redirected to `/my-membership`

**Status:** as-built — cancel sets auto-renew to false; it does not immediately expire the membership.

---

## AC-046: Expired memberships are processed by cron

**Priority:** Must
**Given** one or more `lms_user_membership` entities have `field_end_date <= now()` and `field_auto_renew=false`
**When** Drupal cron runs
**Then** `lms_membership.module`'s `hook_cron()` sets `field_status=expired` on each such membership and sends a `membership_expired` email via `hook_mail`
**And** memberships with `field_auto_renew=true` are logged as deferred to a payment gateway webhook (auto-renewal via webhook is not implemented — see AC-040)

**Status:** as-built (for `auto_renew=false` path); auto-renewal path is **blocked — not implemented** (webhook does not exist).

---

## AC-047: Wishlist toggle adds or removes a course from the user's wishlist

**Priority:** Must
**Given** an authenticated user sends `POST /api/lms/wishlist/toggle` with `{course_id}`
**When** `WishlistController::toggle()` processes the request
**Then** if a `lms_wishlist` entity exists for uid + course, it is deleted and `{status: removed}` is returned
**And** if no such entity exists, a new `lms_wishlist` entity is created and `{status: added}` is returned
**And** the cache tag `lms_wishlist:{uid}` is invalidated in either case

**Status:** as-built — `WishlistController` is fully implemented.

---

## AC-048: Course bundle entity exists but has no checkout integration

**Priority:** Should
**Given** a `lms_course_bundle` entity is created via `/manage/bundles/add` with `name`, `field_courses`, `field_price`, and `field_discount_percent`
**When** an admin or manager accesses `/manage/bundles`
**Then** the entity is listed and can be edited or deleted via auto-generated Drupal admin routes

**Status:** `lms_course_bundle` entity definition is as-built. **However:** there is no frontend checkout flow for bundles, no route that renders a bundle purchase page, and while `lms_order_item.field_type` allows the value `bundle`, no code creates bundle order items. Bundle checkout is **blocked — not implemented**.

---

## Local traceability (F-004)

| AC ID | Test Case | Implementing Story / Module | Status |
|-------|-----------|-----------------------------|--------|
| AC-036 | T-036 | free-checkout / lms_order | PENDING |
| AC-037 | T-037 | stripe-payment-intent / lms_order + lms_stripe | PENDING |
| AC-038 | T-038 | stripe-success-enroll / lms_order + lms_stripe | PENDING |
| AC-039 | T-039 | bank-transfer-flow / lms_order + lms_payment | PENDING |
| AC-040 | T-040 | webhook-completion / lms_stripe + lms_payment | PENDING |
| AC-041 | T-041 | paypal-stub / lms_payment | PENDING |
| AC-042 | T-042 | zalopay-stub / lms_payment | PENDING |
| AC-043 | T-043 | membership-plan-crud / lms_membership | PENDING |
| AC-044 | T-044 | my-membership / lms_membership | PENDING |
| AC-045 | T-045 | cancel-membership / lms_membership | PENDING |
| AC-046 | T-046 | membership-cron-expiry / lms_membership | PENDING |
| AC-047 | T-047 | wishlist-toggle / lms_wishlist | PENDING |
| AC-048 | T-048 | bundle-entity / lms_bundle | PENDING |

> Cross-feature traceability lives in `E05-traceability.md`.
