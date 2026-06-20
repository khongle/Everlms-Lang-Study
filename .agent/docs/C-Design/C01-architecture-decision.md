# C01 — Architecture Decision

> Roll-up: architecture style, design patterns, routing architecture, system diagram, cross-cutting concerns, NFR envelope, and standard-modules note.
> Single file. Update when an ADR materially changes the architecture envelope.
> documentation-mode: **as-built** (reflects code in `/home/hainn/projects/everlms` as of 2026-06-20)

---

## Architecture Overview

EverLMS is a **Drupal 11 modular monolith** — all 27 `lms_*` modules plus `open_switcher` run in a single PHP process on one application server. The architecture is **entity-first**: every domain concept (course, lesson, quiz, order, certificate, etc.) is modelled as a custom Drupal entity; business logic lives in services; UI is rendered exclusively by Twig templates (Twig-first rendering). There is no micro-service split, no GraphQL or REST resource layer for the web UI (only a dedicated `/api/v1/*` surface for the Flutter mobile client).

**Key numbers:**

| Fact | Value |
|---|---|
| Custom modules | 27 `lms_*` + `open_switcher` (28 dirs total) |
| Custom entities | 29 (28 ContentEntity + 1 ConfigEntity: `lms_question_type`) |
| Taxonomy vocabularies | 11 |
| Custom routes | 137 across 5 route groups |
| DB tables | 202 (MariaDB 10.11) |
| Config YAML tracked | 396 files under `/config/sync` |

---

## Architecture Pattern

**Pattern:** Drupal 11 Modular Monolith — Entity-First, Twig-First, Plugin-Extensible

**Rationale:** Drupal's Entity API provides type-safe, fieldable, access-controlled entities out of the box; the Plugin API enables the `@PaymentGateway` and `@AiProvider` extension points without coupling concrete implementations to core services; Twig-first rendering enforces separation of data prep (controller/service) from presentation (template); the monolith approach minimises operational complexity for a solo-dev product with modest scale.

**ADR reference:** `G01-ADR-001` (see `G-Governance/` — planned, not yet materialized)

---

## Routing Architecture

EverLMS uses the **default 5-group profile** from the framework template.

| Route Group | Prefix | Purpose | Auth | Theme | Cache Strategy |
|---|---|---|---|---|---|
| **Public** | `/*` (no prefix) | Marketing site, course catalog, blog, certificate verification, checkout | Anonymous / optional login | `lms_theme` (Frontend) | Drupal page cache; `access content` permission gate |
| **App (Learn)** | `/dashboard`, `/learn/*`, `/my-*`, `/quiz/*`, `/zoom/*`, `/forum/new` etc. | Authenticated learner portal: dashboard, lesson view, quiz taking, notes, wishlist, notifications | `_user_is_logged_in: TRUE` | `lms_theme` (Frontend, same instance) | Per-user; no page cache |
| **Manage (Workspace)** | `/workspace/*`, `/manage/*` | Instructor/manager custom admin UI: course CRUD, orders, users, memberships, certificates, content tools, AI builder | `_permission: 'administer lms_course'` or entity-level | `lms_theme` (Frontend, workspace layout) | Per-user; no page cache |
| **Admin** | `/admin/*` | Drupal platform admin: config, reports, analytics, user management | `administer site configuration` / `administer users` | `claro` (Admin theme) | No cache |
| **API** | `/api/lms/*`, `/api/courses/*`, `/api/v1/*` | JSON endpoints for AJAX (web) and Flutter mobile | `_user_is_logged_in` (web) / bearer token at controller level (mobile `/api/v1/*`) | No theme — JSON only | Per-resource HTTP headers |

**Total routes by group:** Public 19, App/Learn 21, Workspace/Manage 47, Admin 17, API 31, Utility 2 = **137 total**

### Route Group Rules (as implemented)

1. **Workspace ≠ `/admin`**. The `/workspace/*` prefix is a custom parallel admin application (~47 routes) entirely bypassing Drupal Views and the core admin theme. `AdminController` in `lms_course` is its hub. This is an architectural choice: end-user-facing management uses `lms_theme`; platform-level administration uses `claro`.
2. **API group is theme-less.** All `/api/lms/*`, `/api/courses/*`, and `/api/v1/*` routes return JSON. No HTML rendering.
3. **Mobile API auth is controller-enforced.** `/api/v1/*` routes declare `_access: TRUE` in routing YAML; actual authentication is enforced inside each controller using a **custom bearer token backed by Drupal's `keyvalue.expirable` store + OTP flow** — not OAuth2/JWT (despite unused `oauth_*` config keys left in config export).
4. **No Views for page routes.** Config inventory shows 15 Views, all core Drupal admin/utility views (content list, watchdog, people, social_auth_profiles, etc.). Zero custom LMS page routes are served by Views.

### Route Group × Module Mapping

| Module | Group(s) | Prefix Examples |
|---|---|---|
| `lms_content_tools` | Manage | `/workspace/content-tools/*` |
| `lms_course` | Public + App + Manage | `/homepage`, `/courses/*`, `/workspace/*` |
| `lms_progress` | App + API | `/dashboard`, `/my-courses`, `/learn/*`, `/api/lms/progress/*` |
| `lms_quiz` | App | `/quiz/*`, `/my-quiz-results` |
| `lms_certificate` | Public + Manage + Admin | `/certificate/verify`, `/workspace/certificates/*`, `/admin/lms/certificates/*` |
| `lms_order` | Public | `/checkout/*` |
| `lms_membership` | App + Manage | `/my-membership`, `/workspace/pricing/*` |
| `lms_payment` | Admin | `/admin/config/everlms/payment` |
| `lms_ai` | Manage + Admin | `/manage/courses/ai-builder`, `/admin/config/lms/ai`, `/admin/everlms-ai-engine` |
| `lms_analytics` | Admin | `/admin/lms/reports/*` |
| `lms_zoom` | App + API + Admin | `/zoom/join/*`, `/api/lms/websdk/signature`, `/admin/config/lms/zoom` |
| `lms_mobile_api` | API | `/api/v1/*` |
| `lms_forum` | Public + App | `/forum`, `/forum/new`, `/forum/{topic}` |
| `lms_note` | App + API | `/my-notes`, `/api/lms/note/*` |
| `lms_notification` | App + API + Admin | `/notifications`, `/api/lms/notifications/*`, `/admin/lms/notifications/broadcast` |
| `lms_wishlist` | App + API | `/my-wishlist`, `/api/lms/wishlist/*` |
| `lms_blog` | Public + Manage | `/news`, `/news/{node}`, `/manage/blog/*` |
| `lms_rating` | Manage | `/workspace/ratings/*` |
| `open_switcher` | Admin + Utility | `/admin/switch-user`, `/app/switch-user` |

### Route Group Diagram

```
             [DDEV Nginx / Traefik — everlms.ddev.site]
                              |
              +---------------+---------------+
              |               |               |
       [Page Cache]    [PHP-FPM 8.3]    [PHP-FPM 8.3]
       (Public /*)            |               |
                   +----------+-----------+---+----------+
                   |          |           |              |
             /*          /workspace   /api/*        /admin/*
             /learn/*    /manage/*    /api/v1/*     
             /my-*       /forum/new                
             /quiz/*                               
                   |          |           |              |
              [lms_theme] [lms_theme] [No Theme]   [claro]
               Frontend    Workspace    JSON         Admin
               Anon/Auth   Auth Only   Bearer/Auth  Admin Auth
               Page cache  No cache    No cache     No cache
```

---

## System Diagram

```
[Web Browser / Flutter App]
          |
          v
[DDEV Nginx (nginx-fpm) — everlms.ddev.site]
          |
          v
[Drupal 11.3.5 / PHP 8.3]
  |
  +-- [Route Dispatcher — by URL prefix]
  |     +-- /* ---------> [Public Controllers]    → lms_course, lms_blog, lms_forum, lms_order, lms_certificate
  |     +-- /learn,       [App Controllers]       → lms_progress, lms_quiz, lms_membership, lms_note,
  |         /my-*, /quiz                            lms_notification, lms_wishlist, lms_zoom, lms_forum
  |     +-- /workspace,   [Workspace Controllers] → lms_course (AdminController), lms_certificate,
  |         /manage/*                               lms_membership, lms_rating, lms_content_tools, lms_ai
  |     +-- /admin/*      [Admin Controllers]     → lms_analytics, lms_ai, lms_payment, lms_zoom, open_switcher
  |     +-- /api/*        [API Controllers]       → lms_mobile_api, lms_progress, lms_note, lms_notification,
  |                                                  lms_wishlist, lms_zoom, lms_course
  |
  +-- [Service Layer — business logic per module]
  |     lms_certificate.generator / .manager
  |     lms_certificate.event_listener (event subscriber)
  |     plugin.manager.payment_gateway (PaymentGatewayManager)
  |     plugin.manager.lms_ai_provider (AiProviderManager)
  |     lms_ai.course_builder / .scorm_builder / .google_slide_builder
  |     lms_zoom.service / .meeting_manager / .ai_service
  |     lms_analytics.course_report / .user_report / .quiz_analytics
  |     lms_membership.manager / .access_subscriber
  |     lms_base.helper
  |     GradingStrategyFactory (static, not a Drupal service)
  |
  +-- [Entity Layer — 29 custom entity types]
  |     ContentEntities: lms_course, lms_chapter, lms_lesson, lms_quiz, lms_question,
  |       lms_answer, lms_matching_pair, lms_quiz_result, lms_question_response,
  |       lms_progress (see C04-entities for full list)
  |     ConfigEntity: lms_question_type
  |
  +-- [Plugin Layer]
  |     @PaymentGateway: BankTransferGateway, StripeGateway [REAL]; PayPalGateway, ZaloPayGateway [STUB]
  |     @AiProvider: OpenAiProvider, GeminiProvider [REAL]; PollinationsProvider [image only]
  |
  +-- [Event System]
        QuizPassedEvent (lms_quiz.passed) → CertificateEventListener::onQuizPassed()
        KernelEvents::EXCEPTION → MembershipAccessSubscriber (access denied → pricing redirect)
        hook_entity_insert/update (lms_order status=completed) → enrollment + notification

[Data Layer]
  +-- MariaDB 10.11 (db: everlms_pro, utf8mb4_general_ci, InnoDB) — 202 tables
  +-- Public file storage (public://) — uploaded files, certificates, avatars
  +-- [No Redis, Solr, or Elasticsearch configured — Drupal page cache is the only cache layer]

[External Services]
  +-- Stripe API (stripe-php 19.4.1) — PaymentIntent creation + retrieval [TEST MODE]
  +-- OpenAI API (gpt-4o) — JSON + text + image generation «secret in config — see security risk»
  +-- Gemini API (gemini-2.5-flash) — JSON + text + image generation «secret in config — see security risk»
  +-- Zoom API (Server-to-Server OAuth) — meeting CRUD + WebSDK JWT «secret in config — see security risk»
  +-- Google Drive/Slides API (Service Account) — AI slide generation «secret in config — see security risk»
  +-- Pollinations AI (no key) — image generation only
  +-- Mailpit (DDEV, dev only) — SMTP at localhost:1025
```

---

## Layer Responsibilities

| Layer | Responsibility | Allowed Dependencies |
|---|---|---|
| Controller | HTTP handling, input validation, entity upcasting, render-array assembly (`#theme` hook return) | Service layer, Entity API, Form API |
| Service | Business logic, orchestration, plugin instantiation, event dispatch | Entity API, other services, external HTTP clients |
| Entity | Data modelling, field storage, access control, CRUD | Drupal Entity API, Field API, Database |
| Plugin | Interchangeable implementations (payment gateways, AI providers) | Service container (injected via plugin constructor), Entity API |
| Event Subscriber | Decoupled cross-module reactions to domain events | Services injected via constructor |
| Template (Twig) | Presentation only — no PHP logic beyond simple conditionals/loops | Variables passed by controller via `#theme` |
| Infrastructure | Composer packages (Stripe SDK, mPDF, Firebase JWT, Google API Client), DDEV, MariaDB | External APIs, file system |

**Twig-First Rule:** Controllers return `['#theme' => '...', '#variables' => [...]]` render arrays. No HTML is emitted from PHP. All markup lives in `lms_*/templates/*.html.twig` files.

---

## Design Patterns

| Pattern | Where Used | Implementation |
|---|---|---|
| **Entity-first** (domain model) | All 29 custom entities | Drupal ContentEntityBase + custom `baseFieldDefinitions()` |
| **Plugin (Drupal Plugin API)** | Payment gateways | `PaymentGatewayManager` (`DefaultPluginManager`) + `@PaymentGateway` annotation + `PaymentGatewayInterface`; discovered from `Plugin/PaymentGateway/` namespace |
| **Plugin (Drupal Plugin API)** | AI providers | `AiProviderManager` + `@AiProvider` annotation + `AiProviderInterface`; discovered from `Plugin/AiProvider/` namespace |
| **Static Factory + Strategy** | Quiz grading | `GradingStrategyFactory::getStrategy(string $bundle)` (plain PHP `match`, NOT a Drupal Plugin manager) returns one of 8 `QuestionGradingInterface` implementations; binary grading only (no partial credit) |
| **Event-Driven** | Certificate auto-issuance | `QuizController::submitQuiz()` dispatches `QuizPassedEvent` (`lms_quiz.passed`); `CertificateEventListener::onQuizPassed()` traverses Quiz → Lesson → Chapter → Course → issues certificate via `CertificateManager` |
| **Event-Driven** | Membership access denial | `MembershipAccessSubscriber` listens to `KernelEvents::EXCEPTION`; redirects 403s on `/learn/*` to pricing page |
| **Hook-Based** | Order fulfillment | `hook_entity_insert/update` in `lms_order.module` fires on `field_status = completed`; creates `lms_user_course` enrollment + `lms_user_membership` + in-app notification + email |
| **Hook-Based** | Zoom meeting sync | `hook_entity_presave` in `lms_zoom.module` calls `ZoomMeetingManager::syncMeeting()` when a zoom-type Lesson is saved |
| **Twig-First rendering** | All pages | Controller prepares data arrays; returns `['#theme' => 'hook_name', 'variables' => ...]`; no HTML strings in PHP |
| **Queue Worker** | AI lesson content generation | `CourseContentGenerator` queue worker (`lms_ai_content_generator`); cron processes items; calls OpenAI to generate lesson HTML body |
| **Repository (raw SQL)** | Analytics reports | `CourseReportManager`, `UserReportManager`, `QuizAnalyticsManager` — direct `\Drupal::database()->select()` with multi-table joins and sub-queries |
| **PDF generation** | Certificates | `CertificateGenerator` renders `certificate_pdf` Twig theme to HTML, then passes HTML to mPDF 8.3.1; saves PDF as managed file |
| **RouteSubscriber override** | Forum canonical route | `lms_forum/Routing/RouteSubscriber.php` replaces `_controller` on `entity.lms_forum_topic.canonical` route with `LmsForumController::viewTopic` |
| **Theme suggestions** | Zoom WebSDK page | `hook_theme_suggestions_html_alter` + `hook_theme_suggestions_page_alter` inject `html__lms_zoom_web_player` + `page__lms_zoom_web_player` for a stripped no-chrome layout at `/zoom/join/{id}` |
| **Dual-track content** | Blog | Public frontend reads core `node:blog`; `/manage/blog/*` workspace manages a separate `lms_article` entity |

---

## Module Dependency Graph

```
lms_base (no custom deps)
  ↓
lms_course, lms_chapter, lms_lesson, lms_blog, lms_faq, lms_content_tools
  ↓
lms_question (→ lms_base)
lms_quiz (→ lms_base, lms_question)
  ↓
lms_progress (→ lms_base, lms_course, lms_chapter, lms_lesson)
lms_certificate (→ lms_base, lms_quiz)
  ↓
lms_payment (→ lms_base)
  ↓
lms_stripe (→ lms_payment)
lms_paypal (→ lms_payment)          [DEAD CODE — see Known Issues]
lms_order (→ lms_base, lms_payment)
lms_membership (→ lms_base, lms_payment)
  ↓
lms_ai (→ lms_base, key:key)
lms_zoom (→ lms_base, lms_lesson, lms_ai)
  ↓
lms_analytics (→ lms_base)
lms_note (→ lms_base, lms_lesson)
lms_forum (→ lms_base, lms_course)
lms_rating (→ lms_base, lms_course)
lms_notification (→ lms_base)
lms_badge (→ lms_base)             [NEAR-STUB — no award logic]
lms_wishlist (→ lms_base, lms_course)
lms_bundle (→ lms_base, lms_course) [SCAFFOLD ONLY]
lms_mobile_api (→ multiple)

open_switcher (standalone reusable utility)
```

**Rule:** No circular dependencies between modules.

---

## Cross-Cutting Concerns

| Concern | Approach | Route Groups Affected | Implementation | Status |
|---|---|---|---|---|
| **Authentication (web)** | Drupal session + CSRF | App, Manage, Admin | `_user_is_logged_in: TRUE` route requirement; Drupal session middleware | as-built |
| **Authentication (mobile)** | Custom bearer token via `keyvalue.expirable` + OTP | API (`/api/v1/*`) | Controller-level token validation in `MobileAuthController`; NOT OAuth2/JWT | as-built |
| **Authorization** | RBAC (7 roles) + entity access handlers | All | Role permissions in config YAML; entity `AccessControlHandler` per entity type; `administer lms_course` is the primary LMS admin permission | as-built |
| **Logging** | Drupal watchdog (`\Drupal::logger()`) | All | Named channels per module (`lms_zoom`, `lms_ai`, etc.); verbose in DDEV dev mode | as-built |
| **Error handling** | Drupal exception handling | All | HTML error pages for web routes; controllers return JSON errors for API routes; error level = `hide` in production config | as-built |
| **Caching** | Drupal page cache (public) + entity cache | Public (page cache), App/Manage/API (no page cache) | Page cache max age = 0 in current config (dev setting — must be configured for production); entity cache tags used in wishlist, notifications | as-built (dev config) |
| **File storage** | Drupal managed files (public://) | All | `file_system` service; no CDN configured; AVIF/WebP image conversion via `image_convert_avif` effect | as-built |
| **Email** | Drupal `plugin.manager.mail` + `php_mail` | All | SMTP module installed but disabled; DDEV Mailpit override for local dev; `hook_mail` per module | as-built |
| **Search** | Drupal core Search API (`node_search`) | Public | `/search/node` route; no Solr/Elasticsearch | as-built |
| **SEO** | `metatag` contrib module | Public | Token-based meta tags for node/term/user entities | as-built |
| **URL aliases** | `pathauto` contrib module | Public | Only one pattern: `node:blog` → `/blog/[node:title]`; no custom LMS entity path patterns | as-built |
| **Feature Flags** | None — not implemented | — | No feature flag system. Draft docs mention `FeatureFlagManager` but no such service or entity exists in the codebase | target-state (planned) |
| **Rate Limiting** | None | API | No rate limiting middleware for `/api/v1/*` | gap / planned |
| **CORS** | Not configured | API | No CORS headers for `/api/v1/*`; Flutter app assumed same-network (DDEV) in dev | gap |

---

## Standard Modules (Framework Mapping)

The framework mandates three standard modules for every project. EverLMS maps them as follows:

| Standard Module | Framework Role | EverLMS Equivalent | Mount Point | Status |
|---|---|---|---|---|
| **Content Tools** | Global data init, seed, content generation | `lms_content_tools` | `/workspace/content-tools/*` | as-built — dashboard, clean-content form, seed-input form; Drush command `lms:generate-demo` in `lms_base` |
| **Secrets** | Single entry point for secret fetch/rotate | None (no `Secrets` module) | — | gap — all secrets stored in plaintext config YAML (see Security ADR below) |
| **Feature Flag Manager** | Flag evaluation, kill-switch | None | — | target-state — not implemented |

**Note on `lms_content_tools` placement:** Routes are under `/workspace/content-tools/*` (requires `administer lms content tools` permission), consistent with the Manage group. This aligns with the framework's `App (admin section)` recommendation — an authenticated workspace area distinct from the Drupal `/admin/*` CMS layer.

---

## Technology Decisions

| Decision | Choice | Version | Rationale |
|---|---|---|---|
| CMS / Framework | Drupal | 11.3.5 | Entity API, Plugin API, config management, mature ecosystem |
| Language | PHP | 8.3 | Drupal 11 requirement; union types, readonly properties, enums |
| Database | MariaDB | 10.11 | Drupal-native; InnoDB transactions; utf8mb4_general_ci |
| Web server | Nginx + PHP-FPM | DDEV default | Standard Drupal stack |
| Dev environment | DDEV | — | Container-based local dev; single `config.yaml` |
| Template engine | Twig | 3.22.2 (via Drupal) | Drupal standard; enforces presentation/logic separation |
| PDF generation | mPDF | 8.3.1 | PHP-native; no external service; A4 + orientation support |
| Stripe SDK | stripe/stripe-php | 19.4.1 | Official SDK; PaymentIntent server-side retrieval |
| JWT (Zoom + mobile) | firebase/php-jwt | 7.0.4 | Zoom WebSDK HS256 signing; mobile bearer tokens |
| Google API | google/apiclient | 2.19.1 | Slides + Drive API for AI slide builder |
| Excel export | shuchkin/simplexlsxgen | 1.5.15 | Lightweight XLSX; used in analytics exports |
| Frontend CSS | Bootstrap | 5.3.3 (CDN) | Responsive grid; dark glassmorphism theme (`#0d1117`) |
| Mobile backend | `lms_mobile_api` module | — | Custom Drupal module exposing `/api/v1/*` REST endpoints |
| Mobile app | Flutter | v1.0.10+33 | Riverpod + GoRouter + Dio; 33 screens; `app/` dir in repo |
| Config management | Drupal config sync | 396 YAML files | Full site config tracked in `/config/sync`; `drush config:import` |
| Drush | Drush | 13.7.2 | CLI for config import, cache rebuild, maintenance scripts |
| Dependency management | Composer | 2.x | `composer.lock` locked; PSR-4 autoload via Drupal scaffold |

---

## NFR Envelope (as-built constraints)

| NFR | Current State | Production Gap |
|---|---|---|
| **Performance / Caching** | Page cache max age = 0 (disabled — dev setting); no Redis/Varnish; no CDN configured | Must enable page cache + tune max age for public routes; consider Redis for entity cache; add CDN for static assets |
| **Security — Secrets** | All third-party API keys (OpenAI, Gemini, Google RSA key, Stripe test keys, Zoom, PayPal) stored in plaintext committed config YAML; no Key module integration despite `drupal/key` being installed (no `key.key.*` configs) | ADR: move all secrets to Key module or environment variables before production — «secret in config — see security risk» |
| **Security — Webhooks** | No webhook routes exist for Stripe, PayPal, or ZaloPay; `handleWebhook()` in all gateways is a stub or no-op; no signature verification | Implement Stripe webhook endpoint with `WebhookSignature::verifyHeader()` before live payments |
| **Security — Stripe** | Stripe in TEST mode (`pk_test_` / `sk_test_`); `PaymentIntent::create()` called in `buildForm()` — one new PaymentIntent per page load (browser refresh = new intent, orphaned intents accumulate) | Switch to live keys; refactor to create PaymentIntent on first submit, not on form build |
| **Security — AI no rollback** | `lms_ai` pipeline has no saga/compensation; partial AI generation failures leave orphaned entities in DB | Implement rollback or cleanup task for failed AI generation runs |
| **Scalability** | Single-process monolith; single DB; no horizontal scaling configured | Evaluate at load; DDEV is dev-only; production deployment not documented |
| **Observability** | Drupal watchdog (`dblog`) per-module channels; no structured JSON log shipping; no APM | Add log shipper (e.g., Fluentd/Loki) for production; consider Sentry for error tracking |
| **Mobile rate limiting** | No rate limiting on `/api/v1/*` | Add per-IP or per-token rate limiting before public launch |
| **Accessibility** | Not assessed | TBD |

---

## Known Architecture Risks (ADR Pointers)

These risks are accepted or deferred in the current build. Each warrants a formal ADR (`G01-ADR-NNN`):

| ID | Risk | Severity | Status |
|---|---|---|---|
| R-SEC-01 | All third-party secrets in committed config YAML (OpenAI, Gemini, Google RSA, Stripe, Zoom, PayPal) | CRITICAL | Accepted — dev phase; must resolve before production |
| R-SEC-02 | Stripe PaymentIntent created on form BUILD (each page load generates a new intent) | HIGH | Known bug — refactor needed |
| R-SEC-03 | No webhook endpoints — no Stripe/PayPal signature verification possible | HIGH | Deferred — mobile/manual payment flow only currently |
| R-BUG-01 | `lms_forum` `ForumController::viewTopic` throws `"HARD DEBUG!"` (left-in debug statement) | HIGH | Bug — see [E02-issues](../E-Quality/E02-issues/) |
| R-BUG-02 | `lms_rating` missing 3 runtime routes (`lms_rating.toggle_approve`, `.toggle_featured`, `.collection`) | MEDIUM | Bug — routes referenced in list builder but not defined in routing.yml |
| R-BUG-03 | Zoom `generateSignature()` references wrong service id for `lms_zoom.service` | MEDIUM | Bug |
| R-BUG-04 | `lms_user_certificate.field_expiry_date` declared as `datetime` entity field but stored as varchar in DB | MEDIUM | Schema mismatch |
| R-DEAD-01 | `lms_paypal` module's `PayPalGateway` implements wrong interface + has no `@PaymentGateway` annotation — never discovered by plugin manager | MEDIUM | Dead code |
| R-DEAD-02 | `lms_badge` has entity definitions but zero award logic (no service, no event subscriber, no cron) | MEDIUM | Near-stub |
| R-PARTIAL-01 | `lms_notification` in-app channel works; email and push notification channels are stubs | MEDIUM | Partial implementation |
| R-PARTIAL-02 | AI pipeline has no rollback/compensation on partial failure | MEDIUM | Known gap |

---

## Cross-Links

- Entities detail: [C04-entities/](./C04-entities/)
- Module specs: [C05-modules/](./C05-modules/)
- API surface: [C06-apis/](./C06-apis/) (planned, not yet materialized)
- Sitemap / route listing: [C07-sitemap.md](./C07-sitemap.md) (planned, not yet materialized)
- Theme architecture: [C09-skins.md](./C09-skins.md) (planned, not yet materialized)
- Risk register: `G02-risk-register.md` (planned, layer G — not yet materialized)
- Technical requirements: [../B-Requirements/B02-technical-requirements.md](../B-Requirements/B02-technical-requirements.md)
