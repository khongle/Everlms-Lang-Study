# C05 Module - lms_payment

**Name:** Payment gateways (plugin host) · **Phase:** 6 · **Epic:** [E06](../../D-Development/D02-epics/E06-commerce.md) · **Status:** in-progress

## Responsibility
**Plugin host** `@PaymentGateway` (`PaymentGatewayManager` + `PaymentGatewayBase`/`Interface`). Cung cấp sẵn gateway `bank_transfer`, `paypal`, `zalopay`; Stripe được thêm bởi `lms_stripe`. Được `lms_order` gọi lúc checkout.

## Owned / related entities
- Không có (thao tác trên `lms_order`).

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-011](../../B-Requirements/B03-features/F-011-payments-and-gateways.md) | Payments & gateways |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-004](../../B-Requirements/B04-business-workflows/WF-004-order-checkout-and-payment.md) | Order checkout & payment |

## Integrations / APIs (B06 / C06)
- [INT-002 PayPal](../C06-apis/INT-002-paypal.md), [INT-003 ZaloPay](../C06-apis/INT-003-zalopay.md); Stripe qua lms_stripe.

## Backing pages
| Label | URL |
| ----- | --- |
| Payment / gateway return | `/payment/*` |

## Cross references
- Order ở [lms_order](lms_order.md); gateway [lms_stripe](lms_stripe.md), [lms_paypal](lms_paypal.md).

## Dependencies
- lms_base.

## Implementation notes
- `src/Plugin/PaymentGateway/*` (BankTransfer, PayPal, ZaloPay) + `PaymentGatewayManager`. ⚠️ **Không có webhook endpoint / signature verification (R-002)** — chỉ có synchronous return. Gateway key nằm trong `lms_payment.settings.yml` (R-001). bank_transfer = xác nhận thủ công; zalopay/paypal còn stub/một phần.
