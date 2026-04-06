# EverLMS — Business Process Flows

## 1. Student Enrollment & Learning Flow

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
    Q & R & T & U --> V[Mark Lesson Complete]
    S --> W[Submit Quiz]
    W --> X{Pass?}
    X -->|Yes| V
    X -->|No| Y[Retry Quiz]
    Y --> S
    V --> Z{All Lessons Done?}
    Z -->|No| O
    Z -->|Yes| AA[Chapter Complete]
    AA --> AB{All Chapters Done?}
    AB -->|No| O
    AB -->|Yes| AC[Course Complete 🎉]
    AC --> AD{Final Quiz?}
    AD -->|Yes| AE[Take Final Quiz]
    AE --> AF{Pass?}
    AF -->|Yes| AG[Issue Certificate 📜]
    AF -->|No| AE
    AD -->|No| AG
```

---

## 2. Membership Subscription Flow

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

---

## 3. Quiz Taking & Grading Flow

```mermaid
flowchart TD
    A[Click Take Quiz] --> B{Enrolled?}
    B -->|No| C[Redirect to Course]
    B -->|Yes| D{Prerequisites Met?}
    D -->|No| E[Show Prerequisite Message]
    D -->|Yes| F{Attempt Limit?}
    F -->|Exceeded| G[Show Limit Message]
    F -->|OK| H[Start Quiz]
    H --> I[Record Start Time]
    I --> J{Display Style?}
    J -->|One Page| K[Show All Questions]
    J -->|Step-by-Step| L[Show Question 1]
    K --> M[Auto-save via AJAX]
    L --> N[Navigate Questions]
    N --> M
    M --> O{Timer Expired?}
    O -->|Yes| P[Auto-Submit]
    O -->|No| Q[User Submits]
    P & Q --> R[Process Answers]
    R --> S[Calculate Score per Question]
    S --> T{Has Essay Questions?}
    T -->|Yes| U[Queue for Manual Grading]
    T -->|No| V[Calculate Final Score]
    U --> W[Teacher Grades Essays]
    W --> V
    V --> X{Score >= Threshold?}
    X -->|Yes| Y[Result: PASSED ✅]
    X -->|No| Z[Result: FAILED ❌]
    Y --> AA{Certificate Linked?}
    AA -->|Yes| AB[Generate Certificate PDF]
    AA -->|No| AC[Show Result Page]
    AB --> AC
    Z --> AC
    AC --> AD[Update Course Progress]
```

---

## 4. Certificate Issuance Flow

```mermaid
flowchart TD
    A[Quiz Passed] --> B{Certificate Template?}
    B -->|No| C[No Certificate]
    B -->|Yes| D[Create User Certificate Record]
    D --> E[Fill Template Tokens]
    E --> F[Generate PDF]
    F --> G[Store PDF in File System]
    G --> H[Send Email Notification]
    H --> I[Certificate Available in Profile]
    I --> J[User Downloads PDF]
    I --> K[User Shares Verification Link]
    K --> L[Public Verification Page]
    L --> M[Verify Certificate ID → Show Details]
```

---

## 5. Course Creation Flow (Instructor/Admin)

```mermaid
flowchart TD
    A[Create Course] --> B[Fill Course Info]
    B --> C[Set Pricing]
    C --> D[Assign Category & Tags]
    D --> E[Save as Draft]
    E --> F[Add Chapters]
    F --> G[Add Lessons to Chapters]
    G --> H{Content Type?}
    H -->|Video| I[Add Video URL/Embed]
    H -->|Text| J[Write Content]
    H -->|Quiz| K[Create/Link Quiz]
    H -->|H5P| L[Embed H5P]
    H -->|SCORM| M[Upload SCORM Package]
    I & J & K & L & M --> N[Configure Lesson Settings]
    N --> O{More Lessons?}
    O -->|Yes| G
    O -->|No| P{More Chapters?}
    P -->|Yes| F
    P -->|No| Q[Add Final Quiz Optional]
    Q --> R[Link Certificate Template]
    R --> S[Preview Course]
    S --> T{Ready?}
    T -->|No| U[Edit]
    U --> B
    T -->|Yes| V[Publish Course]
    V --> W[Course Live 🚀]
```

---

## 6. Order Management Flow (Admin)

```mermaid
flowchart TD
    A[Receive Order] --> B[Status: Pending]
    B --> C{Payment Verified?}
    C -->|Yes| D[Status: Paid]
    D --> E[Auto-Enroll Student]
    E --> F[Send Confirmation]
    F --> G[Status: Completed]
    C -->|No| H{Manual Order?}
    H -->|Yes| I[Admin Creates Order]
    I --> D
    H -->|No| J[Payment Failed]
    J --> K{Retry?}
    K -->|Yes| L[Retry Payment]
    L --> C
    K -->|No| M[Status: Cancelled]
    
    G --> N{Refund Request?}
    N -->|Yes| O[Process Refund]
    O --> P[Revoke Enrollment]
    P --> Q[Status: Refunded]
```

---

## 7. User Registration Flow

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
    M --> N[Redirect to Dashboard]
    N --> O[Show Welcome Tour Optional]
```
