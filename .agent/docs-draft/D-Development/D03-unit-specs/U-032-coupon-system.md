# D03 — Unit Spec: Coupon System

**Unit ID:** U-032
**Group:** G-COMMERCE
**Title:** Coupon system
**Status:** SPEC
**Priority:** P1
**Assigned:** unassigned

---

## Scope

Implement coupon/discount codes: Coupon entity (code, type, value, expiry, usage limit), coupon validation service, application to checkout, and admin CRUD for coupon management.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Entity/Coupon.php` | Create | lms_payment |
| `src/Entity/CouponUsage.php` | Create | lms_payment |
| `src/Service/CouponService.php` | Create | lms_payment |
| `src/Form/CouponForm.php` | Create | lms_payment |
| `src/Controller/CouponAdminController.php` | Create | lms_payment |
| `config/install/lms_payment.coupon.schema.yml` | Create | lms_payment |
| `templates/coupon-field.html.twig` | Create | lms_payment |

---

## Interfaces / Services

- `CouponService::validate(string $code, int $userId, float $orderTotal): CouponResult`
- `CouponService::apply(Coupon $coupon, Order $order): Order` — returns order with discount applied
- Coupon types: `percent` (e.g. 20%), `fixed` (e.g. 50,000 VND), `free` (100%)
- Constraints: expiry date, usage limit (global), per-user limit, course/membership scope

---

## Acceptance Criteria

- [ ] Valid coupon code reduces order total correctly (percent + fixed types)
- [ ] Expired coupon returns validation error
- [ ] Coupon over usage limit returns validation error
- [ ] Same coupon cannot be used twice by same user (per-user limit = 1 default)
- [ ] Admin can create, enable/disable, view usage stats for coupons
- [ ] Coupon applied at checkout shows discounted price before payment

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | — |
| G4 Adversarial | PENDING | — |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-025 | Order entity — apply discount |
| U-030 | Checkout form — coupon input field |

---

**Feature Flag:** None

*End of U-032-coupon-system.md*
