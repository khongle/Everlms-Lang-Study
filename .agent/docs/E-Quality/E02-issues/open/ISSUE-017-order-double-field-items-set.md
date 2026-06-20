# ISSUE-017 — CheckoutController::createAndFulfillFreeMembershipOrder Sets field_items Twice

| Field | Value |
|---|---|
| ID | `ISSUE-017` |
| Title | CheckoutController::createAndFulfillFreeMembershipOrder calls $order->set('field_items', ...) twice — second call silently overwrites first |
| Source | G4-Code-Quality / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `LOW` |
| Affected page / route | `/checkout/membership/{plan_id}` (`lms_order.checkout_membership`) for free membership plans |
| Owning Epic | E-05 (Commerce) — |
| Owning Story | [`lms_order`](../../../D-Development/D03-stories/lms_order.md) |
| Owning module(s) | `lms_order` |

## Summary

`CheckoutController::createAndFulfillFreeMembershipOrder()` creates an `lms_order` entity for free (zero-price) membership plan enrollments. Within this method, `$order->set('field_items', [$order_item->id()])` is called **twice** in succession. The second call overwrites the first without combining values.

Because both calls likely set `field_items` to the same single item ID, the functional outcome for a simple free membership is identical to a single call. However, the double-set is a code defect that:

1. **Masks a latent bug:** if the two calls ever set different values (e.g., after a future refactor that adds a second order item between the calls), the first set is silently discarded. This is a silent data-loss pattern.
2. **Indicates copy-paste origin:** the analogous `createAndFulfillFreeOrder()` method (for free course checkout) contains only a single `set('field_items', ...)` call. The double call in the membership variant was not caught in review.
3. **Creates misleading intent:** a reader of the code may assume the second `set()` appends to the field (entity reference fields with `UNLIMITED` cardinality support multi-value), when in fact `set()` replaces the entire field value.

No data loss or incorrect enrollment is observed in the current code path because only one order item is being set in both calls and the `hook_entity_insert` / `hook_entity_update` enrollment trigger fires on the saved (correct) order state. However, it is a code quality issue that should be corrected.

## Reproduction

1. Navigate to `/checkout/membership/{plan_id}` where the plan has `field_price = 0`.
2. `CheckoutController::checkoutMembership()` detects the free plan and calls `createAndFulfillFreeMembershipOrder($plan_id, $user_id)`.
3. Review method source — `$order->set('field_items', [$order_item->id()])` appears twice.

Confirm:
```bash
grep -n "field_items" web/modules/custom/lms_order/src/Controller/CheckoutController.php
```
Should show two consecutive `set('field_items', ...)` calls inside `createAndFulfillFreeMembershipOrder()`.

## Root Cause

The method was likely derived from `createAndFulfillFreeOrder()` via copy-paste and the duplicate line was not noticed during authoring or review. No automated test covers this specific code path for free membership plans, so the redundancy was not caught.

Source file:
- `web/modules/custom/lms_order/src/Controller/CheckoutController.php` — `createAndFulfillFreeMembershipOrder()` method

## Resolution (filled at RESOLVED)

1. Open `web/modules/custom/lms_order/src/Controller/CheckoutController.php`.
2. In `createAndFulfillFreeMembershipOrder()`, remove the duplicate `$order->set('field_items', [$order_item->id()])` call; retain only the single, correct call.
3. Add a comment clarifying the expected cardinality: `// field_items: single item for free membership orders`.
4. Add a unit or functional test covering the free membership checkout path, asserting that the created order has exactly one item in `field_items`.

## Trace links

- **Story:** `D-Development/D03-stories/lms_order.md`
- **C05 module spec:** `C-Design/C05-modules/lms_order.md`
- **Facts base:** `modules-commerce.md` §lms_order §CheckoutController — "minor bug: `$order->set('field_items',...)` called twice"
- **K04:** Reusable rule — when deriving methods by copy-paste, diff the copy against the original immediately to identify accidental duplications; entity field `set()` silently replaces, never appends — only `appendItem()` or `setValue()` with the full multi-value array should be used for unlimited-cardinality fields.
