---
id: WF-009-002
workflow: WF-009
title: Coupon checkout feedback is visually inconsistent and low contrast
severity: medium
category: ui
status: done
roles: student
source: visual QA
found: 2026-06-30
---

# WF-009-002 - Coupon checkout feedback is visually inconsistent and low contrast

## Problem
After applying a coupon, checkout shows a duplicate top-page text message ("Coupon SILVERCARE10 applied.") with no alert styling, while the coupon panel remains dark on an otherwise light checkout page. The coupon helper text is low contrast and the panel feels visually detached from the payment method cards.

## Expected
Coupon feedback should appear inline in the checkout form with strong contrast, consistent spacing, and the same light visual language as the rest of the checkout page. Duplicate top-page feedback should be avoided when the inline summary already confirms the applied discount.

## Evidence
- `003-student-coupon-applied.png`
- `004-student-bank-transfer-selected.png`

## Fix Plan
- Keep coupon success feedback inline in the form summary.
- Remove the duplicate messenger status for successful coupon apply.
- Restyle the coupon panel, input, description, and summary for the light checkout surface.

## Verification
- Passed visual retest in `003-student-coupon-applied.png` and `004-student-bank-transfer-selected.png`.
- Coupon feedback now appears inline with light checkout styling, readable helper text, and no duplicate top-page success text.
- Functional retest passed through order `142`.
