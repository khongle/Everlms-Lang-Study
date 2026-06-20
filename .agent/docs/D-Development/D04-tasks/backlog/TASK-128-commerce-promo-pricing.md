# Task: Promotional Pricing with Time-Bound Discounts

**ID:** TASK-128
**Unit:** U-020 (lms_order), U-015 (lms_course)
**Group:** G-COMMERCE (hardening)
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Allow admins to schedule promotional discounts on courses with a start and end datetime. Prices automatically switch to promo price when the window opens and revert when it closes. A countdown banner is shown on the course page during the promo period. Cron handles the automatic activation/deactivation.

## Context

- Module: `lms_course` (product data), `lms_order` (checkout price resolution)
- Distinct from coupon codes (which apply on demand); promo pricing is automatic and visible to all
- VND amounts; no rounding issues as VND has no fractional units

## Acceptance Criteria

- [ ] `field_promo_price` (decimal/integer VND) and `field_promo_start`/`field_promo_end` (datetime) fields on `lms_course`
- [ ] Admin can schedule multiple non-overlapping promo windows per course (validation prevents overlap)
- [ ] Cron (`hook_cron`) checks for promos starting/ending within the current run window and activates/deactivates them
- [ ] `PriceResolverService::resolve()` returns promo price when a promo is active, original price otherwise
- [ ] Course page displays original price struck-through with promo price highlighted when active
- [ ] Countdown banner: "Sale ends in HH:MM:SS" rendered client-side via JS (endpoint provides expiry timestamp)
- [ ] Checkout always resolves price at time of checkout (not at time of cart add)
- [ ] Admin list page shows active promos badge on affected courses
- [ ] Email optional: admin can opt-in to send "Sale starts now" email to wishlist users

## Technical Notes

- New service: `web/modules/custom/lms_course/src/PriceResolverService.php`
- `resolve(Course $course, int $timestamp = REQUEST_TIME): int` returns effective price in VND
- Cron: query courses where `field_promo_start <= NOW < field_promo_end`; use cached flag `field_promo_active` (boolean) to avoid per-request queries
- Countdown JS: new library `lms_course/promo-countdown`; endpoint `GET /api/course/{id}/promo-expiry` returns JSON `{expires_at: timestamp}`
- Checkout price lock: store resolved price in cart/order at creation time; do not re-resolve on submit
- Overlap validation: `PromoScheduleValidator` checks existing promo ranges on entity presave

## Dependencies

- TASK-050 (commerce hardening)
- TASK-127 (VAT calculation applied to promo price)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
