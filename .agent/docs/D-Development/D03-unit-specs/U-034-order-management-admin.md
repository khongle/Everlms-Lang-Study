# D03 — Unit Spec: Order Management (Admin)

**Unit ID:** U-034
**Group:** G-COMMERCE
**Title:** Order management (admin)
**Status:** SPEC
**Priority:** P1
**Assigned:** unassigned

---

## Scope

Admin interface for viewing, filtering, and managing orders. Includes order list with status filter, order detail view, manual status override, refund initiation (stub), and CSV export.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Controller/OrderAdminController.php` | Create | lms_order |
| `src/Form/OrderFilterForm.php` | Create | lms_order |
| `templates/order-list.html.twig` | Create | lms_order |
| `templates/order-detail.html.twig` | Create | lms_order |
| `lms_order.routing.yml` | Modify | lms_order |
| `lms_order.links.menu.yml` | Modify | lms_order |

---

## Interfaces / Services

- Uses `Order` entity (U-025) + `OrderItem` entity (U-025)
- `PaymentManager` (U-027) for refund initiation
- Access: role `administrator` and `lms_manager`
- Route prefix: `/manage/orders`

---

## Acceptance Criteria

- [ ] `/manage/orders` lists all orders with columns: ID, user, date, total, status, gateway
- [ ] Filter by: status, date range, gateway, user (name/email search)
- [ ] Order detail page shows items, payment info, membership/course enrolled
- [ ] Admin can manually set order status (with audit log entry)
- [ ] CSV export of filtered order list
- [ ] Pagination (50 rows default)

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | — |
| G4 Adversarial | PENDING | — |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-025 | Order + OrderItem entities |
| U-026 | UserMembership (linked from order detail) |

---

**Feature Flag:** None

*End of U-034-order-management-admin.md*
