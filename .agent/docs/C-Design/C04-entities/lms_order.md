# C04 Entity - lms_order

**Display name:** Order
**Owning module(s):** [lms_order](../C05-modules/lms_order.md)
**Mode:** as-built (Content Entity) — custom commerce (no Drupal Commerce)

## Purpose
Order header: buyer, tổng tiền, discount/coupon, payment method/kết quả, status, và các line item. Bản ghi doanh thu kết nối catalog và quyền truy cập.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | buyer |
| field_order_number | human order number |
| field_subtotal, field_discount, field_total, field_currency | money fields |
| field_status | pending / paid / completed / cancelled |
| field_payment_method | gateway id (bank_transfer/stripe/paypal/zalopay) |
| field_payment_id | gateway transaction id |
| field_coupon_code | applied coupon (WF-009) |
| field_payment_receipt | uploaded receipt (bank transfer) |
| field_reminder_sent | dunning flag |
| created, changed | timestamps |

## Relationships
- 1→N `lms_order_item` (`field_items`); N→1 `user`. Khi paid/completed → enrollment (`lms_user_course`) / membership (`lms_user_membership`).

## Validation rules
Nguồn: `web/modules/custom/lms_order/src/Entity/Order.php`.
- `field_order_number`: bắt buộc, tối đa 32 ký tự.
- `field_currency`: mặc định `USD`, tối đa 3 ký tự (mã ISO), không có `allowed_values` giới hạn danh sách tiền tệ hợp lệ trong code (gap — có thể lưu bất kỳ chuỗi ≤3 ký tự).
- `field_status`: `list_string`, giá trị hợp lệ `pending|processing|completed|cancelled|refunded`, mặc định `pending`.
- `field_subtotal`, `field_discount`, `field_total`: decimal(10,2), mặc định 0.

## Data invariants
- Về mặt nghiệp vụ `field_total` nên bằng `field_subtotal - field_discount`, nhưng quan hệ này **không được enforce ở entity level** (không có `addConstraint`/validate) — chỉ đúng nếu code tạo order tính đúng (xem Calculations bên dưới); nếu một field bị sửa tay sau đó (qua UI quản trị), `field_total` có thể lệch khỏi công thức mà không bị cảnh báo.
- `field_order_number` được sinh dạng `'ORD-' . time() . '-' . rand(100, 999)` (xem Calculations) — **không có ràng buộc unique ở DB**, và vì dùng `rand()` trong cùng giây có xác suất (dù nhỏ) trùng số — gap as-built về tính duy nhất tuyệt đối của order number.

## Calculations & derived values
- Xác nhận trong `web/modules/custom/lms_order/src/Form/CheckoutForm.php::submitForm()`:
  - `field_order_number = 'ORD-' . time() . '-' . rand(100, 999)`.
  - `field_subtotal = $price` (giá gốc course/plan tại thời điểm checkout).
  - `field_discount` = số tiền giảm từ coupon hợp lệ (0 nếu không có coupon).
  - `field_total` = giá đã áp dụng coupon (`$coupon_result['total']`) nếu coupon hợp lệ, ngược lại bằng `$price` (tức bằng `field_subtotal` khi không coupon).
- Không có webhook reconciliation (R-002, đã ghi ở Editorial/data management) — `field_status` được set đồng bộ ngay trong request, không có cơ chế đối soát lại nếu gateway callback đến trễ/thất bại.

## Editorial/data management
Được tạo tại checkout; manager xem/quản lý trong order list (management UI). ⚠️ Không có webhook reconciliation (R-002) — status được đặt dựa trên phản hồi đồng bộ (synchronous) từ gateway. ~20 order thực tế. Xem [F-010](../../B-Requirements/B03-features/F-010-orders-and-checkout.md), [WF-004](../C12-workflows/WF-004-order-checkout-and-payment.md).
