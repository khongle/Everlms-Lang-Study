# Flow: Student Enrollment & Learning

```mermaid
flowchart TD
    A[Browse Courses] --> B{Free or Paid?}
    B -->|Free| C[Enroll]
    B -->|Paid| D[Add to Cart]
    D --> E[Checkout]
    E --> F{Payment Method}
    F -->|Stripe| G[Stripe Checkout]
    F -->|PayPal| H[PayPal Checkout]
    G --> I[Payment Webhook]
    H --> I
    I --> J{Payment Success?}
    J -->|Yes| K[Create Order ✅]
    J -->|No| L[Payment Failed ❌]
    L --> E
    K --> C
    C --> M[Create User Course Record]
    M --> N[Start Learning]
    N --> O[View Lesson]
    O --> P{Lesson Type}
    P -->|Video| Q[Watch Video]
    P -->|Text| R[Read Content]
    P -->|Quiz| S[Take Quiz]
    P -->|H5P| T[Interactive Content]
    P -->|SCORM| U[SCORM Player]
    P -->|Zoom| V[Join Live Session]
    Q & R & T & U & V --> W[Mark Lesson Complete]
    S --> X[Submit Quiz]
    X --> Y{Pass?}
    Y -->|Yes| W
    Y -->|No| Z[Retry Quiz]
    Z --> S
    W --> AA{All Lessons Done?}
    AA -->|No| O
    AA -->|Yes| AB[Chapter Complete]
    AB --> AC{All Chapters Done?}
    AC -->|No| O
    AC -->|Yes| AD[Course Complete 🎉]
    AD --> AE{Final Quiz?}
    AE -->|Yes| AF[Take Final Quiz]
    AF --> AG{Pass?}
    AG -->|Yes| AH[Issue Certificate 📜]
    AG -->|No| AF
    AE -->|No| AH
```

## Key Decision Points

| Decision | Condition | Action |
|----------|-----------|--------|
| Free vs Paid | `course.field_price_type` | Free → direct enroll, Paid → checkout |
| Lesson completion | Depends on `lesson.field_type` | Video: 90% watched, Text: manual, Quiz: passed |
| Certificate issuance | Quiz passed + template linked | Auto-generate PDF + notify |
