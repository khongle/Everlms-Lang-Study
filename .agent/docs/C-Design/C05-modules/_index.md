---
documentation-mode: as-built
generated: 2026-06-20
---

# C05 — Module Catalog

> Roll-up of all `{module-slug}.md` files in `C-Design/C05-modules/`. Source of truth is the per-module file. Module count: **28** custom modules (27 `lms_*` + `open_switcher`).

| Module | Responsibility (one line) | Route Groups | Status |
|--------|--------------------------|--------------|--------|
| [lms_base](lms_base.md) | Foundation: shared `LmsHelper` service, taxonomy vocab creation, email-login form alter, demo-content Drush command | — (no routes) | DONE |
| [lms_content_tools](lms_content_tools.md) | Admin data tooling: entity clean (3-step confirm), seed-input wizard, AI-generated content importer (13 content types) | Manage (`/workspace/content-tools`) | DONE |
| [open_switcher](open_switcher.md) | "Open Platform" user impersonation utility — admins switch to any non-admin account and switch back | Admin (`/admin/switch-user`), App (`/app/switch-user`) | DONE |
| [lms_course](lms_course.md) | Course entity + admin shell: public catalog/homepage, workspace CRUD for courses/quizzes/orders/users/content, breadcrumb | Public (`/courses`, `/categories`, `/homepage`), Manage (`/workspace`), Admin (`/workspace/*`) | DONE |
| [lms_chapter](lms_chapter.md) | Chapter entity (thin): group lessons within a course; no routes of its own | — (entity links only) | DONE |
| [lms_lesson](lms_lesson.md) | Lesson entity: 9 content types (video/text/quiz/h5p/scorm/pdf/pptx/zoom/mixed), SCORM extraction on save | — (entity links only) | DONE |
| [lms_blog](lms_blog.md) | Dual-track blog: `lms_article` entity backs `/manage/blog` admin CRUD; frontend `/news` reads core `node:blog` | Manage (`/manage/blog`), Public (`/news`) | DONE |
| [lms_faq](lms_faq.md) | FAQ entity (3-scope: global/course/lesson) — entity-only module, no custom routes or forms | — (entity links only) | DONE |
| [lms_question](lms_question.md) | Question bank: `lms_question` (8 bundles), `lms_answer`, `lms_matching_pair` entities; grading strategies live in `lms_quiz` | — (entity links only) | DONE |
| [lms_quiz](lms_quiz.md) | Quiz engine: take/submit/result routes, static `GradingStrategyFactory` + 8 strategy classes, `QuizPassedEvent` dispatch | App (`/quiz`, `/quiz-result`, `/my-quiz-results`) | DONE |
| [lms_progress](lms_progress.md) | Enrollment and progress tracking: lesson-view UI, dashboard, my-courses, certificates page, mark-complete legacy API | App (`/learn`, `/my-courses`, `/dashboard`, `/my-certificates`), API (`/api/lms/progress/mark-complete`) | DONE |
| [lms_note](lms_note.md) | Student notes per lesson: save/delete/get JSON API + my-notes listing page | App (`/my-notes`), API (`/api/lms/note/*`) | DONE |
| [lms_forum](lms_forum.md) | Course discussion forums with threaded replies, best-answer marking, view-count tracking | Public (`/forum`, `/forum/*`) | DONE |
| [lms_rating](lms_rating.md) | Course star ratings and text reviews with admin approve/feature workflow | Manage (`/workspace/ratings`) | DONE (partial — 3 routes missing; see issues) |
| [lms_notification](lms_notification.md) | In-app notification system: delivery, bell block, preferences, broadcast, cron cleanup; email/push channels are stubs | App (`/notifications`, `/manage/settings/notifications`), Admin (`/admin/lms/notifications/broadcast`), API (`/api/lms/notifications/unread-count`) | DONE (in-app only) |
| [lms_order](lms_order.md) | Order management: checkout (course + membership), Stripe PaymentIntent, bank-transfer instructions/receipt upload, auto-enrollment on completion | Public (`/checkout/*`), Manage (`/manage/orders`) | DONE |
| [lms_membership](lms_membership.md) | Membership plans and user subscriptions: plan CRUD, user portal, cron expiry, access guard on `/learn/` | App (`/my-membership`), Manage (`/workspace/pricing`, `/workspace/user-memberships`) | DONE |
| [lms_payment](lms_payment.md) | Payment gateway plugin host: `PaymentGatewayManager`, `@PaymentGateway` annotation, settings form; `BankTransferGateway` (real), `PayPalGateway` (stub), `ZaloPayGateway` (stub) | Admin (`/admin/config/everlms/payment`) | DONE (plugin host real; PayPal/ZaloPay stubs) |
| [lms_stripe](lms_stripe.md) | Stripe payment gateway plugin: `StripeGateway` retrieves PaymentIntent via Stripe SDK; no webhook verification | — (no routes; plugin only) | DONE (webhook TODO) |
| [lms_paypal](lms_paypal.md) | PayPal module: fully stub — wrong interface, no `@PaymentGateway` annotation, never discovered by plugin manager | — (dead code) | STUB |
| [lms_wishlist](lms_wishlist.md) | Course wishlist: toggle/status JSON API + my-wishlist page with course cards | App (`/my-wishlist`), API (`/api/lms/wishlist/*`) | DONE |
| [lms_bundle](lms_bundle.md) | Course bundle entity (data model only): entity definition with courses/price/discount fields; no checkout integration, no controllers | — (entity links only) | STUB |
| [lms_certificate](lms_certificate.md) | Auto-issuance via `lms_quiz.passed` event, mPDF generation, revocation, public verification, admin templates/dashboard | Public (`/certificate/verify`), Manage (`/workspace/certificates`), Admin (`/admin/lms/certificates`) | DONE |
| [lms_badge](lms_badge.md) | Gamification badge entities (`lms_badge`, `lms_user_badge`) — award criteria JSON field exists but **zero award logic implemented** | — (entity links only) | STUB |
| [lms_zoom](lms_zoom.md) | Zoom live class integration: Server-to-Server OAuth, meeting lifecycle sync on lesson save, Web SDK join page, AI agenda generation, transcript summary cron | App (`/zoom/join/{meeting_id}`), Admin (`/admin/config/lms/zoom`), API (`/api/lms/websdk/signature`) | DONE (VTT download stub) |
| [lms_ai](lms_ai.md) | AI platform: `AiProviderManager` (Drupal Plugin API), OpenAI/Gemini (full) + Pollinations (image-only) providers, AI Course Builder, SCORM/Google Slides generation, multi-step admin wizard | Admin (`/admin/config/lms/ai`, `/admin/everlms-ai-engine`), Manage (`/manage/courses/ai-builder`) | DONE (no rollback on partial failure) |
| [lms_analytics](lms_analytics.md) | Internal LMS reporting: course/user/quiz analytics with raw SQL queries, CSV export; no GA4 despite module description | Admin (`/admin/lms/reports/*`) | DONE |
| [lms_mobile_api](lms_mobile_api.md) | Flutter mobile backend: 22 `/api/v1/` routes across 3 controllers; custom keyvalue bearer token + OTP auth; `lms_quiz`/`lms_wishlist` deps undeclared | API (`/api/v1/*`) | DONE |

---

## Status Key

| Status | Meaning |
|--------|---------|
| DONE | All controllers, services, entities, and routes are real, functional code |
| DONE (partial) | Core functionality real; specific sub-features are stubs or have known runtime errors |
| STUB | Entity/class scaffolding exists but core business logic is absent or is a TODO placeholder |

---

## Notes on Undeclared Dependencies

`lms_mobile_api` imports `GradingStrategyFactory` and `QuizPassedEvent` from `lms_quiz`, and queries `lms_wishlist` entity storage, but **neither `lms_quiz` nor `lms_wishlist` is listed in `lms_mobile_api.info.yml` dependencies**. Disabling either dependency module first will cause PHP fatal errors. See [lms_mobile_api](lms_mobile_api.md) for details.

## Notes on Known Bugs and Risks

- `lms_rating`: 3 runtime routes missing (`lms_rating.collection`, `lms_rating.toggle_approve`, `lms_rating.toggle_featured`) — `RatingListBuilder` operations throw `RouteNotFoundException`.
- `lms_forum`: `ForumController::viewTopic()` opens with `throw new \Exception("HARD DEBUG!")` — dead code but dangerous if ever routed.
- `lms_forum`: Debug `messenger()->addMessage("FRESH CONTROLLER LOADED!")` left in production `LmsForumController::viewTopic()`.
- `lms_paypal`: Module exists but its `PayPalGateway` class implements the wrong interface and has no `@PaymentGateway` annotation — never discovered by the plugin manager (dead code).
- All payment gateway webhook handlers: no routes defined anywhere; no signature verification implemented.
- `lms_badge`: `field_criteria` documents a JSON format but no code evaluates it or awards badges.
- Third-party secrets (OpenAI, Gemini, Stripe, Zoom, PayPal, Google RSA key) stored in plaintext committed config YAML — «secret in config — see security risk».

## Route Group Counts (as-built)

| Route Group | Route Count | Primary owners |
|-------------|-------------|----------------|
| Public | 19 | lms_course, lms_blog, lms_forum, lms_certificate |
| App / Learn | 21 | lms_progress, lms_quiz, lms_note, lms_notification, lms_wishlist, lms_membership, lms_zoom |
| Manage / Workspace | 47 | lms_course (admin shell), lms_membership, lms_certificate, lms_ai, lms_analytics, lms_notification |
| Admin (`/admin/*`) | 17 | lms_payment, lms_zoom, lms_ai, lms_analytics, lms_certificate, open_switcher |
| API (`/api/lms` legacy + `/api/v1` mobile) | 31 | lms_progress, lms_note, lms_wishlist, lms_notification, lms_zoom, **lms_mobile_api (22 routes)** |
| Utility | 2 | open_switcher |
| **Total** | **137** | |
