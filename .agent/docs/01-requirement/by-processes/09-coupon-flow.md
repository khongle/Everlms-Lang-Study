# Flow: Coupon Creation & Redemption

## Coupon Creation (Admin)

```mermaid
flowchart TD
    A[Admin: Create Coupon] --> B[Enter Code or Auto-Generate]
    B --> C[Set Discount Type]
    C --> D{Type?}
    D -->|Percentage| E[Set % off + Max discount cap]
    D -->|Fixed| F[Set flat amount off]
    E & F --> G[Set Conditions]
    G --> H[Min order amount]
    G --> I[Valid date range]
    G --> J[Max total uses]
    G --> K[Max uses per user]
    G --> L[Applicable courses/plans]
    H & I & J & K & L --> M[Save Coupon ✅]
```

## Coupon Redemption (Student)

```mermaid
flowchart TD
    A[Checkout Page] --> B[Enter Coupon Code]
    B --> C{Validate}
    C --> D{Code exists?}
    D -->|No| E[❌ Invalid code]
    D -->|Yes| F{Still active?}
    F -->|No| G[❌ Coupon expired]
    F -->|Yes| H{Within date range?}
    H -->|No| G
    H -->|Yes| I{Usage limit reached?}
    I -->|Yes| J[❌ Coupon used up]
    I -->|No| K{User already used max?}
    K -->|Yes| L[❌ Already used]
    K -->|No| M{Min order met?}
    M -->|No| N[❌ Min order $X required]
    M -->|Yes| O{Applicable to items?}
    O -->|No| P[❌ Not applicable]
    O -->|Yes| Q[✅ Apply Discount]
    Q --> R[Update Total]
    R --> S[Continue to Payment]
    S --> T[Record Coupon Usage]
```

## Coupon Entity Quick Reference
| Field | Validation |
|-------|-----------|
| `field_code` | Unique, uppercase, alphanumeric |
| `field_discount_type` | `percentage` or `fixed` |
| `field_discount_value` | > 0, ≤ 100 for percentage |
| `field_max_discount` | Cap for percentage (nullable) |
| `field_min_order` | Min cart total (nullable) |
| `field_valid_from` | Start date (nullable) |
| `field_valid_until` | Expiry date (nullable) |
| `field_max_uses` | Total uses allowed (0 = unlimited) |
| `field_uses_per_user` | Per-user limit (0 = unlimited) |
| `field_applicable_courses` | Course references (empty = all) |
