# INT-001 - Stripe

## Requirement
Thu card payment tại checkout (F-011/WF-004) qua Stripe PHP SDK, đứng sau plugin `@PaymentGateway` `stripe` (`lms_stripe`). Việc tạo charge/intent diễn ra server-side; không có dữ liệu thẻ nào chạm vào server của EverLMS.

## Participating modules
[lms_stripe](../../C-Design/C05-modules/lms_stripe.md), [lms_payment](../../C-Design/C05-modules/lms_payment.md)

## Contract expectations
1. **Auth/authz:** Stripe secret/publishable key; ⚠️ hiện đang lưu trong config YAML (R-001) — cần chuyển sang Key/env. API call chỉ diễn ra server-side.
2. **Failure/retry/fallback:** khi thẻ bị từ chối / API lỗi, order vẫn ở trạng thái `pending`, buyer thấy một lỗi rõ ràng và có thể retry hoặc chọn gateway khác; không entitlement nào được cấp khi thất bại.
3. **CI test fixture/stub:** Stripe test-mode key + một Stripe client được mock cho kernel/unit test; không bao giờ gọi Stripe thật trong CI.
4. **Operational owner + alerting:** Commerce owner; ⚠️ **hiện chưa có webhook endpoint (R-002)** → không có async reconciliation/alerting cho trạng thái charge — gap ưu tiên cao.

## Design link
[C06 INT-001](../../C-Design/C06-apis/INT-001-stripe.md)
