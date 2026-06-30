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
A user subscribes to a membership plan for broad course access.

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
Authenticated; a plan exists.

## Trigger
User selects a plan and checks out.

## Main success scenario
1. View pricing (P006); choose plan.
2. Checkout membership (P030); pay (WF-004).
3. `lms_user_membership` is created, renewed, or reactivated for the user/plan with valid start/end dates.
4. Access to plan courses granted (F-004); status shown (P026).

## Alternate and exception flows
- Payment fails → pending; retry.
- Expiry → access revoked; renew prompt.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Pricing | Public | /pricing |
| Checkout (membership) | App | /checkout/membership/{plan} |
| My membership | App | /my-membership |

## Acceptance and test contract
- Active membership entitles courses; expired revokes (B05 F-012).
- `/my-membership` shows only active/trial memberships whose `field_end_date` is in the future and collapses duplicate rows by plan.
- Buying the same plan again extends/reactivates the user/plan membership instead of creating duplicate active cards.
