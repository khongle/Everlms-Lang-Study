---
usecase_id: UC-WF009-001
workflow: WF-009
type: happy
title: Admin Creates a Percentage-Discount Coupon
status: target-state / planned
---

# UC-WF009-001 — Admin Creates a Percentage-Discount Coupon

**Implementation status: target-state / planned.** No coupon entity, CRUD UI, or validation service exists in the codebase as of 2026-06-20. This use case describes the required target behaviour for the planned `lms_coupon` entity and admin management pages.

---

## Actors

| Actor | Type | Notes |
|-------|------|-------|
| Admin / Manager | Primary | Holds `administer lms_course` (or a dedicated coupon-admin permission TBD) |
| System (planned `lms_coupon` module or extension) | Secondary | Persists coupon entity, enforces uniqueness |

---

## Preconditions

| # | Condition |
|---|-----------|
| P1 | Actor is authenticated with `administer lms_course` permission (or dedicated coupon permission once implemented) |
| P2 | `lms_coupon` entity type exists (planned — not yet implemented) |
| P3 | Coupon management routes are defined (planned — not yet implemented) |

---

## Trigger

Admin navigates to coupon management page (route TBD — not yet defined).

---

## Main Flow

> All steps are target-state. Source code references are planned design, not as-built.

| Step | Given | When | Then |
|------|-------|------|------|
| 1 | Admin at coupon management page (`/workspace/coupons` — planned) | Page loads | System shows coupon list and "Add Coupon" action link |
| 2 | Admin clicks "Add Coupon" | Add form renders | Fields: `field_code` (required, uppercase alphanumeric A–Z 0–9, max 32), `field_discount_type` (percentage \| fixed), `field_discount_value` (decimal > 0), `field_max_discount` (optional cap for percentage type), `field_min_order` (optional minimum cart total) |
| 3 | Admin fills in code `SUMMER20`, type `percentage`, value `20`, max_discount `50.00` | Form validation runs | Validates: code is unique (no existing `lms_coupon` with same `field_code`); value > 0; if type=`percentage`, value ≤ 100 |
| 4 | Admin sets scope fields | Conditional fields | `field_valid_from` / `field_valid_until` (optional timestamps); `field_max_uses` (0 = unlimited); `field_uses_per_user` (0 = unlimited); `field_applicable_courses` (entity_reference unlimited to `lms_course`; empty = all) |
| 5 | Admin saves form | `submitForm()` executes | `lms_coupon` entity saved with `status=TRUE` (active); `field_usage_count=0`; redirects to coupon list |

---

## Entities Mutated

| Entity | Field | Change |
|--------|-------|--------|
| `lms_coupon` (planned) | `field_code`, `field_discount_type`, `field_discount_value`, `field_max_discount`, `field_min_order`, `field_valid_from`, `field_valid_until`, `field_max_uses`, `field_uses_per_user`, `field_usage_count`, `field_applicable_courses`, `status` | Created in step 5 |

---

## Observability Hooks

| Hook / Event | Module | What to check |
|--------------|--------|----------------|
| Coupon entity insert | planned `lms_coupon` | DB row in `lms_coupon` table; `field_code` unique; `status=1`; `field_usage_count=0` |
| Admin list | planned `lms_coupon` | New coupon appears in `/workspace/coupons` list with status Active |

---

## Acceptance Link

| AC ID | Summary | Planned Test |
|-------|---------|--------------|
| No matching B05 AC yet | Coupon creation is not covered in current B05 — AC to be added when feature is implemented | T-TBD (planned, E03 — not yet materialized) |

---

## Notes

- The `lms_coupon` entity does not exist. This entire use case is a specification for future implementation.
- The code format rule (uppercase alphanumeric only) should be enforced at form validation level and also at entity level via a constraint plugin.
- `field_max_discount` caps the absolute discount amount for percentage coupons (e.g., 20% off with $50 cap: on a $400 order the discount is $50, not $80).
- Admin deactivation (setting `status=FALSE`) should immediately prevent any new redemption without affecting existing orders that already used the code.
- Coupon code lookup at redemption time should be case-insensitive even though codes are stored uppercase — `LOWER()` DB comparison or Drupal entity query with `LIKE` or `strToUpper` normalisation.
- No stacking: `lms_order` has a single `field_coupon_code` string field. Only one coupon per order is architecturally supported without schema changes.
