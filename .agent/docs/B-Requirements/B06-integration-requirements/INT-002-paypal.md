# INT-002 - PayPal

## Requirement
Cung cấp PayPal như một tùy chọn checkout qua plugin `@PaymentGateway` `paypal`. Redirect/checkout flow sang PayPal rồi quay lại để confirm order.

## Participating modules
[lms_payment](../../C-Design/C05-modules/lms_payment.md), [lms_paypal](../../C-Design/C05-modules/lms_paypal.md)

## Contract expectations
1. **Auth/authz:** PayPal client id/secret (hiện nằm trong config YAML — R-001); call diễn ra server-side.
2. **Failure/retry/fallback:** PayPal flow bị hủy/thất bại khiến order vẫn ở `pending`; buyer có thể retry hoặc đổi gateway.
3. **CI test fixture/stub:** PayPal sandbox credential + client được mock; không gọi live trong CI.
4. **Operational owner + alerting:** Commerce owner. ⚠️ **Code duplication (R-004):** một PayPal gateway tồn tại ở CẢ `lms_paypal` lẫn `lms_payment` — `lms_paypal` là dead/duplicate và nên bị loại bỏ; gateway hiện chỉ là stub/partial.

## Design link
[C06 INT-002](../../C-Design/C06-apis/INT-002-paypal.md)
