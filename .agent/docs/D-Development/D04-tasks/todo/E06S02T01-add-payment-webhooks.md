---
id: E06S02T01
task_id: E06S02T01
title: Payment webhooks + signature verification
status: todo
story: E06S02
owning_modules: [lms_payment, lms_stripe]
modules: [lms_payment, lms_stripe]
difficulty: 4
risk: R-002
adequacy: NOT_RUN
---

# E06S02T01 - Payment webhooks + signature verification

## Goal
Đối soát (reconcile) trạng thái order/payment một cách bất đồng bộ (asynchronously) từ gateway truth (ưu tiên Stripe), ngăn desync (R-002 / BLK-002, P0).

## Steps
1. Thêm endpoint `POST /api/v1/payment/stripe/webhook` (và paypal/zalopay).
2. Verify chữ ký (signature) của provider; xử lý event một cách idempotent.
3. Cập nhật `lms_order.field_status`/`field_payment_id` khi có webhook event; log + alert khi có mismatch.

## Acceptance / gates
- Chữ ký webhook được validate; replay-safe; order được đối soát khớp với trạng thái gateway.
- G3: test signed-payload + invalid-signature.

## Links
[story](../../D03-stories/E06S02-lms_payment.md) · [INT-001](../../../C-Design/C06-apis/INT-001-stripe.md) · [G02 R-002](../../../G-Governance/G02-risk-register/_index.md)
