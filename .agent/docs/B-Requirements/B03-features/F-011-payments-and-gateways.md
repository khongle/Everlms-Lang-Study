# F-011 - Payments & gateways

**Status:** partial (BankTransfer + Stripe functional; PayPal/ZaloPay stub; **no webhooks** — R-002)
**Primary module:** [lms_payment](../../C-Design/C05-modules/lms_payment.md)
**Epic:** [E06 Commerce](../../D-Development/D02-epics/E06-commerce.md)
**Related workflows:** [WF-004 Order checkout & payment](../B04-business-workflows/WF-004-order-checkout-and-payment.md)
**Acceptance:** [B05 F-011](../B05-acceptance-criteria/F-011-payments-and-gateways.md)
**Source:** `web/modules/custom/lms_payment/`, `lms_stripe/`, `lms_paypal/`

## Requirement
Checkout (F-010) is paid through a pluggable gateway. The system provides a `@PaymentGateway` plugin host (`PaymentGatewayManager`) with gateways: **bank_transfer** (manual confirm), **stripe** (card via Stripe SDK), **paypal**, **zalopay**. The chosen gateway authorises/captures payment and reports success/failure back to the order.

## Business value
Multiple gateways (incl. Vietnamese ZaloPay) widen payment reach; the plugin host keeps adding gateways cheap.

## Scope notes
- Real/functional: `bank_transfer`, `stripe`. Stub/incomplete: `paypal`, `zalopay`.
- ⚠️ `lms_paypal` duplicates the PayPal gateway already in `lms_payment` — dead/duplicate module (R-004).
- ⚠️ **No webhook endpoints / signature verification** — payment truth is not reconciled async (R-002 / BLK-002).
- Secrets (gateway keys) currently in config YAML (R-001).

## Cross references
- Design: [C05 lms_payment](../../C-Design/C05-modules/lms_payment.md), [C06 INT-001 Stripe](../../C-Design/C06-apis/INT-001-stripe.md), [C06 INT-002 PayPal](../../C-Design/C06-apis/INT-002-paypal.md), [C06 INT-003 ZaloPay](../../C-Design/C06-apis/INT-003-zalopay.md)
- Risks: [G02 R-001/R-002/R-004](../../G-Governance/G02-risk-register/_index.md)
