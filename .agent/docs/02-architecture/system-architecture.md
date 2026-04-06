# EverLMS — System Architecture

## 1. Architecture Overview

```mermaid
graph TB
    subgraph Client Layer
        A[Web Browser]
        B[Mobile App iOS/Android]
    end
    
    subgraph CDN
        C[CloudFlare / AWS CloudFront]
    end
    
    subgraph Web Server
        D[Nginx / Apache]
    end
    
    subgraph Application Layer
        E[Drupal 11 + PHP 8.3]
        F[Custom LMS Modules x23]
        G[Drupal Core + Contrib]
    end
    
    subgraph Cache Layer
        H[Redis / Memcache]
        I[Drupal Page Cache]
    end
    
    subgraph Database
        J[(MySQL 8.0 / MariaDB 10.6+)]
    end
    
    subgraph File Storage
        K[Private Files]
        L[Public Files]
        M[SCORM Packages]
    end
    
    subgraph External Services
        N[Stripe API]
        O[PayPal API]
        P[Zoom API]
        Q[Google Analytics 4]
        R[SMTP / Mail Service]
        S[Firebase FCM]
    end
    
    A --> C --> D --> E
    B --> D
    E --> F & G
    E --> H & I
    E --> J
    E --> K & L & M
    E --> N & O & P & Q & R
    B --> S
```

---

## 2. Layered Architecture

| Layer | Responsibility | Components |
|-------|---------------|-----------|
| **Presentation** | UI rendering, templates | Twig templates, SCSS, JS (Drupal behaviors) |
| **Controller** | Request handling, routing | Controllers (Data prep only → Twig) |
| **Service** | Business logic | Manager services, handlers |
| **Data** | Entity CRUD, storage | Entity types, Field storage, Database API |
| **Integration** | External APIs | Gateway plugins, API clients |
| **Infrastructure** | Hosting, caching | Web server, PHP, DB, Redis, CDN |

---

## 3. Module Dependency Graph

```mermaid
graph LR
    subgraph Foundation
        BASE[lms_base]
    end
    
    subgraph Content
        COURSE[lms_course] --> BASE
        CHAPTER[lms_chapter] --> BASE & COURSE
        LESSON[lms_lesson] --> BASE & CHAPTER
    end
    
    subgraph Assessment
        QUIZ[lms_quiz] --> BASE & QUESTION & ANSWER
        QUESTION[lms_question] --> BASE
        ANSWER[lms_answer] --> BASE
        MPAIR[lms_matching_pair] --> BASE
        RESULT[lms_quiz_result] --> BASE & QUIZ
        RESPONSE[lms_question_response] --> BASE & QUESTION
    end
    
    subgraph Progress
        PROGRESS[lms_progress] --> BASE & COURSE & CHAPTER & LESSON & RESULT
    end
    
    subgraph Commerce
        ORDER[lms_order] --> BASE & PAYMENT
        MEMBERSHIP[lms_membership] --> BASE & PAYMENT
        PAYMENT[lms_payment] --> BASE
        STRIPE[lms_stripe] --> PAYMENT
        PAYPAL[lms_paypal] --> PAYMENT
    end
    
    subgraph Certificate
        CERT[lms_certificate] --> BASE & RESULT
    end
    
    subgraph Communication
        NOTIF[lms_notification] --> BASE
        FORUM[lms_forum] --> BASE & COURSE
        RATING[lms_rating] --> BASE & COURSE
    end
    
    subgraph Integration
        ZOOM[lms_zoom] --> BASE & LESSON
        ANALYTICS[lms_analytics] --> BASE
        AI[lms_ai] --> BASE
    end

    subgraph Blog
        BLOG[lms_blog] --> BASE
    end
```

---

## 4. Design Patterns

### 4.1 Strategy Pattern — Question Handler
```
QuestionHandlerInterface
├── MultipleChoiceHandler
├── SingleChoiceHandler
├── TrueFalseHandler
├── FillBlankHandler
├── ShortAnswerHandler
├── MatchingHandler
├── OrderingHandler
└── EssayHandler
```
- **Manager**: `lms_question.handler_manager` → resolves handler by question bundle.
- **Responsibilities**: `buildFormElement()`, `prepareResponseData()`, `calculateScore()`.

### 4.2 Plugin Pattern — Payment Gateway
```
PaymentGatewayInterface
├── StripeGateway
├── PaypalGateway
└── (Future gateways)
```
- **Manager**: `lms_payment.gateway_manager` → resolves gateway by config.

### 4.3 Event-Driven — Notifications
```
Events:
├── CourseEnrolledEvent → Send welcome email
├── QuizCompletedEvent → Send result notification
├── CertificateIssuedEvent → Send certificate email
├── PaymentReceivedEvent → Send receipt
└── MembershipExpiringEvent → Send reminder
```

### 4.4 Twig-First Rendering
- **Controller**: Prepares View Model (data arrays) → passes to `#theme`.
- **Twig**: Handles ALL HTML structure, CSS classes, layout.
- **No HTML in Controller**: Tuyệt đối không build HTML trong PHP.

---

## 5. API Architecture (for Mobile App)

```mermaid
graph LR
    A[Mobile App] -->|REST API| B[API Controller Layer]
    B --> C[Authentication Middleware]
    C --> D[Service Layer]
    D --> E[Entity Storage]
    E --> F[(Database)]
    
    C -->|JWT Token| G[Token Validation]
```

| Component | Technology |
|-----------|-----------|
| API Format | JSON:API / REST |
| Authentication | OAuth2 / JWT |
| Rate Limiting | Drupal + Nginx |
| API Docs | OpenAPI 3.0 / Swagger |
| Versioning | URL prefix `/api/v1/` |

---

## 6. Infrastructure Requirements

### 6.1 Development
| Component | Version |
|-----------|---------|
| PHP | 8.3+ |
| Drupal | 11.x |
| MySQL | 8.0+ / MariaDB 10.6+ |
| Node.js | 20 LTS (SCSS build) |
| Composer | 2.x |
| DDEV | Latest |

### 6.2 Production
| Component | Recommendation |
|-----------|---------------|
| Web Server | Nginx 1.24+ |
| PHP-FPM | PHP 8.3, OPcache |
| Database | MySQL 8.0 (managed) |
| Cache | Redis 7+ |
| CDN | CloudFlare / AWS CloudFront |
| SSL | Let's Encrypt / managed cert |
| Storage | S3-compatible (private/public files) |
| Search | Drupal Search API + Solr (optional) |
| Monitoring | New Relic / Datadog |
| CI/CD | GitHub Actions / GitLab CI |
