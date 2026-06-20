---
workflow: WF-005
slug: membership-subscription
mode: as-built
b04_source: B04/WF-005-membership-subscription.md
adequacy: NOT_RUN
adequacy_run: null
---

# C12 — Workflow: WF-005 Membership Subscription

**Trigger (purchase):** Student visits `/checkout/membership/{plan_id}`. **Trigger (expiry):** Drupal cron fires `hook_cron` in `lms_membership.module`.
**Outcome (purchase):** `lms_user_membership` entity created with `field_status=active`; `MembershipManager::hasActiveMembership()` returns TRUE; student can access membership-gated content. **Outcome (expiry):** `field_status=expired`; access revoked on next check; expiry email sent.
**B04 source:** [`../../B-Requirements/B04-business-workflows/WF-005-membership-subscription.md`](../../B-Requirements/B04-business-workflows/WF-005-membership-subscription.md)
**Mode:** as-built

---

## Actors

| Actor (C02 role) | Step participation | Permissions needed |
|---|---|---|
| Student (authenticated) | Purchase (steps 1–7); cancellation | `_user_is_logged_in: TRUE` |
| System | Fulfillment (step 5); access check | — |
| Cron | Expiry evaluation (cron path steps 1–4) | — (Drupal cron) |
| Admin | Plan management; user membership management | `administer lms_course` |

## Entry Points

| URL | Page (C11) | Method | Form | Feature Flag |
|---|---|---|---|---|
| `/checkout/membership/{plan_id}` | checkout-membership | GET+POST | `MembershipCheckoutForm` | — |
| `/checkout/bank-transfer/{lms_order}` | bank-transfer | GET+POST | `BankTransferForm` | — |
| `/my-membership` | my-membership | GET | — | — |
| `/my-membership/{lms_user_membership}/cancel` | — | POST | — (controller action) | — |
| `/workspace/pricing` | admin-pricing | GET | — | `administer lms_course` |
| `/workspace/pricing/add` | — | GET+POST | entity form | `administer lms_course` |
| `/workspace/pricing/{id}/edit` | — | GET+POST | entity form | `administer lms_course` |
| `/workspace/pricing/{id}/toggle` | — | GET | — (redirect) | `administer lms_course` |
| `/workspace/user-memberships` | admin-user-memberships | GET | — | `administer lms_course` |
| `/workspace/user-memberships/add` | — | GET+POST | entity form | `administer lms_course` |
| `/workspace/user-memberships/{id}/edit` | — | GET+POST | entity form (manager mode) | `administer lms_course` |

## Entities Touched

| Entity | Operation | Fields Written | State Transition |
|---|---|---|---|
| `lms_order` | CREATE (via shared WF-004 checkout) | same as WF-004 | `pending` → `completed` |
| `lms_order_item` | CREATE | `field_membership_plan`, `field_price`, `field_type=membership` | — |
| `lms_user_membership` | CREATE (fulfillment) / UPDATE (cancel; cron expiry) | `uid`, `field_plan`, `field_start_date`, `field_end_date`, `field_status`, `field_auto_renew` | `active` / `trial` → `expired` |
| `lms_notification` | CREATE (on order completed) | type=`payment`, `field_channel=[in_app]` | — |
| `lms_membership_plan` | READ (pricing display); UPDATE (status toggle) | `status` boolean (admin toggle) | `TRUE` ↔ `FALSE` |

## State Machine: lms_user_membership.field_status

```
  [plan purchased / fulfilled]
       │
       └──▶ active  (as-built: _lms_order_process_completed_order() ALWAYS sets field_status='active';
                     field_trial_days > 0 is NOT checked — trial status is NOT auto-assigned by the checkout flow;
                     trial assignment must be done manually by an admin or via direct code)
       │
       [trial]  ← set only via admin UI or direct entity manipulation; NOT triggered by order flow
                         │
                         ├──▶ field_end_date reached (cron) + field_auto_renew = FALSE
                         │         └──▶ expired
                         │
                         ├──▶ field_end_date reached (cron) + field_auto_renew = TRUE
                         │         └──▶ [STUB] logs deferred renewal; NO auto-charge; remains active until next cron
                         │               [effectively expires on next cron run after field_end_date]
                         │
                         └──▶ student cancels (/my-membership/{id}/cancel)
                                   └──▶ field_auto_renew = FALSE; field_status stays active
                                           (expires naturally when field_end_date reached)

[cancelled] — defined in field; NOT set by any code path
```

State guards:

| Transition | Trigger | Guard | Side Effects |
|---|---|---|---|
| `→ active` | `_lms_order_process_completed_order()` | `lms_order_item.field_type=membership`; order `status=completed` | Creates `lms_user_membership`; sends `order_completed` email; creates in-app notification |
| `→ trial` | **NOT SET by order flow — bug/gap** | `field_plan.field_trial_days > 0` | `_lms_order_process_completed_order()` always sets `field_status='active'` regardless of `field_trial_days`; it never reads `field_trial_days`. `trial` status can only be set manually (admin or direct code). The trial state machine described here is target-state; the as-built code omits this logic. |
| `active/trial → expired` | `lms_membership.module::hook_cron()` | `field_end_date <= now()` AND `field_auto_renew=FALSE` | `field_status=expired`; `hook_mail` key `membership_expired`; no in-app notification dispatched by cron |
| `auto_renew=TRUE` at expiry | `hook_cron()` detects | `field_end_date <= now()` AND `field_auto_renew=TRUE` | Logs deferred renewal; no charge; status not changed at this cron run (effectively: expires on next cron) |
| Cancel | `MembershipController::cancelMembership()` | Owner of membership + `_user_is_logged_in` | Sets `field_auto_renew=FALSE` only; status unchanged; natural expiry via cron |

## Access Check Architecture

```
MembershipManager::hasActiveMembership(AccountInterface $account): bool
  ├── account has 'administer lms_course' → TRUE (admin bypass)
  └── query lms_user_membership:
        uid = $account->id()
        field_status IN ['active', 'trial']
        field_end_date > now()
      → TRUE if any result found, FALSE otherwise

Access enforcement points:
  1. MembershipAccessSubscriber (KernelEvents::EXCEPTION, priority 50):
       IF AccessDeniedHttpException AND path starts with '/learn/' or '/course/'
       → redirect to lms_course.pricing with warning
       [NOTE: fires for ALL AccessDenied on those paths, not only membership-related ones]

  2. Explicit calls to MembershipManager::hasActiveMembership() in controllers
     (no @AccessCheck Drupal plugin — plain service call only)
```

## Cron Expiry Detail

```
lms_membership.module::hook_cron():
  Query lms_user_membership:
    field_status IN ['active', 'trial']
    field_end_date <= REQUEST_TIME
  For each found:
    IF field_auto_renew = FALSE:
      field_status = 'expired'
      save entity
      hook_mail key 'membership_expired' → email to user
    ELIF field_auto_renew = TRUE:
      watchdog log: "Auto-renewal deferred to payment gateway webhook"
      [NO CHARGE; NO ROUTE TO RECEIVE WEBHOOK]
```

> **Critical gap**: `field_auto_renew=TRUE` path defers to a webhook that does not exist. Practically, auto-renewal is not implemented. Members with `auto_renew=TRUE` will have their membership expire without charge on the next cron run after `field_end_date`.

## Lifetime Plan Gap

> `field_billing_cycle=lifetime` is a valid value in `lms_membership_plan` but no code sets `field_end_date` to infinity for lifetime plans. `_lms_order_process_completed_order()` sets `field_end_date = now + field_duration_days`. Admin must manually set a far-future date for lifetime plans; otherwise cron will expire them.

## Modules Involved

| Module | Responsibility in This Workflow |
|---|---|
| `lms_order` | Shared checkout flow (see [WF-004](WF-004-order-checkout.md)); `_lms_order_process_completed_order()` creates `lms_user_membership` |
| `lms_membership` | `lms_membership_plan` entity; `lms_user_membership` entity; `MembershipManager::hasActiveMembership()`; `MembershipController` (my-membership, cancel); `MembershipAdminController` (pricing, user-memberships); `MembershipAccessSubscriber` (exception handler); `hook_cron` expiry; `hook_mail` expiry email |
| `lms_payment` | Shared gateway plugins (see [WF-004](WF-004-order-checkout.md)) |
| `lms_notification` | In-app notification created on order completion; **no notification dispatched by cron expiry handler** |

## Permissions

| Step | Public (anon) | Student (auth) | Manager | Admin |
|---|---|---|---|---|
| View pricing | Public (plan listing) | Public | Public | `administer lms_course` (edit) |
| Purchase plan | Redirect to login | submit `MembershipCheckoutForm` | same | same |
| View `/my-membership` | — | own membership | own | `administer lms_course` |
| Cancel membership | — | own membership | own | `administer lms_course` |
| Admin pricing CRUD | — | — | — | `administer lms_course` |
| Admin user memberships | — | — | — | `administer lms_course` |
| Access gated `/learn/*` | Redirect to login | `MembershipManager::hasActiveMembership()=TRUE` | same | always (admin bypass) |

## Notifications

| Trigger | Recipient | Channel | Template / Key |
|---|---|---|---|
| Order `completed` (membership) | Student | email (`hook_mail`) | `order_completed` |
| Order `completed` | Student | in-app (`lms_notification`) | type=`payment` |
| Membership expiry (cron) | Student | email (`hook_mail`) | `membership_expired` |
| Membership expiry | Student | **None** — in-app not dispatched by cron | — |

> **Gap**: Cron expiry only sends email; no in-app `lms_notification` of type `membership_expiry` is created. `lms_notification.field_type` allows `membership_expiry` but no code creates it on expiry.

## Cache Invalidation

| Step | Cache Tag(s) | Search Index | List Views |
|---|---|---|---|
| `lms_user_membership` created / expired | No explicit cache tag (as-built) | — | `/my-membership` (dynamic), `/workspace/user-memberships` |
| `lms_notification` created | `lms_notification_list` (NotificationBellBlock) | — | `/notifications` |
| Plan status toggle | No explicit cache tag | — | `/workspace/pricing` |

## Operations Queues

| Page | Filters | Owner Role |
|---|---|---|
| `/workspace/user-memberships` | `field_status` filter, search | `administer lms_course` |
| `/workspace/pricing` | `status` filter, search | `administer lms_course` |

## Editorial / Management Surfaces

| Entity / Content | Create/Edit UI | Role / Permission | State | Translation | Visible C11 Surfaces |
|---|---|---|---|---|---|
| `lms_membership_plan` | `/workspace/pricing/add`, `.../edit`; status toggle `/workspace/pricing/{id}/toggle` | `administer lms_course` | `status` boolean | — | `/workspace/pricing` |
| `lms_user_membership` | `/workspace/user-memberships/add`, `.../edit` (manager mode) | `administer lms_course` | `field_status` list | — | `/workspace/user-memberships`, `/my-membership` |

## Failure Modes

| Failure | Detection | Handling |
|---|---|---|
| Anonymous checkout | `CheckoutController` anonymous check | Redirect to login |
| `MembershipCheckoutForm::buildForm()` Stripe error | Same bug as WF-004 — PaymentIntent on every build | Form render fails; orphaned PaymentIntents created |
| `createAndFulfillFreeMembershipOrder()` double-set | `$order->set('field_items',...)` called twice | Minor bug; functional (second call overwrites) |
| Auto-renewal at expiry | Cron logs deferred renewal; no charge | Membership effectively expires; student must re-purchase manually |
| No webhook for Stripe recurring billing | No route | Recurring billing events lost; auto-renewal impossible |
| Trial "one per user" not enforced | No unique constraint in code or DB | Multiple trial `lms_user_membership` entities possible for same user+plan — **gap** |
| Lifetime plan expiry | No infinite `field_end_date` set | Cron may expire lifetime plan if `field_duration_days` is small; admin must set far-future date manually |
| `cancelled` status not set | No code calls `field_status=cancelled` | Status is dead state; cancellation only sets `field_auto_renew=FALSE` |
| `MembershipAccessSubscriber` false-positive | Any `AccessDenied` on `/learn/*` (not only membership-related) redirects to pricing | Student sees "upgrade membership" for non-membership access denials |

## Acceptance (B05)

- [B05 acceptance criteria](../../B-Requirements/B05-acceptance-criteria/) — `lms_user_membership` created with correct `field_start_date`, `field_end_date`, `field_status=active`; `MembershipManager::hasActiveMembership()` returns TRUE within active period; cron sets `expired` after `field_end_date` for non-auto-renew; expiry email dispatched; student redirected to pricing on `/learn/*` after expiry

## Implementation Status

| Step | Status | Notes |
|---|---|---|
| Membership plan CRUD (admin) | DONE | `/workspace/pricing` |
| User membership CRUD (admin) | DONE | `/workspace/user-memberships` |
| Free plan fulfillment | DONE | `createAndFulfillFreeMembershipOrder()` (minor double-set bug) |
| Paid plan checkout (Stripe/bank transfer) | DONE | Shared with WF-004 |
| `MembershipManager::hasActiveMembership()` | DONE | Plain service; no @AccessCheck plugin |
| `MembershipAccessSubscriber` access guard | DONE | Exception handler; false-positive known gap |
| Student cancellation (`field_auto_renew=FALSE`) | DONE | Status stays active until `field_end_date` |
| Cron expiry for `auto_renew=FALSE` | DONE | Sets expired, sends email |
| Cron expiry for `auto_renew=TRUE` | PLANNED (stub) | Defers to non-existent webhook; no charge |
| Auto-renewal billing | PLANNED | No webhook endpoint; effectively not implemented |
| In-app notification on expiry | PLANNED | Cron sends only email; no in-app notification |
| Lifetime plan infinite end date | PLANNED | Admin manual workaround required |
| Trial "one per user per plan" guard | PLANNED | No uniqueness enforcement |
| Trial status auto-assignment on purchase | NOT IMPLEMENTED (bug/gap) | `_lms_order_process_completed_order()` always sets `field_status='active'`; `field_trial_days` is never read in the order hook; trial status must be set manually |
