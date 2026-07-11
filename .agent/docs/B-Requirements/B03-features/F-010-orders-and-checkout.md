# F-010 - Orders & checkout

**Status:** built
**Primary module:** [lms_order](../../C-Design/C05-modules/lms_order.md)
**Epic:** [E06 Commerce](../../D-Development/D02-epics/E06-commerce.md)
**Related workflows:** [WF-004 Order checkout & payment](../B04-business-workflows/WF-004-order-checkout-and-payment.md), [WF-009 Coupon redemption](../B04-business-workflows/WF-009-coupon-discount-redemption.md)
**Acceptance:** [B05 F-010](../B05-acceptance-criteria/F-010-orders-and-checkout.md)
**Source:** `web/modules/custom/lms_order/`

## Requirement
Buyer thêm một course/bundle/membership vào order (`lms_order` + `lms_order_item` qua `field_items`), có thể áp coupon/discount (Order entity mang các field discount), và checkout. Order đi qua các state (pending → paid → completed / cancelled), và completion sẽ trigger enrollment/entitlement.

## Business value
Order là bản ghi doanh thu và là cầu nối giữa catalog và quyền truy cập; order state sạch sẽ là yếu tố thiết yếu cho reconciliation và support.

## Scope notes
- Commerce tự xây (không dùng Drupal Commerce): `lms_order` (header) + `lms_order_item` (các dòng line).
- Coupon/discount được áp dụng ở cấp order (WF-009).
- Việc capture payment thuộc F-011; khi ở trạng thái `paid`/`completed`, order sẽ cấp F-004 enrollment / F-012 membership.
- ⚠️ Không có payment webhook (R-002) — order state phụ thuộc vào synchronous return, có thể bị desync.

## Cross references
- Design: [C04 lms_order](../../C-Design/C04-entities/lms_order.md), [C04 lms_order_item](../../C-Design/C04-entities/lms_order_item.md), [C12 WF-004](../../C-Design/C12-workflows/WF-004-order-checkout-and-payment.md)
