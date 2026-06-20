# WF-009 - Coupon / discount redemption

## Business flow
During checkout (WF-004) a buyer enters a coupon/discount code. The order (`lms_order`) validates the code (validity window, applicability, usage limits) and applies the discount to the order total before payment. An invalid/expired code is rejected with a message; a valid one reduces the amount the gateway captures.

## Participating modules
[lms_order](../../C-Design/C05-modules/lms_order.md), [lms_payment](../../C-Design/C05-modules/lms_payment.md)

## Related features (B03)
[F-010](../B03-features/F-010-orders-and-checkout.md)

## Trigger
Buyer applies a coupon code at checkout.

## Outcome
Discounted order total (valid code) or rejection (invalid). Discount fields recorded on the order for reporting.

## Design link
[C12 WF-009](../../C-Design/C12-workflows/WF-009-coupon-discount-redemption.md)
