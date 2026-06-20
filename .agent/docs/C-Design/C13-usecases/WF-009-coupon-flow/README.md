---
workflow: WF-009
title: Coupon Creation & Redemption — Use-Case Catalog
status: target-state / planned
---

# C13 — WF-009: Coupon Creation & Redemption — Use-Case Catalog

**Implementation status: target-state / planned.** The coupon feature is a data scaffold only. The `lms_order` entity has `field_coupon_code` (string, max 32) and `field_discount` (decimal, always 0 in current flows), but no coupon entity, no validation service, no CRUD UI, and no checkout form integration exist as of 2026-06-20. All use cases in this folder describe the planned target state. Only edge cases reference what is actually present in the codebase today.

## Files in this folder

| File | ID | Type | Summary |
|------|----|------|---------|
| [UC-WF009-001-coupon-creation.md](UC-WF009-001-coupon-creation.md) | UC-WF009-001 | happy | Admin creates a percentage-discount coupon (target-state) |
| [UC-WF009-002-coupon-redemption.md](UC-WF009-002-coupon-redemption.md) | UC-WF009-002 | happy | Student applies coupon at checkout; discount applied to order (target-state) |
| [UC-WF009-003-invalid-coupon.md](UC-WF009-003-invalid-coupon.md) | UC-WF009-003 | error | Student enters an invalid or expired coupon code (target-state for error messages; scaffold field as-built) |

## Cross-links

- Workflow: [../../../B-Requirements/B04-business-workflows/WF-009-coupon-flow.md](../../../B-Requirements/B04-business-workflows/WF-009-coupon-flow.md)
- Acceptance criteria: [../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md)
- Related workflow: [../../../B-Requirements/B04-business-workflows/WF-004-order-management.md](../../../B-Requirements/B04-business-workflows/WF-004-order-management.md)
- Primary modules (planned): `lms_order`, `lms_payment` (planned: new `lms_coupon` module or extension of `lms_order`)
- Entities mutated (planned): `lms_coupon` (does not exist), `lms_order` (as-built scaffold fields)

## As-built vs. planned gap

| Component | As-built | Planned |
|-----------|----------|---------|
| `lms_coupon` entity | Does not exist | Custom content entity with full field set |
| `lms_coupon_usage` entity / table | Does not exist | Per-user usage tracking |
| Coupon CRUD admin UI | Does not exist | `/workspace/coupons` routes |
| Checkout form coupon input | Does not exist — `field_coupon_code` on `lms_order` is never set by `CheckoutForm.php` | Code input + AJAX validation + discount calc |
| `field_discount` on `lms_order` | Exists; always 0 in current flows | Populated with calculated discount on successful redemption |
| Validation service | Does not exist | 7-step validation chain |
| Usage counter enforcement | Does not exist | Atomic increment post order-complete |
