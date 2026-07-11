---
story_id: E06S03
story_type: module-foundation
primary_source: C05-modules/lms_stripe.md
owning_modules: [lms_stripe]
adequacy: PASS
---

# E06S03 - lms_stripe build spine

Stripe `@PaymentGateway` (SDK). **Status:** done (khoảng trống webhook dùng chung với E06S02).

## Tasks
| Task | Status |
| ---- | ------ |
| Stripe gateway capture | resolved |
| Stripe webhook | todo (E06S02T01) |

## Links
[C05](../../C-Design/C05-modules/lms_stripe.md) · [C06 INT-001](../../C-Design/C06-apis/INT-001-stripe.md)

## Source Trace
- **Module:** `lms_stripe` — [C05](../../C-Design/C05-modules/lms_stripe.md) · [D07](../D07-code-map/lms_stripe.md)
- **Entity:** n/a — `@PaymentGateway` plugin (SDK Stripe), dữ liệu giao dịch lưu trên `lms_order`
- **Feature:** thuộc [F-010](../../B-Requirements/B03-features/F-010-orders-and-checkout.md) orders-and-checkout
- **Workflow:** [C06 INT-001](../../C-Design/C06-apis/INT-001-stripe.md) tích hợp Stripe

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E06S03T01` Stripe gateway capture | resolved |
| `E06S02T01` (shared) Stripe webhook | todo — dùng chung task webhook của lms_payment |

## Adequacy Checklist
- [x] Entity: n/a
- [x] Route: capture/checkout route đã resolved
- [ ] Access/permission: webhook signature verify chưa có, dùng chung gap R-002 với lms_payment
- [ ] Test: chưa có automated test coverage riêng — gap chung R-006/X01
