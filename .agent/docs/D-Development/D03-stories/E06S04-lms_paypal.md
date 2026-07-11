---
story_id: E06S04
story_type: module-foundation
primary_source: C05-modules/lms_paypal.md
owning_modules: [lms_paypal]
adequacy: FAIL
---

# E06S04 - lms_paypal build spine

Gateway PayPal. **Status:** blocked — trùng lặp với PayPal của lms_payment (R-004); cần xóa.

## Tasks
| Task | Status |
| ---- | ------ |
| Consolidate/remove duplicate | blocked → [E06S04T01](../D04-tasks/todo/E06S04T01-dedupe-paypal-gateway.md) |

## Links
[C05](../../C-Design/C05-modules/lms_paypal.md) · [G02 R-004](../../G-Governance/G02-risk-register/_index.md)

## Source Trace
- **Module:** `lms_paypal` — [C05](../../C-Design/C05-modules/lms_paypal.md) · [D07](../D07-code-map/lms_paypal.md)
- **Entity:** n/a — `@PaymentGateway` plugin (trùng lặp với PayPal gateway đã có trong `lms_payment`)
- **Feature:** thuộc [F-010](../../B-Requirements/B03-features/F-010-orders-and-checkout.md) orders-and-checkout
- **Workflow:** [WF-004](../../C-Design/C12-workflows/WF-004-order-checkout-and-payment.md) order-checkout-and-payment

## WBS Tasks
| Task ID | Mô tả | Trạng thái |
| ---- | ----- | ---------- |
| E06S04T01 | Consolidate/remove duplicate PayPal gateway | blocked → [task file](../D04-tasks/todo/E06S04T01-dedupe-paypal-gateway.md) |

## Adequacy Checklist
- [ ] Entity: n/a — dead code, cần dedup trước khi đánh giá tiếp
- [x] Route: route trùng với PayPal của `lms_payment` (nguồn gây nhầm lẫn, R-004/R-008)
- [ ] Access/permission: n/a cho tới khi dedup xong
- [ ] Test: chưa có automated test coverage riêng — gap chung R-006/X01
