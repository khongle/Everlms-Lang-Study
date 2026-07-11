# S04 Data Flow - WF-009 Coupon / discount redemption

```text
Buyer (checkout, WF-004)
   │ enter coupon code
   ▼
app-drupal: lms_order validates code (config lms_order.settings: window/scope/usage-limit)
   │ valid → apply discount
   ▼
write lms_order.field_coupon_code / field_discount / field_total ──▶ db-mariadb
   │ invalid/expired/over-limit → reject inline
   ▼
checkout continues with adjusted total → WF-004 payment capture
```
Source: [C12 WF-009](../../C-Design/C12-workflows/WF-009-coupon-discount-redemption.md). No external service; coupon definitions are Drupal config, not a DB entity.
