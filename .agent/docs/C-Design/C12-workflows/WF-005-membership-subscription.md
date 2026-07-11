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

## Membership validity contract
- `active`/`trial` is valid only when `field_end_date` is in the future.
- Learner-facing membership pages must hide stale active rows with missing or expired end dates.
- Completing a membership order for an already-current user/plan extends the existing membership instead of creating a duplicate card.
- Completing a membership order when only a stale row exists for the same user/plan reactivates that row with fresh start/end dates.

## Round-2 QA verification
Core validity contracts confirmed working:
- `MembershipManager::hasActiveMembership()` requires `field_status IN (active,trial)` **and** `field_end_date > now`, so NULL/expired rows never count (maria's stale plan-9 row with NULL end date is excluded).
- `MembershipController::myMembership()` (`/my-membership`) dedupes by plan and hides stale rows — maria's 10 duplicate active plan-7 rows render as **one** "Basic Access" card.
- Order completion (`lms_order.module`) extends an existing active membership, else reactivates a stale one, else creates — no new duplicates.

⚠️ **Product gap (`WF-005-001`, open):** entitlement is tier-agnostic — any active membership unlocks the whole catalog, so a free "Basic Access" member can open paid specialist courses despite the differentiated `/pricing` tiers. Needs a product decision + a plan access-level field.

## Notifications
Activation + renewal/expiry reminders (cron). Lapsed → access revoked in F-004 gating.
