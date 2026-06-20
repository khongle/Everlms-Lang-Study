---
id: WF-009
name: Coupon Creation and Redemption
status: PROPOSED
features: [F-004]
---

# WF-009 — Coupon Creation and Redemption

**Implementation status: target-state / planned.** The `lms_order` entity has a `field_coupon_code` string field (max_length=32) and a `field_discount` decimal field on the Order entity. However, as of 2026-06-20, **no coupon entity, no coupon validation service, no coupon CRUD UI, and no checkout form integration that processes a coupon code** exist in any custom module. The field is a data scaffold only. The full coupon lifecycle described below is a requirements specification for the feature to be implemented.

## Trigger

- **Creation**: Admin navigates to coupon management (route not yet defined) and creates a coupon.
- **Redemption**: Student enters a coupon code on the checkout page (`/checkout/{course_id}` or `/checkout/membership/{plan_id}`).

## Actors

| Actor | Role |
|-------|------|
| Admin / Manager | Create, edit, deactivate coupons |
| Student | Enter coupon code during checkout |
| System | Validate code, calculate discount, record usage |

## Preconditions

- **Creation**: Actor has `administer lms_course` or a dedicated coupon-admin permission.
- **Redemption**: Student is authenticated and on a checkout page with a qualifying item in the cart. A valid, active coupon entity exists.

## Happy Path — Coupon Creation (Admin) [target-state]

| Step | Actor | Action | System Response | Feature |
|------|-------|--------|----------------|---------|
| 1 | Admin | Navigate to coupon management (route TBD) | Display coupon list and create form | F-004 |
| 2 | Admin | Enter coupon code (or auto-generate); set discount type and value | Validate code uniqueness; validate discount_value > 0; validate percentage ≤ 100 | F-004 |
| 3 | Admin | Set scope: applicable courses (empty = all), date range, usage limits | Conditional fields appear per discount type | F-004 |
| 4 | Admin | Save | Coupon entity saved and active | F-004 |

## Happy Path — Coupon Redemption (Student) [target-state]

| Step | Actor | Action | System Response | Feature |
|------|-------|--------|----------------|---------|
| 1 | Student | At `/checkout/{course_id}`, enter coupon code in checkout form | Client-side or AJAX validation call to coupon validation endpoint (TBD) | F-004 |
| 2 | System | Run validation chain (see below) | All checks pass | F-004 |
| 3 | System | Calculate discount | Subtract from `field_subtotal`; store result in `field_discount` on the Order entity; set `field_coupon_code` on the Order entity | F-004 |
| 4 | Student | See updated total; proceed to payment | `field_total` = `field_subtotal` − `field_discount` | F-004 |
| 5 | System | On order completion | Record coupon usage (increment usage counter); no double-apply on retry | F-004 |

## Validation Chain (target-state)

The following validation steps are planned. None are implemented in the current checkout forms (`CheckoutForm.php`, `MembershipCheckoutForm.php`):

```
1. Code exists in coupon entity storage?
      No  → "Invalid coupon code"
2. Coupon status = active?
      No  → "Coupon is no longer active"
3. Current date within [valid_from, valid_until] range?
      No  → "Coupon has expired" or "Coupon not yet valid"
4. Total usage < max_uses (or max_uses = 0/unlimited)?
      No  → "Coupon usage limit reached"
5. Current user usage < uses_per_user (or uses_per_user = 0/unlimited)?
      No  → "You have already used this coupon"
6. Order subtotal >= min_order_amount?
      No  → "Minimum order amount not met"
7. Applicable to at least one item in order (or applicable_courses is empty)?
      No  → "Coupon not applicable to selected items"
   → Apply discount
```

## Error Paths [target-state]

| Step | Condition | Planned System Response | Recovery |
|------|-----------|------------------------|----------|
| 2 | Code not found | Show inline error on checkout form | User checks code or contacts admin |
| 2 | Code expired | Show "Coupon has expired" message | User finds valid coupon |
| 2 | Usage limit reached | Show "Coupon fully redeemed" message | Admin creates new coupon |
| 2 | Per-user limit reached | Show "You have already used this coupon" | No recovery for user |
| 2 | Min order amount not met | Show "Minimum order [amount] required" | User adds more items (if applicable) |
| 5 | Race condition on concurrent checkout | Lock coupon usage count check-and-increment atomically | Implement DB-level row lock or atomic counter |

## Data Model (as-built scaffold + planned fields)

### As-built on `lms_order` entity

| Field | Type | Status |
|-------|------|--------|
| `field_coupon_code` | string (max_length=32) | as-built — field exists on Order entity; stores code string; never populated by checkout form |
| `field_discount` | decimal (precision=10, scale=2, default=0) | as-built — field exists on Order entity; always 0 in current checkout flows |
| `field_total` | decimal (precision=10, scale=2) | as-built — currently set equal to subtotal (no discount applied) |

### Planned: Coupon entity (not yet implemented)

A `lms_coupon` (or similar) custom entity would need to be created with at minimum:

| Field | Type | Notes |
|-------|------|-------|
| `field_code` | string | Unique, uppercase alphanumeric |
| `field_discount_type` | list_string | `percentage` or `fixed` |
| `field_discount_value` | decimal | > 0; ≤ 100 for percentage |
| `field_max_discount` | decimal | Cap for percentage discounts (optional) |
| `field_min_order` | decimal | Minimum cart total to activate |
| `field_valid_from` | timestamp | Start date (optional) |
| `field_valid_until` | timestamp | Expiry date (optional) |
| `field_max_uses` | integer | 0 = unlimited |
| `field_uses_per_user` | integer | 0 = unlimited |
| `field_usage_count` | integer | Denormalized counter |
| `field_applicable_courses` | entity_reference (lms_course) | Unlimited; empty = all courses |
| `status` | boolean | Active / inactive |

A `lms_coupon_usage` entity (or table) would be needed to track per-user usage.

## Success Criteria [target-state]

- Coupon entity saved and retrievable by code (case-insensitive lookup).
- On checkout: `lms_order.field_discount` populated with calculated discount amount; `lms_order.field_coupon_code` set to the redeemed code; `lms_order.field_total` = subtotal minus discount.
- On order completion: coupon `field_usage_count` incremented; per-user usage recorded.
- Concurrent redemption does not allow over-use beyond `field_max_uses`.

## Business Rules [target-state]

- **Code format**: Uppercase alphanumeric (A–Z, 0–9) only. Validation at creation time.
- **Discount cap**: For percentage-type coupons, `field_max_discount` caps the absolute amount off. Example: 20% off with cap $50 — on a $400 order, discount is $50 not $80.
- **Usage record timing**: CouponUsage record created after `lms_order` reaches `completed` status, not at time of code application. If payment fails, the coupon is not consumed.
- **Scope**: If `field_applicable_courses` is empty, coupon applies to any item. If non-empty, at least one cart item must match.
- **Admin deactivation**: Setting coupon `status=FALSE` immediately prevents redemption.
- **No stacking**: Only one coupon per order (single `field_coupon_code` field on Order).

## Gap Analysis vs. As-Built Code

| Component | Draft Expectation | As-Built Reality |
|-----------|------------------|-----------------|
| Coupon entity | `lms_coupon` with full field set | Does not exist |
| Coupon CRUD UI | Admin management pages | Does not exist |
| Checkout form integration | Code input + validation + discount calc | `field_coupon_code` field on Order entity exists but `CheckoutForm` / `MembershipCheckoutForm` never set it |
| Validation service | 7-step validation chain | Does not exist |
| Usage tracking | `lms_coupon_usage` entity | Does not exist |
| `field_discount` on Order | Applied discount amount | Field exists, always 0 in current flow |

## Related Docs

- Workflow: [WF-004 Order Management](./WF-004-order-management.md)
- Features: [F-004 Order & Membership](../B03-features/04-order-membership.md)
- Source (as-built scaffold): `web/modules/custom/lms_order/src/Entity/Order.php` (fields `field_coupon_code`, `field_discount`), `web/modules/custom/lms_order/src/Form/CheckoutForm.php`
