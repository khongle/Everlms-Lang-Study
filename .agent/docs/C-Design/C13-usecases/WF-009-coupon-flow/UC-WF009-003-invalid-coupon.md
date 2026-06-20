---
usecase_id: UC-WF009-003
workflow: WF-009
type: error
title: Student Enters Invalid or Expired Coupon Code
status: target-state / planned (scaffold fields as-built)
---

# UC-WF009-003 — Student Enters Invalid or Expired Coupon Code

**Implementation status: target-state / planned.** The error messages and validation chain are planned behaviour. The `lms_order` scaffold fields (`field_coupon_code`, `field_discount`) are as-built but never populated. No validation service exists yet.

---

## Actors

| Actor | Type | Notes |
|-------|------|-------|
| Student | Primary | Enters a coupon code that fails one or more validation steps |
| System (planned coupon validation service) | Secondary | Returns structured error on validation failure |

---

## Preconditions

| # | Condition |
|---|-----------|
| P1 | Student is authenticated and on a checkout page |
| P2 | Student enters a coupon code string in the planned coupon input |

---

## Trigger

Student enters a coupon code and clicks "Apply" (or submits the checkout form with a coupon code set).

---

## Error Sub-flows

### Error A — Code not found

| Step | Given | When | Then |
|------|-------|------|------|
| 1 | Student enters code `INVALID99` | Validation service executes step 1 | `lms_coupon` entity query by `field_code = 'INVALID99'` returns no result |
| 2 | Code not found | Service returns error | `{valid: false, error: "invalid_coupon", message: "Invalid coupon code."}` |
| 3 | Client receives error | Checkout form shows inline error | Coupon field shows error message; subtotal unchanged; `field_coupon_code` NOT set on order |

### Error B — Code expired

| Step | Given | When | Then |
|------|-------|------|------|
| 1 | Student enters code `SPRING24` | Validation service executes steps 1–3 | Code found, `status=TRUE`; but `field_valid_until < now()` |
| 2 | Date check fails | Service returns error | `{valid: false, error: "coupon_expired", message: "This coupon has expired."}` |
| 3 | Client receives error | Checkout form shows inline error | Student checks for a valid coupon |

### Error C — Usage limit reached

| Step | Given | When | Then |
|------|-------|------|------|
| 1 | Student enters code `LIMITEDRUN` | Validation service executes steps 1–4 | Code found, active, in-date; but `field_usage_count >= field_max_uses` (and `field_max_uses > 0`) |
| 2 | Usage count check fails | Service returns error | `{valid: false, error: "coupon_exhausted", message: "This coupon's usage limit has been reached."}` |

### Error D — Per-user limit reached

| Step | Given | When | Then |
|------|-------|------|------|
| 1 | Student has already used this coupon once | Validation service executes step 5 | `lms_coupon_usage` query for `(uid, coupon_id)` returns a record; count ≥ `field_uses_per_user` (and `field_uses_per_user > 0`) |
| 2 | Per-user check fails | Service returns error | `{valid: false, error: "coupon_user_limit", message: "You have already used this coupon."}` |

### Error E — Minimum order not met

| Step | Given | When | Then |
|------|-------|------|------|
| 1 | Course price is $10; coupon has `field_min_order = 50.00` | Validation service executes step 6 | `subtotal (10.00) < field_min_order (50.00)` |
| 2 | Min order check fails | Service returns error | `{valid: false, error: "min_order_not_met", message: "A minimum order of $50.00 is required for this coupon."}` |

### Error F — Not applicable to item

| Step | Given | When | Then |
|------|-------|------|------|
| 1 | Coupon has `field_applicable_courses` referencing course IDs `[10, 20]`; student is buying course ID `99` | Validation service executes step 7 | None of the cart item course IDs appear in `field_applicable_courses` |
| 2 | Scope check fails | Service returns error | `{valid: false, error: "coupon_not_applicable", message: "This coupon is not applicable to the selected items."}` |

---

## Entities Mutated

None — validation failure prevents any entity mutation. `lms_order` is only created when payment is initiated; `field_coupon_code` is never set on a failed validation.

---

## Observability Hooks

| Hook / Event | Module | What to check |
|--------------|--------|----------------|
| Coupon validation endpoint (planned) | planned | Each error sub-flow returns distinct `error` key for frontend localisation |
| `lms_order.field_coupon_code` (as-built) | `lms_order` | Must remain empty (`NULL` or `''`) when coupon is rejected; full-price order proceeds if student chooses to continue without coupon |
| `lms_order.field_discount` (as-built) | `lms_order` | Remains `0.00` on failed coupon |
| Logger (planned) | planned | Invalid coupon attempts may be logged at info level for abuse detection |

---

## Acceptance Link

| AC ID | Summary | Planned Test |
|-------|---------|--------------|
| No matching B05 AC yet | Coupon error paths not yet in B05 — AC to be added when feature is implemented | T-TBD (planned, E03 — not yet materialized) |

---

## Notes

- All error sub-flows in this use case are **target-state / planned**. No coupon validation logic exists in `CheckoutForm.php` or `MembershipCheckoutForm.php` as of 2026-06-20.
- The scaffold fields `field_coupon_code` and `field_discount` are as-built on the `lms_order` entity but are never set by the checkout forms in the current code.
- For sub-flow C (usage limit), a race condition exists if two simultaneous requests both see `usage_count < max_uses` before either increments. The planned implementation must use an atomic check-and-increment (DB transaction with row lock) to prevent over-redemption.
- The checkout form should allow the student to remove an applied coupon (clear `field_coupon_code`) and proceed at full price. No mechanism for this is implemented or planned in detail yet.
- WF-009 is marked `PROPOSED` in its B04 workflow document, indicating it has not been approved for development. These use cases provide design-ready specifications when the feature enters the backlog.
