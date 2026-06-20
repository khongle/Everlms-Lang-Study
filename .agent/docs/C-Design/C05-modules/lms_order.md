# C05 Module - lms_order

**Name:** Orders & checkout · **Phase:** 6 · **Epic:** [E06](../../D-Development/D02-epics/E06-commerce.md) · **Status:** done

## Responsibility
Owns custom commerce: order header + line items, checkout, coupon/discount, order state, and entitlement granting on payment. No Drupal Commerce.

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
- Payment via [lms_payment](lms_payment.md) gateways.

## Backing pages
| Label | URL |
| ----- | --- |
| Checkout | `/checkout` |
| My orders / order detail | `/orders`, `/order/{order}` |

## Cross references
- Entities [C04](../C04-entities/lms_order.md); payment [lms_payment](lms_payment.md); grants enrollment ([lms_progress](lms_progress.md)) / membership ([lms_membership](lms_membership.md)).

## Dependencies
- lms_base, lms_payment.

## Implementation notes
- Order state machine: pending → paid → completed / cancelled. Coupon on `field_coupon_code` (discount logic on Order). On paid/completed → fan-out to enrollment/membership. ⚠️ status set on synchronous gateway return (R-002). ~20 orders live.
