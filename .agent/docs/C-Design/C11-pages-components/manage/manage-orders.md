---
page: manage-orders
mode: as-built
owner_module: lms_course
route_group: manage
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Manage Orders

**Canonical URL:** `/workspace/orders` *(per [`../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.admin_orders`
**Solution:** Custom controller (`AdminController::orderList`) renders `lms_admin_orders` Twig template; order edit uses `lms_admin_entity_form` wrapper with `ContentEntityForm`. Enrollment is triggered by `lms_order.module` `hook_entity_update` on status transition to `completed`.
**Owner module:** `lms_course` (routing/controller); entity owned by [`lms_order`](../../C05-modules/lms_order.md)
**Primary source:** `entity:lms_order`
**Editable source:** `lms_order` entity — fields: `field_order_number`, `field_status`, `field_payment_method`, `field_payment_id`, `field_subtotal`, `field_discount`, `field_total`, `field_currency`, `field_coupon_code`, `field_reminder_sent`, `field_payment_receipt`; related `lms_order_item` (field_course, field_membership_plan, field_price, field_type)
**Editor role:** Administrator
**Editor management UI:** `/workspace/orders` (list), `/workspace/orders/{id}/edit` (edit/approve)
**Revision / moderation:** Not revisionable; no workflow.
**Forms:** `ContentEntityForm` (default; no custom form class for Order in workspace context); `BankTransferForm` (at `/checkout/bank-transfer/{order}` — public-facing receipt upload)
**Theme template:** `lms_course/templates/lms-admin-orders.html.twig` (list); `lms_course/templates/lms-admin-entity-form.html.twig` (edit)
**Access:** Role-gated: `administer lms_course` (list + edit)
**Cache:** Per-user; no anonymous caching. Invalidated on `lms_order` entity save/delete.
**Translation:** Monolingual EN
**Related entities:** [`lms_order`](../../C04-entities/01-content-entities.md), [`lms_order_item`](../../C04-entities/01-content-entities.md), [`lms_user_course`](../../C04-entities/01-content-entities.md), [`lms_user_membership`](../../C04-entities/01-content-entities.md)
**Prototype reference:** [`../../C10-prototypes/html/manage/orders.html`](../../C10-prototypes/html/manage/orders.html)
**Notes:** Setting `field_status` to `completed` via the edit form triggers `hook_entity_update` → `_lms_order_process_completed_order()` which creates `lms_user_course` / `lms_user_membership` records and sends enrollment email + in-app notification. Payment gateway plugins: `stripe` (real PaymentIntent), `bank_transfer` (real manual flow), `paypal` (stub in lms_payment), `zalopay` (stub). No webhook endpoints — async payment confirmation is not implemented. Stripe PaymentIntent is created on form BUILD in `CheckoutForm::buildForm()` (one per page load — known bug). The `OrderListBuilder` operation label is overridden to "View / Edit / Approve".

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined ONCE in [`../_common.md`](../_common.md) and NOT repeated here.

### 1. Workspace Sidebar Navigation

- **Scope:** Global chrome (workspace)
- **Implementation mechanism:** Twig include — `lms-admin-sidebar.html.twig`
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Static nav links
- **Backed by fields / config keys:** Static template markup
- **Editable source:** Static — developer-owned
- **Editor role / permission:** N/A
- **Editor management UI:** `«static»`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** N/A
- **Region / Placement:** Left sidebar
- **Notes:** Shared across all `/workspace/*` pages

### 2. Order List Header + Summary Stats

- **Scope:** Page-specific
- **Implementation mechanism:** `lms-admin-orders.html.twig` template section; `AdminController::orderList` passes stats array
- **Plugin / Module:** `lms_course` / `AdminController::orderList`
- **Source content type / Entity:** `lms_order` aggregate
- **Backed by fields / config keys:** `field_status` (counts by status), `field_total` (revenue sum on completed orders), `field_currency`
- **Editable source:** Read-only aggregate; underlying data via order edit
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/orders`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Mark order as `completed` → revenue total updates
- **View mode / Projection:** Summary stats block
- **Region / Placement:** Top of content area
- **Notes:** Stats computed per page load; no dedicated cache invalidation

### 3. Order List Table

- **Scope:** Page-specific
- **Implementation mechanism:** `lms-admin-orders.html.twig`; `AdminController::orderList` passes order rows; `OrderListBuilder` columns used as reference
- **Plugin / Module:** `lms_course` + `lms_order`
- **Source content type / Entity:** `lms_order` entity
- **Backed by fields / config keys:** `id`, `field_order_number`, `uid` (customer), `field_total`, `field_payment_method`, `field_status`, `created`; customer display name via `user` entity
- **Editable source:** `lms_order` entity via `/workspace/orders/{id}/edit`
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/orders/{id}/edit`
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Edit order status → row status badge updates on reload
- **View mode / Projection:** Table row (order#, customer, total, payment method, status [color-coded], created, actions)
- **Region / Placement:** Main content area
- **Notes:** Status values: pending / processing / completed / cancelled / refunded. Color-coded badges per status. Operation link label: "View / Edit / Approve" (overridden in `OrderListBuilder`).

### 4. Order Edit / Approve Form

- **Scope:** Page-specific
- **Implementation mechanism:** `/workspace/orders/{lms_order}/edit` → `AdminController::orderEditForm` → `ContentEntityForm` wrapped in `lms-admin-entity-form.html.twig`
- **Plugin / Module:** `lms_course` + `lms_order`
- **Source content type / Entity:** `lms_order` (upcasted)
- **Backed by fields / config keys:** All `lms_order` base fields; `field_items` references `lms_order_item` entities (field_course, field_membership_plan, field_price, field_type); `field_payment_receipt` (image)
- **Editable source:** `lms_order` entity
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/orders/{id}/edit`
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Change `field_status` from `pending` → `completed` → save → enrollment record created, email sent
- **View mode / Projection:** Form
- **Region / Placement:** Full page with sidebar
- **Notes:** Setting status to `completed` triggers `hook_entity_update` → `_lms_order_process_completed_order()`. `field_payment_receipt` image field (png/jpg/jpeg, 5 MB, stored at `public://payment_receipts/YYYY-MM`) — added via `lms_order_update_10001`. No custom form class — uses `ContentEntityForm` default.

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Sidebar Nav | Workspace nav links | Static template | Static | Developer only | N/A | N/A | N/A | None | N/A |
| Stats Header | Total orders, pending count, revenue (completed), payment method breakdown | `entity:lms_order` aggregate | `field_status`, `field_total` | Read-only | `administer lms_course` | No | EN | Per-request | Mark order completed → revenue updates |
| Order List Table | Order#, customer name, total, payment method, status badge, created date, edit link | `entity:lms_order` | `field_order_number`, `uid`, `field_total`, `field_payment_method`, `field_status`, `created` | `/workspace/orders/{id}/edit` | `administer lms_course` | No | EN | Per-request | Edit status → badge changes |
| Order Edit Form | All order fields, order items, receipt upload | `entity:lms_order` | All base fields + `field_items` + `field_payment_receipt` | `/workspace/orders/{id}/edit` | `administer lms_course` | No | EN | Per-request | Complete order → enrollment triggered |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Status badge color coding (pending=yellow, completed=green, etc.) | CSS class mapping in `lms-admin-orders.html.twig` | Developer-owned UI chrome | Revisit if admin theme becomes config-driven |
| Payment gateway labels (stripe, bank_transfer, etc.) | Plugin annotation labels in `@PaymentGateway` | Plugin architecture constant | Revisit if gateway label needs editor override |
| Currency symbol display | `field_currency` default=USD; formatting in template | Business default | Revisit when multi-currency added |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Sidebar Nav | DONE | — | Shared workspace include |
| Stats Header | DONE | — | Rendered in `lms-admin-orders.html.twig` |
| Order List Table | DONE | — | `AdminController::orderList` fully implemented |
| Order Edit Form | DONE | — | `AdminController::orderEditForm` + `ContentEntityForm` |
| Auto-enrollment on complete | DONE | — | `hook_entity_update` → `_lms_order_process_completed_order()` |
| Stripe PaymentIntent (checkout) | DONE (with bug) | — | Created on form BUILD — one per page load. Known risk: orphaned intents. |
| Webhook signature verification | NOT DONE | — | All gateways: no webhook routes, no signature verification. TODO in source. |
| PayPal gateway (runtime) | STUB | — | `paypal` plugin in `lms_payment` — no server-side PayPal API verification |
| ZaloPay gateway | STUB | — | Hardcoded sandbox URL; no HMAC verification |
| lms_paypal module | DEAD CODE | — | Wrong interface, no `@PaymentGateway` annotation — never discovered by plugin manager |
