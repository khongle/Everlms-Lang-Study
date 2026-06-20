# A03-project-context.md — Project Context (Living State)

**Tier 1 — load at every session start.**
**Last Updated:** 2026-06-20
**Updated by:** Agent_PM (docs rebuild)

> Update at every session end. This file is the source of truth for current phase, active work, recent decisions, and known gaps. The facts in this file reflect the actual code state as of 2026-06-20 — the draft from 2026-04-16 has been superseded.

---

## Current State

| Field | Value |
|-------|-------|
| Phase | 4-Development |
| Checkpoint | CP3 complete → CP4 in progress |
| Active focus | Hardening stubs, closing security gaps, docs rebuild |
| Sprint | sprint-01 (continuing) |
| Config YAML | 396 files in `/config/sync` (tracked in git) |
| Custom modules | 28 dirs (27 `lms_*` + `open_switcher`) |
| Custom entities | 29 (28 ContentEntity + 1 ConfigEntity `lms_question_type`) |
| Routes total | 137 (19 public, 21 learn/app, 47 workspace/manage, 17 admin, 31 API, 2 utility) |

---

## Implementation Status by Area (as-built 2026-06-20)

### Foundation & Content — as-built

| Module | Status | Notes |
|--------|--------|-------|
| `lms_base` | as-built | Shared services, helpers, base classes |
| `lms_content_tools` | as-built | Content authoring utilities |
| `open_switcher` | as-built | Reusable user impersonation utility (package ≠ EverLMS) |
| `lms_course` | as-built | Course entity, catalog, enrollment hooks |
| `lms_chapter` | as-built | Chapter entity; parent-child hierarchy |
| `lms_lesson` | as-built | 9 lesson types; Zoom hook via `lms_zoom` |
| `lms_blog` | as-built | Dual-track: public frontend reads core `node:blog`; `lms_article` entity backs `/manage/blog` admin CRUD only |
| `lms_faq` | as-built | FAQ entity with categories |

### Assessment — as-built

| Module | Status | Notes |
|--------|--------|-------|
| `lms_quiz` | as-built | Quiz entity; `GradingStrategyFactory` static factory (NOT a Drupal Plugin manager); binary pass/fail; essay → `pending_review`; `QuizPassedEvent` dispatched on pass |
| `lms_question` | as-built | Question entity with 8 bundles (ConfigEntity `lms_question_type`): essay, fill_blank, matching, multiple_choice, ordering, short_answer, single_choice, true_false |

### Progress & User Portal — as-built

| Module | Status | Notes |
|--------|--------|-------|
| `lms_progress` | as-built | `UserPortalController` (dashboard, my-courses, lesson-view, certificates); `ProgressApiController` (POST `/api/lms/progress/mark-complete`); `lms_user_course` + `lms_user_lesson` entities; minor bug in `lms_progress_comment_form_submit_redirect()` — undefined `$response` on non-AJAX path |
| `lms_note` | as-built | Lesson notes with video timestamp; save/delete/get API; grouped notes page |

### Communication & Community — partially as-built

| Module | Status | Notes |
|--------|--------|-------|
| `lms_forum` | as-built (with bugs) | `LmsForumController` is the active controller; `ForumController` is dead code with `throw new \Exception("HARD DEBUG!")` at top — dangerous if ever routed; debug `messenger()->addMessage("FRESH CONTROLLER LOADED!")` left in `LmsForumController::viewTopic()` |
| `lms_rating` | as-built (partial) | `RatingForm` and `RatingManagerController` work; `RatingController` and `RatingListBuilder` reference 3 missing routes (`lms_rating.collection`, `lms_rating.toggle_approve`, `lms_rating.toggle_featured`) → `RouteNotFoundException` at runtime; no `.module` file → `lms_admin_ratings` theme not registered here |
| `lms_notification` | as-built (in_app only) | In-app channel fully works; `NotificationBellBlock` real; **email channel is a stub** (logs only, TODO comment); **push channel is a stub** (logs only, FCM foundation comment); `api/lms/notifications/unread-count` has `_access: TRUE` — anonymous access risk |

### Commerce — partially as-built

| Module | Status | Notes |
|--------|--------|-------|
| `lms_order` | as-built | `CheckoutController`, `CheckoutForm`, `MembershipCheckoutForm`, `BankTransferForm` all real; auto-enrollment on order complete (hook_entity_insert/update); Stripe PaymentIntent created at form BUILD time (one per page load — risk) |
| `lms_membership` | as-built | Plans, user memberships, cron expiry, admin CRUD, user portal; auto-renewal via webhook explicitly deferred |
| `lms_payment` | as-built (plugin host) | `PaymentGatewayManager` + `@PaymentGateway` annotation = real; `BankTransferGateway` = real; `PayPalGateway` (in this module) = stub (no server-side PayPal API call); `ZaloPayGateway` = stub (hardcoded sandbox URL); `PaymentManager` service class exists but NOT registered in DI container |
| `lms_stripe` | as-built (partial) | `processPayment()` real (Stripe SDK `PaymentIntent::retrieve`); `handleWebhook()` = **stub** (TODO comment, no `Stripe\WebhookSignature::verifyHeader()` — CRITICAL security gap); no webhook route exists anywhere |
| `lms_paypal` | stub / dead code | `PayPalGateway` class implements wrong interface (`Service\PaymentGatewayInterface` not `Plugin\PaymentGatewayInterface`), has no `@PaymentGateway` annotation → **never discovered by plugin manager**; all methods are TODO stubs; effectively dead code |
| `lms_wishlist` | as-built | Toggle/status/list all implemented with proper cache invalidation |
| `lms_bundle` | minimal scaffold | Entity definition only — no routes, no controllers, no checkout integration; data model only |

### Certificate & Gamification — partially as-built

| Module | Status | Notes |
|--------|--------|-------|
| `lms_certificate` | as-built | `CertificateGenerator` (mPDF, graceful degrade), `CertificateManager`, `CertificateEventListener` (subscribes to `lms_quiz.passed`); traverses Quiz→Lesson→Chapter→Course to find course; PDF stored at `public://certificates/issued/`; revoke, regenerate, public verification — all real |
| `lms_badge` | stub | Entities (`lms_badge`, `lms_user_badge`) defined; `field_criteria` JSON format documented; **zero award logic** — no services, no routes, no event subscribers, no cron; `field_criteria` is never evaluated |

### Integrations — partially as-built

| Module | Status | Notes |
|--------|--------|-------|
| `lms_zoom` | as-built (one minor stub) | `ZoomService` (Server-to-Server OAuth, token cached in State), `ZoomMeetingManager` (sync/delete on lesson save), Web SDK join page + JWT signature; `ZoomAiService::processSummaries()` has one stub: VTT download is a hardcoded simulation string instead of real HTTP fetch; Zoom credentials in plaintext config |
| `lms_ai` | as-built | `AiProviderManager` (Drupal Plugin system); OpenAI + Gemini = full; Pollinations = image-only; `CourseBuilderService` + queue worker; `AiEngineUserForm` wizard (2361 lines, all AJAX handlers real); `ScormBuilderService`; `GoogleSlideBuilderService`; **no rollback/compensation on partial failure** — orphaned entities possible |
| `lms_analytics` | as-built | All report queries real (`CourseReportManager`, `UserReportManager`, `QuizAnalyticsManager`); CSV export; module description claims "GA4" — no GA4 code exists |

### Mobile — as-built

| Module | Status | Notes |
|--------|--------|-------|
| `lms_mobile_api` | as-built | Backend API module serving `/api/v1/*`; **custom bearer token via `keyvalue.expirable` + OTP** (NOT OAuth2/JWT despite unused `oauth_*` config keys in the Flutter `.env`); 31 API routes total (legacy `api/lms/*` coexist with `api/v1/*`; legacy marked `@Deprecated` in Flutter `AppConstants`) |

Flutter app `app/` (git-tracked at repo root):
- Version: `1.0.10+33` (build 33)
- Stack: Flutter ≥3.0.1, Riverpod, GoRouter, Dio
- Screens: 33 across auth, dashboard, catalog, course detail, lesson view, quiz, certificates, cart/checkout, membership, notifications, forum, notes, search, profile, settings, onboarding, news, meeting, etc.
- Offline mode: `MainOfflineScreen` + `CourseDetailsOfflineScreen`
- Social auth: Google, Facebook, Apple (iOS), Microsoft (MSAL)
- Video: HLS/DASH via better_player, YouTube, Vimeo
- Design tokens: identical to web SCSS (`#0D1117`, `#667EEA`/`#764BA2`, Inter typography, Material 3)
- Both dark and light themes; default dark

---

## Known Gaps / Active Issues (as of 2026-06-20)

These feed into [E02-issues/](../E-Quality/E02-issues/) and G02-risk-register.md (planned, layer not yet materialized).

### CRITICAL Security

| ID | Finding | Location |
|----|---------|----------|
| SEC-001 | OpenAI + Gemini API keys stored in plaintext committed config YAML (`lms_ai.settings.yml`) — «secret in config — see security risk» | `config/sync/lms_ai.settings.yml` |
| SEC-002 | Google service account RSA private key stored in plaintext committed config YAML (`google_api_client.*`) — «secret in config — see security risk» | `config/sync/google_api_client.google_api_service_client.everlms_bot.yml` |
| SEC-003 | Stripe test keys + PayPal credentials in committed config YAML (`lms_payment.settings.yml`) — Stripe in TEST mode — «secret in config — see security risk» | `config/sync/lms_payment.settings.yml` |
| SEC-004 | Zoom `account_id`, `client_id`, `client_secret`, `sdk_key`, `sdk_secret` in committed config YAML — «secret in config — see security risk» | `config/sync/lms_zoom.settings.yml` |
| SEC-005 | Flutter `.env` file with Stripe/PayPal test credentials committed to git | `app/.env` |
| SEC-006 | No Key module usage — `key.key.*` config absent; all secrets in module config | All `lms_*.settings.yml` |
| SEC-007 | `/api/lms/notifications/unread-count` has `_access: TRUE` — anonymous read of notification counts | `lms_notification.routing.yml` |

### CRITICAL Functional Gaps

| ID | Finding | Location |
|----|---------|----------|
| GAP-001 | No webhook endpoints exist anywhere — Stripe, PayPal, ZaloPay webhooks cannot be received; `StripeGateway::handleWebhook()` is a stub with TODO comment | `lms_stripe`, `lms_payment` |
| GAP-002 | `lms_paypal` module (`PayPalGateway`) is dead code — wrong interface, no `@PaymentGateway` annotation, never discovered by plugin manager | `lms_paypal/src/Plugin/PaymentGateway/PayPalGateway.php` |
| GAP-003 | `lms_badge` has zero award logic — badge entities exist but no criteria evaluator, no trigger, no event subscriber | `lms_badge` |
| GAP-004 | Email notifications not delivered — `NotificationManager::dispatch()` for `email` channel only logs; push channel also stub | `lms_notification/src/NotificationManager.php` |
| GAP-005 | `lms_bundle` entity is data model only — no checkout integration, no frontend routes | `lms_bundle` |
| GAP-006 | `lms_ai` pipeline has no rollback/compensation on partial failure — orphaned course/chapter/lesson entities possible | `lms_ai/src/Service/CourseBuilderService.php`, `AiEngineUserForm.php` |

### HIGH Bugs

| ID | Finding | Location |
|----|---------|----------|
| BUG-001 | `lms_forum/ForumController::viewTopic()` opens with `throw new \Exception("HARD DEBUG!")` — dead code but would cause 500 if ever routed | `lms_forum/src/Controller/ForumController.php` |
| BUG-002 | `lms_forum/LmsForumController::viewTopic()` has debug `messenger()->addMessage("FRESH CONTROLLER LOADED!")` left in production code | `lms_forum/src/Controller/LmsForumController.php` |
| BUG-003 | `lms_rating`: 3 missing routes (`lms_rating.collection`, `lms_rating.toggle_approve`, `lms_rating.toggle_featured`) — `RatingListBuilder` operations + `RatingController` redirects throw `RouteNotFoundException` at runtime | `lms_rating/src/` |
| BUG-004 | `lms_zoom/ZoomController::generateSignature()` references wrong service id (see G02-risk-register.md) | `lms_zoom/src/Controller/ZoomController.php` |
| BUG-005 | `lms_user_certificate.field_expiry_date` is declared as `datetime` but stored as varchar — type mismatch | `lms_certificate/src/Entity/UserCertificate.php` |
| BUG-006 | `CheckoutForm::buildForm()` creates a Stripe PaymentIntent on every form BUILD (one per page load, not per submit) | `lms_order/src/Form/CheckoutForm.php` |
| BUG-007 | No atomic transaction between order creation and enrollment — `lms_order_process_completed_order()` called from hook with no DB transaction wrapper | `lms_order/lms_order.module` |
| BUG-008 | `/checkout/{course_id}` and `/checkout/membership/{plan_id}` have `_access: TRUE` — anonymous users can reach checkout (redirect to login is controller-level, not router-level) | `lms_order/lms_order.routing.yml` |
| BUG-009 | Gemini provider sends API key as URL query param — leaks in server logs and proxies | `lms_ai/src/Plugin/AiProvider/GeminiProvider.php` |

### MEDIUM / Known Debt

| ID | Item |
|----|------|
| DEBT-001 | `lms_progress/UserPortalController`: `dashboard()` and `myCourses()` share near-identical "find next lesson" logic — code duplication |
| DEBT-002 | `lms_progress.module` `hook_form_alter` mutates and saves the `authenticated` Role entity at form-alter time — grants all text formats unconditionally; performance + security concern |
| DEBT-003 | `lms_ai.engine.settings.yml` stores a ~758KB cached AI response (`curriculum_suggestion`) in config export — bloats config sync |
| DEBT-004 | `lms_analytics` module description claims "GA4 integration" — no GA4 code exists; description is misleading |
| DEBT-005 | `lms_classic` theme: `node_modules/` not present (build not run); secondary theme effectively frozen |
| DEBT-006 | Page cache `max_age: 0` (disabled) — dev setting; must be configured before production |
| DEBT-007 | `lms_ai` `generateText` method present in all providers but NOT declared in `AiProviderInterface` — interface contract incomplete |
| DEBT-008 | `lms_membership` auto-renewal: `hook_cron` defers to "payment gateway webhook" which doesn't exist (GAP-001) |

---

## Recent Decisions

| Date | Decision | Rationale | ADR |
|------|----------|-----------|-----|
| 2026-04-16 | AI provider = Drupal Plugin system (`AiProviderManager`) | Extensible; standard Drupal discovery; swap providers without business code changes | ADR-006 |
| 2026-04-16 | Payment gateway = Plugin host pattern (`PaymentGatewayManager` + `@PaymentGateway` annotation) | Same rationale — swap gateways without touching checkout logic | ADR-007 |
| 2026-04-16 | Quiz grading = static PHP factory (`GradingStrategyFactory`) NOT a Drupal Plugin | Per-type strategy; binary pass/fail; no runtime plugin discovery overhead | ADR-005 |
| 2026-04-16 | Certificates auto-issued via event subscriber on `lms_quiz.passed` | Decoupled; certificate module listens to quiz module event; traverses hierarchy to find course | — |
| «unverified» | Mobile auth = custom bearer token via keyvalue.expirable + OTP (not OAuth2/JWT) | Lightweight; avoids OAuth2 server complexity for mobile-only token flow | — |

---

## Open Decisions (SA action required)

| ID | Question | Options | Recommendation | Blocks |
|----|----------|---------|----------------|--------|
| DEC-001 | API input validation strategy for `/api/v1/*` | A: per-route validation in controller / B: global EventListener DTO | A for now | Mobile API hardening |
| DEC-003 | Progress race condition on concurrent mark-complete | A: SELECT FOR UPDATE / B: queue worker | A for v1 | Data integrity |
| DEC-004 | Secrets management before production | A: Key module + env vars / B: DDEV env injection only | A (Key module) | SEC-001 through SEC-006 |
| DEC-005 | Stripe webhook endpoint — implement or defer | A: add route + signature verify / B: accept polling-only for v1 | A (security) | GAP-001 |

---

## Active Blockers

| ID | Description | Owner | Since | Blocks |
|----|-------------|-------|-------|--------|
| BLK-001 | Secrets in committed config (SEC-001–SEC-006) — blocks production deployment | SA | 2026-04-16 | CP5, CP6 |
| BLK-002 | No webhook endpoints — Stripe/PayPal async payment confirmation impossible | Dev | 2026-04-16 | Full commerce sign-off |
| BLK-003 | `lms_badge` has no award logic — gamification feature non-functional | Dev | 2026-04-16 | Badge Epic |
| BLK-004 | Email/push notification channels are stubs — notification system incomplete | Dev | 2026-04-16 | Notification Epic |

---

## Feature Flag Status

| Flag ID | Feature | Rollout % | Notes |
|---------|---------|-----------|-------|
| FF_FEAT_AI_PROVIDER | AI provider plugin | 0% (dev only) | `lms_ai` |
| FF_FEAT_AI_BUILDER | AI course builder | 0% (dev only) | `lms_ai.course_builder_form` |
| FF_FEAT_ZOOM | Zoom meeting integration | 0% (dev only) | `lms_zoom` |

---

## Where Active Work Sits (2026-06-20)

1. **Docs rebuild** (current session) — materializing A–E layer canonical docs from code-grounded facts base
2. **Commerce hardening** — Stripe webhook endpoint (GAP-001 / BUG-006); PayPal stub replacement or explicit descope
3. **Secrets extraction** — move all API keys to Key module or environment variables (SEC-001 through SEC-006)
4. **Badge award logic** — implement `field_criteria` evaluator + trigger in `lms_badge` (GAP-003)
5. **Notification delivery** — implement email channel via Drupal queue / SMTP (GAP-004)
6. **Bug fixes** — `ForumController` dead code removal (BUG-001/BUG-002); `lms_rating` missing routes (BUG-003); `field_expiry_date` type fix (BUG-005)
7. **Mobile API stabilization** — migrate legacy `api/lms/*` endpoints to `api/v1/*`; add input validation (DEC-001)

---

## Next Steps

1. SA decision on DEC-004 (Key module adoption) — unblocks SEC-001–SEC-006 and CP5
2. SA decision on DEC-005 (Stripe webhook endpoint) — unblocks full commerce sign-off
3. Implement `lms_rating` missing routes (BUG-003) — quick fix
4. Remove `ForumController` dead code and debug messages (BUG-001/BUG-002)
5. `lms_badge` award logic spike — evaluate complexity before committing to sprint

---

## Health Check

| Dimension | Status | Last Checked |
|-----------|--------|-------------|
| Completeness | WARNING — badge award logic, email/push notifications, webhook endpoints, bundle checkout are functional gaps | 2026-06-20 |
| Consistency | WARNING — 3 missing routes in lms_rating; `lms_paypal` dead code never wired; `generateText` not in AiProviderInterface | 2026-06-20 |
| Freshness | OK — facts base extracted from live code 2026-06-20; this doc anchored to that date | 2026-06-20 |
| Security | CRITICAL — all third-party secrets in plaintext committed config YAML; no Key module; Stripe webhook missing | 2026-06-20 |

---

*End of A03-project-context.md*
