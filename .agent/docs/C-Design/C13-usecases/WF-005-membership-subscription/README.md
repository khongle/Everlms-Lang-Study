# C13 Usecases - WF-005 Membership subscription

## Workflow
- C12: [WF-005](../../C12-workflows/WF-005-membership-subscription.md) · B04: [WF-005](../../../B-Requirements/B04-business-workflows/WF-005-membership-subscription.md)
- Modules: lms_membership, lms_order
- Business flow: chọn plan → thanh toán → membership active → truy cập.

## Usecase index
| Usecase | Title | Primary actor | Process | C11 pages |
| ------- | ----- | ------------- | ------- | --------- |
| [UC-WF-005-001](UC-WF-005-001-subscribe-to-a-plan.md) | Subscribe to a plan | Student | Membership subscription | P006, P030, P026 |

## Completion rule
Đóng khi một membership active cấp quyền truy cập theo phạm vi plan.

## Validity rule
Membership `active`/`trial` chỉ được tính khi `field_end_date` còn ở tương lai. Subscribe lại cùng một plan sẽ gia hạn hoặc reactivate dòng membership user/plan hiện có thay vì tạo thêm membership duplicate hiển thị cho learner.
