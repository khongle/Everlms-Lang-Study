---
id: E06S04T01
title: Remove duplicate lms_paypal gateway
status: todo
story: E06S04
owning_modules: [lms_paypal, lms_payment]
risk: R-004
adequacy: NOT_RUN
---

# E06S04T01 - Remove duplicate lms_paypal gateway

## Goal
Eliminate the duplicate PayPal `@PaymentGateway` (one in `lms_paypal`, one in `lms_payment`) — keep a single implementation (R-004, P2).

## Steps
1. Confirm which PayPal gateway is wired/used.
2. Keep one (recommend `lms_payment`); uninstall + remove `lms_paypal`.
3. Update config/plugin references; regression-test PayPal checkout.

## Acceptance / gates
- Exactly one `paypal` gateway plugin; PayPal checkout works; `lms_paypal` removed.

## Links
[story](../../D03-stories/E06S04-lms_paypal.md) · [INT-002](../../../C-Design/C06-apis/INT-002-paypal.md) · [G02 R-004](../../../G-Governance/G02-risk-register/_index.md)
