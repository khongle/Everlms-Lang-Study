# F-012 - Memberships & subscriptions

**Status:** built
**Primary module:** [lms_membership](../../C-Design/C05-modules/lms_membership.md)
**Epic:** [E07 Membership](../../D-Development/D02-epics/E07-membership.md)
**Related workflows:** [WF-005 Membership subscription](../B04-business-workflows/WF-005-membership-subscription.md)
**Acceptance:** [B05 F-012](../B05-acceptance-criteria/F-012-memberships-and-subscriptions.md)
**Source:** `web/modules/custom/lms_membership/`

## Requirement
Admins define membership plans (`lms_membership_plan`, with `field_features`) that grant broad access (e.g. all courses or a tier) for a period. Users subscribe (via an order), creating a `lms_user_membership` with start/end dates; active membership entitles content access (F-004) without per-course purchase.

## Business value
Recurring/tiered memberships create predictable revenue and a simpler "all-access" path for organisations training many staff.

## Scope notes
- `lms_membership_plan` (catalog) + `lms_user_membership` (subscription instance).
- Entitlement check in F-004 considers active membership as an access source.
- Renewal/expiry handling; no external subscription-billing integration (membership purchased as an order).

## Cross references
- Design: [C04 lms_membership_plan](../../C-Design/C04-entities/lms_membership_plan.md), [C04 lms_user_membership](../../C-Design/C04-entities/lms_user_membership.md), [C12 WF-005](../../C-Design/C12-workflows/WF-005-membership-subscription.md)
