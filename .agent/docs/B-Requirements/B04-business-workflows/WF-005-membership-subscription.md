# WF-005 - Membership subscription

## Business flow
User chọn một membership plan (`lms_membership_plan`) và subscribe qua một order (WF-004). Khi thanh toán xong, một `lms_user_membership` được tạo với ngày bắt đầu/kết thúc và tập tính năng của plan (`field_features`). Trong thời gian membership active, các kiểm tra quyền truy cập nội dung (F-004) coi user có quyền với các course thuộc plan mà không cần mua riêng từng course. Gần/sau khi hết hạn, user được nhắc gia hạn; membership hết hạn (lapsed) sẽ gỡ bỏ quyền truy cập diện rộng.

## Participating modules
[lms_membership](../../C-Design/C05-modules/lms_membership.md), [lms_order](../../C-Design/C05-modules/lms_order.md), [lms_payment](../../C-Design/C05-modules/lms_payment.md)

## Related features (B03)
[F-012](../B03-features/F-012-memberships-and-subscriptions.md)

## Trigger
User subscribe một membership plan (order đã paid).

## Outcome
Một `lms_user_membership` active cấp quyền truy cập theo phạm vi plan trong khoảng thời gian hiệu lực của nó.

## Design link
[C12 WF-005](../../C-Design/C12-workflows/WF-005-membership-subscription.md)
