# C04 Entity - lms_membership_plan

**Display name:** Membership Plan
**Owning module(s):** [lms_membership](../C05-modules/lms_membership.md)
**Mode:** as-built (Content Entity)

## Purpose
A purchasable subscription plan granting broad/tiered course access for a duration, with optional trial.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| name | plan name |
| field_description | formatted description |
| field_price | price |
| field_duration_days | access duration |
| field_billing_cycle | cycle (monthly/yearly/…) |
| field_course_access | scope of courses granted |
| field_trial_days | free trial length |
| field_features | **multi** feature bullets |
| field_recommended | highlight flag |
| status | active |

## Relationships
- Subscribed via `lms_user_membership.field_plan`; purchased via `lms_order_item`.

## Editorial/data management
Managers create plans via the plan entity form. All copy/features in fields. See [F-012](../../B-Requirements/B03-features/F-012-memberships-and-subscriptions.md), [WF-005](../C12-workflows/WF-005-membership-subscription.md).
