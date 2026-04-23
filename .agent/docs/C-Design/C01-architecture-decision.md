# C01 — Architecture Decision Roll-up

## Architecture Overview

EverLMS sử dụng kiến trúc **Layered Modular Architecture** trên nền Drupal 11, với 27 custom modules (26 lms_* + open_switcher) phân theo 9 layers. Mỗi layer có trách nhiệm rõ ràng, dependency graph strict (không circular).

## Architecture Pattern

**Pattern:** Layered Modular (Drupal Entity API + Service Layer + Plugin Pattern)
**Rationale:** Tận dụng Drupal Entity API cho data model, Service layer cho business logic, Plugin pattern cho extensibility (payment, AI).

## System Diagram

```
[Client: Web Browser / Mobile App]
       |
       v
[CDN: CloudFlare / AWS CloudFront]
       |
       v
[Web Server: Nginx 1.24+]
       |
       v
[Application: Drupal 11 + PHP 8.3]
  +-- [Controller Layer] → Data prep → #theme → Twig
  +-- [Service Layer] → Business logic, managers, handlers
  +-- [Entity Layer] → 29 entity classes (28 content + 1 config), Entity API
  +-- [Plugin Layer] → Payment gateways, AI providers
       |
       v
[Data Layer]
  +-- [MariaDB 10.11 — 47 tables]
  +-- [Redis — Cache layer]
  +-- [Files — Private/Public/SCORM]
       |
       v
[External Services]
  +-- [Stripe / PayPal]
  +-- [Zoom API]
  +-- [AI Provider (OpenAI/Google)]
  +-- [SMTP / GA4]
```

## Layer Responsibilities

| Layer | Responsibility | Components |
|-------|---------------|-----------|
| Presentation | UI rendering, templates | Twig 3.x, SCSS, Bootstrap 5.3, Drupal behaviors |
| Controller | Request handling, routing | Controllers (data prep only → #theme → Twig) |
| Service | Business logic | Manager services (Grading, Payment, Progress, Certificate) |
| Entity | Data CRUD, storage | 40+ content entities, Field storage, Database API |
| Plugin | External integration | PaymentGatewayInterface, AI provider plugins |
| Infrastructure | Hosting, caching | Nginx, PHP-FPM, MySQL, Redis, CDN |

## Design Patterns

| Pattern | Usage | Implementation |
|---------|-------|---------------|
| Strategy | Question grading | `QuestionHandlerInterface` → 8 handlers (MC, SC, TF, Fill, Short, Match, Order, Essay) |
| Plugin | Payment gateways | `PaymentGatewayInterface` → StripeGateway, PayPalGateway |
| Plugin | AI providers | Provider plugin architecture |
| Event-Driven | Notifications | CourseEnrolledEvent, QuizCompletedEvent, CertificateIssuedEvent, PaymentReceivedEvent |
| Twig-First | Rendering | Controller → data array → `#theme` → Twig template (NO HTML in PHP) |

## Module Dependency Graph

```
Foundation:    lms_base
                 ↓
Content:       lms_course → lms_chapter → lms_lesson
                 ↓
Assessment:    lms_quiz → lms_question → lms_answer, lms_matching_pair
               lms_quiz_result → lms_question_response
                 ↓
Progress:      lms_progress (depends: course, chapter, lesson, quiz_result)
                 ↓
Commerce:      lms_order → lms_payment → lms_stripe, lms_paypal
               lms_membership → lms_payment
                 ↓
Certificate:   lms_certificate (depends: base, quiz_result)
                 ↓
Communication: lms_notification, lms_forum, lms_rating
                 ↓
Integration:   lms_zoom, lms_analytics, lms_ai
                 ↓
Additional:    lms_wishlist, lms_bundle, lms_note, lms_badge, lms_faq, lms_blog
```

**RULE:** No circular dependencies.

## Cross-Cutting Concerns

| Concern | Approach | Implementation |
|---------|----------|---------------|
| Authentication | Drupal session + CSRF | Core middleware |
| Authorization | RBAC (7 roles) | Entity access handlers + route permissions |
| Logging | Drupal watchdog + structured | `\Drupal::logger()` |
| Error Handling | Exception handlers | Global error handler + per-module |
| Validation | Entity constraints + Form API | Field validation + custom constraints |
| Caching | Page cache + entity cache + Redis | Drupal cache API + Redis backend |
| Feature Flags | FeatureFlagManager (planned) | Service + config entity |

## Technology Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| CMS Framework | Drupal 11 | Enterprise entity management, mature ecosystem |
| Database | MariaDB 10.11 | Drupal native, proven relational store (MySQL 8.0+ also supported) |
| Theme Framework | Bootstrap 5.3 | Responsive, dark theme, community |
| PDF Generation | dompdf | PHP-native, no external service needed |
| Payment Architecture | Plugin pattern | Extensible: Stripe now, others later |
| AI Architecture | Plugin pattern | Provider-agnostic: OpenAI, Google, etc. |
| Search | Drupal Search API | Extensible to Solr/Elasticsearch later |

---

*End of C01-architecture-decision.md*
