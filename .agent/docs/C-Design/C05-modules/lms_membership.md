# C05 Module - lms_membership

**Name:** Memberships & subscriptions · **Phase:** 7 · **Epic:** [E07](../../D-Development/D02-epics/E07-membership.md) · **Status:** done

## Responsibility
Owns membership plans and user subscriptions; active membership entitles broad/tiered course access without per-course purchase. ~9 routes.

## Owned / related entities
- [lms_membership_plan](../C04-entities/lms_membership_plan.md) · [lms_user_membership](../C04-entities/lms_user_membership.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-012](../../B-Requirements/B03-features/F-012-memberships-and-subscriptions.md) | Memberships & subscriptions |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-005](../../B-Requirements/B04-business-workflows/WF-005-membership-subscription.md) | Membership subscription |

## Integrations / APIs (B06 / C06)
- Purchased via [lms_order](lms_order.md)/[lms_payment](lms_payment.md).

## Backing pages
| Label | URL |
| ----- | --- |
| Plans / pricing | `/membership`, `/membership/plans` |
| Manage plans | `/manage/membership-plans` |

## Cross references
- Entitlement consumed by [lms_progress](lms_progress.md) gating; entities [C04](../C04-entities/lms_membership_plan.md).

## Dependencies
- lms_base, lms_payment.

## Implementation notes
- Plan → subscription via order; `lms_user_membership` start/end + status; expiry by date/cron. Access source in F-004 gating.
