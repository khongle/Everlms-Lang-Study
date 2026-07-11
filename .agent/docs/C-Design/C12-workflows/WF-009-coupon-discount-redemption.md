# C12 Workflow - WF-009 Coupon / discount redemption

## Business source
[B04 WF-009](../../B-Requirements/B04-business-workflows/WF-009-coupon-discount-redemption.md)

## Modules
[lms_order](../C05-modules/lms_order.md), [lms_payment](../C05-modules/lms_payment.md)

## Related features (B03)
[F-010](../../B-Requirements/B03-features/F-010-orders-and-checkout.md)

## State model
coupon application on `lms_order` (sub-step of WF-004):
| State | Meaning |
| ----- | ------- |
| submitted (applied) | code entered + validated |
| approved_or_completed (accepted) | discount applied to `field_discount`/total |
| rejected_or_returned (invalid) | expired/ineligible/over-limit → rejected |

## Permission model
- Initiator: buyer at checkout.
- Admin: coupon definitions are currently managed through Drupal configuration (`lms_order.settings`). A manager-facing coupon administration UI is a product gap, not an as-built page.

## Round-2 QA evidence
Verified healthy at checkout (`julia.nowak`, course 124 $85): **SILVERCARE10** (10%) → −$8.50, total $76.50, green "applied" message + coupon badge + Discount line in Order Summary; **WELCOME20** ($20 fixed, min $50) → −$20, total $65.00; **invalid code** → rejected; **WELCOME20 on a $45 course** → rejected (below minimum). Coupons remain config-defined in `lms_order.settings` (manager coupon-admin UI is still a product gap). Screens: `.agent/docs/E-Quality/E00-workflows/round-2/_screens/009-coupon-discount-redemption/`.

## Notifications
Inline validation message at checkout; discount recorded on `lms_order.field_coupon_code`, `field_discount`, and adjusted `field_total` for reporting.
