# F-010 - Orders & checkout

**Status:** built
**Primary module:** [lms_order](../../C-Design/C05-modules/lms_order.md)
**Epic:** [E06 Commerce](../../D-Development/D02-epics/E06-commerce.md)
**Related workflows:** [WF-004 Order checkout & payment](../B04-business-workflows/WF-004-order-checkout-and-payment.md), [WF-009 Coupon redemption](../B04-business-workflows/WF-009-coupon-discount-redemption.md)
**Acceptance:** [B05 F-010](../B05-acceptance-criteria/F-010-orders-and-checkout.md)
**Source:** `web/modules/custom/lms_order/`

## Requirement
A buyer adds a course/bundle/membership to an order (`lms_order` + `lms_order_item` via `field_items`), optionally applies a coupon/discount (Order entity carries discount fields), and checks out. The order moves through states (pending → paid → completed / cancelled), and completion triggers enrollment/entitlement.

## Business value
Orders are the revenue record and the bridge between catalog and access; clean order state is essential for reconciliation and support.

## Scope notes
- Custom commerce (no Drupal Commerce): `lms_order` (header) + `lms_order_item` (lines).
- Coupon/discount applied at order level (WF-009).
- Payment capture is F-011; on `paid`/`completed` the order grants F-004 enrollment / F-012 membership.
- ⚠️ No payment webhooks (R-002) — order state relies on synchronous return, can desync.

## Cross references
- Design: [C04 lms_order](../../C-Design/C04-entities/lms_order.md), [C04 lms_order_item](../../C-Design/C04-entities/lms_order_item.md), [C12 WF-004](../../C-Design/C12-workflows/WF-004-order-checkout-and-payment.md)
