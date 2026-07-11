# C12 Workflow - WF-005 Membership subscription

**Object:** membership

## Business source
[B04 WF-005](../../B-Requirements/B04-business-workflows/WF-005-membership-subscription.md)

## Modules
[lms_membership](../C05-modules/lms_membership.md), [lms_order](../C05-modules/lms_order.md)

## Related features (B03)
[F-012](../../B-Requirements/B03-features/F-012-memberships-and-subscriptions.md)

## State model
`lms_user_membership.field_status`:
| State | Meaning |
| ----- | ------- |
| submitted (pending) | plan order pending |
| approved_or_completed (active) | paid; entitlement window open |
| rejected_or_returned (cancelled) | user cancelled |
| archived (expired) | past `field_end_date` |

## Transitions
| From | To | Trigger |
| ---- | -- | ------- |
| submitted | approved_or_completed | order thanh toán hoàn tất |
| approved_or_completed | rejected_or_returned | user cancel membership |
| approved_or_completed | archived | quá field_end_date |

## Permission model
- Initiator: user (subscribe qua order).
- Admin: manager tạo/sửa membership.

## Membership validity contract
- `active`/`trial` chỉ valid khi `field_end_date` còn ở tương lai.
- Các trang membership hướng tới learner phải ẩn các dòng active đã cũ (stale) có end date thiếu hoặc đã hết hạn.
- Hoàn tất một order membership cho user/plan đang current sẽ gia hạn membership hiện có thay vì tạo thêm card duplicate.
- Hoàn tất một order membership khi chỉ tồn tại một dòng stale cho cùng user/plan sẽ reactivate dòng đó với start/end date mới.

## Round-2 QA verification
Các contract về validity cốt lõi đã xác nhận hoạt động đúng:
- `MembershipManager::hasActiveMembership()` yêu cầu `field_status IN (active,trial)` **và** `field_end_date > now`, nên các dòng NULL/expired không bao giờ được tính (dòng plan-9 stale của maria với end date NULL bị loại).
- `MembershipController::myMembership()` (`/my-membership`) dedupe theo plan và ẩn các dòng stale — 10 dòng active plan-7 duplicate của maria chỉ render thành **một** card "Basic Access".
- Hoàn tất order (`lms_order.module`) sẽ gia hạn membership active hiện có, nếu không thì reactivate dòng stale, nếu không nữa mới tạo mới — không phát sinh duplicate mới.

⚠️ **Product gap (`WF-005-001`, còn mở):** entitlement không phân biệt tier — bất kỳ membership active nào cũng mở khóa toàn bộ catalog, nên một member "Basic Access" miễn phí vẫn mở được các course chuyên sâu trả phí, dù `/pricing` đã phân tier khác nhau. Cần quyết định product + thêm field access-level cho plan.

## Notifications
Nhắc kích hoạt + gia hạn/hết hạn (qua cron). Hết hạn (lapsed) → access bị revoke theo gating của F-004.
