---
story_id: E06S01
story_type: module-data
primary_source: C05-modules/lms_order.md
owning_modules: [lms_order]
adequacy: PASS
---

# E06S01 - lms_order build spine

Commerce tùy chỉnh: order header/item, checkout, coupon, state, entitlement. **Status:** done.

## Tasks
| Task | Status |
| ---- | ------ |
| Order + item entities + state | resolved |
| Checkout + coupon | resolved |
| Entitlement grant on paid | resolved |

## Links
[C05](../../C-Design/C05-modules/lms_order.md) · [WF-004](../../C-Design/C12-workflows/WF-004-order-checkout-and-payment.md)/[WF-009](../../C-Design/C12-workflows/WF-009-coupon-discount-redemption.md) · [F-010](../../B-Requirements/B03-features/F-010-orders-and-checkout.md)

## Source Trace
- **Module:** `lms_order` — [C05](../../C-Design/C05-modules/lms_order.md) · [D07](../D07-code-map/lms_order.md)
- **Entity:** [lms_order](../../C-Design/C04-entities/lms_order.md), [lms_order_item](../../C-Design/C04-entities/lms_order_item.md)
- **Feature:** [F-010](../../B-Requirements/B03-features/F-010-orders-and-checkout.md) orders-and-checkout
- **Workflow:** [WF-004](../../C-Design/C12-workflows/WF-004-order-checkout-and-payment.md) order-checkout-and-payment, [WF-009](../../C-Design/C12-workflows/WF-009-coupon-discount-redemption.md) coupon-discount-redemption

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E06S01T01` Order + item entities + state | resolved |
| `E06S01T02` Checkout + coupon | resolved |
| `E06S01T03` Entitlement grant on paid | resolved |

## Adequacy Checklist
- [x] Entity: `lms_order` + `lms_order_item` (C04)
- [x] Route: checkout route (C05/D07)
- [x] Access/permission: entitlement grant chỉ khi state=paid, đã resolved
- [ ] Test: chưa có automated test coverage riêng — gap chung R-006/X01
