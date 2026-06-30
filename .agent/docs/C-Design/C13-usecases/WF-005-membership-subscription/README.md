# C13 Usecases - WF-005 Membership subscription

## Workflow
- C12: [WF-005](../../C12-workflows/WF-005-membership-subscription.md) · B04: [WF-005](../../../B-Requirements/B04-business-workflows/WF-005-membership-subscription.md)
- Modules: lms_membership, lms_order
- Business flow: choose plan → pay → active membership → access.

## Usecase index
| Usecase | Title | Primary actor | Process | C11 pages |
| ------- | ----- | ------------- | ------- | --------- |
| [UC-WF-005-001](UC-WF-005-001-subscribe-to-a-plan.md) | Subscribe to a plan | Student | Membership subscription | P006, P030, P026 |

## Completion rule
Closed when an active membership grants plan-scoped access.

## Validity rule
`active`/`trial` memberships count only when `field_end_date` is in the future. Re-subscribing to the same plan renews or reactivates the existing user/plan membership row rather than creating duplicate learner-facing memberships.
