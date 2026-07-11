---
usecase_id: UC-WF-009-001
workflow: WF-009-coupon-discount-redemption
status: as-built
primary_actor: Student
modules: [lms_order, lms_payment]
processes: [coupon-discount-redemption]
---

# UC-WF-009-001 - Apply a coupon at checkout

## Intent
Một buyer giảm tổng tiền của mình bằng một coupon.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-009 |
| Business flow | enter → validate → apply → persist |
| Modules | lms_order, lms_payment |
| Related features | F-010 |
| B04 source | WF-009 |

## Actors
Buyer (primary); administrator/config maintainer cho định nghĩa coupon.

## Preconditions
Đang ở checkout với một order đang thực hiện.

## Trigger
Buyer nhập một mã coupon.

## Main success scenario
1. Nhập mã tại checkout (P029).
2. Validate (tính hợp lệ/điều kiện/giới hạn).
3. Redirect về lại checkout với coupon đã áp trong query state.
4. Hiện xác nhận coupon inline, subtotal, discount, và tổng đã điều chỉnh.
5. Khi submit checkout, lưu `field_coupon_code`, `field_discount`, và `field_total` đã điều chỉnh.

## Alternate and exception flows
- Không hợp lệ/hết hạn/vượt giới hạn → bị từ chối kèm thông báo.
- Các phương thức thanh toán bên ngoài phải dùng tổng phải trả đã điều chỉnh sau khi áp coupon.
- Định nghĩa coupon as-built hiện tại được điều khiển qua config (`lms_order.settings`); UI coupon cho manager chưa có.

## State transitions (evidence)
- `submitted` → `approved_or_completed`: coupon hợp lệ, discount áp vào tổng order.
- `submitted` → `rejected_or_returned`: mã hết hạn/không đủ điều kiện/vượt giới hạn.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Checkout | App | /checkout/{course} |

## Acceptance and test contract
- Hợp lệ → tổng giảm; không hợp lệ → bị từ chối; discount được ghi lại (B05 F-010).
- Coupon test đã verify: `SILVERCARE10` áp discount 10% cho một course SilverCare trả phí.
