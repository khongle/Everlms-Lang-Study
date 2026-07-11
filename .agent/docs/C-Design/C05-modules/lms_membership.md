# C05 Module - lms_membership

**Name:** Memberships & subscriptions · **Phase:** 7 · **Epic:** [E07](../../D-Development/D02-epics/E07-membership.md) · **Status:** done

## Responsibility
Sở hữu membership plan và subscription của user; membership đang active cho phép truy cập course diện rộng/theo tier mà không cần mua từng course. ~9 route.

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
- Mua thông qua [lms_order](lms_order.md)/[lms_payment](lms_payment.md).

## Backing pages
| Label | URL |
| ----- | --- |
| Plans / pricing | `/membership`, `/membership/plans` |
| Manage plans | `/manage/membership-plans` |

## Cross references
- Entitlement được [lms_progress](lms_progress.md) sử dụng để gating; entity [C04](../C04-entities/lms_membership_plan.md).

## Dependencies
- lms_base, lms_payment.

## Implementation notes
- Plan → subscription qua order; `lms_user_membership` có start/end + status; hết hạn theo date/cron. Là nguồn access trong gating của F-004.
