# C05 Module - lms_paypal

**Name:** PayPal gateway (duplicate) · **Phase:** 6 · **Epic:** [E06](../../D-Development/D02-epics/E06-commerce.md) · **Status:** blocked (dead/duplicate — R-004)

## Responsibility
Intended to add a PayPal `@PaymentGateway`. ⚠️ **Duplicates** the PayPal gateway already shipped inside `lms_payment` — this module is effectively dead/duplicate code.

## Owned / related entities
- None.

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-011](../../B-Requirements/B03-features/F-011-payments-and-gateways.md) | Payments & gateways |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-004](../../B-Requirements/B04-business-workflows/WF-004-order-checkout-and-payment.md) | Order checkout & payment |

## Integrations / APIs (B06 / C06)
- [INT-002 PayPal](../C06-apis/INT-002-paypal.md)

## Backing pages
- —

## Cross references
- Duplicate of [lms_payment](lms_payment.md) PayPalGateway; [G02 R-004](../../G-Governance/G02-risk-register/_index.md).

## Dependencies
- lms_payment.

## Implementation notes
- `src/Plugin/PaymentGateway/PayPalGateway.php` mirrors `lms_payment/.../PayPalGateway.php`. **Recommendation:** remove this module and keep one PayPal gateway in `lms_payment` (tech-debt H04 / R-004).
