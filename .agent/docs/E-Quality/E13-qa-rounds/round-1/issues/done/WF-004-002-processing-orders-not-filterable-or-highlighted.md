# WF-004-002: Processing orders are not filterable or highlighted in manager list

## Status
Done

## Workflow
WF-004 - Order checkout and payment

## Evidence
- `lms_order.field_status` supports `processing`.
- C12/C13 define `processing` as bank transfer receipt uploaded and waiting manager verification.
- `/workspace/orders` filter does not include `processing`.
- Order status badge map does not style `processing`.

## Problem
Bank transfer orders move from `pending` to `processing` after receipt upload, but managers cannot filter by that state and the list does not visually distinguish it.

## Impact
Managers can miss receipt-uploaded orders that are ready for verification, delaying entitlement approval.

## Expected
`/workspace/orders` should include `Processing` in the status filter and show a clear warning/info badge for processing orders.

## Resolution Plan
- Add `Processing` option to the manager order status filter.
- Add a badge class mapping for `processing`.
- Retest with a real bank transfer order.

## Resolution
- Added `Processing` to `/workspace/orders` status filter.
- Added explicit `processing` badge mapping and scoped `lms-badge--info` styling in `lms-admin-orders.html.twig`.
- Retested with real student/manager users and real bank transfer order `#147`.

## Verification
- Screenshot: `.agent/docs/E-Quality/E00-workflows/round-1/_screens/004-order-checkout-and-payment/007-manager-orders-processing-before-approval.png`
- DB: order `147` moved `processing` → `completed` after manager approval.
