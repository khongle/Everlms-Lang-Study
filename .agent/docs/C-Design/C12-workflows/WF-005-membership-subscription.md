# C12 Workflow - WF-005 Membership subscription

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

## Permission model
- Initiator: user (subscribe via order).
- Admin: manager creates/edits memberships.

## Notifications
Activation + renewal/expiry reminders (cron). Lapsed → access revoked in F-004 gating.
