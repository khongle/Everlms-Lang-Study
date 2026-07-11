# WF-005-001: Membership subscriptions can create duplicate/stale active records

## Status
Done

## Workflow
WF-005 - Membership subscription

## Evidence
- DB for uid `61` contains multiple `active` rows for the same Basic Access plan.
- DB also contains an `active` membership row with missing start/end dates.
- `MembershipController::myMembership()` filters only by status and does not require `field_end_date > now`, so stale active rows can appear on `/my-membership`.
- `_lms_order_process_completed_order()` creates a new `lms_user_membership` every time a membership order completes.

## Problem
Repeated membership purchases can produce duplicate active cards instead of a renewal/extension, while stale active rows with missing or expired dates can still render in the learner membership page.

## Impact
Learners may see confusing duplicate plans or invalid active memberships. Access checks and the learner-facing membership page can disagree.

## Expected
Completing a membership order should create one active membership per user/plan or extend the current active one. `/my-membership` should show only valid, unexpired active/trial memberships and collapse duplicate rows by plan.

## Resolution Plan
- Make membership fulfillment idempotent per user/plan.
- Extend an existing active/trial membership from its current end date when the same plan is purchased again.
- Filter `/my-membership` by future end date and dedupe by plan.
- Retest with a real student, real order, and manager approval.

## Resolution
- Updated membership order fulfillment to extend an existing current membership per user/plan.
- If only a stale active/trial row exists for the same user/plan, the completed order reactivates that row with valid start/end dates instead of creating another duplicate.
- Updated `/my-membership` to show only active/trial memberships with `field_end_date > now` and one card per plan.

## Verification
- Retested WF-005 with real student uid `67`, manager approval, and bank transfer order `#152`.
- DB: membership id `29` for uid `67` / plan `8` was reactivated with dates `2026-06-30` to `2026-07-30`; no duplicate plan `8` membership was created.
- DB: course `89` enrollment count for uid `67` remained `0`, confirming course unlock came from membership access.
