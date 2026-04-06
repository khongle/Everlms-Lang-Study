# Flow: Membership Subscription

```mermaid
flowchart TD
    A[View Pricing Page] --> B[Select Plan]
    B --> C{Logged in?}
    C -->|No| D[Login / Register]
    D --> C
    C -->|Yes| E[Checkout]
    E --> F[Process Payment]
    F --> G{Success?}
    G -->|Yes| H[Activate Membership]
    H --> I[Grant Course Access]
    I --> J[Send Confirmation Email]
    G -->|No| K[Payment Failed]
    K --> E
    
    H --> L{Recurring?}
    L -->|Yes| M[Schedule Auto-Renewal]
    M --> N[7-day Expiry Reminder]
    N --> O[Auto-Charge]
    O --> P{Payment OK?}
    P -->|Yes| Q[Extend Membership]
    P -->|No| R[3-day Grace Period]
    R --> S{Payment Retry?}
    S -->|Yes| Q
    S -->|No| T[Expire Membership]
    T --> U[Revoke Course Access]
    
    L -->|No / Lifetime| V[No Renewal Needed]
```

## Subscription States

| State | Trigger | Actions |
|-------|---------|---------|
| `trial` | Plan has trial_days > 0 | Access granted, trial expiry scheduled |
| `active` | Payment success | Full access to plan courses |
| `expired` | End date passed + no renewal | Access revoked, notification sent |
| `cancelled` | User cancels | Access until period end, no renewal |
