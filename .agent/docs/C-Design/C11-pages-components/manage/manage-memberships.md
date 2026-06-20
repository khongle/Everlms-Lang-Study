---
page: manage-memberships
mode: as-built
owner_module: lms_membership
route_group: manage
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Manage Memberships

> Covers the workspace membership management surface: Pricing Plans (CRUD + toggle) and User Memberships (CRUD), served by `MembershipAdminController` at `/workspace/pricing` and `/workspace/user-memberships`.

**Canonical URLs:**
- `/workspace/pricing` — Membership Pricing Plans list
- `/workspace/pricing/add` — Add Pricing Plan
- `/workspace/pricing/{lms_membership_plan}/edit` — Edit Pricing Plan
- `/workspace/pricing/{lms_membership_plan}/toggle` — Toggle Plan Status (GET, redirect)
- `/workspace/user-memberships` — User Memberships list
- `/workspace/user-memberships/add` — Add User Membership
- `/workspace/user-memberships/{lms_user_membership}/edit` — Edit User Membership

*(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*

**Route names:**
- `lms_membership.admin_pricing`
- `lms_membership.admin_pricing.add`
- `lms_membership.admin_pricing.edit`
- `lms_membership.admin_pricing.toggle`
- `lms_membership.admin_user_memberships`
- `lms_membership.admin_user_memberships.add`
- `lms_membership.admin_user_memberships.edit`

**Solution:** `MembershipAdminController` custom workspace controller; list views rendered via `lms_admin_pricing` and `lms_admin_user_memberships` Twig themes; entity forms wrapped in `lms_admin_entity_form` theme.
**Owner module:** [`lms_membership`](../../C05-modules/lms_membership.md)
**Primary source:** `lms_membership_plan` entity (plans); `lms_user_membership` entity (user assignments)
**Editable source:** `lms_membership_plan` + `lms_user_membership` entities via workspace forms
**Editor role:** Administrator, Manager
**Editor management UI:** `/workspace/pricing` and `/workspace/user-memberships` (these pages)
**Revision / moderation:** Not revisionable
**Forms:**
- Entity forms for `lms_membership_plan` (default form handler)
- Entity forms for `lms_user_membership` (default + `manager` form mode)
**Theme templates:**
- `web/modules/custom/lms_membership/templates/lms-admin-pricing.html.twig` (plan list)
- `web/modules/custom/lms_membership/templates/lms-admin-user-memberships.html.twig` (user membership list)
- `web/modules/custom/lms_course/templates/lms-admin-entity-form.html.twig` (form wrapper)
**Access:** Role-gated: `administer lms_course`
**Cache:** No-cache (admin workspace, `_admin_route: TRUE`)
**Translation:** Monolingual EN
**Related entities:** [`lms_membership_plan`](../../C04-entities/01-content-entities.md), [`lms_user_membership`](../../C04-entities/01-content-entities.md)
**Prototype reference:** No C10 prototype.
**Notes:**
- Auto-renewal via webhook is explicitly deferred (logged, not implemented). Cron handles expiry for non-auto-renew memberships.
- `MembershipManager::hasActiveMembership()` is the canonical access-check service (called externally by other modules).
- `MembershipAccessSubscriber` redirects `AccessDeniedHttpException` on `/learn/*` paths to the pricing page.

---

## Components (visual order, top-to-bottom)

> Global chrome defined in [`../_common.md`](../_common.md).

### 1. Workspace Sidebar Navigation — *Thanh điều hướng sidebar workspace*

- **Scope:** Global workspace chrome
- **Implementation mechanism:** `lms-admin-sidebar.html.twig` included inside workspace templates
- **Plugin / Module:** `lms_course` (sidebar template), `lms_theme`
- **Source content type / Entity:** Static nav structure in sidebar template
- **Backed by fields / config keys:** Template-rendered links to `/workspace/*` routes
- **Editable source:** `web/modules/custom/lms_course/templates/lms-admin-sidebar.html.twig` (developer-maintained)
- **Editor role / permission:** Developer
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** Left sidebar panel
- **Region / Placement:** `sidebar_first` region or inline in workspace templates
- **Notes:** Shared by all `/workspace/*` pages; `is_manager` flag from theme preprocess controls visibility.
- **How to implement (EN):** Sidebar template at `web/modules/custom/lms_course/templates/lms-admin-sidebar.html.twig`.
- **How to implement (VI):** Template sidebar tại `web/modules/custom/lms_course/templates/lms-admin-sidebar.html.twig`.
- **Solution Architect Request:** —

---

### 2. Membership Plans List — *Danh sách gói hội viên*

**Route:** `/workspace/pricing`

- **Scope:** Page-specific
- **Implementation mechanism:** `MembershipAdminController::pricingList()` → `lms_admin_pricing` theme; template `lms-admin-pricing.html.twig`
- **Plugin / Module:** `lms_membership` → `src/Controller/MembershipAdminController.php`
- **Source content type / Entity:** `lms_membership_plan` entities
- **Backed by fields / config keys:**
  | Field | Display purpose |
  |---|---|
  | `name` | Plan name |
  | `field_price` | Price (decimal) |
  | `field_billing_cycle` | monthly / quarterly / yearly / lifetime |
  | `field_duration_days` | Duration in days |
  | `field_features` | Feature list (unlimited cardinality) |
  | `field_trial_days` | Trial period |
  | `field_recommended` | Recommended badge |
  | `field_course_access` | all / selected |
  | `status` | Active / inactive (boolean) |
- **Editable source:** `/workspace/pricing/{id}/edit` or `/workspace/pricing/add`
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/pricing` (this page); add/edit links per row; toggle status via `/workspace/pricing/{id}/toggle`
- **Revision / moderation:** Not revisionable
- **Translation source:** N/A
- **Edit smoke:** Create plan → observe row in list; toggle status → observe status change
- **View mode / Projection:** Table list with search + status filter; rendered in `lms-admin-pricing.html.twig`
- **Region / Placement:** `content` region (workspace layout)
- **Notes:** `toggleStatus()` flips `status` boolean and redirects back. Search + status filter read from GET params.
- **How to implement (EN):** `MembershipAdminController::pricingList()` in `web/modules/custom/lms_membership/src/Controller/MembershipAdminController.php`. Template: `templates/lms-admin-pricing.html.twig`.
- **How to implement (VI):** Controller tại `src/Controller/MembershipAdminController.php`. Template: `templates/lms-admin-pricing.html.twig`.
- **Solution Architect Request:** —

---

### 3. Add / Edit Membership Plan Form — *Form thêm/sửa gói hội viên*

**Routes:** `/workspace/pricing/add`, `/workspace/pricing/{id}/edit`

- **Scope:** Page-specific
- **Implementation mechanism:** `MembershipAdminController::pricingAddForm()` / `pricingEditForm()` → entity form builder → `lms_admin_entity_form` theme wrapper
- **Plugin / Module:** `lms_membership` + `lms_course` (entity form template)
- **Source content type / Entity:** `lms_membership_plan` entity (ContentEntityForm default handler)
- **Backed by fields / config keys:** All `lms_membership_plan` base fields (see list above); form renders all except system fields
- **Editable source:** `/workspace/pricing/{id}/edit`
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** These form pages
- **Revision / moderation:** Not revisionable
- **Translation source:** N/A
- **Edit smoke:** Edit plan price → save → observe updated price in list
- **View mode / Projection:** ContentEntityForm wrapped in `lms-admin-entity-form.html.twig`
- **Region / Placement:** `content` region (workspace)
- **Notes:** No custom form handler; uses Drupal default ContentEntityForm. `field_features` is unlimited cardinality (multi-value widget).
- **How to implement (EN):** Entity form via `pricingAddForm()` / `pricingEditForm()` in `MembershipAdminController`. Form template: `web/modules/custom/lms_course/templates/lms-admin-entity-form.html.twig`.
- **How to implement (VI):** Entity form qua controller; template `lms-admin-entity-form.html.twig`.
- **Solution Architect Request:** —

---

### 4. Toggle Plan Status — *Bật/tắt trạng thái gói*

**Route:** `/workspace/pricing/{lms_membership_plan}/toggle` (GET, redirect)

- **Scope:** Page-specific (action, no template)
- **Implementation mechanism:** `MembershipAdminController::toggleStatus()` flips `status` field; saves entity; redirects to `lms_membership.admin_pricing`
- **Plugin / Module:** `lms_membership`
- **Source content type / Entity:** `lms_membership_plan.status` boolean field
- **Backed by fields / config keys:** `status`
- **Editable source:** Triggered from list page row action link
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** List page action link
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Click toggle → redirect → observe status changed in list
- **View mode / Projection:** No template (redirect response)
- **Region / Placement:** N/A
- **Notes:** Active plan can be deactivated; deactivated plan can be reactivated. No cascade effect on existing `lms_user_membership` entities.
- **How to implement (EN):** `toggleStatus()` method in `MembershipAdminController`.
- **How to implement (VI):** Phương thức `toggleStatus()` trong `MembershipAdminController`.
- **Solution Architect Request:** —

---

### 5. User Memberships List — *Danh sách hội viên người dùng*

**Route:** `/workspace/user-memberships`

- **Scope:** Page-specific
- **Implementation mechanism:** `MembershipAdminController::userMembershipsList()` → `lms_admin_user_memberships` theme; template `lms-admin-user-memberships.html.twig`
- **Plugin / Module:** `lms_membership` → `MembershipAdminController`
- **Source content type / Entity:** `lms_user_membership` entities (with user + plan data)
- **Backed by fields / config keys:**
  | Field | Display purpose |
  |---|---|
  | `uid` → user entity | User avatar, name, email |
  | `field_plan` → `lms_membership_plan` | Plan name |
  | `field_start_date` | Start timestamp |
  | `field_end_date` | End timestamp |
  | `field_status` | active / trial / expired / cancelled |
  | `field_auto_renew` | Auto-renewal flag |
  | `created` | Created timestamp |
- **Editable source:** `/workspace/user-memberships/{id}/edit` or `/workspace/user-memberships/add`
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/user-memberships` (this page); add/edit links per row
- **Revision / moderation:** Not revisionable
- **Translation source:** N/A
- **Edit smoke:** Add user membership manually → observe row; check expiry cron → observe status→expired for past end_date
- **View mode / Projection:** Table list with status filter; user avatar via `ui-avatars.com` fallback if no picture
- **Region / Placement:** `content` region (workspace)
- **Notes:**
  - Status filter reads GET `?status=` param.
  - `lms_membership.module` `hook_cron()` sets expired status for non-auto-renew memberships with `field_end_date <= now`.
  - Auto-renewal via payment gateway webhook: **NOT implemented** — cron only logs and defers (known gap).
  - `MembershipManager::hasActiveMembership()` checks for status in [active, trial] with end_date in future OR `administer lms_course` permission.
- **How to implement (EN):** `MembershipAdminController::userMembershipsList()` in `src/Controller/MembershipAdminController.php`. Template: `templates/lms-admin-user-memberships.html.twig`.
- **How to implement (VI):** Controller tại `src/Controller/MembershipAdminController.php`. Template: `templates/lms-admin-user-memberships.html.twig`.
- **Solution Architect Request:** —

---

### 6. Add / Edit User Membership Form — *Form thêm/sửa hội viên người dùng*

**Routes:** `/workspace/user-memberships/add`, `/workspace/user-memberships/{id}/edit`

- **Scope:** Page-specific
- **Implementation mechanism:** `userMembershipAddForm()` / `userMembershipEditForm()` → entity form builder; edit uses `manager` form mode
- **Plugin / Module:** `lms_membership`
- **Source content type / Entity:** `lms_user_membership` entity
- **Backed by fields / config keys:** `uid` (autocomplete), `field_plan` (entity_reference), `field_start_date`, `field_end_date`, `field_status`, `field_auto_renew`
- **Editable source:** These form pages
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** These form pages
- **Revision / moderation:** Not revisionable
- **Translation source:** N/A
- **Edit smoke:** Add membership for user → save → verify in list; edit end_date → save → verify
- **View mode / Projection:** ContentEntityForm wrapped in `lms-admin-entity-form.html.twig`; edit uses `manager` form mode (may show fewer/different fields)
- **Region / Placement:** `content` region (workspace)
- **Notes:** Admin-managed manual membership assignment — no payment flow triggered. To grant membership via payment, checkout flow creates `lms_user_membership` automatically via `lms_order.module hook_entity_update`.
- **How to implement (EN):** `userMembershipAddForm()` / `userMembershipEditForm()` in `MembershipAdminController`. Form mode `manager` defined in entity annotation handlers.
- **How to implement (VI):** Phương thức trong `MembershipAdminController`; form mode `manager` trong annotation entity.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Workspace Sidebar | Nav links to all workspace sections | Static template | N/A | Developer (template) | `administer lms_course` | N/A | N/A | `user.roles` | N/A |
| Membership Plans List | Plan name, price, billing cycle, duration, features, trial days, recommended, status | `lms_membership_plan` entities | All base fields | `/workspace/pricing/add`, `/workspace/pricing/{id}/edit` | `administer lms_course` | Not revisionable | EN | no-cache | Create plan → observe row |
| Add/Edit Plan Form | All plan fields (name, price, billing cycle, duration, features, course access, trial, recommended) | `lms_membership_plan` entity | All base fields | `/workspace/pricing/add` or `/workspace/pricing/{id}/edit` | `administer lms_course` | N/A | EN | no-cache | Edit price → save → verify |
| Toggle Plan Status | Status field (active/inactive) | `lms_membership_plan.status` | `status` boolean | Row action link on list | `administer lms_course` | N/A | EN | no-cache | Toggle → observe status change |
| User Memberships List | User avatar/name/email, plan name, start/end dates, status, auto-renew | `lms_user_membership` + `user` + `lms_membership_plan` | All `lms_user_membership` base fields | `/workspace/user-memberships/add`, `/workspace/user-memberships/{id}/edit` | `administer lms_course` | Not revisionable | EN | no-cache | Add membership → observe row |
| Add/Edit User Membership Form | User (autocomplete), plan, start/end date, status, auto-renew | `lms_user_membership` entity | `uid`, `field_plan`, `field_start_date`, `field_end_date`, `field_status`, `field_auto_renew` | `/workspace/user-memberships/add` or `/{id}/edit` | `administer lms_course` | N/A | EN | no-cache | Add membership → save → verify |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| `field_status` allowed values (active/trial/expired/cancelled) | Defined in entity PHP `baseFieldDefinitions()` | D00 code convention | On business rule change |
| `field_billing_cycle` values (monthly/quarterly/yearly/lifetime) | Defined in entity PHP | D00 code convention | On billing model expansion |
| Auto-renewal webhook gap ("logs and defers") | Not implemented — payment gateway webhook absent | Known issue | When webhook infrastructure is built |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Pricing Plans CRUD | DONE | | All CRUD + toggle fully implemented |
| User Memberships CRUD | DONE | — | Manual assignment works; auto-enrollment via order also works |
| Auto-renewal via webhook | NOT IMPLEMENTED | — | Cron only logs; webhook absent; `field_auto_renew=TRUE` deferred |
| Membership expiry cron | DONE | — | `hook_cron` sets status=expired for non-auto-renew past end_date |
| `MembershipAccessSubscriber` redirect | DONE | — | Redirects 403 on `/learn/*` to pricing page |
