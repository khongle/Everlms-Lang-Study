# C12 Workflow - WF-009 Coupon / discount redemption

**Object:** order

## Business source
[B04 WF-009](../../B-Requirements/B04-business-workflows/WF-009-coupon-discount-redemption.md)

## Modules
[lms_order](../C05-modules/lms_order.md), [lms_payment](../C05-modules/lms_payment.md)

## Related features (B03)
[F-010](../../B-Requirements/B03-features/F-010-orders-and-checkout.md)

## State model
việc áp coupon lên `lms_order` (bước con của WF-004):
| State | Meaning |
| ----- | ------- |
| submitted (applied) | code entered + validated |
| approved_or_completed (accepted) | discount applied to `field_discount`/total |
| rejected_or_returned (invalid) | expired/ineligible/over-limit → rejected |

## Transitions
| From | To | Trigger |
| ---- | -- | ------- |
| submitted | approved_or_completed | coupon hợp lệ, discount áp vào tổng order |
| submitted | rejected_or_returned | mã hết hạn/không đủ điều kiện/vượt giới hạn |

## Permission model
- Initiator: buyer lúc checkout.
- Admin: định nghĩa coupon hiện đang được quản lý qua Drupal configuration (`lms_order.settings`). Một UI quản trị coupon dành cho manager là product gap, chưa phải trang as-built.

## Round-2 QA evidence
Đã verify hoạt động tốt lúc checkout (`julia.nowak`, course 124 $85): **SILVERCARE10** (10%) → −$8.50, tổng $76.50, thông báo "applied" màu xanh + coupon badge + dòng Discount trong Order Summary; **WELCOME20** ($20 cố định, tối thiểu $50) → −$20, tổng $65.00; **mã không hợp lệ** → bị từ chối; **WELCOME20 trên course $45** → bị từ chối (dưới mức tối thiểu). Coupon vẫn được định nghĩa qua config trong `lms_order.settings` (UI quản trị coupon cho manager vẫn còn là product gap). Screens: `.agent/docs/E-Quality/E00-workflows/round-2/_screens/009-coupon-discount-redemption/`.

## Notifications
Thông báo validation inline lúc checkout; discount được ghi vào `lms_order.field_coupon_code`, `field_discount`, và `field_total` được điều chỉnh để phục vụ báo cáo.
