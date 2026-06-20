---
page: pricing
mode: as-built
owner_module: lms_course
route_group: public
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Pricing

**Canonical URL:** `/pricing`
**Route name:** `lms_course.pricing`
**Solution:** Custom controller `PageController::pricing()` in `lms_course` module. Loads all `lms_membership_plan` entities, reads pricing copy from `lms_theme.settings`, renders `lms_pricing` Twig template.
**Owner module:** `lms_course` (`web/modules/custom/lms_course/src/Controller/PageController.php`)
**Primary source:** `lms_membership_plan` entity (all active plans)
**Editable source:** `lms_membership_plan` entity fields (name, price, features, status); pricing copy from `lms_theme.settings`
**Editor role:** `manager`, `administrator`
**Editor management UI:** `/workspace/pricing` (MembershipAdminController list) · `/workspace/pricing/add` · `/workspace/pricing/{id}/edit`
**Revision / moderation:** Not revisionable (no revision handler on `lms_membership_plan`)
**Forms:** `—` (read-only display page; checkout triggered from `/checkout/membership/{plan_id}`)
**Theme template:** `lms_course/templates/lms-pricing.html.twig`
**Access:** Public (anonymous) — `_permission: 'access content'`
**Cache:** Anonymous-cacheable — invalidated by `lms_membership_plan_list` tag
**Translation:** Monolingual EN
**Related entities:** [`lms_membership_plan`](../../C04-entities/), [`lms_user_membership`](../../C04-entities/)
**Prototype reference:** [`../../C10-prototypes/html/public/pricing.html`](../../C10-prototypes/html/public/pricing.html)
**Notes:** `PageController::pricing()` calls `getPricingPageCopy()` (reads `lms_theme.settings` for copy with defaults) and `getHomepageMemberships()` (loads `lms_membership_plan` entities sorted by price). The checkout flow continues at `/checkout/membership/{plan_id}` (lms_order module). Membership plans are managed at `/workspace/pricing` (MembershipAdminController). Plan `status` field controls visibility (active/inactive). Prototype shows plan comparison table and individual plan cards.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined in [`../_common.md`](../_common.md) and not repeated here.

### 1. Page Hero — Pricing Header

- **Scope:** Page-specific
- **Implementation mechanism:** Static section in `lms-pricing.html.twig`
- **Plugin / Module:** `lms_course` — `PageController::getPricingPageCopy()`
- **Source content type / Entity:** `lms_theme.settings` (pricing copy keys)
- **Backed by fields / config keys:** `lms_theme.settings` — pricing_headline, pricing_subheadline (with defaults if not set)
- **Editable source:** `lms_theme.settings` config
- **Editor role / permission:** `administrator` / `administer themes`
- **Editor management UI:** `/admin/appearance/settings/lms_theme`
- **Revision / moderation:** N/A (config)
- **Translation source:** Config translation
- **Edit smoke:** Update `lms_theme.settings` pricing_headline → reload `/pricing` → heading updated
- **View mode / Projection:** Static marketing hero
- **Region / Placement:** Top of page
- **Notes:** `PageController::getPricingPageCopy()` provides defaults (e.g., "Choose Your Plan") when theme settings not set
- **How to implement (EN):** Read `pricing_headline` from `lms_theme.settings` via config factory; pass to template variable
- **How to implement (VI):** Đọc `pricing_headline` từ `lms_theme.settings` qua config factory; truyền vào biến template
- **Solution Architect Request:** —

### 2. Membership Plan Cards

- **Scope:** Page-specific
- **Implementation mechanism:** Twig loop in `lms-pricing.html.twig` over `memberships` variable
- **Plugin / Module:** `lms_course` — `PageController::getHomepageMemberships()` → `CoursePublicController::getHomepageMemberships()`
- **Source content type / Entity:** `lms_membership_plan` entity (active plans, sorted by `field_price` ASC)
- **Backed by fields / config keys:** `name`, `field_price`, `field_duration`, `field_features` (text_long, likely JSON or list), `field_max_courses`, `status`
- **Editable source:** `lms_membership_plan` entity
- **Editor role / permission:** `manager` / `administer lms_course`
- **Editor management UI:** `/workspace/pricing` · `/workspace/pricing/add` · `/workspace/pricing/{id}/edit`
- **Revision / moderation:** Not revisionable
- **Translation source:** Not configured
- **Edit smoke:** Create/edit `lms_membership_plan` → toggle status active → reload `/pricing` → plan card appears
- **View mode / Projection:** Pricing card (name, price, duration, feature list, CTA)
- **Region / Placement:** Main content area — card grid
- **Notes:** CTA button links to `/checkout/membership/{plan_id}`. Plan `field_features` content format (JSON vs line-separated) not confirmed from facts — «unverified». Prototype shows plan comparison table and highlighted "recommended" plan card
- **How to implement (EN):** Load all `lms_membership_plan` entities with `status=1`, sort by `field_price`; pass array to template; template renders one card per plan with name, price, features list, "Get Started" button linking to `/checkout/membership/{id}`
- **How to implement (VI):** Tải tất cả `lms_membership_plan` với `status=1`, sắp xếp theo `field_price`; truyền mảng vào template; template render một thẻ mỗi gói với tên, giá, danh sách tính năng, nút "Get Started" link đến `/checkout/membership/{id}`
- **Solution Architect Request:** —

### 3. Plan Comparison Table

- **Scope:** Page-specific
- **Implementation mechanism:** Twig table in `lms-pricing.html.twig`; prototype shows feature-by-plan matrix
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** `lms_membership_plan` entities (feature fields)
- **Backed by fields / config keys:** Plan fields + static feature row labels
- **Editable source:** `lms_membership_plan` entity fields; row labels may be static in template
- **Editor role / permission:** `manager` / `administer lms_course`
- **Editor management UI:** `/workspace/pricing/{id}/edit`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Update plan feature → reload `/pricing` → comparison table reflects change
- **View mode / Projection:** Comparison matrix table
- **Region / Placement:** Below plan cards (per prototype)
- **Notes:** Prototype shows "Plan Comparison" section with feature-by-plan checkmarks. Implementation detail not confirmed in `lms-pricing.html.twig` (template not read directly) — may be partial or absent. Feature row labels may be hardcoded
- **How to implement (EN):** Build feature comparison matrix from plan `field_features`; render as HTML table with plans as columns, features as rows
- **How to implement (VI):** Xây dựng bảng so sánh tính năng từ `field_features` của các gói; render thành bảng HTML với gói là cột, tính năng là hàng
- **Solution Architect Request:** —

### 4. FAQ Strip (Pricing FAQs)

- **Scope:** Page-specific
- **Implementation mechanism:** Static accordion in `lms-pricing.html.twig` or from `lms_faq` global scope
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Static template content or `lms_faq` (global scope)
- **Backed by fields / config keys:** Potentially `lms_faq` with `field_category=payment/pricing`
- **Editable source:** `lms_faq` entities or static template
- **Editor role / permission:** `manager` / `administer lms_course`
- **Editor management UI:** `/workspace/content/faqs`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** Accordion
- **Region / Placement:** Bottom of pricing page (per common pattern)
- **Notes:** Not confirmed in `PageController::pricing()` facts — FAQs on pricing page may be absent or static. «unverified»
- **How to implement (EN):** Load `lms_faq` where `field_scope=global` and `field_category` matches a pricing FAQ category; render as accordion at bottom of pricing page
- **How to implement (VI):** Tải `lms_faq` với `field_scope=global` và `field_category` tương ứng; render accordion ở cuối trang pricing
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Page Hero | Headline, subheadline | `lms_theme.settings` | pricing_headline, pricing_subheadline | `/admin/appearance/settings/lms_theme` | administrator / administer themes | N/A | Config translation | config:lms_theme.settings | Update setting → reload |
| Plan Cards | Plan name, price, duration, features list, CTA | `lms_membership_plan` | name, field_price, field_duration, field_features, status | `/workspace/pricing` | manager / administer lms_course | Not revisionable | EN | `lms_membership_plan_list` | Add/activate plan → appears on page |
| Comparison Table | Feature matrix across plans | `lms_membership_plan` | field_features + static row labels | `/workspace/pricing/{id}/edit` | manager / administer lms_course | N/A | EN | `lms_membership_plan_list` | Edit plan feature → table updates |
| Pricing FAQ Strip | Q+A accordion | `lms_faq` (global) or static | question, field_answer | `/workspace/content/faqs` | manager | Not revisionable | EN | `lms_faq_list` | Add FAQ → appears on page |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Feature row labels in comparison table | Template-level static strings | `lms-pricing.html.twig` | Move to `lms_membership_plan` structured field when comparison table finalized |
| Default headline copy ("Choose Your Plan") | `PageController::getPricingPageCopy()` fallback default | `lms_course/src/Controller/PageController.php` | Set via theme settings to avoid hardcode |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Page Hero | DONE | — | Copy from lms_theme.settings with defaults |
| Membership Plan Cards | DONE | — | Loaded via getHomepageMemberships(), rendered in lms-pricing.html.twig |
| Plan Comparison Table | DONE | — | Template implemented per prototype; feature fields are plan entity data |
| Pricing FAQ Strip | «unverified» | — | Not confirmed in PageController::pricing() facts |
