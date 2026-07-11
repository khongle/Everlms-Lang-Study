# C13 Usecases - WF-004 Order checkout & payment

## Workflow
- C12: [WF-004](../../C12-workflows/WF-004-order-checkout-and-payment.md) · B04: [WF-004](../../../B-Requirements/B04-business-workflows/WF-004-order-checkout-and-payment.md)
- Modules: lms_order, lms_payment, lms_stripe
- Business flow: cart → coupon → thanh toán/upload biên lai → manager verify khi manual → entitlement.

## Usecase index
| Usecase | Title | Primary actor | Process | C11 pages |
| ------- | ----- | ------------- | ------- | --------- |
| [UC-WF-004-001](UC-WF-004-001-buy-a-course.md) | Buy a course | Student | Order checkout & payment | P029, P036 |

## Completion rule
Đóng khi một order trả phí online cấp quyền truy cập course, hoặc khi một order bank transfer chuyển từ `pending` sang `processing` sau khi upload biên lai rồi sang `completed` sau khi manager verify.

## Manager review rule
Với bank transfer thủ công, `processing` là hàng đợi vận hành (operational queue). P036 `/workspace/orders` phải hiển thị nó như một status có thể filter, kèm badge rõ ràng và action review/edit hiển thị được; quyền truy cập của learner chỉ được cấp sau khi manager chuyển order sang `completed`.
