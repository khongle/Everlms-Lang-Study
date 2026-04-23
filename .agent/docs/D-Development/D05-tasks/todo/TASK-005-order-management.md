# Task: Order Management Admin

**ID:** TASK-005
**Unit:** U-034
**Group:** G-COMMERCE
**Priority:** P1
**Estimate:** 8h
**Assigned:** unassigned
**Status:** TODO (35% done)

## Audit Notes — What Already Exists
- OrderListBuilder: admin listing at /manage/orders — ID, order number, customer, total, payment method, status (color-coded), created ✅
- Entity links provide canonical + collection + auto-generated edit/delete ✅
- Admin can View/Edit/Approve orders via default entity form ✅

## Remaining Work
- [ ] Add filtering/search in list builder (status, date range, user)
- [ ] Build order detail page with payment timeline/history
- [ ] Implement CSV/Excel export
- [ ] Add bulk operations (bulk approve, reject)
- [ ] Process refund action (Admin only)
- [ ] Revenue dashboard/stats

## Description
Admin/Manager interface để quản lý orders: list, filter, view detail, process refunds.

## Acceptance Criteria
- [ ] Order listing page với filters (status, date, user)
- [ ] Order detail page (items, payment info, timeline)
- [ ] Process refund action (Admin only)
- [ ] Export orders CSV
- [ ] Role-based access (Manager+)

## Technical Notes
- Module: `lms_order`
- Controller: `OrderManagementController`
- Routes: `/manage/orders`, `/manage/orders/{id}`
- Templates: `lms-order-list.html.twig`, `lms-order-detail.html.twig`

## Dependencies
- U-025 (Order entity) ✅ DONE
- TASK-001 (Checkout workflow)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Access control tests
