# C05 Module - lms_paypal

**Name:** PayPal gateway (duplicate) · **Phase:** 6 · **Epic:** [E06](../../D-Development/D02-epics/E06-commerce.md) · **Status:** blocked (dead/duplicate — R-004)

## Responsibility
Dự định thêm một `@PaymentGateway` PayPal. ⚠️ **Trùng lặp (duplicate)** với gateway PayPal đã có sẵn trong `lms_payment` — module này về bản chất là code chết/trùng lặp.

## Owned / related entities
- Không có.

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
- Trùng lặp với PayPalGateway của [lms_payment](lms_payment.md); [G02 R-004](../../G-Governance/G02-risk-register/_index.md).

## Dependencies
- lms_payment.

## Implementation notes
- `src/Plugin/PaymentGateway/PayPalGateway.php` mirror lại `lms_payment/.../PayPalGateway.php`. **Khuyến nghị:** loại bỏ module này và chỉ giữ một gateway PayPal trong `lms_payment` (tech-debt H04 / R-004).
