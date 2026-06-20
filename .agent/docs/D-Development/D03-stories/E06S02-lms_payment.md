---
story_id: E06S02
story_type: module-foundation
primary_source: C05-modules/lms_payment.md
owning_modules: [lms_payment]
adequacy: FAIL
---

# E06S02 - lms_payment build spine

`@PaymentGateway` host + bank_transfer/paypal/zalopay. **Status:** in-progress (webhooks/secrets open).

## Tasks
| Task | Status |
| ---- | ------ |
| Gateway plugin host | resolved |
| bank_transfer gateway | resolved |
| Webhooks + signature verify | todo → [E06S02T01](../D04-tasks/todo/E06S02T01-add-payment-webhooks.md) |
| Secrets → Key/env | todo → [E06S02T02](../D04-tasks/todo/E06S02T02-move-secrets-to-key.md) |

## Links
[C05](../../C-Design/C05-modules/lms_payment.md) · [G02 R-001/R-002](../../G-Governance/G02-risk-register/_index.md)
