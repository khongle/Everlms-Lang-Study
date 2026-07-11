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

## Manager review contract
- Bank transfer receipt upload changes the order to `processing`; it must not grant course entitlement yet.
- `/workspace/orders` must allow managers to filter `processing` orders directly.
- The manager order list must show complete Order ID, learner, amount, date, status badge, and a visible review/edit action at standard desktop widths.
- Manager approval changes the order to `completed`; this is the point where course entitlement is granted.

## Notifications
Order confirmation + receipt instructions to buyer. Bank transfer buyers upload a receipt; manager verification moves the order to completed and grants entitlement. ⚠️ No webhook reconciliation (R-002) — online gateways rely on synchronous return, while bank transfer remains manual.

## Round-2 QA evidence
End-to-end verified with a real non-member buyer: `julia.nowak` (uid 71) bought paid course 90 ($45) via **Bank Transfer** → order 154 `processing` (receipt uploaded, **no entitlement yet**) → manager `Jenny Pham` filtered `/workspace/orders?status=processing`, reviewed via `ApproveOrderForm`, set **Completed → order 154 `completed`**, and entitlement was granted (`lms_user_course` 218 for uid 71/course 90). Manager list shows full Order ID / learner (full name) / amount / date / status badge / Edit action. Payment methods: Stripe, PayPal, Bank Transfer (VietQR); the Stripe test-mode "Autofill" chip is a Stripe Elements artifact (absent under live keys), not app code. Round-1 fixes (WF-004-001..003) hold. Screens: `.agent/docs/E-Quality/E00-workflows/round-2/_screens/004-order-checkout-and-payment/`.
