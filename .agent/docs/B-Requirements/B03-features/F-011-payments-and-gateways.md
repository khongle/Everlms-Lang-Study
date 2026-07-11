# F-011 - Payments & gateways

**Status:** partial (BankTransfer + Stripe functional; PayPal/ZaloPay stub; **no webhooks** — R-002)
**Primary module:** [lms_payment](../../C-Design/C05-modules/lms_payment.md)
**Epic:** [E06 Commerce](../../D-Development/D02-epics/E06-commerce.md)
**Related workflows:** [WF-004 Order checkout & payment](../B04-business-workflows/WF-004-order-checkout-and-payment.md)
**Acceptance:** [B05 F-011](../B05-acceptance-criteria/F-011-payments-and-gateways.md)
**Source:** `web/modules/custom/lms_payment/`, `lms_stripe/`, `lms_paypal/`

## Requirement
Checkout (F-010) được thanh toán qua một gateway dạng pluggable. Hệ thống cung cấp một plugin host `@PaymentGateway` (`PaymentGatewayManager`) với các gateway: **bank_transfer** (xác nhận thủ công), **stripe** (thẻ qua Stripe SDK), **paypal**, **zalopay**. Gateway được chọn sẽ authorise/capture payment và báo kết quả thành công/thất bại về lại cho order.

## Business value
Nhiều gateway (kể cả ZaloPay của Việt Nam) mở rộng phạm vi thanh toán; plugin host giúp việc bổ sung gateway mới có chi phí thấp.

## Scope notes
- Thực sự hoạt động: `bank_transfer`, `stripe`. Ở dạng stub/chưa hoàn chỉnh: `paypal`, `zalopay`.
- ⚠️ `lms_paypal` bị trùng lặp với PayPal gateway đã có sẵn trong `lms_payment` — module chết/trùng lặp (R-004).
- ⚠️ **Không có webhook endpoint / signature verification** — trạng thái payment thật sự không được reconcile bất đồng bộ (R-002 / BLK-002).
- Secret (gateway key) hiện đang nằm trong config YAML (R-001).

## Cross references
- Design: [C05 lms_payment](../../C-Design/C05-modules/lms_payment.md), [C06 INT-001 Stripe](../../C-Design/C06-apis/INT-001-stripe.md), [C06 INT-002 PayPal](../../C-Design/C06-apis/INT-002-paypal.md), [C06 INT-003 ZaloPay](../../C-Design/C06-apis/INT-003-zalopay.md)
- Risks: [G02 R-001/R-002/R-004](../../G-Governance/G02-risk-register/_index.md)
