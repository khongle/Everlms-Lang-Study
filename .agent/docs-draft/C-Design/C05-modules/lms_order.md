# lms_order — Order Management Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_order` |
| Name | LMS Order |
| Layer | **Commerce** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base`, `lms_payment` |

## Mô tả

Module quản lý đơn hàng mua khóa học và membership. Cung cấp checkout flow (course checkout + membership checkout), bank transfer instruction, và các form thanh toán. Lưu Order entity qua lifecycle: pending → completed / failed / refunded.

---

## Provided Entities

### `lms_order` (Content Entity)
| Fields | Description |
|--------|-------------|
| uid | Người mua |
| status | Enum: pending/completed/failed/refunded |
| total_amount | Tổng tiền |
| currency | VND/USD |
| gateway | Payment gateway used |
| transaction_id | External transaction ref |
| created/changed | Timestamps |

### `lms_order_item` (Content Entity)
Chi tiết đơn hàng: order ref, item type (course/membership/bundle), item ref ID, quantity, unit_price.

### `lms_coupon` (Content Entity)
Mã giảm giá: code (unique), discount_type (percent/fixed), discount_value, max_uses, used_count, expiry.

---

## Provided Routes

| Route ID | Path | Controller/Form | Access |
|----------|------|----------------|--------|
| `lms_order.checkout` | `/checkout/{course_id}` | `CheckoutController::checkout` | public |
| `lms_order.checkout_membership` | `/checkout/membership/{plan_id}` | `CheckoutController::checkoutMembership` | public |
| `lms_order.bank_transfer` | `/checkout/bank-transfer/{lms_order}` | `CheckoutController::bankTransferInstruction` | `access content` |

---

## Provided Services

Không có services file riêng. Checkout logic nằm trong `CheckoutController`.

---

## Provided Plugins

Không có plugin. Tích hợp gateway thông qua `lms_payment` plugin system.

---

## Hook Implementations

Có `.module` file nhưng nội dung cần xác nhận thêm (không đọc trong session này).

---

## Forms

| Form | Path | Purpose |
|------|------|---------|
| `CheckoutForm` | (inline in controller) | Checkout course |
| `MembershipCheckoutForm` | (inline in controller) | Checkout membership plan |
| `BankTransferForm` | (inline in controller) | Hướng dẫn chuyển khoản |

---

## Templates & Cache Strategy

Checkout pages không cache (user + cart specific). Order pages cần `cache_context: user`.

---

## Related Units

- **C03** § Commerce Domain — entities Order, OrderItem, Coupon
- **C05** `03-progress-commerce.md` — layer overview
- `lms_payment.md` — gateway plugin system
- `lms_membership.md` — membership checkout
- `lms_course.md` — course purchase
- `lms_bundle.md` — bundle purchase

---

## Known Issues / Debt

- `/checkout/{course_id}` dùng raw numeric ID thay vì entity parameter upcasting — không benefit từ access check tự động.
- Coupon validation logic không rõ ràng trong controller — cần audit.
- Order status transition không có explicit state machine — chỉ là free-form field value.
- Không có idempotency key cho payment — double submit risk.
