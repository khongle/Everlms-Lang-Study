# E06 - Commerce & payments

**Status:** partial (orders/stripe/bank done; **webhooks + paypal/zalopay** open)

## Goal
Custom commerce: order, bundle, coupon, thanh toán đa gateway (multi-gateway payments).

## Scope
- Modules: [lms_order](../../C-Design/C05-modules/lms_order.md), [lms_bundle](../../C-Design/C05-modules/lms_bundle.md), [lms_payment](../../C-Design/C05-modules/lms_payment.md), [lms_stripe](../../C-Design/C05-modules/lms_stripe.md), [lms_paypal](../../C-Design/C05-modules/lms_paypal.md)
- Entities: lms_order, lms_order_item, lms_course_bundle
- Features: F-009, F-010, F-011 · Workflows: WF-004, WF-009

## Stories
- [E06S01](../D03-stories/E06S01-lms_order.md), [E06S02](../D03-stories/E06S02-lms_payment.md), [E06S03](../D03-stories/E06S03-lms_stripe.md), [E06S04](../D03-stories/E06S04-lms_paypal.md), [E06S05](../D03-stories/E06S05-lms_bundle.md)

## Dependencies
E01.

## Open work
- ⚠️ Payment webhooks + signature verification (BLK-002 / R-002) — task [E06S02T01](../D04-tasks/todo/E06S02T01-add-payment-webhooks.md).
- ⚠️ Secrets → Key/env (R-001).
- ⚠️ Loại bỏ `lms_paypal` bị trùng lặp (R-004) — task [E06S04T01](../D04-tasks/todo/E06S04T01-dedupe-paypal-gateway.md).
- Hoàn thiện ZaloPay gateway.
