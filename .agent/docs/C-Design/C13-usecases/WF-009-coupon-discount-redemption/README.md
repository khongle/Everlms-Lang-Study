# C13 Usecases - WF-009 Coupon / discount redemption

## Workflow
- C12: [WF-009](../../C12-workflows/WF-009-coupon-discount-redemption.md) · B04: [WF-009](../../../B-Requirements/B04-business-workflows/WF-009-coupon-discount-redemption.md)
- Modules: lms_order, lms_payment
- Business flow: nhập mã → validate → áp discount → lưu các field discount trên order.

## Usecase index
| Usecase | Title | Primary actor | Process | C11 pages |
| ------- | ----- | ------------- | ------- | --------- |
| [UC-WF-009-001](UC-WF-009-001-apply-coupon-at-checkout.md) | Apply a coupon at checkout | Student | Coupon redemption | P029 |

## Completion rule
Đóng khi một mã hợp lệ giảm được tổng tiền checkout hiển thị, lưu `field_coupon_code`/`field_discount`/`field_total` đã điều chỉnh trên order, và một mã không hợp lệ bị từ chối trước khi order được tạo.
