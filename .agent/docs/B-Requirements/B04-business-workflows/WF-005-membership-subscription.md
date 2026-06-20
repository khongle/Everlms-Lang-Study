# WF-005 - Membership subscription

## Business flow
A user chooses a membership plan (`lms_membership_plan`) and subscribes via an order (WF-004). On payment, a `lms_user_membership` is created with start/end dates and the plan's feature set (`field_features`). While the membership is active, content access checks (F-004) treat the user as entitled to the plan's courses without per-course purchase. Near/after expiry the user is prompted to renew; lapsed membership removes broad access.

## Participating modules
[lms_membership](../../C-Design/C05-modules/lms_membership.md), [lms_order](../../C-Design/C05-modules/lms_order.md), [lms_payment](../../C-Design/C05-modules/lms_payment.md)

## Related features (B03)
[F-012](../B03-features/F-012-memberships-and-subscriptions.md)

## Trigger
User subscribes to a membership plan (order paid).

## Outcome
Active `lms_user_membership` granting plan-scoped access for its validity window.

## Design link
[C12 WF-005](../../C-Design/C12-workflows/WF-005-membership-subscription.md)
