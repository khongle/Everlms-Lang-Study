# C04 Entity - lms_user_membership

**Display name:** User Membership (subscription)
**Owning module(s):** [lms_membership](../C05-modules/lms_membership.md)
**Mode:** as-built (Content Entity)

## Purpose
An active/expired subscription instance entitling a user to a plan's courses for its validity window.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | subscriber |
| field_plan | ref → lms_membership_plan |
| field_start_date, field_end_date | validity window |
| field_status | active / expired / cancelled |
| field_auto_renew | renewal flag |
| created | timestamp |

## Relationships
- N→1 `user`, N→1 `lms_membership_plan`. Consulted by F-004 content gating.

## Editorial/data management
Created on plan purchase (order paid). Expiry handled by date + cron/status. See [F-012](../../B-Requirements/B03-features/F-012-memberships-and-subscriptions.md).
