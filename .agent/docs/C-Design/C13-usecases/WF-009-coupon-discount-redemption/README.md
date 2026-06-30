# C13 Usecases - WF-009 Coupon / discount redemption

## Workflow
- C12: [WF-009](../../C12-workflows/WF-009-coupon-discount-redemption.md) · B04: [WF-009](../../../B-Requirements/B04-business-workflows/WF-009-coupon-discount-redemption.md)
- Modules: lms_order, lms_payment
- Business flow: enter code → validate → apply discount → persist discount fields on order.

## Usecase index
| Usecase | Title | Primary actor | Process | C11 pages |
| ------- | ----- | ------------- | ------- | --------- |
| [UC-WF-009-001](UC-WF-009-001-apply-coupon-at-checkout.md) | Apply a coupon at checkout | Student | Coupon redemption | P029 |

## Completion rule
Closed when a valid code reduces the displayed checkout total, persists `field_coupon_code`/`field_discount`/adjusted `field_total` on the order, and an invalid code is rejected before order creation.
