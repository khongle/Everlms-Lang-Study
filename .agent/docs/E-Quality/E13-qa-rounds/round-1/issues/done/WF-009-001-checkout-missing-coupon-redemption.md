---
id: WF-009-001
workflow: WF-009
title: Checkout has no coupon redemption control or validation
severity: critical
category: functionality
status: done
roles: student
source: C12/C13/C16
found: 2026-06-30
---

# WF-009-001 - Checkout has no coupon redemption control or validation

## Problem
C12, C13, B04, C11, and C16 all describe coupon redemption as part of checkout, but the live checkout form only renders payment methods and submit. There is no coupon code field, no validation feedback, and completed orders do not record `field_coupon_code`/`field_discount`.

## Expected
At checkout the buyer can enter a coupon code. A valid code reduces the displayed and persisted total, while an invalid code is rejected with a clear message before payment/order creation.

## Evidence
- `001-student-checkout-before-coupon.png` showed the checkout form before coupon input.
- `002-student-coupon-after-fill.png` showed the filled coupon code.
- First retest exposed a second blocker: Stripe Elements intercepted the Apply coupon submit and showed "Your card number is incomplete" instead of allowing the Drupal coupon submit handler to run.

## Fix Plan
- Add a Drupal service for config-driven coupon validation.
- Add Form API coupon field and validation to course and membership checkout forms.
- Persist discount/code on `lms_order`.
- Update checkout template to show subtotal, discount, and total.
- Seed demo coupon config for the running QA site.
- Scope Stripe Elements submit interception to the primary `Complete Purchase` submit button only.

## Verification
- Passed retest with real student `Maria Santos` (uid 61).
- Screenshots captured in `.agent/docs/E-Quality/E00-workflows/round-1/_screens/009-coupon-discount-redemption/001..007-*.png`.
- Latest verified order: `142`.
- DB verification: subtotal `50.00`, discount `5.00`, total `45.00`, coupon `SILVERCARE10`, payment method `bank_transfer`.
- PHP syntax checks passed for checkout controller/forms and coupon service.
