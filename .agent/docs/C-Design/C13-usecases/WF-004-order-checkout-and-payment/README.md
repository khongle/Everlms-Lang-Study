# C13 Usecases - WF-004 Order checkout & payment

## Workflow
- C12: [WF-004](../../C12-workflows/WF-004-order-checkout-and-payment.md) · B04: [WF-004](../../../B-Requirements/B04-business-workflows/WF-004-order-checkout-and-payment.md)
- Modules: lms_order, lms_payment, lms_stripe
- Business flow: cart → coupon → pay/upload receipt → manager verification when manual → entitlement.

## Usecase index
| Usecase | Title | Primary actor | Process | C11 pages |
| ------- | ----- | ------------- | ------- | --------- |
| [UC-WF-004-001](UC-WF-004-001-buy-a-course.md) | Buy a course | Student | Order checkout & payment | P029, P036 |

## Completion rule
Closed when an online paid order grants course access, or when a bank transfer order moves from `pending` to `processing` after receipt upload and then to `completed` after manager verification.
