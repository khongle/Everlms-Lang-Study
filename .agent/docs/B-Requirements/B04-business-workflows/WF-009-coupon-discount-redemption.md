# WF-009 - Coupon / discount redemption

## Business flow
Trong quá trình checkout (WF-004), buyer nhập một mã coupon/discount. Order (`lms_order`) sẽ validate mã (khoảng thời gian hiệu lực, phạm vi áp dụng, giới hạn số lần dùng) và áp dụng discount vào tổng order trước khi thanh toán. Mã không hợp lệ/hết hạn sẽ bị từ chối kèm thông báo; mã hợp lệ sẽ giảm số tiền mà gateway capture.

## Participating modules
[lms_order](../../C-Design/C05-modules/lms_order.md), [lms_payment](../../C-Design/C05-modules/lms_payment.md)

## Related features (B03)
[F-010](../B03-features/F-010-orders-and-checkout.md)

## Trigger
Buyer áp dụng mã coupon lúc checkout.

## Outcome
Tổng order được giảm giá (mã hợp lệ) hoặc bị từ chối (mã không hợp lệ). Các field discount được ghi lại trên order để phục vụ báo cáo.

## Design link
[C12 WF-009](../../C-Design/C12-workflows/WF-009-coupon-discount-redemption.md)
