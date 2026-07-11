---
id: E06S04T01
task_id: E06S04T01
title: Remove duplicate lms_paypal gateway
status: todo
story: E06S04
owning_modules: [lms_paypal, lms_payment]
modules: [lms_paypal, lms_payment]
difficulty: 2
risk: R-004
adequacy: NOT_RUN
---

# E06S04T01 - Remove duplicate lms_paypal gateway

## Goal
Loại bỏ `@PaymentGateway` PayPal bị trùng lặp (một ở `lms_paypal`, một ở `lms_payment`) — chỉ giữ lại một implementation duy nhất (R-004, P2).

## Steps
1. Xác nhận (confirm) PayPal gateway nào đang được wire/sử dụng.
2. Giữ lại một cái (khuyến nghị `lms_payment`); uninstall + remove `lms_paypal`.
3. Cập nhật config/plugin reference; regression-test luồng checkout PayPal.

## Acceptance / gates
- Chỉ còn đúng một `paypal` gateway plugin; checkout PayPal hoạt động; `lms_paypal` đã được remove.

## Links
[story](../../D03-stories/E06S04-lms_paypal.md) · [INT-002](../../../C-Design/C06-apis/INT-002-paypal.md) · [G02 R-004](../../../G-Governance/G02-risk-register/_index.md)
