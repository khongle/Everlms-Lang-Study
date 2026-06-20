---
id: WF-005
name: Membership Subscription
status: APPROVED
features: [F-004]
---

# WF-005 — Membership Subscription

End-to-end flow for purchasing a membership plan, activating access to membership-gated content, and handling expiry. Covers the purchase path, cron-based expiry, and the cancellation model.

## Trigger

Student visits `/checkout/membership/{plan_id}` to purchase a plan, OR the Drupal cron job fires to evaluate membership expiry.

## Actors

| Actor | Role |
|-------|------|
| Student (authenticated) | Purchases and manages membership |
| System | Plan activation, access control, cron expiry |
| Cron (Drupal scheduler) | Evaluates expiry; sends expiry email |
| Admin | Creates/edits plans; manages user memberships |

## Preconditions

- Student is authenticated.
- `lms_membership_plan` entity exists with `status=TRUE` (active).
- At least one payment gateway is enabled (for paid plans).

## Happy Path — Purchase & Activate

| Step | Actor | Action | System Response | Modules / Code | Feature |
|------|-------|--------|----------------|----------------|---------|
| 1 | Student | Views pricing page | Admin-configured `lms_membership_plan` entities displayed; `MembershipAdminController::pricingList()` renders `lms_admin_pricing` theme | `lms_membership` | F-004 |
| 2 | Student | Selects a plan and proceeds to `/checkout/membership/{plan_id}` | `CheckoutController::checkoutMembership()` loads plan. **Free plan** (price=0): calls `createAndFulfillFreeMembershipOrder()` — skip to step 6. **Paid plan**: builds `MembershipCheckoutForm`. | `lms_order` | F-004 |
| 3 | System | `MembershipCheckoutForm::buildForm()` | Identical flow to `CheckoutForm`: Stripe `PaymentIntent::create()` called on form build (same bug as WF-004). Payment method radios from `lms_payment.settings`. | `lms_order/src/Form/MembershipCheckoutForm.php` | F-004 |
| 4 | Student | Completes payment | Same gateway dispatch as [WF-004](./WF-004-order-management.md): Stripe real, bank transfer manual, PayPal/ZaloPay stub. | `lms_payment`, `lms_stripe` | F-004 |
| 5 | System | Order set to `completed` | `hook_entity_insert/update` in `lms_order.module` fires `_lms_order_process_completed_order()`. For `lms_order_item.field_type = membership`: creates `lms_user_membership` entity. | `lms_order.module` | F-004 |
| 6 | System | `lms_user_membership` created | Fields set: `uid`, `field_plan` (ref to plan), `field_start_date=now`, `field_end_date=now + field_duration_days`, `field_status=active` (or `trial` if `field_trial_days > 0`), `field_auto_renew=TRUE`. | `lms_order.module` + `lms_user_membership` entity | F-004 |
| 7 | System | Confirmation email | `hook_mail` key `order_completed` sends purchase confirmation. | `lms_order.module` | F-004 |
| 8 | Student | Accesses membership-gated content | `MembershipManager::hasActiveMembership()` checks: `field_status` in `[active, trial]` AND `field_end_date > now()`. Returns `TRUE` for admins regardless. | `lms_membership/src/MembershipManager.php` | F-004 |

## Happy Path — Expiry (Cron)

| Step | Actor | Action | System Response | Code | Feature |
|------|-------|--------|----------------|------|---------|
| 1 | Cron | Drupal cron fires | `lms_membership.module::hook_cron()` runs: queries `lms_user_membership` where `field_status` IN `[active, trial]` AND `field_end_date <= now`. | `lms_membership.module` | F-004 |
| 2 | System | `field_auto_renew = FALSE` | Sets `field_status = expired`; sends `membership_expired` email via `hook_mail`. | `lms_membership.module` | F-004 |
| 3 | System | `field_auto_renew = TRUE` | Logs deferred renewal and skips — **no automatic charge**. Comment in code: "defer to payment gateway webhook". Since no webhook endpoints exist, auto-renewal is effectively not implemented. | `lms_membership.module` — **planned/stub** | F-004 |
| 4 | System | Content access revoked | Next access check by `MembershipManager::hasActiveMembership()` returns `FALSE` for `expired` status. `MembershipAccessSubscriber` redirects `/learn/*` requests to pricing page. | `lms_membership/src/MembershipManager.php`, `MembershipAccessSubscriber.php` | F-004 |

## Cancellation Flow

| Step | Actor | Action | System Response | Code |
|------|-------|--------|----------------|------|
| 1 | Student | POST `/my-membership/{lms_user_membership}/cancel` | `MembershipController::cancelMembership()` sets `field_auto_renew = FALSE` on the `lms_user_membership` entity. Status does NOT change immediately. | `lms_membership/src/Controller/MembershipController.php` |
| 2 | System | Access continues until end date | Membership status remains `active` until `field_end_date`. Cron will then expire it naturally. | `lms_membership.module::hook_cron()` |

## Membership States (as-built)

| `field_status` value | Meaning | Access granted? |
|----------------------|---------|----------------|
| `active` | Current active membership | Yes, if `field_end_date > now()` |
| `trial` | Trial period (if `field_trial_days > 0`) | Yes, if `field_end_date > now()` |
| `expired` | End date passed, not renewed | No |
| `cancelled` | Available in field definition; not set by current code — cancellation sets `field_auto_renew=FALSE` only | N/A — not used in practice |

**Note**: `cancelled` is defined as an allowed value in `field_status` but no code currently sets it. The cancellation action sets `field_auto_renew=FALSE` and leaves status as `active`.

## Error Paths

| Step | Condition | System Response | Recovery |
|------|-----------|----------------|----------|
| 2-E1 | Anonymous student accesses `/checkout/membership/{plan_id}` | Redirect to login | Student logs in |
| 3-E1 | `createAndFulfillFreeMembershipOrder()` double-set bug | `$order->set('field_items', ...)` called twice; second call overwrites first — **minor bug, functional** | No action; self-correcting |
| 3-E2 | Stripe `PaymentIntent::create()` fails on form build | Exception on form render | Student retries |
| 6-E1 | `field_auto_renew=TRUE` at expiry | Cron skips renewal (no charge); membership expires | Student must re-purchase manually |
| Webhook | Stripe/PayPal send recurring billing event | **No webhook endpoint** — event lost | Not recoverable without webhook implementation |
| 4-E1 | `MembershipAccessSubscriber` fires on non-membership error | Any `AccessDeniedHttpException` on `/learn/*` redirects to pricing, even if denial is unrelated to membership | Known false-positive; improve condition check |

## Success Criteria

- `lms_user_membership` entity created with correct `field_start_date`, `field_end_date`, and `field_status=active`.
- `MembershipManager::hasActiveMembership()` returns `TRUE` within the active period.
- On cron run after `field_end_date`: `field_status` set to `expired` for non-auto-renew; email dispatched.
- Student redirected to `/course/pricing` (via `lms_course.pricing` route) when accessing protected content after expiry.

## Business Rules

- **Trial**: if `lms_membership_plan.field_trial_days > 0`, the first `lms_user_membership` for the student uses `field_status=trial`. No code in the current implementation enforces "one trial per user per plan" — **gap, not implemented**.
- **Lifetime plans**: `field_billing_cycle=lifetime`; no code currently sets an infinite `field_end_date`. Cron would expire a lifetime plan if `field_end_date` is reached — admin must set a far-future date manually.
- **Auto-renewal**: `field_auto_renew=TRUE` is stored, but actual charge on renewal is not implemented. Cron logs and defers to a webhook that does not exist. Auto-renewal is effectively a planned feature.
- **Admin override**: `MembershipManager::hasActiveMembership()` grants access to any user with `administer lms_course` permission regardless of membership status — admins can always access content.
- **Access check not wired to Drupal access plugins**: `MembershipManager::hasActiveMembership()` is a plain service; access checks are applied by `MembershipAccessSubscriber` (exception handler) and by explicit calls in controllers. There is no `@AccessCheck` plugin.
- **Email channel only**: expiry notification is sent via `hook_mail`. In-app `lms_notification` is not dispatched by the cron expiry handler (only by order completion). Push is a stub in `lms_notification`.

## Related Docs

- [WF-004 — Order & Checkout](./WF-004-order-management.md) — shared payment flow
- [WF-001 — Enrollment & Learning](./WF-001-enrollment-learning.md) — content access post-membership
- Source: `web/modules/custom/lms_membership/lms_membership.module`
- Source: `web/modules/custom/lms_membership/src/MembershipManager.php`
- Source: `web/modules/custom/lms_membership/src/Controller/MembershipController.php`
- Source: `web/modules/custom/lms_membership/src/EventSubscriber/MembershipAccessSubscriber.php`
- Source: `web/modules/custom/lms_order/src/Controller/CheckoutController.php`
- Source: `web/modules/custom/lms_order/lms_order.module`
