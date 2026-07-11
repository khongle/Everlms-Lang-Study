# WF-004-003: Manager orders table truncates critical columns

## Status
Done

## Workflow
WF-004 - Order checkout and payment

## Evidence
- Screenshot: `.agent/docs/E-Quality/E00-workflows/round-1/_screens/004-order-checkout-and-payment/007-manager-orders-processing-before-approval.png`

## Problem
The manager order list truncates column headers, order IDs, and the action column at desktop width. A processing bank transfer order appears as split fragments such as `#1 / 4...`, and the edit action is not visually clear.

## Impact
Managers reviewing bank transfer receipts may struggle to identify the order and action, increasing the chance of delayed or wrong approvals.

## Expected
Order ID, learner, amount, date, status, and action columns remain readable at standard desktop widths, with horizontal scroll only as a fallback.

## Resolution Plan
- Add a scoped order table class and column widths.
- Prevent critical columns from ellipsizing.
- Retest WF-004 manager processing and approval screens.

## Resolution
- Added scoped `.lms-admin-orders-page` and `.lms-orders-table` layout rules.
- Restored table-cell display for order rows that were affected by global admin table fixes.
- Added a dedicated order actions cell class so the `Edit` action is visible and clickable within the viewport.
- Retested with real student/manager users and real bank transfer order `#147`.

## Verification
- Screenshot: `.agent/docs/E-Quality/E00-workflows/round-1/_screens/004-order-checkout-and-payment/007-manager-orders-processing-before-approval.png`
- Screenshot: `.agent/docs/E-Quality/E00-workflows/round-1/_screens/004-order-checkout-and-payment/008-manager-order-review-before-status-change.png`
- DB: order `147` item course `93` / `$80.00`; enrollment `216` created for uid `61`.
