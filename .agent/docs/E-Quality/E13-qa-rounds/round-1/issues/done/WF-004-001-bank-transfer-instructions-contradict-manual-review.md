---
id: WF-004-001
workflow: WF-004
title: Bank transfer instructions contradict manual receipt review
severity: high
category: content-functionality
status: done
roles: student,manager
source: C12/WF-004, visual QA
found: 2026-06-30
---

# WF-004-001 - Bank transfer instructions contradict manual receipt review

## Problem
The bank transfer page tells learners to scan the QR code "below" even though it appears above the instruction block, and says the system will automatically approve the course. The live flow creates a pending bank transfer order and shows a receipt upload form for later verification, so the instruction is misleading.

## Expected
The page should tell the learner to transfer the exact amount, keep the order number as transfer content, upload a receipt, and wait for manager verification. After receipt upload, the order should move to a reviewable status such as `processing`.

## Evidence
- `005-student-bank-transfer-instructions.png`
- Verified order `139` remains `pending` after bank transfer checkout.

## Fix Plan
- Update payment config instructions.
- Keep the config/sync default aligned with the live QA site.
- Set bank transfer orders to `processing` after receipt upload so managers can identify orders awaiting review.

## Verification
- Passed retest in `005-student-bank-transfer-instructions.png`, `006-student-receipt-after-file-selected.png`, and `007-student-receipt-submitted-confirmation.png`.
- Active config verified with `drush config:get lms_payment.settings bank_instructions`.
- Latest verified order `142` moved to `processing` after receipt upload and has receipt file id `667`.
