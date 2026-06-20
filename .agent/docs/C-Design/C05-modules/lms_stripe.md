# C05 Module - lms_stripe

**Name:** Stripe gateway · **Phase:** 6 · **Epic:** [E06](../../D-Development/D02-epics/E06-commerce.md) · **Status:** done

## Responsibility
Adds the `stripe` `@PaymentGateway` plugin using the Stripe PHP SDK — the primary card-payment path.

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
- [INT-001 Stripe](../C06-apis/INT-001-stripe.md)

## Backing pages
| Label | URL |
| ----- | --- |
| (Stripe checkout return handled by lms_payment) | `/payment/stripe/*` |

## Cross references
- Host [lms_payment](lms_payment.md); contract [INT-001](../C06-apis/INT-001-stripe.md).

## Dependencies
- lms_payment.

## Implementation notes
- `src/Plugin/PaymentGateway/StripeGateway.php`. ⚠️ Keys in config (R-001); **no Stripe webhook** (R-002) — add `/api/.../stripe/webhook` + signature verification.
