# C05 Module - lms_order

**Name:** Orders & checkout · **Phase:** 6 · **Epic:** [E06](../../D-Development/D02-epics/E06-commerce.md) · **Status:** done

## Responsibility
Sở hữu hệ thống commerce tùy biến: order header + line item, checkout, coupon/discount, order state, và cấp entitlement khi thanh toán. Không dùng Drupal Commerce.

## Owned / related entities
- [lms_order](../C04-entities/lms_order.md) · [lms_order_item](../C04-entities/lms_order_item.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-010](../../B-Requirements/B03-features/F-010-orders-and-checkout.md) | Orders & checkout |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-004](../../B-Requirements/B04-business-workflows/WF-004-order-checkout-and-payment.md) | Order checkout & payment |
| [WF-009](../../B-Requirements/B04-business-workflows/WF-009-coupon-discount-redemption.md) | Coupon / discount redemption |

## Integrations / APIs (B06 / C06)
- Thanh toán qua các gateway của [lms_payment](lms_payment.md).

## Backing pages
| Label | URL |
| ----- | --- |
| Checkout | `/checkout` |
| My orders / order detail | `/orders`, `/order/{order}` |

## Cross references
- Entity [C04](../C04-entities/lms_order.md); payment ở [lms_payment](lms_payment.md); cấp enrollment ([lms_progress](lms_progress.md)) / membership ([lms_membership](lms_membership.md)).

## Dependencies
- lms_base, lms_payment.

## Implementation notes
- State machine của order: pending → paid → completed / cancelled. Coupon trên `field_coupon_code` (logic discount nằm trên Order). Khi paid/completed → fan-out sang enrollment/membership. ⚠️ status được set khi gateway trả về đồng bộ (synchronous) (R-002). ~20 order đang live.
