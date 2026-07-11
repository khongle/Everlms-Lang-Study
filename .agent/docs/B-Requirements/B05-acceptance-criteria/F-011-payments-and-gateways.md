# F-011 Acceptance Criteria - Payments & gateways

## Core criteria
1. **Given** checkout, **when** buyer chọn một gateway, **then** `PaymentGatewayManager` load plugin `@PaymentGateway` tương ứng (`bank_transfer`/`stripe`/`paypal`/`zalopay`).
2. **Given** gateway Stripe, **when** một thẻ hợp lệ được charge, **then** payment được capture và order được đánh dấu paid.
3. **Given** gateway `bank_transfer`, **when** được chọn, **then** order chờ xác nhận thủ công và được đánh dấu paid khi confirm.
4. **(partial)** **Given** gateway paypal/zalopay, **when** được gọi, **then** chúng được nhận diện nhưng có thể chỉ là stub — đã được ghi nhận trong E06, không fail âm thầm.

## Evidence expected
- Modules: [lms_payment](../../C-Design/C05-modules/lms_payment.md), [lms_stripe](../../C-Design/C05-modules/lms_stripe.md).
- Test: gateway-plugin resolution test; Stripe capture test với fixture ở test-mode; ⚠️ secret nằm trong config (R-001), chưa có webhook (R-002).
