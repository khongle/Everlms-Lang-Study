---
page: Checkout (membership)
drupal_concept: Form API
mode: as-built
owner_module: lms_membership
route_group: app
route_name: lms_membership.checkout
prototype_type: form
adequacy: PASS
---

# C11 Page - Checkout (membership)

## Purpose
Đăng ký một gói thuê bao tại `/checkout/membership/{plan_id}` (form): xem lại gói, thanh toán, tạo `lms_user_membership`.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/checkout/membership/{plan_id}` |
| Route name | `lms_membership.checkout` |
| Primitive | Form API (order + payment) |
| Access | authenticated |
| Cache | uncacheable |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Plan + price | `lms_membership_plan` | — | plan tag | pay → membership active |

## Page States
Normal: summary + pay · Empty: invalid plan → 404 · Access denied: anon → login · Validation error: declined → retry · Loading: gateway.

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Primary CTA (submit) | "Subscribe — $24.00 today" | the module.checkout form — nút submit chính (owner module: the module) |
| Coupon apply | "Apply" | the module.checkout form — nút action bên cạnh field coupon code |
| Coupon success | "Coupon WELCOME5 applied" / "You saved $5.00 on your first month." | the module.checkout — response khi apply coupon (alert--success) |
| Plan renewal terms | "Then $29.00/mo, cancel anytime." | tính từ the module.field price + chu kỳ billing → template order summary |
| Validation error — card number | "Card number is invalid." (suy luận từ field `cardnum`, prototype tĩnh chưa render state lỗi) | the module gateway client-side validation (không phải C04 entity validation) |
| Validation error — payment declined | "Your card was declined. Please try another payment method." (khớp Page States: Validation error: declined → retry) | the module gateway response → banner lỗi trên form checkout |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile | ở ≤575.98px: `.container` co về 100% width; `.detail` đã chuyển 1 cột (kích hoạt từ breakpoint ≤991.98px) nên panel "Contact" + "Payment method" nằm trên, `aside.panel` "Order summary" nằm dưới theo đúng thứ tự DOM; `.field--row` (Expiry date / CVC) chuyển từ 2 cột sang 1 cột dọc; toggle Monthly/Annual vẫn giữ dạng pill ngang. |
| tablet | 768–991px (áp dụng tới hết 991.98px): `.detail{grid-template-columns:1fr}` nên vẫn xếp 1 cột dọc như mobile (form thanh toán trên, "Order summary" dưới); `.field--row` đã trở lại 2 cột (Expiry date / CVC) vì breakpoint riêng của nó chỉ kích hoạt ở ≤575.98px. |
| desktop | 992–1199px: `.detail{grid-template-columns:minmax(0,1fr) 380px}` khôi phục 2 cột — cột trái là form thanh toán (panel "Contact" + panel "Payment method"), cột phải là `aside.panel` "Order summary" rộng cố định 380px, `align-items:start` nên không kéo giãn theo chiều cao form. |
| wide | ≥1200px: `.container` mở rộng theo `--container-xl` rồi `--container-xxl` (≥1400px); layout `.detail` giữ nguyên 2 cột (form trái, order summary phải 380px), chỉ tăng khoảng lề hai bên và gap giữa 2 cột theo `clamp(1.8rem,4vw,3rem)`. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Membership checkout | done | same gateways (R-002 caveat) |
