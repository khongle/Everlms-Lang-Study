# WF-009 — Coupon Creation & Redemption

**ID:** WF-009
**Trigger:** Admin tạo coupon (creation); Student nhập code ở checkout (redemption)
**Actors:** Admin, Student, System
**Preconditions (redemption):** Student có cart với items. Coupon code exists.
**Related Features:** F-004 (Commerce & Orders)
**Related Entities:** Coupon, CouponUsage, Order, OrderItem

---

## Happy Path — Coupon Creation (Admin)

| Step | Actor | Action | System Response |
|------|-------|--------|----------------|
| 1 | Admin | Vào coupon management, click Create | Hiển thị coupon form |
| 2 | Admin | Nhập code (hoặc auto-generate) | Validate uniqueness |
| 3 | Admin | Set discount type: percentage hoặc fixed | Conditional fields hiện |
| 4 | Admin | Set conditions: dates, limits, applicable courses | Validation rules |
| 5 | Admin | Save | Coupon active, ready for use |

## Happy Path — Coupon Redemption (Student)

| Step | Actor | Action | System Response |
|------|-------|--------|----------------|
| 1 | Student | Ở checkout, nhập coupon code | Trigger real-time validation |
| 2 | System | Validate code | Pass all checks (see validation chain) |
| 3 | System | Apply discount | Cập nhật order total |
| 4 | Student | Proceed to payment | Order với discounted total |
| 5 | System | Record coupon usage | Increment `usage_count`, tạo CouponUsage record |

## Validation Chain (Redemption)

```
Code exists?
  ↓ No → "Invalid code"
Still active?
  ↓ No → "Coupon expired"
Within date range?
  ↓ No → "Coupon expired"
Usage limit reached?
  ↓ Yes → "Coupon used up"
User already used max?
  ↓ Yes → "Already used by this account"
Min order amount met?
  ↓ No → "Minimum order $X required"
Applicable to cart items?
  ↓ No → "Not applicable to selected items"
✅ Apply discount
```

## Error Paths

| Condition | Message to Student |
|-----------|-------------------|
| Code not found | "Mã giảm giá không hợp lệ" |
| Expired date | "Mã giảm giá đã hết hạn" |
| Usage limit reached | "Mã giảm giá đã được dùng hết" |
| Per-user limit reached | "Bạn đã sử dụng mã này rồi" |
| Min order not met | "Đơn hàng tối thiểu $X" |
| Not applicable | "Mã không áp dụng cho khóa học này" |

## Coupon Entity Fields

| Field | Validation |
|-------|-----------|
| `field_code` | Unique, uppercase, alphanumeric |
| `field_discount_type` | `percentage` or `fixed` |
| `field_discount_value` | > 0; ≤ 100 cho percentage |
| `field_max_discount` | Cap for percentage (nullable) |
| `field_min_order` | Min cart total (nullable) |
| `field_valid_from` | Start date (nullable) |
| `field_valid_until` | Expiry date (nullable) |
| `field_max_uses` | Total uses allowed; 0 = unlimited |
| `field_uses_per_user` | Per-user limit; 0 = unlimited |
| `field_applicable_courses` | Course refs; empty = all courses |

## Business Rules

- Coupon code: luôn uppercase, chỉ alphanumeric (A-Z, 0-9).
- Discount cap: `field_max_discount` giới hạn số tiền giảm tối đa cho percentage coupon.
- CouponUsage record tạo sau khi order completed (không phải khi apply).
- Concurrent checkout: lock khi check và increment usage_count để tránh over-redemption.
- Admin có thể deactivate coupon bất kỳ lúc nào.

## Postconditions

- Order total reflect discount.
- CouponUsage record sau order completed.
- `usage_count` incremented chính xác.

## Related Docs

- [WF-004 — Order Management](./WF-004-order-management.md)
- B03 Feature: [F-004 Order & Membership](../B03-features/04-order-membership.md)
