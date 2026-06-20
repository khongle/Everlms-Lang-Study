# S04 Data Flow - WF-004 Order checkout & payment

```text
Buyer → checkout (P029)
   ▼
app-drupal: create lms_order + items (pending) ──▶ db
   │ optional coupon (WF-009) → field_discount
   ▼
PaymentGatewayManager → @PaymentGateway plugin
   │ outbound HTTPS ──▶ ext-stripe/paypal/zalopay
   ◀── result (sync return)        ⚠️ no webhook reconcile (R-002)
   ▼
order → paid/completed; field_payment_id stored ──▶ db
   │ grant entitlement
   ▼
→ WF-001 enrollment / WF-005 membership
```
Source: [C12 WF-004](../../C-Design/C12-workflows/WF-004-order-checkout-and-payment.md). External: payment gateway (INT-001/002/003). Secrets in config (R-001).
