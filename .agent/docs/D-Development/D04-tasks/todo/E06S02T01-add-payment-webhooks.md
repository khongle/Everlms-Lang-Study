---
id: E06S02T01
title: Payment webhooks + signature verification
status: todo
story: E06S02
owning_modules: [lms_payment, lms_stripe]
risk: R-002
adequacy: NOT_RUN
---

# E06S02T01 - Payment webhooks + signature verification

## Goal
Reconcile order/payment state asynchronously from gateway truth (Stripe first), preventing desync (R-002 / BLK-002, P0).

## Steps
1. Add `POST /api/v1/payment/stripe/webhook` (and paypal/zalopay) endpoints.
2. Verify provider signatures; idempotent event handling.
3. Update `lms_order.field_status`/`field_payment_id` on webhook events; log + alert on mismatch.

## Acceptance / gates
- Webhook signature validated; replay-safe; order reconciles to gateway state.
- G3: signed-payload + invalid-signature tests.

## Links
[story](../../D03-stories/E06S02-lms_payment.md) · [INT-001](../../../C-Design/C06-apis/INT-001-stripe.md) · [G02 R-002](../../../G-Governance/G02-risk-register/_index.md)
