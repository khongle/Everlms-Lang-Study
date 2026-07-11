---
usecase_id: UC-WF-005-001
workflow: WF-005-membership-subscription
status: as-built
primary_actor: Student
modules: [lms_membership, lms_order]
processes: [membership-subscription]
---

# UC-WF-005-001 - Subscribe to a plan

## Intent
Một user subscribe một membership plan để có quyền truy cập rộng vào course.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-005 |
| Business flow | choose plan → pay → active → access |
| Modules | lms_membership, lms_order |
| Related features | F-012 |
| B04 source | WF-005 |

## Actors
User (primary); gateway.

## Preconditions
Đã authenticated; đã tồn tại một plan.

## Trigger
User chọn một plan và checkout.

## Main success scenario
1. Xem pricing (P006); chọn plan.
2. Checkout membership (P030); thanh toán (WF-004).
3. `lms_user_membership` được tạo, gia hạn, hoặc reactivate cho user/plan với start/end date hợp lệ.
4. Quyền truy cập course thuộc plan được cấp (F-004); status hiển thị (P026).

## Alternate and exception flows
- Thanh toán thất bại → pending; retry.
- Hết hạn → access bị revoke; nhắc renew.

## State transitions (evidence)
- `submitted` → `approved_or_completed`: order thanh toán hoàn tất.
- `approved_or_completed` → `rejected_or_returned`: user cancel membership.
- `approved_or_completed` → `archived`: quá field_end_date.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Pricing | Public | /pricing |
| Checkout (membership) | App | /checkout/membership/{plan} |
| My membership | App | /my-membership |

## Supporting pages
Manager tạo/sửa plan tại Manage pricing (P038) trước khi plan xuất hiện ở trang Pricing (P006).

## Acceptance and test contract
- Membership active cấp quyền truy cập course; hết hạn thì revoke (B05 F-012).
- `/my-membership` chỉ hiển thị membership active/trial có `field_end_date` còn ở tương lai và gộp các dòng duplicate theo plan.
- Mua lại cùng plan sẽ gia hạn/reactivate membership của user/plan thay vì tạo thêm card active duplicate.
