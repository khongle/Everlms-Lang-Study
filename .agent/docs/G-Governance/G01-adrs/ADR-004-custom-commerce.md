# ADR-004 - Custom commerce (no Drupal Commerce)

**Status:** Accepted (as-built) · **Date:** 2026-06-21

## Context
EverLMS sells courses, bundles, and memberships with coupons and multiple gateways, but does not need the full Drupal Commerce stack.

## Decision
Implement **custom commerce**: `lms_order` (header) + `lms_order_item` (lines) + `lms_payment` gateway host; entitlement granted on paid order. Membership purchased as an order.

## Consequences
- Lean, domain-fit commerce; fewer contrib dependencies.
- Must hand-build reconciliation — currently missing webhooks (R-002, ADR-tracked debt).
- Re-implements some Commerce features (coupons, refunds) by hand.
