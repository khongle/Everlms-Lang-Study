---
usecase_id: UC-WF005-001
workflow: WF-005
title: Student purchases a paid membership plan and gains access
type: happy
status: as-built
---

# UC-WF005-001 — Membership Purchase and Activation (Happy Path)

Covers a student purchasing a paid `lms_membership_plan` via Stripe, the creation of a `lms_user_membership` entity on order completion, and the subsequent successful access check on membership-gated course content.

---

## Actors

| Actor | Role |
|-------|------|
| Student | Authenticated user purchasing membership |
| System | Order creation, gateway dispatch, membership activation |
| Stripe (external) | Card payment processor |

---

## Preconditions

1. Student is authenticated.
2. `lms_membership_plan` entity exists with `status = TRUE`, `field_price > 0`, `field_duration_days = 30` (example).
3. A `lms_course` with `field_price_type = membership` exists.
4. `lms_payment.settings` has `enable_stripe = TRUE` and Stripe keys configured (`«secret in config — see security risk»`).
5. `lms_membership.manager` service (`MembershipManager`) is available.

---

## Trigger

Student views `/pricing` (renders active `lms_membership_plan` entities via `PageController::pricing()`), selects a plan, and navigates to `/checkout/membership/{plan_id}`.

---

## Main Flow

| # | Given / When / Then | Entity / Code |
|---|---------------------|---------------|
| 1 | **Given** the student visits `/checkout/membership/{plan_id}` | Route: `lms_order.checkout_membership`; `CheckoutController::checkoutMembership()` loads plan by `plan_id` |
| 2 | **When** plan has `field_price > 0` (paid plan) | Controller builds `MembershipCheckoutForm` (free plans call `createAndFulfillFreeMembershipOrder()` directly) |
| 3 | **When** `MembershipCheckoutForm::buildForm()` executes | Calls `\Stripe\PaymentIntent::create()` server-side with plan price; injects `client_secret` + `publishable_key` into `drupalSettings`; renders checkout UI — **same PaymentIntent-on-page-load bug as `CheckoutForm`** |
| 4 | **When** student enters card details and submits | `stripe_elements.js` confirms payment intent in browser; sets `payment_intent_id` hidden field; form is submitted |
| 5 | **When** `MembershipCheckoutForm::submitForm()` runs | Dispatches to `StripeGateway::processPayment()` via `plugin.manager.payment_gateway`; retrieves PaymentIntent; checks `status === 'succeeded'` |
| 6 | **Then** order is created | Creates `lms_order` (field_status=`completed`, field_payment_method=`stripe`, field_total=plan_price); creates `lms_order_item` (field_membership_plan=plan_id, field_type=`membership`, field_price=plan_price); links to order |
| 7 | **Then** `lms_order_entity_insert()` fires | `_lms_order_process_completed_order()` iterates `field_items`; detects `field_type = membership`; creates `lms_user_membership` (uid=student, field_plan=plan, field_start_date=now, field_end_date=now+field_duration_days, field_status=`active`, field_auto_renew=TRUE) |
| 8 | **Then** confirmation email and notification | `hook_mail` key `order_completed` sends email; `lms_notification` entity created for student |
| 9 | **When** student accesses a membership-gated course/lesson | `lms_course.module::hook_entity_access()` calls `MembershipManager::hasActiveMembership(account)`; method queries `lms_user_membership` for student with `field_status IN [active, trial]` AND `field_end_date > now()`; returns TRUE |
| 10 | **Then** access is granted | Student can view course content; no redirect |

---

## Entities Mutated

| Entity | Fields Set | When |
|--------|-----------|------|
| `lms_order` | `uid`, `field_order_number`, `field_items`, `field_total`, `field_currency`, `field_status=completed`, `field_payment_method=stripe`, `field_payment_id` | Step 6 |
| `lms_order_item` | `field_membership_plan`, `field_type=membership`, `field_price` | Step 6 |
| `lms_user_membership` | `uid`, `field_plan`, `field_start_date`, `field_end_date`, `field_status=active`, `field_auto_renew=TRUE` | Step 7 |
| `lms_notification` | `uid`, notification payload | Step 8 |

---

## Observability Hooks

| Signal | Where | Detail |
|--------|-------|--------|
| Drupal mail | `lms_order.module::hook_mail` key `order_completed` | Purchase confirmation email to student |
| In-app notification | `_lms_order_process_completed_order()` | `lms_notification` entity created |
| Stripe PaymentIntent retrieve | `StripeGateway::processPayment()` | Observable in Stripe dashboard |
| `lms_user_membership` created | `_lms_order_process_completed_order()` | Entity stored in `lms_user_membership` DB table |

---

## Acceptance Link

| Acceptance Criterion | Test Definition |
|---------------------|----------------|
| [AC-044](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md) — Membership purchase creates `lms_user_membership` | E03-TEST-WF005-001-A |
| [AC-045](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md) — `MembershipManager::hasActiveMembership()` grants access for active/trial memberships | E03-TEST-WF005-001-B |

---

## Notes

- **Trial plans**: If `lms_membership_plan.field_trial_days > 0`, the created `lms_user_membership.field_status` should be `trial`. The `_lms_order_process_completed_order()` helper must distinguish trial vs. active — confirm exact field-setting logic in `lms_order.module` if implementation differs from this description.
- **Auto-renewal**: `field_auto_renew` is set to `TRUE` by default. When the plan expires, `hook_cron` in `lms_membership.module` logs a deferral message but does NOT initiate any charge. Auto-renewal is effectively a no-op at runtime — see [UC-WF005-002](UC-WF005-002-membership-expiry-cron.md).
- **Admin bypass**: `MembershipManager::hasActiveMembership()` returns `TRUE` for any user with the `administer lms_course` permission, regardless of actual membership status.
- Membership plan's `field_billing_cycle` (monthly/quarterly/yearly/lifetime) is descriptive only — no billing automation is implemented.
