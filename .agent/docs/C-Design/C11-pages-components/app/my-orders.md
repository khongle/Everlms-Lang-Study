---
page: My Orders
mode: target-state
owner_module: lms_progress
route_group: Learn/App
adequacy: NOT_RUN
---

# My Orders — `/my-orders` (target-state — no route implemented)

Learner-facing purchase history page. Lists all orders placed by the current user with order ID, date, product name, amount, payment status, and a view/receipt action. A C10 prototype HTML exists at [`/account/orders.html`](../../../C10-prototypes/html/account/orders.html).

> **Status: target-state.** No learner-facing route or controller method for `/my-orders` exists in the codebase as of 2026-06-20. The `lms_order` module defines the `view own lms_order` permission but does not expose a learner portal page. Admin order management is at `/workspace/orders` (requires `administer lms_course`). The checkout flow has `CheckoutController` but no "order history" route in `lms_order.routing.yml` or `lms_progress.routing.yml`. Implementation requires a new route + controller method + template.

---

## Header Block

| Property | Value |
|---|---|
| **Canonical URL** | `/my-orders` (planned) |
| **Route name** | `lms_progress.my_orders` (planned) or `lms_order.my_orders` (planned) |
| **Solution** | Drupal 11 custom page — not yet implemented |
| **Owner module** | `lms_progress` (natural home with other portal pages) or `lms_order` |
| **Primary source** | To be created — `UserPortalController::myOrders()` (planned) |
| **Editable source** | To be created — `lms-my-orders.html.twig` (planned) |
| **Editor role** | Admin (template/theme) |
| **Editor management UI** | n/a |
| **Theme template** | `lms_my_orders` (planned; hook to be registered in `lms_progress.module` or `lms_order.module`) |
| **Access** | `_user_is_logged_in: TRUE` (planned) |
| **Cache** | Per-user (cache tag: `lms_order_list`) |
| **Related entities** | `lms_order`, `lms_order_item`, `lms_course`, `lms_membership_plan` |

---

## Components (Visual Order — Based on C10 Prototype)

| # | Component | Planned editable source | Notes |
|---|---|---|---|
| 1 | **Authenticated navbar** | `lms_theme/templates/` | Standard authenticated nav |
| 2 | **Page header** | `lms-my-orders.html.twig` | "My Orders" H1; subtitle "Manage your purchase history and receipts." |
| 3 | **Filters row** | `lms-my-orders.html.twig` | Status filter dropdown (All / Pending / Paid / Refunded), search by order ID or name, results count. No AJAX — full-page filter or JS-driven (design decision pending). |
| 4 | **Orders table** | `lms-my-orders.html.twig` | Columns: Order ID, Date, Product (course name or membership plan), Amount, Status badge, Action ("View" button). Status values: pending / processing / completed / cancelled / failed (from `lms_order.field_status` allowed list). |
| 5 | **Pagination** | `lms-my-orders.html.twig` | Standard Drupal pager; prototype shows 9 orders per page. |
| 6 | **Empty state** | `lms-my-orders.html.twig` | "No orders yet" icon + text + "Browse Courses" link to `/courses`. |
| 7 | **Footer** | `lms_theme/templates/` | Shared site footer |

---

## Visible Content Inventory

| Item | Source entity / field | Notes |
|---|---|---|
| Order ID | `lms_order.id` | |
| Order date | `lms_order.created` | formatted date |
| Product name | `lms_order_item.field_product_type` + referenced entity label | `lms_order_item.field_type` = course/membership/bundle; linked entity name from `field_course` / `field_membership_plan` / `field_bundle` |
| Amount | `lms_order.field_total` (decimal) | formatted as currency |
| Payment status | `lms_order.field_status` | allowed: pending, processing, completed, cancelled, failed |
| Payment method | `lms_order.field_payment_method` | bank_transfer / stripe / «others stub» |
| Receipt image | `lms_order.field_payment_receipt` (image) | uploaded for bank transfer orders |
| View action | link to order detail (no detail route defined — design decision pending: inline expand or modal) | |

---

## Implementation Requirements (target-state)

To implement this page:

1. **Route** — Add to `lms_progress.routing.yml` (or `lms_order.routing.yml`):
   ```yaml
   lms_progress.my_orders:
     path: '/my-orders'
     defaults:
       _controller: '\Drupal\lms_progress\Controller\UserPortalController::myOrders'
       _title: 'My Orders'
     requirements:
       _user_is_logged_in: 'TRUE'
   ```

2. **Controller method** — Add `myOrders()` to `UserPortalController`:
   - Load `lms_order` entities WHERE `uid = current_user`, sorted by `created DESC`.
   - Apply pager (10 or 15 per page).
   - Build items array with order ID, date, product names (resolve from `lms_order_item.field_course` / `field_membership_plan`), total, status, payment_method.
   - Render `lms_my_orders` theme.

3. **Template** — Create `web/modules/custom/lms_progress/templates/lms-my-orders.html.twig`.

4. **Hook** — Register `lms_my_orders` in `lms_progress.module` `hook_theme()`.

5. **Permission** — Use existing `view own lms_order` permission (already defined in `lms_order.permissions.yml`).

---

## Implementation Status

| Item | Status |
|---|---|
| Route `/my-orders` | target-state — not implemented |
| Controller method `myOrders()` | target-state — not implemented |
| Template `lms-my-orders.html.twig` | target-state — not implemented |
| `lms_order` entity (data model) | as-built — entity + fields exist |
| `view own lms_order` permission | as-built — defined in `lms_order.permissions.yml` |
| C10 prototype HTML | exists — [`/account/orders.html`](../../../C10-prototypes/html/account/orders.html) |
| Admin order management (`/workspace/orders`) | as-built (admin-only; requires `administer lms_course`) |

---

## Related Pages

- [dashboard.md](./dashboard.md) — logical entry point for learner portal
- [my-courses.md](./my-courses.md) — related learner portal section
- [my-certificates.md](./my-certificates.md) — related learner portal section
- Admin counterpart: [C11 workspace orders](../../C11-pages-components/)
