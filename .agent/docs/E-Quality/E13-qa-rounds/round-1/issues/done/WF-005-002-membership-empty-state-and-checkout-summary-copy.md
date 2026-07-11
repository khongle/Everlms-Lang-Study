# WF-005-002: Membership empty state has low contrast and checkout summary says lifetime access

## Status
Done

## Workflow
WF-005 - Membership subscription

## Evidence
- Screenshot: `.agent/docs/E-Quality/E00-workflows/round-1/_screens/005-membership-subscription/001-student-my-membership-before-subscription.png`
- Screenshot: `.agent/docs/E-Quality/E00-workflows/round-1/_screens/005-membership-subscription/004-student-membership-checkout-before-payment.png`

## Problem
The empty `/my-membership` card uses dark text/icon on a dark panel, making the title and explanation hard to read. The membership checkout summary reuses course checkout copy and labels a monthly membership as `Lifetime access`.

## Impact
Learners may miss the empty-state guidance and may be misled about the billing/access period while buying a membership.

## Expected
The empty state should meet readable contrast on its dark panel. Membership checkout summary should describe the actual billing period, such as monthly membership access.

## Resolution Plan
- Increase empty state icon/title/body contrast.
- Add a checkout summary subtitle variable so course checkout can keep lifetime access while membership checkout shows billing-period access.
- Retest WF-005 with a real clean student and regenerate screenshots.

## Resolution
- Improved empty `/my-membership` contrast for icon, title, and explanatory text.
- Added `item_subtitle` to the checkout theme contract.
- Course checkout keeps `Lifetime access`; membership checkout now renders billing-cycle copy such as `Monthly membership access`.

## Verification
- Screenshot: `.agent/docs/E-Quality/E00-workflows/round-1/_screens/005-membership-subscription/001-student-my-membership-before-subscription.png`
- Screenshot: `.agent/docs/E-Quality/E00-workflows/round-1/_screens/005-membership-subscription/004-student-membership-checkout-before-payment.png`
- Retested full WF-005 with real student uid `67` and order `#152`.
