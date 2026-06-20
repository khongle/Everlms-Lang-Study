# WF-004 - Order checkout & payment

## Business flow
A buyer adds a course/bundle/membership to an order (`lms_order` + `lms_order_item`), optionally applies a coupon (WF-009), and proceeds to checkout. They select a payment gateway (`bank_transfer`, `stripe`, `paypal`, `zalopay`); the `PaymentGatewayManager` invokes the chosen `@PaymentGateway` plugin to authorise/capture. On success the order transitions to paid/completed and grants entitlement — enrollment (WF-001) and/or membership (WF-005). On failure the order stays pending and the buyer can retry.

## Participating modules
[lms_order](../../C-Design/C05-modules/lms_order.md), [lms_payment](../../C-Design/C05-modules/lms_payment.md), [lms_stripe](../../C-Design/C05-modules/lms_stripe.md)

## Related features (B03)
[F-010](../B03-features/F-010-orders-and-checkout.md), [F-011](../B03-features/F-011-payments-and-gateways.md), [F-009](../B03-features/F-009-course-bundles.md)

## Trigger
Buyer confirms checkout / payment.

## Outcome
Paid order recorded; entitlement granted. ⚠️ No webhook reconciliation today (R-002) — relies on synchronous gateway return.

## Design link
[C12 WF-004](../../C-Design/C12-workflows/WF-004-order-checkout-and-payment.md)
