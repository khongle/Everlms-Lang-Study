# Commerce Entities

> Order management + Membership subscription system.

---

## Order (`lms_order`)

| Field | Type | Mô tả |
|-------|------|-------|
| `field_customer` | entity_reference (user) | Khách hàng |
| `field_items` | entity_reference (multi) | Order items |
| `field_total` | decimal | Tổng tiền |
| `field_subtotal` | decimal | Tiền trước discount |
| `field_discount` | decimal | Số tiền giảm |
| `field_currency` | string | Đơn vị tiền tệ (USD, VND...) |
| `field_status` | list_string | `pending` / `paid` / `completed` / `refunded` / `cancelled` |
| `field_payment_method` | string | `stripe` / `paypal` |
| `field_payment_id` | string | Transaction ID từ gateway |
| `field_coupon` | entity_reference | Coupon đã áp dụng |
| `field_notes` | text_long | Ghi chú admin |
| `field_invoice_pdf` | file | Invoice PDF |

### Status Flow
```
pending → paid → completed
pending → cancelled
paid → refunded
```

### Business Rules
- Khi `status = paid` → auto enroll student vào courses
- Khi `status = refunded` → revoke enrollment
- Invoice PDF auto-generated khi `status = paid`

---

## Order Item (`lms_order_item`)

| Field | Type | Mô tả |
|-------|------|-------|
| `field_order` | entity_reference | Parent order |
| `field_course` | entity_reference | Course |
| `field_plan` | entity_reference | Membership Plan (if subscription) |
| `field_price` | decimal | Giá tại thời điểm mua |
| `field_quantity` | integer | Số lượng |

---

## Coupon (`lms_coupon`)

| Field | Type | Mô tả |
|-------|------|-------|
| `name` | string | Coupon code |
| `field_type` | list_string | `percentage` / `fixed` |
| `field_value` | decimal | Giá trị (% hoặc fixed amount) |
| `field_max_uses` | integer | Giới hạn sử dụng (0 = unlimited) |
| `field_used_count` | integer | Số lần đã dùng |
| `field_start_date` | timestamp | Ngày bắt đầu hiệu lực |
| `field_end_date` | timestamp | Ngày hết hạn |
| `field_courses` | entity_reference (multi) | Áp dụng cho courses cụ thể (empty = all) |
| `status` | boolean | Active / Inactive |

---

## Membership Plan (`lms_membership_plan`)

| Field | Type | Mô tả |
|-------|------|-------|
| `name` | string | Tên plan |
| `field_description` | text_long | Mô tả |
| `field_price` | decimal | Giá |
| `field_duration` | list_string | `monthly` / `yearly` / `lifetime` |
| `field_courses` | entity_reference (multi) | Courses trong plan (empty = all courses) |
| `field_trial_days` | integer | Số ngày dùng thử |
| `field_features` | text_long | Feature list (for pricing page display) |
| `field_tier` | list_string | `basic` / `premium` / `enterprise` |
| `weight` | integer | Thứ tự hiển thị |
| `status` | boolean | Active / Inactive |

---

## User Membership (`lms_user_membership`)

| Field | Type | Mô tả |
|-------|------|-------|
| `field_user` | entity_reference (user) | User |
| `field_plan` | entity_reference | Membership plan |
| `field_status` | list_string | `trial` / `active` / `expired` / `cancelled` |
| `field_start_date` | timestamp | Ngày bắt đầu |
| `field_end_date` | timestamp | Ngày hết hạn |
| `field_auto_renew` | boolean | Tự động gia hạn |
| `field_subscription_id` | string | Stripe/PayPal subscription ID |

### Business Rules
- `status = active` → user được access tất cả courses trong plan
- 7 ngày trước `field_end_date` → send expiry reminder
- `auto_renew = true` → auto charge tại `field_end_date`
- Payment fail → 3-day grace period → expire

---

## Wishlist (`lms_wishlist`)

| Field | Type | Mô tả |
|-------|------|-------|
| `field_user` | entity_reference (user) | User |
| `field_course` | entity_reference | Course đã save |
| `created` | timestamp | Thời điểm thêm |

### Business Rules
- Unique constraint: `(field_user, field_course)` — 1 course per user
- Toggle: add/remove qua AJAX button ❤️
- Notify user khi wishlist course giảm giá

---

## Course Bundle (`lms_bundle`)

| Field | Type | Mô tả |
|-------|------|-------|
| `name` | string | Tên bundle |
| `field_description` | text_long | Mô tả |
| `field_thumbnail` | image | Ảnh đại diện |
| `field_courses` | entity_reference (multi) | Courses trong bundle |
| `field_price` | decimal | Giá bundle (< tổng giá courses) |
| `field_original_price` | decimal | Tổng giá gốc (auto-calculated) |
| `field_savings_percent` | integer | % tiết kiệm (auto-calculated) |
| `field_featured` | boolean | Hiển thị trên homepage |
| `weight` | integer | Thứ tự |
| `status` | boolean | Active / Inactive |

### Business Rules
- `field_original_price` = SUM giá các courses
- `field_savings_percent` = ROUND((1 - price/original_price) * 100)
- Mua bundle → auto-create enrollment cho tất cả courses
- Bundle phải có ≥ 2 courses

