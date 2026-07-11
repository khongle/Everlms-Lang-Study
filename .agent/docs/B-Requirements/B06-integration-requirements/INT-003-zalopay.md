# INT-003 - ZaloPay

## Requirement
Cung cấp ví điện tử Việt Nam **ZaloPay** như một tùy chọn checkout qua plugin `@PaymentGateway` `zalopay`, dành cho buyer thị trường nội địa.

## Participating modules
[lms_payment](../../C-Design/C05-modules/lms_payment.md)

## Contract expectations
1. **Auth/authz:** ZaloPay app id / key (hiện nằm trong config YAML — R-001); server-side.
2. **Failure/retry/fallback:** flow thất bại khiến order vẫn ở `pending`; có thể retry/đổi gateway.
3. **CI test fixture/stub:** ZaloPay sandbox credential + client được mock.
4. **Operational owner + alerting:** Commerce owner. ⚠️ **Stub** — gateway được nhận diện nhưng chưa hoàn chỉnh cho production; đã ghi nhận trong [E06](../../E-Quality/E06-implementation-status/_index.md).

## Design link
[C06 INT-003](../../C-Design/C06-apis/INT-003-zalopay.md)
