---
page: my-membership
mode: as-built
owner_module: lms_membership
route_group: app
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: My Membership

**Canonical URL:** `/my-membership` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_membership.my_membership`
**Solution:** Custom controller (`MembershipController::myMembership`) queries active/trial `lms_user_membership` entities for the current user; returns `lms_my_membership` theme render array.
**Owner module:** [`lms_membership`](../../C05-modules/lms_membership.md)
**Primary source:** `entity:lms_user_membership` (status in `active`, `trial`) for current user
**Editable source:** `lms_membership_plan` entities (plan details shown in card); `lms_user_membership` (status/dates set by system on checkout completion or cron)
**Editor role:** Administrator (`administer lms_course`) for plan management; system for membership record lifecycle
**Editor management UI:** `/workspace/pricing/{plan_id}/edit` (plan details); `/workspace/user-memberships/{id}/edit` (admin user membership edit)
**Revision / moderation:** Not revisionable
**Forms:** Cancel membership via `GET /my-membership/{lms_user_membership}/cancel` (`lms_membership.cancel_membership`)
**Theme template:** `web/modules/custom/lms_membership/templates/lms-my-membership.html.twig` *(see [`../../C09-skins.md`](../../C09-skins.md))*
**Access:** Authenticated (`_user_is_logged_in: TRUE`)
**Cache:** Per-user — membership status changes on checkout, cancellation, and cron expiry
**Translation:** Monolingual EN
**Related entities:** [`lms_user_membership`](../../C04-entities/README.md), [`lms_membership_plan`](../../C04-entities/README.md)
**Prototype reference:** [`../../C10-prototypes/html/account/my-learning.html`](../../C10-prototypes/html/account/my-learning.html)
**Notes:** Cancel membership (`MembershipController::cancelMembership`) sets `field_auto_renew=FALSE` — it does NOT immediately expire the membership. The membership remains active until `field_end_date` is reached, at which point `hook_cron` sets status to `expired`. Auto-renewal via payment gateway webhook is a TODO (logged by cron but not implemented — no webhook endpoint exists). The `MembershipManager::hasActiveMembership()` service is the canonical access-check; it is not wired into any Drupal Access plugin — external modules must call it explicitly.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined once in [`../_common.md`](../_common.md) and not repeated here.

### 1. No Active Membership State

- **Scope:** Page-specific
- **Implementation mechanism:** Twig conditional when `memberships` variable is empty
- **Plugin / Module:** `lms_membership` — `lms-my-membership.html.twig`
- **Source content type / Entity:** Static chrome
- **Backed by fields / config keys:** Static text
- **Editable source:** computed/read-only
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** Empty state block with CTA to `/pricing`
- **Region / Placement:** Main content area (only shown when no active membership)
- **Notes:** Should provide clear CTA linking to `/pricing` for plan selection.
- **How to implement (EN):** `{% if memberships is empty %}` renders empty-state block with link to `lms_course.pricing` route.
- **How to implement (VI):** `{% if memberships is empty %}` hiển thị trạng thái trống với link đến route `lms_course.pricing`.
- **Solution Architect Request:** —

### 2. Active Membership Card(s)

- **Scope:** Page-specific
- **Implementation mechanism:** Twig loop over `memberships` array from controller; builds a card per `lms_user_membership` entity
- **Plugin / Module:** `lms_membership` — `MembershipController::myMembership` + `lms-my-membership.html.twig`
- **Source content type / Entity:** `lms_user_membership`, `lms_membership_plan`
- **Backed by fields / config keys:** `lms_user_membership`: `field_plan` (plan ref), `field_start_date`, `field_end_date`, `field_status` (`active`/`trial`), `field_auto_renew`; `lms_membership_plan`: `name`, `field_price`, `field_billing_cycle`, `field_features`, `field_description`, `field_trial_days`
- **Editable source:** `lms_membership_plan` entity (plan details); `lms_user_membership` (dates/status — system-managed)
- **Editor role / permission:** `administer lms_course` (plan edits); system (membership record)
- **Editor management UI:** `/workspace/pricing/{plan_id}/edit`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Admin edits plan name → user reloads page → confirm updated plan name on card
- **View mode / Projection:** Membership status card: plan name, billing cycle, status badge, start/end dates, feature list, auto-renew indicator
- **Region / Placement:** Main content area
- **Notes:** A user may theoretically have multiple memberships (e.g. trial + active). Controller loads all with `field_status IN [active, trial]`. `field_billing_cycle` values: monthly/quarterly/yearly/lifetime. Days-remaining computed from `field_end_date - now()`.
- **How to implement (EN):** Controller queries `lms_user_membership` by `uid=current_user` and `field_status IN [active, trial]`; for each loads the `field_plan` entity; builds display array. Template loops over `memberships`.
- **How to implement (VI):** Controller truy vấn `lms_user_membership` theo `uid=current_user` và `field_status IN [active, trial]`; với mỗi item load entity `field_plan`; xây dựng mảng hiển thị. Template lặp qua `memberships`.
- **Solution Architect Request:** —

### 3. Cancel Membership Action

- **Scope:** Page-specific
- **Implementation mechanism:** Link to `GET /my-membership/{lms_user_membership}/cancel` (`lms_membership.cancel_membership`) which sets `field_auto_renew=FALSE`
- **Plugin / Module:** `lms_membership` — `MembershipController::cancelMembership`
- **Source content type / Entity:** `lms_user_membership.field_auto_renew`
- **Backed by fields / config keys:** `field_auto_renew` (boolean)
- **Editable source:** `lms_user_membership` entity (system-side update; no form — direct GET action)
- **Editor role / permission:** Authenticated (self; controller does not yet enforce ownership check beyond `_user_is_logged_in`)
- **Editor management UI:** Inline "Cancel" link on membership card
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Click Cancel → redirect to `/my-membership` → confirm `field_auto_renew=FALSE` in admin view
- **View mode / Projection:** "Cancel Membership" link/button, shown only when `field_auto_renew=TRUE`
- **Region / Placement:** Bottom of membership card
- **Notes:** Cancel does NOT immediately expire the membership. The plan remains accessible until `field_end_date`. Cron (`hook_cron` in `lms_membership.module`) checks expiry and sets `field_status=expired`. No confirmation dialog in the current implementation — single GET action.
- **How to implement (EN):** Render `path('lms_membership.cancel_membership', {lms_user_membership: id})` as a link. The controller sets `field_auto_renew=FALSE` and redirects to `lms_membership.my_membership`. Consider adding CSRF/confirmation step.
- **How to implement (VI):** Render `path('lms_membership.cancel_membership', {lms_user_membership: id})` dưới dạng link. Controller set `field_auto_renew=FALSE` và redirect về `lms_membership.my_membership`. Nên thêm CSRF/xác nhận.
- **Solution Architect Request:** —

### 4. Upgrade / Browse Plans CTA

- **Scope:** Page-specific
- **Implementation mechanism:** Static link to `/pricing` route
- **Plugin / Module:** `lms_membership` — `lms-my-membership.html.twig`
- **Source content type / Entity:** Static chrome
- **Backed by fields / config keys:** Static
- **Editable source:** computed/read-only
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** Button link below membership card(s)
- **Region / Placement:** Below membership card section
- **Notes:** Allows user to view and purchase a different plan.
- **How to implement (EN):** Static `<a href="{{ path('lms_course.pricing') }}">Browse Plans</a>` below the memberships section.
- **How to implement (VI):** Link tĩnh `<a href="{{ path('lms_course.pricing') }}">Xem các gói</a>` bên dưới phần danh sách gói.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| No Active Membership State | Empty-state message + CTA to `/pricing` | Static chrome | N/A | computed/read-only | N/A | N/A | N/A | N/A | N/A |
| Active Membership Card(s) | Plan name, billing cycle, status badge, start/end dates, days remaining, feature list, auto-renew indicator | `lms_user_membership`, `lms_membership_plan` | `field_plan`, `field_start_date`, `field_end_date`, `field_status`, `field_auto_renew`, plan `name`, `field_billing_cycle`, `field_features` | `/workspace/pricing/{id}/edit` (plan); `/workspace/user-memberships/{id}/edit` (membership) | `administer lms_course` (admin); system (lifecycle) | N/A | Monolingual EN | per-user | Checkout membership → load page → confirm plan card |
| Cancel Membership Action | "Cancel Membership" link/button | `lms_user_membership.field_auto_renew` | `field_auto_renew` | Inline link on card | Authenticated (owner) | N/A | N/A | per-user | Click Cancel → confirm `field_auto_renew=FALSE` |
| Browse Plans CTA | "Browse Plans" or "Upgrade" link | Static chrome | N/A | computed/read-only | N/A | N/A | N/A | N/A | N/A |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| "My Membership" page title | Static UI chrome | Developer convention | — |
| "Cancel Membership" link label | Static developer string | Developer convention | — |
| No confirmation dialog for cancel | Cancel is a GET action; no CSRF guard | Known UX gap | Add confirmation step / POST form |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| No Active Membership State | DONE | — | As-built |
| Active Membership Card(s) | DONE | — | As-built; plan + dates + features displayed |
| Cancel Membership Action | DONE | — | As-built; sets `field_auto_renew=FALSE` only; no confirmation dialog |
| Upgrade / Browse Plans CTA | DONE | — | As-built (static link) |
| Auto-renewal via webhook | NOT IMPLEMENTED | — | Cron logs "auto-renew triggered" but defers to gateway webhook; no webhook endpoint exists — see E02-issues |
| Ownership enforcement on cancel | GAP | — | `cancelMembership` only checks `_user_is_logged_in`; does not verify ownership of the `lms_user_membership` entity |
