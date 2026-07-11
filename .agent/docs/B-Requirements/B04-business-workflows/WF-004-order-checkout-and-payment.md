# WF-004 - Order checkout & payment

## Business flow
Buyer thêm một course/bundle/membership vào order (`lms_order` + `lms_order_item`), có thể áp dụng coupon (WF-009), rồi tiến hành checkout. Họ chọn một payment gateway (`bank_transfer`, `stripe`, `paypal`, `zalopay`); `PaymentGatewayManager` gọi plugin `@PaymentGateway` tương ứng để authorise/capture. Khi thành công, order chuyển sang trạng thái paid/completed và cấp quyền (entitlement) — enrollment (WF-001) và/hoặc membership (WF-005). Khi thất bại, order vẫn ở trạng thái pending và buyer có thể thử lại.

## Participating modules
[lms_order](../../C-Design/C05-modules/lms_order.md), [lms_payment](../../C-Design/C05-modules/lms_payment.md), [lms_stripe](../../C-Design/C05-modules/lms_stripe.md)

## Related features (B03)
[F-010](../B03-features/F-010-orders-and-checkout.md), [F-011](../B03-features/F-011-payments-and-gateways.md), [F-009](../B03-features/F-009-course-bundles.md)

## Trigger
Buyer xác nhận checkout / thanh toán.

## Outcome
Order paid được ghi nhận; entitlement được cấp. ⚠️ Hiện chưa có webhook reconciliation (R-002) — phụ thuộc vào phản hồi đồng bộ (synchronous) từ gateway.

## Design link
[C12 WF-004](../../C-Design/C12-workflows/WF-004-order-checkout-and-payment.md)
