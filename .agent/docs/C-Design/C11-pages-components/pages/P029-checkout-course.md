---
page: Checkout (course)
drupal_concept: Form API + controller
mode: as-built
owner_module: lms_order
route_group: App
route_name: lms_order.checkout
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Checkout (course)

## Purpose
Buy a course at `/checkout/{course_id}`: review item, apply coupon, pick gateway, pay (F-010/F-011/WF-004).

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

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Checkout + coupon + gateways | done (partial gateways) | ⚠️ no webhook (R-002); paypal/zalopay stub |
