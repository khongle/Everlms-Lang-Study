---
documentation-mode: as-built
generated: 2026-06-20
rebuilt-from: code-ground-truth (a 2026-06-20 code scan of web/modules/custom/ + C05-modules/_index.md)
---

# D01 — Development Overview

> Build order, Epic/Story registry, progress dashboard, module registry, workflow buckets, coverage status, and source-of-truth chain for the EverLMS Drupal 11 LMS.
>
> **Canonical vocabulary:** Epic ⊇ Story ⊇ Task. "Group" and "Unit" are deprecated — do not use.
> **Source of Truth:** this file is the SoT for build order and delivery status. Updated when any Epic or Story status changes.

---

## Round Notes / Recent Delivery History

| Round | Date | Summary |
|-------|------|---------|
| R01 | 2026-06-20 | Full rebuild from code ground truth. Prior draft (`docs-draft/D-Development/D01-dev-overview.md`) used forbidden Group/Unit vocabulary and stale DONE/SPEC labels predating current code. Rebuilt to canonical Epic/Story/Task, verified against `a 2026-06-20 code scan of web/modules/custom/` and `C05-modules/_index.md`. `lms_mobile_api` added (absent from draft). 11 Groups → 11 Epics; 84 Units condensed to ~35 Stories (one Story per logical module cluster + cross-cutting stories). |

---

## Build Order (Epics)

Epics are sequenced by dependency. Stories within an Epic may run in parallel subject to intra-Epic constraints listed below.

| Order | Epic ID | Epic Name | Stories | Dependencies | Est. Sprints | Status |
|-------|---------|-----------|---------|--------------|-------------|--------|
| 1 | E-01 | Foundation & Tooling | S-BASE, S-TOOLS, S-SWITCHER, S-THEME | None | 1 | DONE |
| 2 | E-02 | Content Layer | S-COURSE, S-CHAPTER, S-LESSON, S-BLOG, S-FAQ | E-01 | 2 | DONE |
| 3 | E-03 | Assessment Engine | S-QUESTION, S-QUIZ | E-01 | 2 | DONE |
| 4 | E-04 | Progress & Learning | S-PROGRESS, S-NOTE | E-02, E-03 | 1 | DONE |
| 5 | E-05 | Commerce & Payment | S-ORDER, S-MEMBERSHIP, S-PAYMENT, S-STRIPE, S-PAYPAL, S-WISHLIST, S-BUNDLE | E-01 | 2 | IN_PROGRESS |
| 6 | E-06 | Certificate System | S-CERTIFICATE | E-03, E-04 | 1 | DONE |
| 7 | E-07 | Communication | S-FORUM, S-RATING, S-NOTIFICATION | E-01, E-02 | 1 | IN_PROGRESS |
| 8 | E-08 | Integration & AI | S-ZOOM, S-AI, S-ANALYTICS | E-02, E-03 | 2 | IN_PROGRESS |
| 9 | E-09 | Additional & Mobile | S-BADGE, S-MOBILE | E-02, E-03, E-04, E-05 | 1 | IN_PROGRESS |
| 10 | E-10 | Quality & Hardening | S-SEC-HARDENING, S-PAYMENT-HARDENING, S-AI-HARDENING, S-INFRA-HARDENING | ALL above | 3 | PLANNED |
| 11 | E-11 | UI/UX Polish | S-UI-PUBLIC, S-UI-LEARN, S-UI-MANAGE, S-UI-MOBILE | E-10 | 2 | PLANNED |

---

## Story Registry

Story ID is stable. Type values: `module` | `cross-cutting` | `nfr` | `convention`.

| Story ID | Title | Type | Primary Source (C05) | Owning Module(s) | Epic | Status | Gate |
|----------|-------|------|----------------------|------------------|------|--------|------|
| S-BASE | Foundation services & taxonomy seeding | module | [lms_base.md](../C-Design/C05-modules/lms_base.md) | `lms_base` | E-01 | DONE | G4 pass |
| S-TOOLS | Content Tools admin (clean/seed/import) | module | [lms_content_tools.md](../C-Design/C05-modules/lms_content_tools.md) | `lms_content_tools` | E-01 | DONE | G4 pass |
| S-SWITCHER | User impersonation utility | module | [open_switcher.md](../C-Design/C05-modules/open_switcher.md) | `open_switcher` | E-01 | DONE | G4 pass |
| S-THEME | Bootstrap 5.3 dark theme (lms_theme + lms_classic) | cross-cutting | [C09-skins.md](../C-Design/C09-skins.md) | `lms_theme`, `lms_classic` | E-01 | DONE | G4 pass |
| S-COURSE | Course entity, catalog, homepage, admin shell | module | [lms_course.md](../C-Design/C05-modules/lms_course.md) | `lms_course` | E-02 | DONE | G4 pass |
| S-CHAPTER | Chapter entity | module | [lms_chapter.md](../C-Design/C05-modules/lms_chapter.md) | `lms_chapter` | E-02 | DONE | G4 pass |
| S-LESSON | Lesson entity (9 content types, SCORM extraction) | module | [lms_lesson.md](../C-Design/C05-modules/lms_lesson.md) | `lms_lesson` | E-02 | DONE | G4 pass |
| S-BLOG | Blog dual-track (lms_article admin + node:blog frontend) | module | [lms_blog.md](../C-Design/C05-modules/lms_blog.md) | `lms_blog` | E-02 | DONE | G4 pass |
| S-FAQ | FAQ entity 3-scope (global/course/lesson) | module | [lms_faq.md](../C-Design/C05-modules/lms_faq.md) | `lms_faq` | E-02 | DONE | G4 pass |
| S-QUESTION | Question bank: 3 entities, 8 bundles, InlineEntityForm | module | [lms_question.md](../C-Design/C05-modules/lms_question.md) | `lms_question` | E-03 | DONE | G4 pass |
| S-QUIZ | Quiz engine: take/submit/result, GradingStrategyFactory (8 strategies), QuizPassedEvent | module | [lms_quiz.md](../C-Design/C05-modules/lms_quiz.md) | `lms_quiz` | E-03 | DONE | G4 pass |
| S-PROGRESS | Enrollment, lesson-view, dashboard, progress cascade | module | [lms_progress.md](../C-Design/C05-modules/lms_progress.md) | `lms_progress` | E-04 | DONE | G4 pass |
| S-NOTE | Student notes per lesson (JSON API) | module | [lms_note.md](../C-Design/C05-modules/lms_note.md) | `lms_note` | E-04 | DONE | G4 pass |
| S-ORDER | Order management, checkout flow, auto-enrollment | module | [lms_order.md](../C-Design/C05-modules/lms_order.md) | `lms_order` | E-05 | DONE | G4 pass |
| S-MEMBERSHIP | Membership plans, user subscriptions, cron expiry, access guard | module | [lms_membership.md](../C-Design/C05-modules/lms_membership.md) | `lms_membership` | E-05 | DONE | G4 pass |
| S-PAYMENT | Payment gateway plugin host (BankTransfer real; PayPal/ZaloPay stubs) | module | [lms_payment.md](../C-Design/C05-modules/lms_payment.md) | `lms_payment` | E-05 | IN_PROGRESS | — |
| S-STRIPE | Stripe PaymentIntent gateway (processPayment real; webhook stub) | module | [lms_stripe.md](../C-Design/C05-modules/lms_stripe.md) | `lms_stripe` | E-05 | IN_PROGRESS | — |
| S-PAYPAL | PayPal gateway — dead code (wrong interface, no annotation) | module | [lms_paypal.md](../C-Design/C05-modules/lms_paypal.md) | `lms_paypal` | E-05 | STUB | — |
| S-WISHLIST | Course wishlist toggle/status JSON API + page | module | [lms_wishlist.md](../C-Design/C05-modules/lms_wishlist.md) | `lms_wishlist` | E-05 | DONE | G4 pass |
| S-BUNDLE | Course bundle entity (data model only, no checkout integration) | module | [lms_bundle.md](../C-Design/C05-modules/lms_bundle.md) | `lms_bundle` | E-05 | STUB | — |
| S-CERTIFICATE | Auto-issuance on QuizPassedEvent, mPDF generation, public verification | module | [lms_certificate.md](../C-Design/C05-modules/lms_certificate.md) | `lms_certificate` | E-06 | DONE | G4 pass |
| S-FORUM | Course discussion forums with threaded replies | module | [lms_forum.md](../C-Design/C05-modules/lms_forum.md) | `lms_forum` | E-07 | IN_PROGRESS | — |
| S-RATING | Course star ratings and reviews (3 routes missing; admin CRUD partial) | module | [lms_rating.md](../C-Design/C05-modules/lms_rating.md) | `lms_rating` | E-07 | IN_PROGRESS | — |
| S-NOTIFICATION | In-app notification system (email/push channels are stubs) | module | [lms_notification.md](../C-Design/C05-modules/lms_notification.md) | `lms_notification` | E-07 | IN_PROGRESS | — |
| S-ZOOM | Zoom Server-to-Server OAuth, meeting lifecycle, Web SDK join, AI agenda | module | [lms_zoom.md](../C-Design/C05-modules/lms_zoom.md) | `lms_zoom` | E-08 | IN_PROGRESS | — |
| S-AI | AI provider plugin (OpenAI/Gemini full; Pollinations image-only), CourseBuilder queue worker | module | [lms_ai.md](../C-Design/C05-modules/lms_ai.md) | `lms_ai` | E-08 | IN_PROGRESS | — |
| S-ANALYTICS | Internal reporting: course/user/quiz analytics with raw SQL + CSV export | module | [lms_analytics.md](../C-Design/C05-modules/lms_analytics.md) | `lms_analytics` | E-08 | DONE | G4 pass |
| S-BADGE | Badge + UserBadge entities — zero award logic (near-stub) | module | [lms_badge.md](../C-Design/C05-modules/lms_badge.md) | `lms_badge` | E-09 | STUB | — |
| S-MOBILE | Flutter mobile backend: `/api/v1/*` (22 routes, 3 controllers, custom bearer token + OTP) | module | [lms_mobile_api.md](../C-Design/C05-modules/lms_mobile_api.md) | `lms_mobile_api` | E-09 | IN_PROGRESS | — |
| S-SEC-HARDENING | API input validation, access control audit, CSRF, security headers, secrets extraction from config | nfr | [B02-technical-requirements.md](../B-Requirements/B02-technical-requirements.md) | cross-cutting | E-10 | PLANNED | — |
| S-PAYMENT-HARDENING | Stripe/PayPal webhook endpoints + signature verification; idempotency keys; atomic payment transactions | nfr | [B02-technical-requirements.md](../B-Requirements/B02-technical-requirements.md) | `lms_payment`, `lms_stripe`, `lms_order` | E-10 | PLANNED | — |
| S-AI-HARDENING | AI pipeline rollback/compensation on partial failure; dead PayPal module cleanup | nfr | [B02-technical-requirements.md](../B-Requirements/B02-technical-requirements.md) | `lms_ai`, `lms_paypal` | E-10 | PLANNED | — |
| S-INFRA-HARDENING | Redis cache strategy, N+1 query fixes, static-to-DI service refactor, logging, forum debug cleanup | nfr | [B02-technical-requirements.md](../B-Requirements/B02-technical-requirements.md) | cross-cutting | E-10 | PLANNED | — |
| S-UI-PUBLIC | Public surface polish: homepage, catalog, course detail, search, auth forms, SEO | cross-cutting | [C07-sitemap.md](../C-Design/C07-sitemap.md) | `lms_course`, `lms_blog` | E-11 | PLANNED | — |
| S-UI-LEARN | Learning surface polish: lesson view, quiz UX, dashboard, my-courses, certificates | cross-cutting | [C07-sitemap.md](../C-Design/C07-sitemap.md) | `lms_progress`, `lms_quiz` | E-11 | PLANNED | — |
| S-UI-MANAGE | Workspace/admin polish: CRUD pages, grading queue, notification broadcast, settings UI | cross-cutting | [C07-sitemap.md](../C-Design/C07-sitemap.md) | `lms_course` (admin shell) | E-11 | PLANNED | — |
| S-UI-MOBILE | Flutter app UX: 33-screen audit, API contract hardening, mobile API OpenAPI spec | cross-cutting | [lms_mobile_api.md](../C-Design/C05-modules/lms_mobile_api.md) | `lms_mobile_api` | E-11 | PLANNED | — |

**Story Status Values:** DONE | IN_PROGRESS | STUB | PLANNED | BLOCKED | DEFERRED

---

## Progress Dashboard

### Per-Epic Counts

| Epic | Total Stories | DONE | IN_PROGRESS | STUB | PLANNED | BLOCKED | CP4 Status |
|------|--------------|------|-------------|------|---------|---------|------------|
| E-01 Foundation | 4 | 4 | 0 | 0 | 0 | 0 | PASSED |
| E-02 Content | 5 | 5 | 0 | 0 | 0 | 0 | PASSED |
| E-03 Assessment | 2 | 2 | 0 | 0 | 0 | 0 | PASSED |
| E-04 Progress | 2 | 2 | 0 | 0 | 0 | 0 | PASSED |
| E-05 Commerce | 7 | 4 | 2 | 2 | 0 | 0 | PARTIAL |
| E-06 Certificate | 1 | 1 | 0 | 0 | 0 | 0 | PASSED |
| E-07 Communication | 3 | 0 | 3 | 0 | 0 | 0 | PARTIAL |
| E-08 Integration & AI | 3 | 1 | 2 | 0 | 0 | 0 | PARTIAL |
| E-09 Additional & Mobile | 2 | 0 | 1 | 1 | 0 | 0 | PARTIAL |
| E-10 Hardening | 4 | 0 | 0 | 0 | 4 | 0 | NOT_STARTED |
| E-11 UI Polish | 4 | 0 | 0 | 0 | 4 | 0 | NOT_STARTED |
| **Total** | **37** | **19** | **8** | **3** | **8** | **0** | — |

**Overall Progress:** 19/37 Stories DONE (51%). 8 IN_PROGRESS (partial). 3 STUB (dead/near-stub). 8 PLANNED (not started). Phase 4: IN PROGRESS.

### Story Status Notes (code-grounded)

- **DONE = as-built** — all controllers, services, entities, routes functional; no stubs or TODOs in critical paths.
- **IN_PROGRESS** — core entity/data model complete; specific sub-features are stubs or have known runtime bugs.
- **STUB** — entity/plugin scaffolding exists; zero business logic. Specifically: `lms_paypal` (wrong interface, no annotation, never discovered by plugin manager — dead code), `lms_bundle` (entity only, no checkout integration), `lms_badge` (entity + criteria JSON format documented but no award logic exists anywhere).

---

## Epic Dependency Graph

```
E-01 (Foundation)
  ├── E-02 (Content)
  │     ├── E-04 (Progress)
  │     │     └── E-06 (Certificate)  ← also depends on E-03
  │     ├── E-07 (Communication)
  │     └── E-08 (Integration & AI)   ← also depends on E-03
  ├── E-03 (Assessment)
  │     ├── E-04 (Progress)
  │     └── E-06 (Certificate)
  └── E-05 (Commerce)
        └── E-09 (Additional & Mobile) ← also depends on E-02, E-03, E-04

E-01..E-09 → E-10 (Hardening)
E-10 → E-11 (UI Polish)
```

---

## Intra-Epic Story Dependencies

| Epic | Story | Depends on (inside same Epic) |
|------|-------|-------------------------------|
| E-05 | S-ORDER | S-PAYMENT (plugin host must exist first) |
| E-05 | S-STRIPE | S-PAYMENT (StripeGateway extends PaymentGatewayBase from lms_payment) |
| E-05 | S-PAYPAL | S-PAYMENT (lms_paypal.info.yml dep: lms_payment) |
| E-08 | S-ZOOM | S-AI (lms_zoom depends on lms_ai for agenda/summary generation) |
| E-10 | S-PAYMENT-HARDENING | S-SEC-HARDENING (security baseline must pass first) |
| E-10 | S-AI-HARDENING | S-SEC-HARDENING |

---

## Module Registry

Canonical 28-module list (27 `lms_*` + `open_switcher`). Reflects C05 status.

| Module | Service/Epic | Purpose (one line) | Status | Related Story |
|--------|-------------|-------------------|--------|---------------|
| `lms_base` | E-01 | Shared LmsHelper service, taxonomy vocab creation (8 vocabs), email-login alter, demo-content Drush command | DONE | S-BASE |
| `lms_content_tools` | E-01 | 3-step entity clean, 6-section seed wizard, 13-type AI-generated content importer | DONE | S-TOOLS |
| `open_switcher` | E-01 | "Open Platform" user impersonation utility (package ≠ EverLMS) | DONE | S-SWITCHER |
| `lms_course` | E-02 | Course entity + admin shell (catalog, homepage, workspace CRUD shell for 8 entity types) | DONE | S-COURSE |
| `lms_chapter` | E-02 | Chapter entity (thin: groups lessons within a course, no own routes) | DONE | S-CHAPTER |
| `lms_lesson` | E-02 | Lesson entity (9 content types: video/text/quiz/h5p/scorm/pdf/pptx/zoom/mixed) | DONE | S-LESSON |
| `lms_blog` | E-02 | Dual-track: `lms_article` entity backs `/manage/blog`; frontend `/news` reads `node:blog` | DONE | S-BLOG |
| `lms_faq` | E-02 | FAQ entity: 3-scope (global/course/lesson), entity-only module | DONE | S-FAQ |
| `lms_question` | E-03 | Question bank: `lms_question` (8 bundles/`lms_question_type` config entity), `lms_answer`, `lms_matching_pair` | DONE | S-QUESTION |
| `lms_quiz` | E-03 | Quiz engine: take/submit/result routes, static `GradingStrategyFactory` + 8 strategy classes, `QuizPassedEvent` | DONE | S-QUIZ |
| `lms_progress` | E-04 | Enrollment, lesson-view UI, progress cascade, dashboard, my-courses | DONE | S-PROGRESS |
| `lms_note` | E-04 | Student notes per lesson: save/delete/get JSON API + my-notes listing | DONE | S-NOTE |
| `lms_order` | E-05 | Checkout flow (course + membership), Stripe PaymentIntent, bank-transfer, auto-enrollment on completion | DONE | S-ORDER |
| `lms_membership` | E-05 | Membership plans + user subscriptions, cron expiry, access guard on `/learn/` | DONE | S-MEMBERSHIP |
| `lms_payment` | E-05 | Plugin host: `PaymentGatewayManager`, `@PaymentGateway` annotation. BankTransfer real; PayPal/ZaloPay stubs | IN_PROGRESS | S-PAYMENT |
| `lms_stripe` | E-05 | Stripe PaymentIntent gateway (processPayment real; handleWebhook stub — no signature verification) | IN_PROGRESS | S-STRIPE |
| `lms_paypal` | E-05 | Dead code: wrong interface, no `@PaymentGateway` annotation, never discovered by plugin manager | STUB | S-PAYPAL |
| `lms_wishlist` | E-05 | Course wishlist toggle/status JSON API + my-wishlist page | DONE | S-WISHLIST |
| `lms_bundle` | E-05 | Course bundle entity only — no checkout integration, no controllers | STUB | S-BUNDLE |
| `lms_certificate` | E-06 | Auto-issuance via `lms_quiz.passed` event, mPDF generation, public verification, admin templates | DONE | S-CERTIFICATE |
| `lms_forum` | E-07 | Course discussion forums with threaded replies — `ForumController::viewTopic` is dead code with `throw new \Exception("HARD DEBUG!")` | IN_PROGRESS | S-FORUM |
| `lms_rating` | E-07 | Course star ratings: `RatingForm` and admin CRUD real; 3 runtime routes missing (`lms_rating.collection`, `toggle_approve`, `toggle_featured`) → `RouteNotFoundException` | IN_PROGRESS | S-RATING |
| `lms_notification` | E-07 | In-app notification delivery complete; email = TODO (only logs); push = foundation only (logs only) | IN_PROGRESS | S-NOTIFICATION |
| `lms_zoom` | E-08 | Zoom Server-to-Server OAuth, meeting lifecycle on lesson save, Web SDK join page, AI agenda, transcript summary cron (VTT download stub) | IN_PROGRESS | S-ZOOM |
| `lms_ai` | E-08 | `AiProviderManager` + OpenAI/Gemini (full) + Pollinations (image-only); CourseBuilder queue worker; no rollback on partial failure | IN_PROGRESS | S-AI |
| `lms_analytics` | E-08 | Internal reporting: course/user/quiz SQL analytics + CSV export (no GA4 despite module description) | DONE | S-ANALYTICS |
| `lms_badge` | E-09 | Badge + UserBadge entities; `field_criteria` documents JSON format but zero award logic implemented | STUB | S-BADGE |
| `lms_mobile_api` | E-09 | Flutter backend: 22 `/api/v1/` routes, 3 controllers, custom keyvalue bearer token + OTP; `lms_quiz`/`lms_wishlist` deps undeclared in `.info.yml` | IN_PROGRESS | S-MOBILE |

---

## Known Issues Affecting Story Status

The following are code-grounded issues that block story completion. Canonical tracker: `G02-risk-register.md` (planned, layer not yet materialized) (planned, layer G — not yet materialized).

| Issue | Affected Story | Severity | Description |
|-------|---------------|----------|-------------|
| FIND-001 | S-STRIPE, S-PAYMENT-HARDENING | P0 | No Stripe webhook endpoint; no signature verification — `handleWebhook()` is a stub |
| FIND-002 | S-PAYPAL, S-PAYMENT-HARDENING | P0 | `lms_paypal\PayPalGateway` implements wrong interface + no `@PaymentGateway` annotation — never discovered by plugin manager |
| FIND-003 | S-FORUM | P0 | `ForumController::viewTopic()` opens with `throw new \Exception("HARD DEBUG!")` — dead code, 500 if ever routed |
| FIND-004 | S-FORUM | P1 | `LmsForumController::viewTopic()` has debug `addMessage("FRESH CONTROLLER LOADED!")` in production |
| FIND-005 | S-RATING | P1 | 3 missing routes: `lms_rating.collection`, `lms_rating.toggle_approve`, `lms_rating.toggle_featured` → `RouteNotFoundException` at runtime |
| FIND-006 | S-NOTIFICATION | P1 | Email and push notification channels are TODO stubs — only `in_app` channel delivers |
| FIND-007 | S-SEC-HARDENING | P0 | All third-party secrets (OpenAI, Gemini, Stripe, Zoom, PayPal, Google RSA key) stored in **plaintext committed config YAML** — «secret in config — see security risk» |
| FIND-008 | S-AI-HARDENING | P1 | AI pipeline has no rollback — partial entity creation on failure leaves DB inconsistent |
| FIND-009 | S-MOBILE | P1 | `lms_mobile_api` imports `GradingStrategyFactory` + `QuizPassedEvent` (from `lms_quiz`) and queries `lms_wishlist` storage, but neither is declared in `.info.yml` dependencies |
| FIND-010 | S-STRIPE | P1 | `CheckoutForm::buildForm()` calls `\Stripe\PaymentIntent::create()` on every form build — one PaymentIntent per page load, not per intent to pay |
| FIND-011 | S-CERTIFICATE | P2 | `lms_user_certificate.field_expiry_date` is declared as `datetime` (date-only) but prior draft noted it was varchar — verify actual schema |
| FIND-012 | S-ZOOM | P2 | `ZoomAiService::processSummaries()` VTT download is a hardcoded simulated string, not a real HTTP fetch |

---

## Workflow Buckets (`D04-tasks/`)

```text
backlog/      # planned, not yet active
todo/         # ready for SA + Agent_Implementer
in-progress/  # active
blocked/      # waiting on decision or dependency
in-review/    # implementation done, review pending
done/         # accepted
closed/       # non-done closure, with rationale
```

Tasks from the prior draft (`D01-dev-overview.md` TASK-001 through TASK-241) are pre-staged as backlog items. P0 tasks (FIND-001..003, FIND-007) should move to `todo/` before any E-10 Hardening sprint begins.

---

## Coverage Status

| Family | Coverage | Notes |
|--------|----------|-------|
| B-Requirements → Stories | ~85% | Core features covered; VN market payments (VNPay/MoMo), GDPR, multi-tenancy deferred |
| C05 module specs (28 modules) | 100% | All 28 modules have a Story in registry |
| C07 sitemap pages (137 routes) | ~75% | Public + Manage + App routes covered by existing Stories; SEO/sitemap.xml/legal pages in S-UI-PUBLIC |
| C06 API specs | ~60% | Legacy `/api/lms/*` + `/api/v1/*` covered by S-MOBILE; formal OpenAPI spec not yet written |
| B05 acceptance criteria | ~50% | Core entity ACs covered; payment/badge/email/webhook ACs not yet verified |
| B06 integration requirements | ~40% | Stripe real, Zoom real, OpenAI/Gemini real; PayPal/ZaloPay stubs; VNPay/MoMo absent |
| NFR / E10 hardening | ~20% | Security hardening PLANNED; no test suite; no Redis cache strategy in place |

---

## Source-of-Truth Chain

1. **Code ground truth** (primary) — `a 2026-06-20 code scan of web/modules/custom/` (scouted 2026-06-20) + repo at `/home/hainn/projects/everlms/web/modules/custom/`.
2. **C05 module catalog** — [`C-Design/C05-modules/_index.md`](../C-Design/C05-modules/_index.md) and per-module files — per-module design-of-record.
3. **C-Design layer** — [`C04-entities/`](../C-Design/C04-entities/), [`C06-apis/`](../C-Design/C06-apis/), [`C07-sitemap.md`](../C-Design/C07-sitemap.md) — entity, API, and route contracts.
4. **B-Requirements** — [`B01`](../B-Requirements/B01-project-requirements.md), [`B02`](../B-Requirements/B02-technical-requirements.md), [`B03-features/`](../B-Requirements/B03-features/), [`B05-acceptance-criteria/`](../B-Requirements/B05-acceptance-criteria/) — requirements driving Stories.
5. **D02 / D03 / D04** — [`D02-epics/`](D02-epics/), [`D03-stories/`](D03-stories/) — Epic and Story detail files; `D04-tasks/` (backlog/todo/in-progress/…) — Task queue.
6. **D08 sessions** — [`D08-sessions/`](D08-sessions/) — session transcripts and rationale history.
7. **A07 evolution log** — [`A-Project-Information/A07-evolution-log.md`](../A-Project-Information/A07-evolution-log.md) — architectural decision history.

---

*Rebuilt 2026-06-20 from code ground truth. Previous draft (docs-draft/D-Development/D01-dev-overview.md) is superseded — it used deprecated Group/Unit vocabulary and stale DONE/SPEC labels.*
