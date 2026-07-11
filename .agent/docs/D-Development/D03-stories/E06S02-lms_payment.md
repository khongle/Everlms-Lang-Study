---
story_id: E06S02
story_type: module-foundation
primary_source: C05-modules/lms_payment.md
owning_modules: [lms_payment]
adequacy: FAIL
---

# E06S02 - lms_payment build spine

Host `@PaymentGateway` + bank_transfer/paypal/zalopay. **Status:** in-progress (webhooks/secrets còn mở).

## Tasks
| Task | Status |
| ---- | ------ |
| Gateway plugin host | resolved |
| bank_transfer gateway | resolved |
| Webhooks + signature verify | todo → [E06S02T01](../D04-tasks/todo/E06S02T01-add-payment-webhooks.md) |
| Secrets → Key/env | todo → [E06S02T02](../D04-tasks/todo/E06S02T02-move-secrets-to-key.md) |

## Links
[C05](../../C-Design/C05-modules/lms_payment.md) · [G02 R-001/R-002](../../G-Governance/G02-risk-register/_index.md)

## Source Trace
- **Module:** `lms_payment` — [C05](../../C-Design/C05-modules/lms_payment.md) · [D07](../D07-code-map/lms_payment.md)
- **Entity:** n/a — host `@PaymentGateway` plugin, dữ liệu giao dịch lưu trên `lms_order`/`lms_order_item`
- **Feature:** thuộc [F-010](../../B-Requirements/B03-features/F-010-orders-and-checkout.md) orders-and-checkout
- **Workflow:** [WF-004](../../C-Design/C12-workflows/WF-004-order-checkout-and-payment.md) order-checkout-and-payment

## WBS Tasks
| Task ID | Mô tả | Trạng thái |
| ---- | ----- | ---------- |
| `E06S02T03` | Gateway plugin host | resolved (as-built, không có D04 card riêng) |
| `E06S02T04` | bank_transfer gateway | resolved (as-built, không có D04 card riêng) |
| E06S02T01 | Webhooks + signature verify | todo → [task file](../D04-tasks/todo/E06S02T01-add-payment-webhooks.md) |
| E06S02T02 | Secrets → Key/env | todo → [task file](../D04-tasks/todo/E06S02T02-move-secrets-to-key.md) |

## Adequacy Checklist
- [x] Entity: n/a (plugin host, không có entity riêng)
- [x] Route: bank_transfer checkout route đã resolved
- [ ] Access/permission: webhook signature verify chưa có → gateway truth có thể desync (R-002)
- [ ] Test: secrets vẫn ở config YAML, chưa dùng Key module (R-001); chưa có automated test — R-006/X01
