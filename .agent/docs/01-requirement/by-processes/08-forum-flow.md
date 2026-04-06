# Flow: Forum Discussion

```mermaid
flowchart TD
    A[Browse Forum] --> B{Logged in?}
    B -->|No| C[Login Required]
    B -->|Yes| D{Course-scoped Forum?}
    D -->|Yes| E{Enrolled?}
    E -->|No| F[Redirect: Enroll First]
    E -->|Yes| G[View Topics]
    D -->|No / Global| G
    
    G --> H{Action}
    H -->|Read| I[View Topic + Replies]
    H -->|Create| J[New Topic Form]
    J --> K[Enter Title + Body]
    K --> L[Post Topic]
    L --> M[Topic Published]
    M --> N[Notify Course Instructor]
    
    I --> O{Reply?}
    O -->|Yes| P[Write Reply]
    P --> Q[Post Reply]
    Q --> R[Notify Topic Author]
    O -->|No| S[Continue Browsing]
    
    I --> T{Mark Answer?}
    T -->|Topic Author or Instructor| U[Mark Reply as Best Answer]
    U --> V[Topic Status: Resolved ✅]
    
    I --> W{Moderation}
    W -->|Flag Report| X[Reported to Moderator]
    X --> Y{Moderator Review}
    Y -->|Approve| Z[Keep Published]
    Y -->|Hide| AA[Hide Reply/Topic]
    Y -->|Delete| AB[Delete Reply/Topic]
```

## Key Business Rules
| Rule | Detail |
|------|--------|
| Course forum access | Only enrolled users + course instructor |
| Global forum access | All authenticated users |
| Pinned topics | Always display first, only admin/manager can pin |
| Locked topics | No new replies allowed |
| Best answer | Topic author or instructor can mark |
| Moderation | Flag → moderator queue → approve/hide/delete |
