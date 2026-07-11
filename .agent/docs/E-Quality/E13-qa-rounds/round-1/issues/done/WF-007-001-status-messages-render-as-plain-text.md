---
id: WF-007-001
workflow: WF-007
title: Drupal status messages can render as plain text on the page top
severity: medium
category: ui
status: done
roles: authenticated
source: visual QA
found: 2026-06-30
---

# WF-007-001 - Drupal status messages can render as plain text on the page top

## Problem
After submitting the bank transfer receipt, the homepage shows "Payment receipt uploaded successfully! We will verify your payment shortly." as plain text at the top of the page. It lacks alert background, padding, icon/chrome, and visual hierarchy.

## Expected
Any Drupal status message should render as a readable success alert with consistent width, spacing, border, and contrast on light pages.

## Evidence
- `009-coupon-discount-redemption/007-student-receipt-submitted-confirmation.png`

## Fix Plan
- Add a global fallback style for `.lms-messages-wrapper:not(:empty)` when message markup is plain text.
- Preserve existing `.messages` markup styling when Drupal outputs the normal status message wrapper.

## Verification
- Passed visual retest in `009-coupon-discount-redemption/007-student-receipt-submitted-confirmation.png`.
- The receipt upload status message now appears as a readable green success alert instead of plain text.
