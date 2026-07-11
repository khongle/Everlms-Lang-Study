---
page: Checkout (course)
drupal_concept: Form API + controller
mode: as-built
owner_module: lms_order
route_group: app
route_name: lms_order.checkout
prototype_type: form
adequacy: PASS
---

# C11 Page - Checkout (course)

## Purpose
Mua một khoá học tại `/checkout/{course_id}`: xem lại mục hàng, áp mã giảm giá, chọn cổng thanh toán, thanh toán (F-010/F-011/WF-004).

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/checkout/{course_id}` (bank: `/checkout/bank-transfer/{lms_order}`) |
| Route name | `lms_order.checkout` |
| Drupal page primitive | Form API (order + payment) |
| Owner module | lms_order + lms_payment |
| Access | authenticated |
| Cache | uncacheable (per-order) |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| enroll_cta (reused) | Order summary | sidebar_second | totals |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Summary | item, subtotal, coupon, total |
| Coupon | apply/validate (WF-009) |
| Gateway | bank_transfer / stripe / paypal / zalopay |
| Submit | create order → invoke gateway → on success grant enrollment |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Item + price | `lms_course.field_price` | — | course tag | price change → reflects |
| Totals | computed on `lms_order` | own | per-order | apply coupon → total drops |
| Gateways | enabled gateways (config) | administrator | config | enable gateway → option |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| None | prices/labels from entity/config | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | summary + payment form |
| Empty | invalid course → 404 |
| Access denied | anonymous → login |
| Validation error | invalid coupon → message; declined → retry |
| Loading | gateway redirect/processing |

## Message copy inventory
| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| CTA (submit payment) | "Pay $79.99" | Form API submit button label; `$79.99` = dynamic total from the module computed total |
| Coupon applied (Success) | "Coupon SAVE10 applied" | the module coupon apply confirmation (WF-009) — module string; `SAVE10` = dynamic coupon code |
| Coupon applied detail (Success) | "You saved an extra $10.00 on this order." | the module coupon discount summary — module string; `$10.00` = dynamic discount amount |
| Coupon invalid/expired (Validation error) | "Coupon code is invalid or expired." | the module coupon validation (WF-009) / C04 the module.md ## Validation rules — prototype chỉ demo trạng thái success, chuỗi lỗi suy luận từ field `coupon` bắt buộc hợp lệ |
| Card number invalid (Validation error) | "Card number is invalid." | the module gateway (Stripe/card) client-side validation cho field `cardnum` — chưa có mẫu tĩnh trong prototype, suy luận từ field số thẻ bắt buộc |
| Payment declined (Payment error) | "Your card was declined. Please try again or use a different payment method." | the module gateway response mapping — tương ứng dòng "declined → retry" ở Page States, chưa có mẫu tĩnh trong prototype |

## Responsive behavior
| Breakpoint | Behavior |
| --- | --- |
| mobile | `.detail` (wrapper 2 cột) đã gộp về 1 cột từ breakpoint `@media(max-width:991.98px)`; panel thanh toán (Contact + Payment method) hiển thị trước, `aside.panel` Order summary xếp ngay bên dưới theo đúng thứ tự DOM (không có CSS `order` đảo vị trí). Trong panel Payment method, `.field--row` (Expiry date / CVC) cũng gộp về 1 cột do `@media(max-width:575.98px){.field--row{grid-template-columns:1fr}}`. Container co về 100% width. |
| tablet | Vẫn nằm trong vùng `@media(max-width:991.98px){.detail{grid-template-columns:1fr}}` nên `.detail` vẫn 1 cột, Order summary vẫn nằm dưới form thanh toán. Khác mobile: `.field--row` (Expiry date / CVC) trở lại 2 cột vì rộng hơn 575.98px. Container dùng `--container-md`. |
| desktop | Vượt breakpoint 991.98px nên `.detail` chuyển sang `grid-template-columns:minmax(0,1fr) 380px` — 2 cột đặt cạnh nhau: panel thanh toán (cột co giãn) bên trái, `aside.panel` Order summary cột cố định 380px bên phải. `aside.panel` không có class `.enroll`/`position:sticky` nên không dính (sticky) khi cuộn trang. Container dùng `--container-lg`. |
| wide | Layout `.detail` giữ nguyên 2 cột `minmax(0,1fr) 380px`, chỉ khoảng cách giữa 2 cột giãn thêm theo `clamp(1.8rem,4vw,3rem)`. Container mở rộng tới `--container-xl` (1200–1399.98px) hoặc kích thước container mặc định ở ≥1400px. |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Checkout + coupon + gateways | done (partial gateways) | ⚠️ no webhook (R-002); paypal/zalopay stub |
