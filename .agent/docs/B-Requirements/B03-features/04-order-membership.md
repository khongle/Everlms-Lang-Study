---
id: F-004
name: Order and Membership
priority: Must
status: APPROVED
parent-requirements: [BR-004]
owning_modules: [lms_order, lms_membership, lms_payment, lms_stripe, lms_paypal, lms_wishlist, lms_bundle]
primary_source: C05
story_anchors: []
---

# F-004 — Order and Membership

## User story

As a **learner**, I want to purchase a course or subscribe to a membership plan using my preferred payment method, so that I am automatically enrolled in the content I have paid for.

As an **administrator**, I want to review pending orders, approve bank-transfer receipts, manage membership plans, and monitor payment status, so that I can ensure accurate access control and revenue tracking.

## Description

This feature covers the complete purchase-to-enrollment pipeline for paid content. It spans order creation and checkout, payment gateway integration (Stripe, Bank Transfer — both real; PayPal stub within lms_payment; ZaloPay stub; lms_paypal module dead code), auto-enrollment on order completion, membership plan management, and the learner's wishlist.

The payment architecture uses a Drupal Plugin API plugin manager (`PaymentGatewayManager`, `@PaymentGateway` annotation) hosted in `lms_payment`. Active plugins are: `stripe` (lms_stripe — real, uses Stripe PHP SDK + PaymentIntent), `bank_transfer` (lms_payment — real, manual flow with receipt upload), `paypal` (lms_payment — stub, no server-side PayPal REST API verification), `zalopay` (lms_payment — stub, hardcoded sandbox URL). The `lms_paypal` module contains a separate class that implements the wrong interface and has no annotation — it is **dead code, never discovered or instantiated**.

Auto-enrollment is triggered by `lms_order.module`'s `hook_entity_insert` / `hook_entity_update`: when an order's `field_status` transitions to `completed`, it creates `lms_user_course` entities for each course item and `lms_user_membership` entities for each membership plan item, sends a confirmation email, and creates an in-app notification.

The Stripe flow creates a `PaymentIntent` in `CheckoutForm::buildForm()` (on every page load, not deferred) — this is a known security/UX issue. Webhook signature verification is missing for all gateways; no webhook routes exist.

Membership plan expiry is enforced by `lms_membership.module hook_cron`; auto-renewal via webhook is logged but not implemented. `MembershipManager::hasActiveMembership()` is the canonical access-check service, called by `lms_course.module hook_entity_access` for membership-gated courses.

Wishlist is a lightweight feature providing toggle/status JSON API and a "My Wishlist" listing page.

## In scope

**Order management:**
- `lms_order` entity: order number, items (unlimited `lms_order_item` references), subtotal, discount, total, currency (default USD), status (pending/processing/completed/cancelled/refunded), payment method, payment ID (transaction ID), coupon code, reminder-sent flag, payment receipt image.
- `lms_order_item` entity: course reference OR membership plan reference, price at purchase, type (course/bundle/membership).
- Checkout flow: `CheckoutController::checkout()` — free courses get immediate fulfillment via `createAndFulfillFreeOrder()`; paid courses render `CheckoutForm` with payment method radios loaded from `lms_payment.settings` config.
- Membership checkout: `CheckoutController::checkoutMembership()` / `MembershipCheckoutForm` — same pattern.
- Bank transfer: `bankTransferInstruction()` renders VietQR payment instructions; `BankTransferForm` handles receipt upload (PNG/JPG/JPEG, 5 MB limit, stored at `public://payment_receipts/YYYY-MM`).
- Admin order list (`/workspace/orders`): paginated with search (name/ID) and status filter.
- Admin order approve (`ApproveOrderForm` at `/workspace/orders/{lms_order}/edit`): renders order details, status select, receipt image; save redirects to order list.
- Auto-enrollment: `_lms_order_process_completed_order()` creates `lms_user_course` + `lms_user_membership` on `field_status = completed`; sends `order_completed` email via `plugin.manager.mail`; creates in-app notification.
- `OrderAccessControlHandler`: admin sees all orders; non-admin can only view own orders.
- Coupon code field exists on `lms_order` but no coupon validation logic is implemented.

**Payment gateways:**
- `PaymentGatewayManager` (plugin manager in lms_payment): scans `Plugin/PaymentGateway/` namespace for `@PaymentGateway` annotation.
- `StripeGateway` (`lms_stripe`): `processPayment()` calls `\Stripe\PaymentIntent::retrieve()` and checks `status === succeeded` — real Stripe SDK call. `handleWebhook()` is a stub (TODO — no signature verification).
- `BankTransferGateway` (`lms_payment`): `processPayment()` returns `status=pending`, `transaction_id=MANUAL_{order_id}` — real for its use case (manual). `handleWebhook()` is a no-op 200.
- `PayPalGateway` in `lms_payment` (not `lms_paypal`): accepts any `paypal_order_id` without server-side REST API verification — stub.
- `ZaloPayGateway` (`lms_payment`): hardcoded `https://sandbox.zalopay.vn/redirect/demo` URL — stub.
- `PaymentSettingsForm` (`/admin/config/everlms/payment`): configures all gateway keys (Stripe publishable/secret, PayPal client ID/secret, ZaloPay app ID/keys, bank account details). **SECURITY: All secrets stored in plaintext `lms_payment.settings` config YAML. No Key module. «secret in config — see security risk».**
- Stripe Elements client-side library (`lms_order/stripe_elements`): loads `https://js.stripe.com/v3/` + local `js/stripe_elements.js`.

**Membership:**
- `lms_membership_plan` entity: name, description, price, duration_days (default 30), billing cycle (monthly/quarterly/yearly/lifetime), features (unlimited string list), course access (all/selected), trial days, recommended flag, status.
- `lms_user_membership` entity: owner, plan reference, start/end date, status (active/trial/expired/cancelled), auto_renew flag.
- `MembershipManager::hasActiveMembership()`: checks user has `administer lms_course` permission OR has an `lms_user_membership` with status in [active, trial] and `field_end_date > now()`.
- `MembershipAccessSubscriber`: on `AccessDeniedHttpException` on `/learn/` or `/course/` paths, redirects to pricing page.
- My Membership page (`/my-membership`): shows active/trial memberships; cancel link.
- Cancel membership: sets `field_auto_renew = FALSE` (does not expire immediately).
- Admin pricing management (`/workspace/pricing`): list, add, edit, toggle-status for plans.
- Admin user memberships (`/workspace/user-memberships`): list, add, edit.
- Cron expiry: finds active/trial memberships with `field_end_date <= now()`; sets `expired` if `field_auto_renew = FALSE`; logs + defers to webhook if `field_auto_renew = TRUE` (webhook not implemented — deferred).
- Membership expiry email (`membership_expired` key via `hook_mail`).

**Wishlist:**
- `lms_wishlist` entity: owner + course reference + created timestamp.
- Toggle API (`/api/lms/wishlist/toggle`, POST): adds or removes wishlist entry; cache invalidation via `lms_wishlist:{uid}` tag.
- Status API (`/api/lms/wishlist/status`, GET): returns boolean map for given course IDs + total count.
- My Wishlist page (`/my-wishlist`): rich course-card listing with instructor info, enrollment status.

## Out of scope

- Shopping cart with multiple items — checkout is per-course or per-plan (single-item); no multi-item cart entity exists.
- Coupon code validation logic — `field_coupon_code` field exists but no server-side discount calculation is implemented.
- Tax calculation — not implemented.
- Invoice PDF generation — not implemented.
- Guest checkout (anonymous purchase) — checkout redirects anonymous users to login.
- Refund processing via payment gateway API — no refund routes or SDK calls exist.
- CSV/Excel order export — not implemented.
- Auto-renewal via Stripe webhooks — webhook routes do not exist in any module; Stripe subscription billing not implemented.
- `lms_paypal` module (`PayPalGateway` class in `lms_paypal/`) — dead code: implements wrong interface, no `@PaymentGateway` annotation, never discovered by `PaymentGatewayManager`.
- ZaloPay real integration — hardcoded sandbox redirect URL, no HMAC verification.
- Course bundle checkout — `lms_course_bundle` entity exists (data model only); `lms_order_item.field_type` has `bundle` value but no code creates bundle order items.
- `lms_membership_plan.field_course_access = selected` — the "selected courses" access control logic is not implemented (no field listing specific courses within a plan).

## Acceptance summary

A learner can check out a paid course using Stripe card payment or bank transfer; on order completion the learner is automatically enrolled (`lms_user_course` created); a membership plan can be purchased similarly and the `MembershipManager` access check grants access to membership-gated lessons. Admins can review and approve pending bank-transfer orders. Full Given/When/Then lives in `B05-acceptance-criteria/F-004.md`.

## Content ownership / editability

| Visible content family | Business owner | Editor role | Expected edit surface | Notes |
|---|---|---|---|---|
| Membership plans (name, price, features, billing cycle) | Platform owner | administrator | `/workspace/pricing` | Plans shown on pricing page `/pricing` (reads lms_membership_plan entities) |
| Payment gateway settings (enable/disable, API keys) | Platform owner | administrator | `/admin/config/everlms/payment` | «secret in config — see security risk»; all keys in `lms_payment.settings` plaintext YAML |
| Order status (approve/reject bank transfers) | Platform admin | administrator | `/workspace/orders/{id}/edit` via ApproveOrderForm | Receipt image viewable in form |
| Bank transfer instructions and VietQR details | Platform owner | administrator | `lms_payment.settings` (bank_bin, bank_account_no, etc.) | Displayed in lms-bank-transfer.html.twig |

## Dependencies (requirement-level only)

| Type | Reference | Reason |
|---|---|---|
| Feature | F-001 | Enrollment (lms_user_course creation) references lms_course; membership gating checked in lms_course hook_entity_access |
| Feature | F-003 | Auto-enrollment on order completion feeds into learning progress tracking |
| Workflow | WF-004 | Order management workflow (checkout → payment → completion → enrollment) |
| Integration | INT-001 | Stripe PaymentIntent API (real); Stripe webhook (stub — no signature verification) |

## Backing modules and entities

| Module | Role | Maturity |
|---|---|---|
| `lms_order` | Order + OrderItem entities, CheckoutController, CheckoutForm, MembershipCheckoutForm, BankTransferForm, auto-enrollment hooks | as-built (minor bug: `createAndFulfillFreeMembershipOrder` calls `set('field_items',…)` twice) |
| `lms_membership` | MembershipPlan + UserMembership entities, MembershipManager, admin controllers, cron expiry, MembershipAccessSubscriber | as-built (auto-renewal via webhook deferred) |
| `lms_payment` | PaymentGatewayManager (plugin host), BankTransferGateway (real), PayPalGateway (stub), ZaloPayGateway (stub), PaymentSettingsForm | as-built for plugin system; gateway stubs for PayPal/ZaloPay |
| `lms_stripe` | StripeGateway plugin — real Stripe SDK processPayment; stub handleWebhook | as-built for processPayment; stub for webhook |
| `lms_paypal` | PayPalGateway class implementing wrong interface, no annotation | dead code — not wired to plugin system |
| `lms_wishlist` | Wishlist entity, WishlistController (toggle/status/listing) | as-built |
| `lms_bundle` | CourseBundle entity | stub — data model only |

| Entity | Key fields | Maturity |
|---|---|---|
| `lms_order` | field_order_number, field_items, field_subtotal, field_discount, field_total, field_currency, field_status (pending/processing/completed/cancelled/refunded), field_payment_method, field_payment_id, field_coupon_code, field_payment_receipt | as-built |
| `lms_order_item` | field_course, field_membership_plan, field_price, field_type (course/bundle/membership) | as-built |
| `lms_membership_plan` | name, field_price, field_duration_days, field_billing_cycle, field_features, field_course_access, field_trial_days, field_recommended, status | as-built |
| `lms_user_membership` | field_plan, field_start_date, field_end_date, field_status (active/trial/expired/cancelled), field_auto_renew | as-built |
| `lms_wishlist` | field_course, created | as-built |
| `lms_course_bundle` | name, field_courses, field_price, field_discount_percent, field_thumbnail, status | stub |

## Priority matrix

| Capability | Priority | Maturity |
|---|---|---|
| Order entity CRUD + checkout flow | Must | as-built |
| Free course immediate fulfillment | Must | as-built |
| Stripe card payment (PaymentIntent) | Must | as-built |
| Bank transfer with receipt upload | Must | as-built |
| Auto-enrollment on order completion | Must | as-built |
| Admin order review and status approval | Must | as-built |
| Membership plan CRUD + pricing page | Must | as-built |
| User membership tracking + cron expiry | Must | as-built |
| MembershipManager access check for gated lessons | Must | as-built |
| My Membership page + cancel | Must | as-built |
| Wishlist (toggle/status/listing) | Should | as-built |
| PayPal payment (real server-side verification) | Should | stub — lms_payment PayPalGateway accepts any ID without verification |
| Webhook signature verification (Stripe) | Should | stub — TODO comment in StripeGateway::handleWebhook() |
| Auto-renewal via payment gateway webhooks | Should | planned — webhook routes do not exist |
| Coupon code discount calculation | Could | planned — field exists, no server-side logic |
| ZaloPay real integration | Could | stub — hardcoded sandbox URL |
| Multi-item shopping cart | Could | planned — not implemented |
| Tax calculation / invoice PDF | Could | planned — not implemented |
| Course bundle checkout | Could | stub — entity only, no checkout wiring |
| Refund processing via gateway API | Could | planned — not implemented |
| `lms_paypal` module | Wont | dead code — incompatible interface, no annotation; will not be used |

## Known bugs and security risks

| Issue | Severity | Location |
|---|---|---|
| Stripe `PaymentIntent::create()` called in `buildForm()` — one PaymentIntent created per page load (wasted API calls; stale intents on back-navigation) | Medium | `CheckoutForm::buildForm()`, `MembershipCheckoutForm::buildForm()` |
| No webhook routes exist for any gateway — async payment confirmations cannot be received | High | All gateway modules |
| Stripe webhook `handleWebhook()` has no signature verification (TODO comment) | High | `lms_stripe/StripeGateway.php` |
| `lms_payment.settings` stores all API keys in plaintext committed config YAML — no Key module | Critical | `config/sync/lms_payment.settings.yml` — «secret in config — see security risk» |
| `.env` with test Stripe/PayPal credentials is committed to the repo | Critical | Repo root `.env` — «secret in config — see security risk» |
| `lms_paypal` module `PayPalGateway` implements `Service\PaymentGatewayInterface` (wrong interface) and has no `@PaymentGateway` annotation — never discovered; dead code | Low | `web/modules/custom/lms_paypal/src/Plugin/PaymentGateway/PayPalGateway.php` |
| `createAndFulfillFreeMembershipOrder()` calls `$order->set('field_items', …)` twice (redundant double-set) | Low | `CheckoutController.php` |
| Two `PaymentGatewayInterface` definitions exist (`Plugin\PaymentGatewayInterface` vs `Service\PaymentGatewayInterface`) — only the Plugin one is used; Service one is only referenced by dead `lms_paypal` code | Low | `lms_payment` module |
| `cancelMembership()` sets `field_auto_renew = FALSE` but does not expire the membership immediately — confusing UX; learner retains access until `field_end_date` | Low | `MembershipController::cancelMembership()` |

## Open questions

- [ ] When should `lms_paypal` module be removed or rewritten to implement the correct `Plugin\PaymentGatewayInterface` with `@PaymentGateway` annotation?
- [ ] Stripe webhook: what signing secret and webhook endpoint URL should be configured? `handleWebhook()` needs `Stripe\Webhook::constructEvent()` verification.
- [ ] `lms_membership_plan.field_course_access = selected`: which entity or field stores the list of selected courses for a plan? Currently no such field exists on MembershipPlan.
- [ ] Is the single-item checkout flow (per-course / per-plan) the intended long-term UX, or is a multi-item cart required?
- [ ] API keys in config YAML: when will Key module (or env-var-based config override) be adopted to avoid committing secrets?
