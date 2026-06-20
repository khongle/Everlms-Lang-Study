# C04 Entity - lms_order

**Display name:** Order
**Owning module(s):** [lms_order](../C05-modules/lms_order.md)
**Mode:** as-built (Content Entity) — custom commerce (no Drupal Commerce)

## Purpose
The order header: buyer, totals, discount/coupon, payment method/result, status, and the line items. The revenue record bridging catalog and access.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | buyer |
| field_order_number | human order number |
| field_subtotal, field_discount, field_total, field_currency | money fields |
| field_status | pending / paid / completed / cancelled |
| field_payment_method | gateway id (bank_transfer/stripe/paypal/zalopay) |
| field_payment_id | gateway transaction id |
| field_coupon_code | applied coupon (WF-009) |
| field_payment_receipt | uploaded receipt (bank transfer) |
| field_reminder_sent | dunning flag |
| created, changed | timestamps |

## Relationships
- 1→N `lms_order_item` (`field_items`); N→1 `user`. On paid/completed → enrollment (`lms_user_course`) / membership (`lms_user_membership`).

## Editorial/data management
Created at checkout; managers view/manage in the order list (management UI). ⚠️ No webhook reconciliation (R-002) — status set on synchronous gateway return. ~20 live orders. See [F-010](../../B-Requirements/B03-features/F-010-orders-and-checkout.md), [WF-004](../C12-workflows/WF-004-order-checkout-and-payment.md).
