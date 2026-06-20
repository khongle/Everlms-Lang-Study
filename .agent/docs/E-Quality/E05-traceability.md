---
documentation-mode: as-built
generated: 2026-06-20
layer: E-Quality
---

# E05 — Story-Anchored Traceability Matrix

> REQ → FEAT → STORY → SURFACE/SOURCE → TEST/EVIDENCE chain for EverLMS. Every B03 Feature is mapped to the D03 Stories that deliver it, the C05 modules and entities that realize it, and the E03 test IDs that verify it. This is the single source of truth for traceability; rows are updated in-place when status changes.

---

## Column Reference

- **Story type:** `module` / `page` / `workflow` / `integration` / `nfr` / `cross-cutting`
- **Primary source:** `C05` (module spec) / `C09` (skins) / `B06` (integration req) / `E10` (NFR)
- **Owning module(s):** C05 module slugs contributing code.
- **Surface:** page / API / entity / service / job / block.
- **AC refs:** `B05-acceptance-criteria/F-{NNN}.md` (all planned — not yet materialized).
- **Tests:** E03 test IDs (planned, not yet materialized) or adequacy cross-reference from `quality-test-suite` story.
- **Editorial smoke:** Link to D04/E04 editor-facing smoke test confirming a non-developer editor can maintain content. N/A for purely programmatic or developer-only surfaces.
- **Status:** `COMPLETE` / `GAP` / `DEFERRED`

---

## Main Traceability Matrix

| Requirement | Feature | Story | Story type | Primary source | Owning module(s) | Surface | AC refs | Tests | Editorial smoke | Status |
|---|---|---|---|---|---|---|---|---|---|---|
| BR-001 | F-001 Course Management | lms_course | module | C05 | lms_course, lms_chapter, lms_lesson, lms_faq, lms_blog, lms_base | Pages: `/courses`, `/courses/{id}`, `/categories`, `/categories/{term}`, `/instructor/{user}`, `/workspace/courses`, `/workspace/*`; Entity: lms_course, lms_chapter, lms_lesson | B05/F-001.md (planned) | T-001 (Kernel-Course CRUD), T-002 (E2E catalog browse), T-003 (E2E course detail + free-enroll) | `/workspace/courses/{id}/edit` — course form edit | COMPLETE |
| BR-001 | F-001 | lms_chapter | module | C05 | lms_chapter | Entity: lms_chapter; inline entity form within course edit | B05/F-001.md | T-001 | N/A (managed inline via course form) | COMPLETE |
| BR-001 | F-001 | lms_lesson | module | C05 | lms_lesson | Entity: lms_lesson (9 types); SCORM extraction on save | B05/F-001.md | T-004 (Kernel-Lesson CRUD, all 9 types), T-005 (SCORM extraction) | `/workspace/lessons/{id}/admin-edit` | COMPLETE |
| BR-001 | F-001 | lms_blog | module | C05 | lms_blog | Dual-track: `/news` (node:blog) + `/manage/blog` (lms_article CRUD) | B05/F-001.md | T-006 (blog frontend), T-007 (article admin CRUD) | `/manage/blog/add`, `/manage/blog/{id}/edit` | COMPLETE |
| BR-001 | F-001 | lms_faq | module | C05 | lms_faq | Entity: lms_faq (3 scopes); admin at `/workspace/content/faqs` | B05/F-001.md | T-008 (FAQ CRUD, scope display) | `/workspace/content/faqs/{id}/edit` | COMPLETE |
| BR-001 | F-001 | lms_base | module | C05 | lms_base | Foundation: email-login, student role auto-assign, 11 taxonomy vocabs, Drush demo-content | B05/F-001.md | T-009 (LmsHelper unit), T-010 (taxonomy creation) | N/A (no editor surface; infrastructure only) | COMPLETE |
| BR-002 | F-002 Assessment System | lms_quiz | module | C05 | lms_quiz | Routes: `/quiz/{id}/take`, `/quiz/{id}/submit`, `/quiz-result/{result}`, `/my-quiz-results`; Service: GradingStrategyFactory + 8 strategies, QuizPassedEvent | B05/F-002.md | T-011 (Kernel-8 GradingStrategy implementations), T-012 (QuizPassedEvent dispatch), T-013 (E2E quiz take + result) | `/manage/quizzes/{id}/edit` | COMPLETE |
| BR-002 | F-002 | lms_question | module | C05 | lms_question | Entity: lms_question (8 bundles), lms_answer, lms_matching_pair; inline entity form in quiz | B05/F-002.md | T-014 (Kernel-question entity CRUD per bundle) | `/manage/questions/{id}/edit` | COMPLETE |
| BR-003 | F-003 Learning Progress | lms_progress | module | C05 | lms_progress | Routes: `/learn/{course}/{lesson}`, `/dashboard`, `/my-courses`, `/my-certificates`; API: `/api/lms/progress/mark-complete`; Entities: lms_user_course, lms_user_lesson | B05/F-003.md | T-015 (Kernel-ProgressCascade correctness), T-016 (E2E lesson view + mark-complete), T-017 (dashboard/my-courses) | N/A (learner portal; content created programmatically by enrollment and progress events) | COMPLETE |
| BR-003 | F-003 | lms_note | module | C05 | lms_note | API: `/api/lms/note/save`, `delete`, `get`; Page: `/my-notes` | B05/F-003.md | T-018 (Kernel-note CRUD + cache tag) | N/A (learner-authored notes; no editor surface) | COMPLETE |
| BR-004 | F-004 Order and Membership | lms_order | module | C05 | lms_order | Routes: `/checkout/{course}`, `/checkout/membership/{plan}`, `/bank-transfer-instructions`, `/workspace/orders`; Entities: lms_order, lms_order_item; Auto-enrollment hook | B05/F-004.md | T-019 (Kernel-order auto-enroll hook), T-020 (E2E Stripe checkout flow), T-021 (E2E bank-transfer flow) | `/workspace/orders/{id}/edit` (ApproveOrderForm) | COMPLETE |
| BR-004 | F-004 | lms_payment | module | C05 | lms_payment | Service: PaymentGatewayManager, BankTransferGateway (real), PayPalGateway (stub), ZaloPayGateway (stub); Admin: `/admin/config/everlms/payment` | B05/F-004.md | T-022 (Kernel-PaymentGatewayManager plugin discovery), T-023 (BankTransfer real flow) | `/admin/config/everlms/payment` | GAP: webhook stub + PayPal/ZaloPay are stubs; `lms_paypal` dead code |
| BR-004 | F-004 | lms_stripe | module | C05 | lms_stripe | Plugin: StripeGateway (real processPayment; stub handleWebhook) | B05/F-004.md | T-024 (Kernel-StripeGateway processPayment mock) | N/A (plugin only; no editor surface) | GAP: handleWebhook is TODO stub; PaymentIntent created on form BUILD (bug) |
| BR-004 | F-004 | lms_paypal | module | C05 | lms_paypal | Dead code — PayPalGateway implements wrong interface, no @PaymentGateway annotation | B05/F-004.md | — | N/A | DEFERRED: dead code; must rewrite with correct interface before usable |
| BR-004 | F-004 | lms_membership | module | C05 | lms_membership | Routes: `/my-membership`, `/workspace/pricing`, `/workspace/user-memberships`; Service: MembershipManager; Entities: lms_membership_plan, lms_user_membership; Cron expiry | B05/F-004.md | T-025 (Kernel-MembershipManager.hasActiveMembership), T-026 (cron expiry) | `/workspace/pricing/{id}/edit`, `/workspace/user-memberships/{id}/edit` | COMPLETE |
| BR-004 | F-004 | lms_wishlist | module | C05 | lms_wishlist | Routes: `/my-wishlist`; API: `/api/lms/wishlist/toggle`, `/api/lms/wishlist/status`; Entity: lms_wishlist | B05/F-004.md | T-027 (Kernel-wishlist toggle + cache tag) | N/A (learner-owned data; no editor content surface) | COMPLETE |
| BR-004 | F-004 | lms_bundle | module | C05 | lms_bundle | Entity: lms_course_bundle (data model only); no checkout, no routes | B05/F-004.md | — | N/A | DEFERRED: entity stub only; checkout integration not implemented |
| BR-005 | F-005 Content & Media | lms_lesson | module | C05 | lms_lesson, lms_zoom, lms_ai, lms_content_tools | Lesson multi-type (9 types) with conditional form; SCORM extraction; H5P embed; video resume via lms_user_lesson.field_video_position | B05/F-005.md | T-004 (Kernel-Lesson 9 types), T-005 (SCORM extraction), T-028 (E2E video resume) | `/workspace/lessons/{id}/admin-edit` | COMPLETE |
| BR-005 | F-005 | lms_zoom | module | C05 | lms_zoom | Route: `/zoom/join/{meeting_id}`; Service: ZoomMeetingManager, ZoomAiService; API: `/api/lms/websdk/signature`; Lesson presave sync; AI agenda; cron summaries (VTT stub) | B05/F-005.md | T-029 (Zoom meeting sync on lesson save), T-030 (Web SDK join) | Lesson edit form (`field_type=zoom`) | COMPLETE (VTT transcript download = stub; generateSignature() has wrong service ID bug) |
| BR-005 | F-005 | lms_content_tools | module | C05 | lms_content_tools | Routes: `/workspace/content-tools`; Services: EntityCleanForm (3-step), SeedInputForm, AI-generated content importer; Drush: `everlms:import` | B05/F-005.md | T-031 (content-tools entity clean), T-032 (AI import Drush command) | `/workspace/content-tools` (admin-only; no public content) | COMPLETE |
| BR-006 | F-006 Certification & Badges | lms_certificate | module | C05 | lms_certificate | Event subscriber on lms_quiz.passed; mPDF generation; Routes: `/certificate/verify`, `/workspace/certificates`, `/workspace/certificate-templates`; Entities: lms_certificate_template, lms_user_certificate; Revoke/regenerate | B05/F-006.md | T-033 (Kernel-CertificateEventListener on QuizPassedEvent), T-034 (mPDF generation), T-035 (E2E public verify) | `/workspace/certificate-templates/{id}/edit` | COMPLETE (known bug: field_expiry_date is varchar not int) |
| BR-006 | F-006 | lms_badge | module | C05 | lms_badge | Entities only: lms_badge, lms_user_badge; field_criteria JSON format documented; zero award logic | B05/F-006.md | — | `/manage/badges/{id}/edit` | DEFERRED: stub — no award criteria evaluation, no event subscribers, no cron |
| BR-007 | F-007 User Management | lms_base | module | C05 | lms_base, lms_course, open_switcher | Hook: email-login, student role auto-assign; 7 roles via config/sync; Admin workspace: `/workspace/users`, `/workspace/users/bulk`, `/workspace/users/create`, `/workspace/users/{user}/admin-edit`; Impersonation: `/app/switch-user`, `/admin/switch-back` | B05/F-007.md | T-036 (Kernel-student role auto-assign), T-037 (E2E user workspace CRUD), T-038 (impersonation flow) | `/workspace/users/create`, `/workspace/users/{user}/admin-edit` | COMPLETE (social login infrastructure installed but no providers configured) |
| BR-007 | F-007 | open_switcher | module | C05 | open_switcher | Routes: `/admin/switch-user`, `/app/switch-user`, `/admin/switch-back`; Impersonation banner block in lms_theme | B05/F-007.md | T-038 (impersonation flow) | N/A (admin-only; no editor-owned content) | COMPLETE |
| BR-008 | F-008 Communication & Community | lms_notification | module | C05 | lms_notification | Routes: `/notifications`, `/manage/settings/notifications`, `/admin/lms/notifications/broadcast`; API: `/api/lms/notifications/unread-count`; Block: NotificationBellBlock; Drush: `lms_notification:remind-inactive`; Email/push stubs | B05/F-008.md | T-039 (Kernel-NotificationManager in-app dispatch), T-040 (Kernel-NotificationEventSubscriber on QuizPassedEvent) | `/admin/lms/notifications/broadcast` | GAP: email and push channels are stubs (TODO log only); SMTP disabled |
| BR-008 | F-008 | lms_forum | module | C05 | lms_forum | Routes: `/forum`, `/forum/new`, `/forum/{topic}`, `/forum/{topic}/reply`; Entities: lms_forum_topic, lms_forum_reply; AJAX modal editing; best-answer flag; RouteSubscriber override | B05/F-008.md | T-041 (Kernel-forum topic + threaded reply creation), T-042 (E2E forum post + reply) | `/forum/new` (learner-created; admin can pin/lock) | GAP: LmsForumController::viewTopic has debug messenger left in production; ForumController dead code with throw Exception("HARD DEBUG!") |
| BR-008 | F-008 | lms_rating | module | C05 | lms_rating | Entity: lms_rating; RatingForm (1-5 star); Route: `/workspace/ratings`; RatingBlock plugin | B05/F-008.md | T-043 (Kernel-rating entity CRUD + duplicate prevention) | `/workspace/ratings` | GAP: 3 missing runtime routes (lms_rating.collection, lms_rating.toggle_approve, lms_rating.toggle_featured) — RouteNotFoundException; no .module file → lms_admin_ratings theme not registered |
| BR-008 | F-008 | lms_note | module | C05 | lms_note | (covered under F-003 and F-016) | B05/F-008.md | T-018 | N/A | COMPLETE |
| BR-009 | F-009 Analytics & Reporting | lms_analytics | module | C05 | lms_analytics | Routes: `/admin/lms/reports/courses`, `/admin/lms/reports/users`, `/admin/lms/reports/quizzes`, `/admin/lms/reports/quizzes/{id}`; CSV exports; Services: CourseReportManager, UserReportManager, QuizAnalyticsManager | B05/F-009.md | T-044 (Kernel-CourseReportManager SQL correctness), T-045 (E2E reports pages), T-046 (CSV export format) | N/A (report UI is code-driven; no editor-editable content) | COMPLETE (GA4 claim in module description is inaccurate — no GA4 code exists) |
| BR-010 | F-010 Platform Integrations | lms_stripe | module | C05 | lms_stripe | Plugin: StripeGateway (real processPayment + Stripe SDK); stub handleWebhook | B05/F-010.md | T-024 (mock Stripe processPayment) | N/A (plugin only) | GAP: webhook signature verification is a TODO; no webhook route exists |
| BR-010 | F-010 | lms_zoom | module | C05 | lms_zoom | Zoom Server-to-Server OAuth; ZoomMeetingManager lifecycle sync; Web SDK JWT endpoint; AI agenda; cron summaries | B05/F-010.md | T-029 (Zoom meeting sync), T-030 (Web SDK join) | `/admin/config/lms/zoom` | COMPLETE (VTT stub; generateSignature() wrong service ID — bug) |
| BR-010 | F-010 | lms_ai | module | C05 | lms_ai | Plugin manager: AiProviderManager; Providers: OpenAI (full), Gemini (full), Pollinations (image-only); Services: CourseBuilderService, ScormBuilderService, GoogleSlideBuilderService; Queue: CourseContentGenerator; Admin wizard: AiEngineUserForm | B05/F-010.md | T-047 (Kernel-AiProviderManager plugin discovery), T-048 (Kernel-CourseBuilderService entity creation) | `/admin/config/lms/ai`, `/admin/everlms-ai-engine`, `/manage/courses/ai-builder` | COMPLETE (no rollback on partial failure — known risk; secrets in plaintext config — critical risk) |
| BR-010 | F-010 | payment-webhooks | integration | B06 | lms_stripe, lms_payment, lms_paypal | Webhook routes + signature verification for Stripe, PayPal, ZaloPay | B05/F-010.md | T-049 (webhook signature verification unit test) | N/A | DEFERRED: no webhook routes exist anywhere; SPEC story not started |
| BR-011 | F-011 Mobile App | lms_mobile_api | module | C05 | lms_mobile_api | 22 routes under `/api/v1/*`; Controllers: MobileAuthController, MobileCourseController, MobileLearningController; Custom bearer token via keyvalue.expirable + OTP; Flutter app `app/` v1.0.10+33 | B05/F-011.md | T-050 (API contract tests: auth OTP flow), T-051 (API contract tests: course catalog), T-052 (API contract tests: quiz submit + QuizPassedEvent), T-053 (API contract tests: progress mark-complete) | N/A (API only; no editor surface) | COMPLETE (known gaps: no CORS config; no refresh token; lms_quiz + lms_wishlist undeclared in .info.yml deps) |
| BR-012 | F-012 Security & Performance | hardening-security | nfr | E10 | all lms_* | Cross-cutting: input validation, access audit, CSRF, error handling, DI cleanup; secret management (Key module migration) | B05/F-012.md | T-054 (OWASP ZAP baseline), T-055 (PHPCS Drupal lint), T-056 (route permission audit) | N/A (cross-cutting; no editor surface) | DEFERRED: SPEC story not started; critical gap — all API keys in plaintext committed config YAML |
| BR-012 | F-012 | progress-race-condition | nfr | E10 | lms_progress | DB lock/transaction on cascade progress update — prevent double-count on concurrent mark-complete | B05/F-012.md | T-057 (concurrent progress update race test) | N/A | DEFERRED: SPEC story not started |
| BR-012 | F-012 | cache-performance | nfr | E10 | lms_course, lms_analytics, lms_progress, lms_lesson | Redis backend, N+1 query elimination, cache tags; page cache max_age=0 in dev config | B05/F-012.md | T-058 (k6 load: 100 concurrent/catalog), T-059 (Redis cache backend integration) | N/A | DEFERRED: SPEC story not started; page cache disabled (dev config) |
| BR-001 | F-013 Suggested Modern Features | lms_badge, lms_notification, lms_analytics, lms_forum, lms_ai | cross-cutting | mixed | lms_badge (entities), lms_notification (in-app), lms_forum (forum), lms_analytics (reporting) | Gamification (stub), social learning, advanced analytics, marketplace — roadmap backlog | B05/F-013.md (planned) | — | N/A | DEFERRED: all items target-state; lms_badge = near-stub (entities only, zero award logic) |
| BR-001 | F-014 AI-Powered Content Generation | lms_ai | module | C05 | lms_ai, lms_zoom | Routes: `/manage/courses/ai-builder`, `/admin/everlms-ai-engine`, `/admin/config/lms/ai`; CourseBuilderService, ScormBuilderService, GoogleSlideBuilderService, CourseContentGenerator queue; ZoomAiService (agenda) | B05/F-014.md | T-047 (AiProviderManager), T-048 (CourseBuilderService), T-060 (Kernel-ScormBuilderService ZIP output) | `/manage/courses/ai-builder`, `/admin/everlms-ai-engine` | COMPLETE (no rollback on partial failure; AI secrets in plaintext config) |
| BR-001 | F-015 Customization & Branding | — | — | C09 | lms_theme (primary), lms_classic, open_switcher, lms_mobile_api | 15 switchable homepage skins, theme settings UI (13 section toggles, footer config), mobile SwitchDomainScreen white-label | B05/F-015.md | T-061 (E2E skin switch via Drupal Appearance settings), T-062 (footer config fields update page) | Drupal Appearance → EverLMS Theme settings | COMPLETE (lms_classic frozen — node_modules absent; per-user dark-mode toggle not implemented) |
| BR-001 | F-016 Essential Enhancements | lms_wishlist, lms_bundle, lms_note | module | C05 | lms_wishlist, lms_bundle, lms_note, lms_order, lms_notification, lms_zoom | Wishlist (as-built), bundles (as-built), notes (as-built); Abandoned cart recovery, free preview, social login, calendar/iCal, instructor revenue dashboard, email drip — all target-state | B05/F-016.md | T-027 (wishlist toggle), T-018 (note CRUD), T-063 (bundle entity checkout) | N/A for wishlist/notes (learner-owned); `/manage/bundles/{id}/edit` for bundles | GAP: abandoned cart, free preview, social login (web), calendar/iCal, instructor dashboard, email drip — all target-state / not implemented |
| — | — | quality-test-suite | nfr | E10 | all lms_* | No test files exist in any lms_*/tests/; no CI pipeline | — | T-064..T-086 (SPEC — planned Kernel/Functional/E2E/a11y/security/perf/visual-regression suite) | N/A | DEFERRED: SPEC story not started; zero tests exist as-built |
| — | — | ui-polish | cross-cutting | E11 | lms_theme (primary) + all content modules | 14-item UX polish pass across homepage, course, quiz, dashboard, checkout, forum, notifications, profile, search, error states | — | T-083 (visual regression baseline), T-085 (a11y Playwright/axe-core) | N/A | DEFERRED: SPEC story not started |
| — | — | public-surface-seo | page | E11 | lms_base, lms_course, lms_theme | Auth-page branding, search, sitemap.xml, GDPR pages | — | T-087 (sitemap.xml present), T-088 (GDPR page exists) | N/A | DEFERRED: SPEC (PARTIAL) story not started |
| — | — | ai-rollback-compensation | cross-cutting | E10 | lms_ai | Rollback and compensation for partial CourseBuilderService / AiEngineUserForm pipeline failures | — | T-089 (rollback on partial course build failure) | N/A | DEFERRED: SPEC story not started; no rollback mechanism exists |

---

## Coverage Summary

- **Must features (F-001 through F-012, F-014, F-015):** 9/14 COMPLETE (F-001, F-002, F-003, F-004 core, F-005, F-006 cert only, F-007, F-009, F-014, F-015); 4/14 with material GAPs (F-004 webhooks, F-006 badges, F-008 email/push/forum bugs, F-010 webhooks); 1/14 DEFERRED (F-012 security hardening)
- **Should/Could features (F-013, F-016):** 1/2 DEFERRED (F-013 — all target-state); 1/2 GAP (F-016 — wishlist/bundles/notes COMPLETE, 6 enhancements target-state)
- **Editorial smoke coverage:** 12 visible-content surfaces have identified edit paths; 6 surfaces are learner-owned or code-driven (N/A justified per row)
- **Gaps requiring action before production:**
  - F-004 / F-010: No webhook routes for Stripe, PayPal, ZaloPay — async payment confirmation impossible
  - F-004: Stripe PaymentIntent created on form BUILD (not submit) — orphaned intents, security concern
  - F-006: lms_badge — zero award logic; field_expiry_date varchar bug
  - F-008 / BR-008: lms_rating missing 3 routes (RouteNotFoundException); lms_forum debug artifacts in production
  - F-008: Email and push notification channels are stubs
  - F-012 (CRITICAL): All third-party API keys in plaintext committed config YAML — no Key module
  - F-011: No CORS config; no refresh token; undeclared module dependencies in lms_mobile_api.info.yml
  - quality-test-suite: Zero test files exist; no CI pipeline
- **Deferred (with reason):**
  - lms_paypal: dead code — wrong interface, no annotation; will not work until rewritten
  - lms_bundle: entity stub only — no checkout integration
  - lms_badge: entity stub only — no award logic
  - payment-webhooks story: SPEC, not started
  - hardening-security story: SPEC, not started
  - progress-race-condition story: SPEC, not started
  - cache-performance story: SPEC, not started
  - ai-rollback-compensation story: SPEC, not started
  - quality-test-suite story: SPEC, not started
  - ui-polish story: SPEC, not started
  - public-surface-seo story: SPEC (PARTIAL), not started
  - F-013 Suggested Modern Features: all items target-state / planned

---

## Integration Traceability (from B06)

| Integration | Feature | Story | Owning module(s) | Surface | Test | Status |
|---|---|---|---|---|---|---|
| INT-001 Stripe PaymentIntent | F-004, F-010 | lms_stripe | lms_stripe | Plugin: StripeGateway + Stripe Elements client; `/checkout/{course}` (CheckoutForm) | T-024 (mock processPayment) | COMPLETE (processPayment real; webhook stub — no route, no signature check) |
| INT-002 Stripe Webhook | F-010, F-012 | payment-webhooks | lms_stripe | No webhook route exists; handleWebhook() is TODO stub | T-049 (planned — not run) | DEFERRED: SPEC story not started; critical production gap |
| INT-003 Bank Transfer VietQR | F-004 | lms_payment | lms_payment | BankTransferGateway + `/bank-transfer-instructions`; receipt upload | T-023 (real flow) | COMPLETE |
| INT-004 PayPal (lms_payment plugin) | F-004 | lms_payment | lms_payment | PayPalGateway (accepts any paypal_order_id without server-side verification) | — | GAP: stub — no REST API verification |
| INT-005 PayPal (lms_paypal module) | F-004 | lms_paypal | lms_paypal | Dead code — wrong interface, no @PaymentGateway annotation | — | DEFERRED: dead code; never instantiated by plugin manager |
| INT-006 ZaloPay | F-004 | lms_payment | lms_payment | ZaloPayGateway — hardcoded sandbox redirect URL | — | GAP: stub — hardcoded URL, no HMAC verification |
| INT-007 Zoom Server-to-Server OAuth | F-005, F-010 | lms_zoom | lms_zoom | ZoomMeetingManager (token cached in Drupal State); meeting lifecycle sync on lesson presave | T-029 (meeting sync) | COMPLETE (generateSignature() references wrong service ID — bug «unverified» exact error) |
| INT-008 Zoom Web SDK | F-005, F-010 | lms_zoom | lms_zoom | `/zoom/join/{meeting_id}`; HS256 JWT via firebase/php-jwt | T-030 (Web SDK join) | COMPLETE (VTT transcript download = stub — hardcoded simulation string) |
| INT-009 OpenAI (gpt-4o) | F-010, F-014 | lms_ai | lms_ai | AiProviderManager; generateJson/Text/Image (DALL-E 3); CourseBuilderService; queue worker | T-047, T-048, T-060 | COMPLETE (secrets in plaintext config — «secret in config — see security risk»; no rollback) |
| INT-010 Gemini (gemini-2.5-flash) | F-010, F-014 | lms_ai | lms_ai | AiProviderManager; generateJson/Text/Image (Imagen 4.0) | T-047 | COMPLETE (secrets in plaintext config; Gemini API key passed via URL parameter — HIGH risk) |
| INT-011 Pollinations (image-only) | F-014 | lms_ai | lms_ai | AiProviderManager; generateImage only (free, no API key) | T-047 | COMPLETE |
| INT-012 Google Slides API | F-014 | lms_ai | lms_ai | GoogleSlideBuilderService; requires google/apiclient + google_api_client contrib | T-060 | COMPLETE (RSA private key in plaintext committed config — «secret in config — see security risk») |
| INT-013 Mobile Bearer Token / OTP | F-011 | lms_mobile_api | lms_mobile_api | Custom keyvalue.expirable token store; 6-digit OTP; `/api/v1/oauth/token`, `/api/v1/user/register/verify-otp` | T-050 | COMPLETE (no refresh token; oauth_* config keys unused) |
| INT-014 Firebase FCM (mobile push) | F-011, F-008 | lms_mobile_api | lms_mobile_api (Flutter app) | Flutter app side: FCM + APNs; Drupal side: lms_notification push = stub (log only) | — | GAP: Drupal push channel is stub; FCM fully wired in Flutter |
| INT-015 SMTP Email | F-008, F-016 | lms_notification, lms_order | lms_notification, lms_order | Drupal php_mail/sendmail; SMTP module installed but smtp_on=false; enrollment and membership expiry emails via hook_mail | — | GAP: SMTP disabled; email notification dispatch is log-only stub |
| INT-016 Social Auth (web) | F-007, F-016 | (no D03 story) | social_auth (contrib) | social_auth module installed; social_auth.settings.yml: providers=[] — no providers active | — | DEFERRED: infrastructure present, zero providers configured |
| INT-017 H5P embed | F-005 | lms_lesson | lms_lesson | field_h5p_embed (raw embed code); rendered in lessonView(); no xAPI bridge | — | COMPLETE (score/completion tracking not implemented — known gap) |

---

## Known Bugs Cross-Reference (feeds E02-issues)

| Bug | Severity | Story | Feature | Location |
|---|---|---|---|---|
| lms_forum `ForumController::viewTopic()` opens with `throw new \Exception("HARD DEBUG!")` — dead code, 500 if routed | HIGH | lms_forum, hardening-security | F-008 | `web/modules/custom/lms_forum/src/Controller/ForumController.php` |
| `LmsForumController::viewTopic()` has `messenger()->addMessage("FRESH CONTROLLER LOADED!")` committed in production | MEDIUM | lms_forum, ui-polish | F-008 | `web/modules/custom/lms_forum/src/Controller/LmsForumController.php` |
| lms_rating: 3 missing routes (`lms_rating.collection`, `lms_rating.toggle_approve`, `lms_rating.toggle_featured`) — `RouteNotFoundException` on RatingController/RatingListBuilder | HIGH | lms_rating, hardening-security | F-008 | `lms_rating` module routing |
| Zoom `generateSignature()` references wrong service ID | MEDIUM | lms_zoom | F-005, F-010 | `web/modules/custom/lms_zoom/src/Controller/ZoomController.php` |
| `lms_user_certificate.field_expiry_date` declared datetime but underlying column is varchar | MEDIUM | lms_certificate, hardening-security | F-006 | `lms_certificate` entity definition |
| Stripe `PaymentIntent::create()` called in `buildForm()` — one intent per page load, not per submit | HIGH | lms_stripe, payment-webhooks | F-004, F-010, F-012 | `CheckoutForm::buildForm()`, `MembershipCheckoutForm::buildForm()` |
| No webhook routes exist for Stripe, PayPal, ZaloPay — async payment confirmation impossible | CRITICAL | payment-webhooks | F-004, F-010, F-012 | All payment gateway modules |
| All third-party API keys (OpenAI, Gemini, Stripe, Zoom, PayPal, Google RSA key) in plaintext committed config YAML | CRITICAL | hardening-security | F-012 | `config/sync/lms_*.settings.yml`, `config/sync/google_api_client.*.yml`, `app/.env` |
| lms_badge: `field_criteria` JSON format documented but zero award logic evaluates it | MEDIUM | lms_badge | F-006, F-013 | `lms_badge` module |
| lms_paypal: `PayPalGateway` implements `Service\PaymentGatewayInterface` (wrong interface), no `@PaymentGateway` annotation — never discovered | MEDIUM | lms_paypal | F-004 | `web/modules/custom/lms_paypal/src/Plugin/PaymentGateway/PayPalGateway.php` |
| lms_mobile_api: `lms_quiz` and `lms_wishlist` used via PHP `use` but not declared in `lms_mobile_api.info.yml` dependencies | MEDIUM | lms_mobile_api | F-011 | `lms_mobile_api.info.yml` |
| lms_notification: email channel dispatch is log-only (TODO comment); SMTP module disabled | HIGH | lms_notification | F-008 | `NotificationManager::dispatch()` |
| lms_progress `hook_form_alter` saves authenticated Role entity at form-alter time — grants all text formats unconditionally | MEDIUM | lms_progress, hardening-security | F-003, F-012 | `web/modules/custom/lms_progress/lms_progress.module` |
| lms_ai: no rollback or compensation on partial CourseBuilderService or AiEngineUserForm failure | HIGH | lms_ai, ai-rollback-compensation | F-014 | `CourseBuilderService`, `AiEngineUserForm` |
| Page cache `max_age: 0` in config export — effectively disabled for all pages | MEDIUM | cache-performance | F-012 | `config/sync/system.performance.yml` |
| No CORS headers configured for `/api/v1/*` — cross-origin requests from web clients fail | HIGH | lms_mobile_api, hardening-security | F-011 | `sites/default/services.yml` (missing) |

---

## Implementation Status Summary by Feature

| Feature ID | Feature name | Priority | Story / Stories | Overall status | Notes |
|---|---|---|---|---|---|
| F-001 | Course Management | Must | lms_course, lms_chapter, lms_lesson, lms_blog, lms_faq, lms_base | COMPLETE | Minor: glossary/categoryDetail methods unconfirmed; bundle checkout stub |
| F-002 | Assessment System | Must | lms_quiz, lms_question | COMPLETE | Minor bugs: start_time hardcoded, quiz-result template threshold ignores field value; pending_grading mock |
| F-003 | Learning Progress Tracking | Must | lms_progress, lms_note | COMPLETE | SCORM/H5P completion not tracked back; lesson comment hook_form_alter security issue |
| F-004 | Order and Membership | Must | lms_order, lms_payment, lms_stripe, lms_paypal, lms_membership, lms_wishlist, lms_bundle | PARTIAL | Core order/Stripe/bank real; webhooks critical gap; PayPal/ZaloPay stubs; lms_paypal dead; bundle entity stub |
| F-005 | Content & Media Management | Must | lms_lesson, lms_zoom, lms_content_tools | COMPLETE | Zoom VTT stub; generateSignature() wrong service ID bug |
| F-006 | Certification & Badges | Must | lms_certificate, lms_badge | PARTIAL | Certificates: COMPLETE; Badges: STUB (zero award logic); field_expiry_date varchar bug |
| F-007 | User Management | Must | lms_base, lms_course, open_switcher | COMPLETE | Social login infrastructure present but no providers configured |
| F-008 | Communication & Community | Must | lms_notification, lms_forum, lms_rating, lms_note | PARTIAL | In-app notifications COMPLETE; email/push stubs; forum debug artifacts; rating 3 routes missing |
| F-009 | Analytics & Reporting | Must | lms_analytics | COMPLETE | GA4 claim in module description is inaccurate — no GA4 code exists |
| F-010 | Platform Integrations | Must | lms_stripe, lms_zoom, lms_ai, payment-webhooks | PARTIAL | Stripe/Zoom/AI real; no webhook routes; Stripe PaymentIntent on buildForm bug |
| F-011 | Mobile App | Must | lms_mobile_api | COMPLETE | No CORS; no refresh token; undeclared deps in info.yml |
| F-012 | Security & Performance | Must | hardening-security, progress-race-condition, cache-performance | DEFERRED | CRITICAL: all API keys plaintext in committed config; no webhook security; page cache disabled |
| F-013 | Suggested Modern Features | Could | lms_badge, lms_notification, lms_analytics, lms_forum, lms_ai | DEFERRED | All items target-state; badge entities only; no roadmap delivery date |
| F-014 | AI-Powered Content Generation | Must | lms_ai | COMPLETE | No rollback on partial failure; API keys in config |
| F-015 | Customization & Branding | Must | lms_theme (external), open_switcher | COMPLETE | lms_classic frozen; per-user dark-mode not implemented |
| F-016 | Essential Enhancements | Must | lms_wishlist, lms_bundle, lms_note | PARTIAL | Wishlist/bundles/notes COMPLETE; 6 of 9 enhancements target-state |

---

## Notes on Story Type Rows without a B03 Requirement

The following stories appear with `—` in Requirement / Feature because they are cross-cutting NFR, integration, or UX stories driven by E10/E11 epics rather than a specific B03 feature file. They are listed here so they can be traced from D07 reverse-trace.

| Story | Type | Primary source | Driven by |
|---|---|---|---|
| quality-test-suite | nfr | E10 / E01-test-strategy.md (planned) | No test infrastructure exists (zero files in lms_*/tests/); CI pipeline absent |
| hardening-security | nfr | E10 / F-012 | Critical: API key secret management, access audit, CSRF, DI cleanup |
| payment-webhooks | integration | E10 / B06 | No webhook routes or signature verification for any payment gateway |
| progress-race-condition | nfr | E10 / F-012 | DB locking missing on concurrent mark-complete cascade |
| ai-rollback-compensation | cross-cutting | E10 / F-014 | No rollback/compensation on partial AI pipeline failure |
| cache-performance | nfr | E10 / F-012 | Page cache disabled; Redis not configured; N+1 queries in analytics |
| ui-polish | cross-cutting | E11 / F-015 | 14-item UX polish pass across all public/workspace pages |
| public-surface-seo | page | E11 / F-001 | Sitemap.xml, GDPR pages, auth-page branding |

---

*End of E05-traceability.md*
