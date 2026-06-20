---
workflow: WF-009
slug: coupon-flow
mode: target-state
b04_source: B04/WF-009-coupon-flow.md
adequacy: NOT_RUN
adequacy_run: null
---

# C12 — Workflow: WF-009 Coupon Creation and Redemption

**Trigger:** Admin creates a coupon (route TBD); Student enters a coupon code during checkout at `/checkout/{course_id}` or `/checkout/membership/{plan_id}`.
**Outcome:** Order `field_discount` populated with calculated discount amount; `field_coupon_code` set on `lms_order`; `field_total` = subtotal − discount; coupon usage counter incremented on order completion.
**B04 source:** [`../../B-Requirements/B04-business-workflows/WF-009-coupon-flow.md`](../../B-Requirements/B04-business-workflows/WF-009-coupon-flow.md)
**Mode:** target-state

> **IMPLEMENTATION STATUS: TARGET-STATE — NOTHING IS IMPLEMENTED BEYOND DATA SCAFFOLDING.**
>
> Verification against the codebase (2026-06-20):
> - `lms_order` entity has `field_coupon_code` (string, max_length=32) and `field_discount` (decimal 10,2, default=0) — these fields exist as **data scaffold only**.
> - `CheckoutForm.php` and `MembershipCheckoutForm.php` do **NOT** set `field_coupon_code` or `field_discount`. The coupon code input field does not exist in either form.
> - No `lms_coupon` entity, no coupon validation service, no coupon CRUD UI, and no coupon usage tracking entity exist in any custom module.
> - `field_discount` is always `0` in all current orders. `field_total` equals `field_subtotal` in all current flows.
>
> Everything below the "As-built scaffold" section is a target-state specification.

---

## Actors

| Actor (C02 role) | Step participation | Permissions needed |
|---|---|---|
| Administrator | Create, edit, deactivate coupons | `administer lms_course` (or dedicated coupon permission — to be defined) |
| Manager | Same as Admin for coupon management | `administer lms_course` |
| Student | Enter coupon code during checkout | Authenticated (`_user_is_logged_in: TRUE`) |
| System | Validate code, calculate discount, record usage, increment counter | — |

## Entry points

| URL (C07) | Page (C11) | Method | Form | Feature flag |
|---|---|---|---|---|
| `/checkout/{course_id}` | checkout | GET/POST | `CheckoutForm` | — |
| `/checkout/membership/{plan_id}` | checkout-membership | GET/POST | `MembershipCheckoutForm` | — |
| Coupon management (TBD) | coupon-list (target-state) | GET | — | Coupon feature implemented |
| Coupon create (TBD) | coupon-add (target-state) | GET/POST | `CouponForm` (target-state) | Coupon feature implemented |

## Entities touched

| Entity (C04) | Operation | Fields written | State transition |
|---|---|---|---|
| [`lms_order`](../C04-entities/) | CREATE / UPDATE | `field_coupon_code` (scaffold, never written), `field_discount` (scaffold, always 0), `field_subtotal`, `field_total` | No state change from coupon |
| `lms_coupon` (target-state, does not exist) | CREATE / READ / UPDATE | `field_code`, `field_discount_type`, `field_discount_value`, `field_max_uses`, `field_usage_count`, `field_valid_from`, `field_valid_until`, `field_applicable_courses`, `status` | `active` → deactivated (admin toggle) |
| `lms_coupon_usage` (target-state, does not exist) | CREATE | `uid`, `coupon_id`, `order_id`, `used_at` | — |

## State transitions

```
AS-BUILT (scaffold only):
  CheckoutForm/MembershipCheckoutForm creates lms_order with:
    field_coupon_code = '' (never set)
    field_discount = 0 (always)
    field_total = field_subtotal (no reduction)

TARGET-STATE (not implemented):
  [Student enters coupon code on checkout form]
    → AJAX validation call to coupon endpoint (TBD)
    → Validation chain runs (7 steps, see below)
    → Discount calculated; checkout total updated in UI

  [Student submits checkout form]
    → lms_order created with field_coupon_code set, field_discount set
    → field_total = field_subtotal - field_discount

  [Order reaches status=completed]
    → lms_coupon.field_usage_count += 1
    → lms_coupon_usage record created (uid, coupon_id, order_id)
```

State guards (who can trigger which transition):

| Transition | Trigger | Guard | Side effects |
|---|---|---|---|
| Create coupon (target-state) | Admin submits CouponForm | `administer lms_course` (or TBD permission) | Coupon active and retrievable |
| Apply coupon code to order (target-state) | Student submits checkout with coupon code | Validation chain passes; student authenticated | `field_discount` set; `field_total` reduced |
| Record coupon usage (target-state) | `hook_entity_update` on `lms_order` when `status → completed` | `field_coupon_code` non-empty | `field_usage_count` increment; `lms_coupon_usage` created |
| Deactivate coupon (target-state) | Admin toggles status | `administer lms_course` | No further redemptions; ongoing incomplete orders with this code should still be honoured (TBD policy) |

## Modules involved (C05)

| Module | Responsibility in this workflow |
|---|---|
| [`lms_order`](../C05-modules/) | `lms_order` entity has `field_coupon_code` and `field_discount` scaffold; `CheckoutForm` and `MembershipCheckoutForm` — to be extended with coupon input + validation call |
| `lms_coupon` (target-state — does not exist) | Coupon entity, CRUD routes, `CouponValidationService`, usage tracking |

## Permissions

| Step | Anonymous | Authenticated (student) | Admin / Manager |
|---|---|---|---|
| Enter coupon code on checkout | — | ✓ (checkout requires login) | ✓ |
| Create / edit / deactivate coupon | — | — | ✓ |
| View coupon list / usage stats | — | — | ✓ |

## Notifications

No notifications defined for this workflow in B04 or code.

## Cache invalidation

| Step | Cache tag(s) | Search index | List view(s) |
|---|---|---|---|
| Coupon created/updated (target-state) | `lms_coupon:{id}` | — | Coupon list page |
| Coupon usage_count incremented (target-state) | `lms_coupon:{id}` | — | Coupon list page |
| Order created with discount applied | `lms_order:{id}` | — | `/workspace/orders` |

## Operations queues

No queue processing in this workflow.

| Page (C11) | Filters | Owner role |
|---|---|---|
| `/workspace/orders` (`AdminController::orderList()`) | search, status | Admin, Manager |
| Coupon management page (target-state) | code, status, date range | Admin, Manager |

## Editorial / management surfaces

| Entity / content source | Create/Edit UI | Role / permission | Revision/moderation state | Translation | Visible C11 surfaces |
|---|---|---|---|---|---|
| `lms_coupon` (target-state) | TBD admin routes | `administer lms_course` | No moderation; `status` boolean | Not translatable | Coupon management page |
| `lms_order` (as-built) | `/workspace/orders/{id}/edit` (`ApproveOrderForm`) | `administer lms_order` | No moderation | Not translatable | `/workspace/orders`, self-service (TBD) |

## Failure modes

| Failure | Detection | Handling |
|---|---|---|
| Coupon code not found (target-state) | Validation endpoint: code not in `lms_coupon` storage | Show inline error "Invalid coupon code" on checkout form |
| Coupon inactive / expired (target-state) | Validation: `status=FALSE` or date out of range | Show "Coupon is no longer active" / "Coupon has expired" |
| Usage limit reached (target-state) | Validation: `field_usage_count >= field_max_uses` (and max_uses > 0) | Show "Coupon usage limit reached" |
| Per-user limit reached (target-state) | Validation: `lms_coupon_usage` query for uid+coupon_id | Show "You have already used this coupon" |
| Minimum order amount not met (target-state) | Validation: `subtotal < field_min_order` | Show "Minimum order [amount] required" |
| Race condition on concurrent checkout (target-state) | Two users apply same limited coupon simultaneously | Implement DB-level row lock or atomic counter on `field_usage_count` increment |

## As-built scaffold (confirmed in code)

The following fields exist on `lms_order` entity as data scaffold. No checkout form writes to them.

| Field on `lms_order` | Type | As-built status |
|---|---|---|
| `field_coupon_code` | string (max_length=32) | Field definition exists; never set by `CheckoutForm` or `MembershipCheckoutForm` |
| `field_discount` | decimal (precision=10, scale=2, default=0) | Field definition exists; always `0` in current checkout flow |
| `field_total` | decimal (precision=10, scale=2) | Field exists and is used; currently equals `field_subtotal` (no discount applied) |

## Planned data model (target-state — none implemented)

### `lms_coupon` entity (to be created)

| Field | Type | Notes |
|---|---|---|
| `field_code` | string | Unique, uppercase alphanumeric; case-insensitive lookup |
| `field_discount_type` | list_string | `percentage` or `fixed` |
| `field_discount_value` | decimal | > 0; ≤ 100 for percentage type |
| `field_max_discount` | decimal | Cap for percentage discounts (e.g. 20% off but max $50) |
| `field_min_order` | decimal | Minimum cart total to activate coupon |
| `field_valid_from` | timestamp | Optional start date |
| `field_valid_until` | timestamp | Optional expiry date |
| `field_max_uses` | integer | 0 = unlimited |
| `field_uses_per_user` | integer | 0 = unlimited |
| `field_usage_count` | integer | Denormalized counter; must be incremented atomically |
| `field_applicable_courses` | entity_reference (lms_course, unlimited) | Empty = applies to all courses |
| `status` | boolean | Active / inactive |

### `lms_coupon_usage` entity (to be created, or DB table)

| Field | Type | Notes |
|---|---|---|
| `uid` | entity_reference (user) | Who used the coupon |
| `field_coupon` | entity_reference (lms_coupon) | Which coupon |
| `field_order` | entity_reference (lms_order) | Linked order |
| `created` | created | When usage was recorded |

### Validation chain (target-state — 7 steps)

```
1. Code exists in lms_coupon storage?
      No  → "Invalid coupon code"
2. coupon.status = TRUE (active)?
      No  → "Coupon is no longer active"
3. Current date within [field_valid_from, field_valid_until] (if set)?
      No  → "Coupon has expired" or "Coupon not yet valid"
4. field_usage_count < field_max_uses (or field_max_uses = 0)?
      No  → "Coupon usage limit reached"
5. Per-user usage < field_uses_per_user (or field_uses_per_user = 0)?
      No  → "You have already used this coupon"
6. Order subtotal >= field_min_order (if set)?
      No  → "Minimum order [amount] required"
7. field_applicable_courses is empty OR at least one cart item matches?
      No  → "Coupon not applicable to selected items"
   → Calculate discount; apply to order
```

## Business rules (target-state)

- **Code format:** Uppercase alphanumeric (A–Z, 0–9) only. Validation at creation time.
- **Discount cap:** For percentage-type coupons, `field_max_discount` caps the absolute discount amount.
- **Usage record timing:** `lms_coupon_usage` created after `lms_order` reaches `completed` status — not at code application time. If payment fails, the coupon is not consumed.
- **No stacking:** Single `field_coupon_code` field on Order — one coupon per order only.
- **Admin deactivation:** Setting coupon `status=FALSE` immediately prevents further redemptions.
- **Concurrent checkout:** `field_usage_count` increment must be atomic (DB row lock or Drupal Queue) to prevent over-redemption.

## Implementation status

| Component | Status | Notes |
|---|---|---|
| `field_coupon_code` on `lms_order` | DONE (data scaffold) | Field exists; never set by checkout forms |
| `field_discount` on `lms_order` | DONE (data scaffold) | Field exists; always 0 in current flow |
| Coupon code input on `CheckoutForm` | NOT IMPLEMENTED | Target-state |
| Coupon code input on `MembershipCheckoutForm` | NOT IMPLEMENTED | Target-state |
| `lms_coupon` entity | NOT IMPLEMENTED | Target-state |
| `CouponValidationService` | NOT IMPLEMENTED | Target-state |
| Coupon CRUD admin UI | NOT IMPLEMENTED | Target-state |
| `lms_coupon_usage` tracking | NOT IMPLEMENTED | Target-state |
| Usage counter increment on order completion | NOT IMPLEMENTED | Target-state |
| Concurrent checkout protection | NOT IMPLEMENTED | Target-state |

## Acceptance (B05)

- B05 acceptance criteria not yet materialized.

## Related

- Modules: [`lms_order`](../C05-modules/)
- Entities: [`C04-entities/`](../C04-entities/)
- Roles: [`C02-user-roles.md`](../C02-user-roles.md)
- B04 source: [`../../B-Requirements/B04-business-workflows/WF-009-coupon-flow.md`](../../B-Requirements/B04-business-workflows/WF-009-coupon-flow.md)
- Related workflow: [`../../B-Requirements/B04-business-workflows/WF-004-order-management.md`](../../B-Requirements/B04-business-workflows/WF-004-order-management.md)
