# Flow: User Registration

```mermaid
flowchart TD
    A[Click Register] --> B{Method?}
    B -->|Email| C[Fill Registration Form]
    B -->|Social Login| D[OAuth Redirect]
    D --> E[Get Profile from Provider]
    E --> F{Account Exists?}
    F -->|Yes| G[Link Social Account]
    F -->|No| H[Create Account]
    C --> I[Validate Form]
    I --> J[Create Account]
    J --> K[Send Verification Email]
    K --> L[User Clicks Verify Link]
    L --> M[Activate Account]
    H --> M
    G --> M
    M --> N[Assign Student Role]
    N --> O[Redirect to Dashboard]
    O --> P[Show Welcome Tour Optional]
```

## Registration Fields

| Field | Required | Validation |
|-------|----------|-----------|
| Full Name | ✅ | Min 2 chars |
| Email | ✅ | Valid email, unique |
| Password | ✅ | Min 8 chars, 1 uppercase, 1 number |
| Confirm Password | ✅ | Must match |
| Terms & Conditions | ✅ | Must accept |
