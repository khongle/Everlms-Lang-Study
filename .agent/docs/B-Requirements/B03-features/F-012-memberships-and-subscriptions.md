# F-012 - Memberships & subscriptions

**Status:** built
**Primary module:** [lms_membership](../../C-Design/C05-modules/lms_membership.md)
**Epic:** [E07 Membership](../../D-Development/D02-epics/E07-membership.md)
**Related workflows:** [WF-005 Membership subscription](../B04-business-workflows/WF-005-membership-subscription.md)
**Acceptance:** [B05 F-012](../B05-acceptance-criteria/F-012-memberships-and-subscriptions.md)
**Source:** `web/modules/custom/lms_membership/`

## Requirement
Admin định nghĩa membership plan (`lms_membership_plan`, kèm `field_features`) cấp quyền truy cập diện rộng (ví dụ toàn bộ course hoặc một tier) trong một khoảng thời gian. User subscribe (qua một order), tạo ra một `lms_user_membership` với ngày start/end; membership đang active sẽ entitle quyền truy cập nội dung (F-004) mà không cần mua từng course.

## Business value
Membership dạng recurring/theo tier tạo ra doanh thu dự đoán được và một lối đi "all-access" đơn giản hơn cho tổ chức đào tạo nhiều nhân sự.

## Scope notes
- `lms_membership_plan` (catalog) + `lms_user_membership` (instance subscription).
- Kiểm tra entitlement trong F-004 coi membership đang active là một nguồn access.
- Có xử lý renewal/expiry; không tích hợp subscription-billing bên ngoài (membership được mua như một order).

## Cross references
- Design: [C04 lms_membership_plan](../../C-Design/C04-entities/lms_membership_plan.md), [C04 lms_user_membership](../../C-Design/C04-entities/lms_user_membership.md), [C12 WF-005](../../C-Design/C12-workflows/WF-005-membership-subscription.md)
