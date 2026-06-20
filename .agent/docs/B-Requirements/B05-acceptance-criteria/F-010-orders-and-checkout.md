# F-010 Acceptance Criteria - Orders & checkout

## Core criteria
1. **Given** a buyer, **when** they add a course/bundle/membership, **then** an `lms_order` + `lms_order_item` (`field_items`) is created in `pending`.
2. **Given** a pending order, **when** payment succeeds (F-011), **then** the order transitions to paid/completed and entitlement is granted.
3. **Given** a pending order, **when** payment fails/cancels, **then** the order stays pending/cancelled and no entitlement is granted.
4. **Given** an order, **when** a coupon is applied (WF-009), **then** the total reflects the discount.

## Evidence expected
- Module: [lms_order](../../C-Design/C05-modules/lms_order.md).
- Test: order state-machine test; entitlement-on-paid test; ⚠️ note no webhook reconciliation (R-002) (E03).
