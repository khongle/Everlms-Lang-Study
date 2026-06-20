---
module: lms_membership
service: Commerce
route-groups: [App, Manage]
status: DONE
---

# C05 — Module: LMS Membership

**Directory:** `web/modules/custom/lms_membership/`
**Responsibility:** Manages membership plan templates and per-user subscription instances, including admin CRUD, cron-based expiry, access enforcement on `/learn/` and `/course/` paths, and a user-facing membership portal.

**Documentation mode:** as-built

---

## Route Bindings

| Route Group | Prefix | Controller | Paths |
|-------------|--------|-----------|-------|
| App | `/my-membership` | `MembershipController` | `/my-membership`, `/my-membership/{lms_user_membership}/cancel` |
| Manage | `/workspace` | `MembershipAdminController` | `/workspace/pricing`, `/workspace/pricing/add`, `/workspace/pricing/{id}/edit`, `/workspace/pricing/{id}/toggle`, `/workspace/user-memberships`, `/workspace/user-memberships/add`, `/workspace/user-memberships/{id}/edit` |
| Admin (entity auto) | `/manage/memberships` | `AdminHtmlRouteProvider` | CRUD for `lms_membership_plan` |
| Admin (entity auto) | `/manage/user-memberships` | `AdminHtmlRouteProvider` | CRUD for `lms_user_membership` |

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `MembershipManager` | Service (`lms_membership.manager`) | `hasActiveMembership(AccountInterface): bool` — canonical active-status check; used by other modules |
| `MembershipAccessSubscriber` | Event Subscriber (`lms_membership.access_subscriber`) | Redirects `AccessDeniedHttpException` on `/learn/` or `/course/` to `lms_course.pricing` |
| `MembershipPlan` (`lms_membership_plan`) | Content Entity | Plan template: price, duration, billing cycle, features |
| `UserMembership` (`lms_user_membership`) | Content Entity | Per-user subscription instance: dates, status, auto-renew flag |

---

## Internal Structure

```
web/modules/custom/lms_membership/
+-- lms_membership.info.yml             # deps: lms_base, lms_payment
+-- lms_membership.routing.yml          # 9 routes (see Route Bindings)
+-- lms_membership.services.yml         # lms_membership.manager, lms_membership.access_subscriber
+-- lms_membership.links.menu.yml       # Admin menu links for plan + user membership collections
+-- lms_membership.links.action.yml     # "Add" action links on collection pages
+-- lms_membership.module               # hook_theme, hook_cron, hook_mail
+-- src/
|   +-- MembershipManager.php           # hasActiveMembership() — checks permission OR active/trial + end_date
|   +-- Controller/
|   |   +-- MembershipController.php    # myMembership(), cancelMembership()
|   |   +-- MembershipAdminController.php  # pricingList/Add/Edit, toggleStatus, userMembershipsList/Add/Edit
|   +-- EventSubscriber/
|   |   +-- MembershipAccessSubscriber.php  # KernelEvents::EXCEPTION priority 50
|   +-- Entity/
|   |   +-- MembershipPlan.php
|   |   +-- UserMembership.php
|   +-- MembershipPlanListBuilder.php
|   +-- UserMembershipListBuilder.php
+-- templates/
|   +-- lms-admin-pricing.html.twig
|   +-- lms-admin-user-memberships.html.twig
|   +-- lms-my-membership.html.twig
```

### Key Entities

#### `lms_membership_plan` (base table: `lms_membership_plan`)

| Field | Type | Notes |
|-------|------|-------|
| id, uuid | auto | entity keys |
| name | string(255) | required; label key |
| field_description | text_long | — |
| field_price | decimal(10,2) | default 0 |
| field_duration_days | integer | default 30 |
| field_billing_cycle | list_string | monthly / quarterly / yearly / lifetime; default monthly |
| field_features | string(255) | unlimited cardinality |
| field_course_access | list_string | all / selected; default all |
| field_trial_days | integer | default 0 |
| field_recommended | boolean | default FALSE |
| status | boolean | TRUE = active/inactive toggle |

#### `lms_user_membership` (base table: `lms_user_membership`)

| Field | Type | Notes |
|-------|------|-------|
| id, uuid | auto | entity keys |
| uid | entity_reference (user) | owner; `EntityOwnerTrait`; autocomplete widget |
| field_plan | entity_reference | target: `lms_membership_plan`; required |
| field_start_date | timestamp | — |
| field_end_date | timestamp | — |
| field_status | list_string | active / trial / expired / cancelled; default active |
| field_auto_renew | boolean | default TRUE |
| created | created | — |

### Service: `MembershipManager`

```
lms_membership.manager  →  MembershipManager(@entity_type.manager)
```

| Method | Behavior |
|--------|----------|
| `hasActiveMembership(AccountInterface $account): bool` | Returns TRUE if account has `administer lms_course` permission OR has a `lms_user_membership` with `field_status` in [active, trial] AND `field_end_date > now()` |

> This service is the canonical access gate. It is NOT wired into a Drupal Access plugin — other modules call it directly (e.g., `lms_course` for gated content).

### Event Subscriber: `MembershipAccessSubscriber`

- Listens: `KernelEvents::EXCEPTION`, priority 50.
- Intercept condition: `AccessDeniedHttpException` on a path beginning with `/learn/` or `/course/`.
- Action: Redirects to `lms_course.pricing` route with a messenger warning.

### Hook Implementations

| Hook | Behavior |
|------|----------|
| `hook_theme` | Registers `lms_my_membership`, `lms_admin_pricing`, `lms_admin_user_memberships` |
| `hook_cron` | Queries active/trial memberships where `field_end_date <= now()`; if `field_auto_renew = FALSE` → sets `field_status = expired`, sends `membership_expired` mail; if `field_auto_renew = TRUE` → logs and defers (no actual renewal — **target-state**) |
| `hook_mail` | Key `membership_expired`: sends expiry notification with plan name |

### Auto-Renewal Status

`hook_cron` logs a deferred notice for auto-renew memberships but performs no actual charge. Webhook-driven renewal is explicitly deferred (no webhook endpoints exist in any module). **Status: target-state / planned** — not implemented.

### Known Issues / Risks

| ID | Severity | Description |
|----|----------|-------------|
| RISK-MEMBERSHIP-CANCEL | Medium | `cancelMembership()` does not verify entity ownership — any logged-in user knowing the `{lms_user_membership}` ID can cancel another user's membership |
| DEBT-NO-AUTO-RENEW | Medium | `hook_cron` defers auto-renewal without implementing it; no webhook endpoint receives payment provider callbacks |
| DEBT-MANAGER-METHODS | Low | `MembershipManager` exposes only `hasActiveMembership()`; no `getActiveMembership()`, `isExpiringSoon()`, etc. |

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_base` | Internal | Base traits, helper |
| `lms_payment` | Internal | Membership checkout consumes payment gateway |
| `lms_order` | Internal (runtime) | `lms_order.module` creates `lms_user_membership` on completed order |
| Symfony `HttpKernel` | Framework | `KernelEvents::EXCEPTION` for access subscriber |

**No circular dependencies.** `lms_payment` does not depend on `lms_membership`.

---

## Cross-links

- Entities: [MembershipPlan, UserMembership](../C04-entities/01-content-entities.md)
- Order checkout: [lms_order](lms_order.md)
- Payment plugin host: [lms_payment](lms_payment.md)
