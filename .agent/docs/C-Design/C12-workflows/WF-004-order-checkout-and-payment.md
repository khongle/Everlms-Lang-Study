# C12 Workflow - WF-004 Order checkout & payment

## Business source
[B04 WF-004](../../B-Requirements/B04-business-workflows/WF-004-order-checkout-and-payment.md)

## Modules
[lms_order](../C05-modules/lms_order.md), [lms_payment](../C05-modules/lms_payment.md), [lms_stripe](../C05-modules/lms_stripe.md)

## Related features (B03)
[F-010](../../B-Requirements/B03-features/F-010-orders-and-checkout.md), [F-011](../../B-Requirements/B03-features/F-011-payments-and-gateways.md)

## State model
`lms_order.field_status`:
| State | Meaning |
| ----- | ------- |
| draft (cart) | items added |
| submitted (pending) | checkout submitted, awaiting payment |
| in_review (processing) | bank transfer receipt uploaded; waiting manager verification |
| approved_or_completed (paid/completed) | payment captured → entitlement granted |
| rejected_or_returned (cancelled/failed) | payment failed/cancelled |
| archived (refunded) | refunded (manual) |

## Permission model
- Initiator: buyer.
- Responsible: payment gateway (capture); manager confirms bank_transfer.
- Admin: manager edits/refunds orders.

## Notifications
Order confirmation + receipt instructions to buyer. Bank transfer buyers upload a receipt; manager verification moves the order to completed and grants entitlement. ⚠️ No webhook reconciliation (R-002) — online gateways rely on synchronous return, while bank transfer remains manual.
