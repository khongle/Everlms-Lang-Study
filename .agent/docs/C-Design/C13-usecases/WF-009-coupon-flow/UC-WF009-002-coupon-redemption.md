---
usecase_id: UC-WF009-002
workflow: WF-009
type: happy
title: Student Applies Coupon at Checkout — Discount Applied to Order
status: target-state / planned
---

# UC-WF009-002 — Student Applies Coupon at Checkout

**Implementation status: target-state / planned.** The `lms_order` entity already has `field_coupon_code` and `field_discount` scaffold fields (as-built), but `CheckoutForm.php` and `MembershipCheckoutForm.php` never populate them. The validation service, coupon input UI, and discount calculation are all planned.

---

## Actors

| Actor | Type | Notes |
|-------|------|-------|
| Student | Primary | Authenticated; enters coupon code at checkout |
| System (planned coupon validation service) | Secondary | Runs the 7-step validation chain |
| System (`lms_order.module`) | Secondary | As-built: `hook_entity_insert/update` triggers enrollment on order `completed` |

---

## Preconditions

| # | Condition |
|---|-----------|
| P1 | Student is authenticated |
| P2 | A valid, active `lms_coupon` entity exists with the entered code (planned entity) |
| P3 | Current date is within `field_valid_from` – `field_valid_until` range (or range not set) |
| P4 | `field_usage_count < field_max_uses` (or `field_max_uses = 0`) |
| P5 | Student has not exceeded `field_uses_per_user` for this coupon |
| P6 | Order subtotal ≥ `field_min_order` |
| P7 | At least one cart item matches `field_applicable_courses` (or `field_applicable_courses` is empty = all) |

---

## Trigger

Student is on the checkout page (`/checkout/{course_id}` or `/checkout/membership/{plan_id}`) and enters a coupon code in the planned coupon input field, then clicks "Apply".

---

## Main Flow

> Steps 1–4 are target-state. Steps 5–9 involve as-built entities and hooks.

| Step | Given | When | Then |
|------|-------|------|------|
| 1 | Student at `/checkout/{course_id}` | Coupon input field visible on `CheckoutForm` (planned addition) | Student types `SUMMER20`; client sends AJAX or form request to coupon validation endpoint (planned route: `POST /api/lms/coupon/validate` or inline form action — TBD) |
| 2 | Validation endpoint receives `{code, course_id, user_id}` | Planned validation service executes 7-step chain | Step 1: lookup `lms_coupon` by `field_code` = `SUMMER20` (case-insensitive); found and `status=TRUE` |
| 3 | Code found and active | Steps 2–7 run in sequence | Date range check; total usage check; per-user usage check; min order check; applicable courses check — all pass |
| 4 | All validation steps pass | Discount calculated | `discount_amount = min(subtotal * 0.20, 50.00)` (20% capped at $50); response returned to client with `{valid: true, discount_type: "percentage", discount_value: 20, max_discount: 50.00, calculated_discount: <amount>, final_total: <amount>}` |
| 5 | Student confirms checkout | `CheckoutForm::submitForm()` executes (planned modification) | Sets `$order->set('field_coupon_code', 'SUMMER20')` and `$order->set('field_discount', <calculated_discount>)` and `$order->set('field_total', subtotal - discount)` before creating payment |
| 6 | Order entity created | Payment processing runs | Payment gateway dispatched for `field_total` (discounted amount); on success, order `field_status` set to `completed` |
| 7 | Order reaches `completed` status | `lms_order.module` `hook_entity_update` fires (as-built) | `_lms_order_process_completed_order()` creates `lms_user_course` enrollment entity; sends confirmation email and in-app notification |
| 8 | Enrollment created | Post-order hook (planned) | Coupon `field_usage_count` incremented by 1; `lms_coupon_usage` record created for `(user_id, coupon_id, order_id)` — prevents per-user re-use |

---

## Entities Mutated

| Entity | Field | Change | Status |
|--------|-------|--------|--------|
| `lms_coupon` (planned) | `field_usage_count` | Incremented by 1 in step 8 | planned |
| `lms_coupon_usage` (planned) | `uid`, `coupon_id`, `order_id`, `created` | Created in step 8 | planned |
| `lms_order` (as-built entity) | `field_coupon_code` | Set to `'SUMMER20'` in step 5 | as-built field — currently never set by checkout |
| `lms_order` (as-built entity) | `field_discount` | Set to calculated discount amount in step 5 | as-built field — currently always 0 |
| `lms_order` (as-built entity) | `field_total` | Set to `subtotal - discount` in step 5 | as-built field — currently equals subtotal |
| `lms_user_course` (as-built entity) | `field_course`, `uid`, `field_status`, `field_enrolled_at` | Created in step 7 | as-built (`_lms_order_process_completed_order`) |

---

## Observability Hooks

| Hook / Event | Module | What to check |
|--------------|--------|----------------|
| Coupon validation service (planned) | planned | Returns correct `calculated_discount` for `SUMMER20` on a $200 subtotal: `min(200*0.20, 50) = 40.00` |
| `lms_order` entity fields | `lms_order` (as-built schema) | After checkout: `field_coupon_code = 'SUMMER20'`, `field_discount = 40.00`, `field_total = 160.00` |
| `hook_entity_update` → `_lms_order_process_completed_order()` | `lms_order.module` (as-built) | `lms_user_course` row exists for `uid + field_course` |
| `lms_coupon.field_usage_count` (planned) | planned | Incremented by 1 post order completion; not incremented if payment fails |
| Race condition guard (planned) | planned | DB-level row lock or atomic increment prevents over-use beyond `field_max_uses` in concurrent checkout |

---

## Acceptance Link

| AC ID | Summary | Planned Test |
|-------|---------|--------------|
| No matching B05 AC yet | Coupon redemption is not covered in current B05 — AC to be added when feature is implemented | T-TBD (planned, E03 — not yet materialized) |

---

## Notes

- **Usage record timing:** The `lms_coupon_usage` record and `field_usage_count` increment should be created only after the `lms_order` reaches `completed` status, not at the time of code application. If payment fails after the code is applied, the coupon is not consumed.
- **Concurrency risk:** Without an atomic counter or DB row-lock, two simultaneous checkouts with the same coupon can both pass the `usage_count < max_uses` check and both be accepted, exceeding the limit. This must be handled with a DB transaction and pessimistic locking or an atomic SQL increment with a conditional check.
- **No stacking:** `lms_order.field_coupon_code` is a single string field. Only one coupon per order is supported by the current schema.
- **Stripe PaymentIntent creation timing:** `CheckoutForm::buildForm()` currently creates a `\Stripe\PaymentIntent` with the full `field_subtotal` amount. When coupon discount is added, the PaymentIntent amount must be recalculated after coupon validation — requiring either a new PaymentIntent or an update to the existing one. This is an additional implementation concern.
- The `field_discount` and `field_coupon_code` fields on `lms_order` exist in the as-built codebase but are never populated by `CheckoutForm.php` or `MembershipCheckoutForm.php` in the current code.
