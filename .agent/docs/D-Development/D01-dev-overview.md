# D01 — Development Overview

> Build order, group/unit map, progress dashboard.

---

## Build Order

| Order | Group ID | Group Name | Units | Dependencies | Est. Sprints | Status |
|-------|----------|-----------|-------|-------------|-------------|--------|
| 1 | G-FOUNDATION | Foundation & Theme | U-001 – U-004 | None | 1 | ✅ DONE |
| 2 | G-CONTENT | Content Layer | U-005 – U-012 | G-FOUNDATION | 2 | ✅ DONE (scaffold) |
| 3 | G-ASSESSMENT | Assessment Engine | U-013 – U-020 | G-FOUNDATION | 2 | ✅ DONE (scaffold) |
| 4 | G-PROGRESS | Progress Tracking | U-021 – U-024 | G-CONTENT, G-ASSESSMENT | 1 | ✅ DONE (scaffold) |
| 5 | G-COMMERCE | Commerce & Payment | U-025 – U-034 | G-FOUNDATION | 2 | ✅ DONE (scaffold) |
| 6 | G-CERTIFICATE | Certificate System | U-035 – U-038 | G-ASSESSMENT, G-PROGRESS | 1 | ✅ DONE (scaffold) |
| 7 | G-COMMUNICATION | Communication | U-039 – U-044 | G-FOUNDATION, G-CONTENT | 1 | ✅ DONE (scaffold) |
| 8 | G-INTEGRATION | Integration & AI | U-045 – U-050 | G-CONTENT, G-ASSESSMENT | 1 | ✅ DONE (scaffold) |
| 9 | G-ADDITIONAL | Additional Features | U-051 – U-056 | G-FOUNDATION | 1 | ✅ DONE (scaffold) |
| 10 | G-HARDENING | Quality & Hardening | U-057 – U-070 | ALL above | 2-3 | 🔴 NOT_STARTED |
| 11 | G-UI-POLISH | UI/UX Polish | U-071 – U-084 | G-HARDENING | 2 | 🔴 NOT_STARTED |

---

## Unit Registry

### G-FOUNDATION — Foundation & Theme

| Unit ID | Name | Status | Gate | Feature Flag |
|---------|------|--------|------|-------------|
| U-001 | DDEV + Drupal 11.3 setup | DONE | G4 pass | — |
| U-002 | Contrib modules integration | DONE | G4 pass | — |
| U-003 | lms_theme (Bootstrap 5.3 dark premium) | DONE | G4 pass | — |
| U-004 | lms_base (taxonomies + LmsHelper) | DONE | G4 pass | — |

### G-CONTENT — Content Layer

| Unit ID | Name | Status | Gate | Feature Flag |
|---------|------|--------|------|-------------|
| U-005 | Course entity + handlers | DONE | G4 pass | — |
| U-006 | Chapter entity + handlers | DONE | G4 pass | — |
| U-007 | Lesson entity (7 types) + handlers | DONE | G4 pass | — |
| U-008 | Course CRUD controller + templates | DONE | G4 pass | — |
| U-009 | Catalog page + search/filter | DONE | G4 pass | — |
| U-010 | Category management | DONE | G4 pass | — |
| U-011 | Course detail page | DONE | G4 pass | — |
| U-012 | Homepage controller + template | DONE | G4 pass | — |

### G-ASSESSMENT — Assessment Engine

| Unit ID | Name | Status | Gate | Feature Flag |
|---------|------|--------|------|-------------|
| U-013 | Quiz entity | DONE | G4 pass | — |
| U-014 | Question entity (8 bundles) | DONE | G4 pass | — |
| U-015 | Answer + MatchingPair entities | DONE | G4 pass | — |
| U-016 | QuestionHandlerManager (8 strategies) | DONE | G4 pass | — |
| U-017 | QuizResult + QuestionResponse entities | DONE | G4 pass | — |
| U-018 | Quiz take controller + template | DONE | G4 pass | — |
| U-019 | Quiz result view + template | DONE | G4 pass | — |
| U-020 | Grading service (auto + essay route) | DONE | G4 pass | — |

### G-PROGRESS — Progress Tracking

| Unit ID | Name | Status | Gate | Feature Flag |
|---------|------|--------|------|-------------|
| U-021 | UserCourse/UserLesson entities | DONE | G4 pass | — |
| U-022 | Progress cascade service | DONE | G4 pass | — |
| U-023 | Dashboard controller + template | DONE | G4 pass | — |
| U-024 | My Courses + Continue learning | DONE | G4 pass | — |

### G-COMMERCE — Commerce & Payment

| Unit ID | Name | Status | Gate | Feature Flag |
|---------|------|--------|------|-------------|
| U-025 | Order + OrderItem entities | DONE | G4 pass | — |
| U-026 | MembershipPlan + UserMembership entities | DONE | G4 pass | — |
| U-027 | PaymentGatewayInterface + PaymentManager | DONE | G4 pass | — |
| U-028 | StripeGateway (stub) | DONE | G4 pass | — |
| U-029 | PayPalGateway (stub) | DONE | G4 pass | — |
| U-030 | Checkout workflow | SPEC | — | — |
| U-031 | Webhook handler + signature verification | SPEC | — | — |
| U-032 | Coupon system | SPEC | — | — |
| U-033 | Membership lifecycle (activate/expire/renew) | SPEC | — | — |
| U-034 | Order management (admin) | SPEC | — | — |

### G-CERTIFICATE — Certificate System

| Unit ID | Name | Status | Gate | Feature Flag |
|---------|------|--------|------|-------------|
| U-035 | CertificateTemplate + UserCertificate entities | DONE | G4 pass | — |
| U-036 | Certificate issuance service (auto trigger) | SPEC | — | — |
| U-037 | PDF generation (dompdf) | SPEC | — | — |
| U-038 | Public verification endpoint | SPEC | — | — |

### G-COMMUNICATION — Communication

| Unit ID | Name | Status | Gate | Feature Flag |
|---------|------|--------|------|-------------|
| U-039 | Notification entity (6 types) + controller | DONE | G4 pass | — |
| U-040 | ForumTopic + ForumReply entities | DONE | G4 pass | — |
| U-041 | Rating entity (1-5 stars) | DONE | G4 pass | — |
| U-042 | Notification dispatch service | SPEC | — | — |
| U-043 | Forum thread UI + permissions | SPEC | — | — |
| U-044 | Email notification integration | SPEC | — | — |

### G-INTEGRATION — Integration & AI

| Unit ID | Name | Status | Gate | Feature Flag |
|---------|------|--------|------|-------------|
| U-045 | ZoomService (API stub) | DONE | G4 pass | — |
| U-046 | AnalyticsService (3 queries) | DONE | G4 pass | — |
| U-047 | AI provider plugin architecture | SPEC | — | FF_FEAT_AI_PROVIDER |
| U-048 | AI course builder | SPEC | — | FF_FEAT_AI_BUILDER |
| U-049 | Zoom meeting integration | SPEC | — | FF_FEAT_ZOOM |
| U-050 | Analytics dashboards (admin/manager) | SPEC | — | — |

### G-ADDITIONAL — Additional Features

| Unit ID | Name | Status | Gate | Feature Flag |
|---------|------|--------|------|-------------|
| U-051 | Wishlist entity | DONE | G4 pass | — |
| U-052 | CourseBundle entity | DONE | G4 pass | — |
| U-053 | Note entity (video timestamp) | DONE | G4 pass | — |
| U-054 | Badge + UserBadge entities | DONE | G4 pass | — |
| U-055 | FAQ entity (3-scope) | DONE | G4 pass | — |
| U-056 | Blog entity | DONE | G4 pass | — |

### G-HARDENING — Quality & Hardening (P0 REMEDIATION)

| Unit ID | Name | Status | Gate | Feature Flag |
|---------|------|--------|------|-------------|
| U-057 | API input validation — all write endpoints | SPEC | — | — |
| U-058 | Access control hardening — all routes | SPEC | — | — |
| U-059 | Progress race condition fix (DB lock/transaction) | SPEC | — | — |
| U-060 | Webhook signature verification (Stripe) | SPEC | — | — |
| U-061 | Webhook signature verification (PayPal) | SPEC | — | — |
| U-062 | Idempotency keys for payment callbacks | SPEC | — | — |
| U-063 | AI rollback + compensation logic | SPEC | — | FF_FEAT_AI_PROVIDER |
| U-064 | Static service call cleanup (\Drupal:: → DI) | SPEC | — | — |
| U-065 | Entity access handler audit | SPEC | — | — |
| U-066 | CSRF protection audit | SPEC | — | — |
| U-067 | Error handling standardization | SPEC | — | — |
| U-068 | Logging for critical operations | SPEC | — | — |
| U-069 | Cache strategy implementation (Redis) | SPEC | — | — |
| U-070 | Performance optimization (N+1 queries, lazy load) | SPEC | — | — |

### G-UI-POLISH — UI/UX Polish

| Unit ID | Name | Status | Gate | Feature Flag |
|---------|------|--------|------|-------------|
| U-071 | Homepage redesign (final iteration) | SPEC | — | — |
| U-072 | Course detail page polish | SPEC | — | — |
| U-073 | Lesson view UX improvements | SPEC | — | — |
| U-074 | Quiz taking UX | SPEC | — | — |
| U-075 | Dashboard redesign | SPEC | — | — |
| U-076 | Management pages CRUD polish | SPEC | — | — |
| U-077 | Checkout flow UI | SPEC | — | — |
| U-078 | Certificate detail page | SPEC | — | — |
| U-079 | Mobile responsive audit | SPEC | — | — |
| U-080 | Forum UI | SPEC | — | — |
| U-081 | Notification center UI | SPEC | — | — |
| U-082 | Profile page UI | SPEC | — | — |
| U-083 | Search results UX | SPEC | — | — |
| U-084 | Error/empty state pages | SPEC | — | — |

---

## Progress Tracker

| Group | Total Units | DONE | SPEC | IN_PROGRESS | BLOCKED | CP4 Status |
|-------|------------|------|------|-------------|---------|------------|
| G-FOUNDATION | 4 | 4 | 0 | 0 | 0 | ✅ PASSED |
| G-CONTENT | 8 | 8 | 0 | 0 | 0 | ✅ PASSED |
| G-ASSESSMENT | 8 | 8 | 0 | 0 | 0 | ✅ PASSED |
| G-PROGRESS | 4 | 4 | 0 | 0 | 0 | ✅ PASSED |
| G-COMMERCE | 10 | 5 | 5 | 0 | 0 | ⏳ PARTIAL |
| G-CERTIFICATE | 4 | 1 | 3 | 0 | 0 | ⏳ PENDING |
| G-COMMUNICATION | 6 | 3 | 3 | 0 | 0 | ⏳ PENDING |
| G-INTEGRATION | 6 | 2 | 4 | 0 | 0 | ⏳ PENDING |
| G-ADDITIONAL | 6 | 6 | 0 | 0 | 0 | ✅ PASSED |
| G-HARDENING | 14 | 0 | 14 | 0 | 0 | 🔴 NOT_STARTED |
| G-UI-POLISH | 14 | 0 | 14 | 0 | 0 | 🔴 NOT_STARTED |

**Overall Progress:** 41/84 units done (49%). Phase 4: IN PROGRESS.

---

## Task ↔ Unit ↔ RISK / FIND / PROTO Map

> Last updated: 2026-04-26. Companion table to the kanban in `D-Development/D05-tasks/`. Total tasks: **150** (74 → 150 expanded with test depth, devops, security expansion, a11y/i18n, course/commerce edge cases, gamification, marketing tools).

### Spike-then-implement (Hardening) — Run spikes FIRST

| TASK | Status | Unit (precedes) | Priority | Resolves | Blocks |
|------|--------|-----------------|----------|----------|--------|
| **TASK-049** | backlog | U-059 | P0 | RISK-002, DEC-003 | TASK-017 |
| **TASK-050** | backlog | U-060/061/062 | P0 | RISK-004, FIND-001/002/005/009 | TASK-029, TASK-030, TASK-031 |
| **TASK-051** | backlog | U-063 | P1 | RISK-003, FIND-003, FIND-008 | TASK-032, TASK-054 |
| **TASK-052** | backlog | (new) | P2 | SCORM/xAPI scope | (new units after spike) |
| **TASK-053** | backlog | extends U-037 | P3 | C01 mPDF mismatch | independent |

### Hardening implementation (after spikes)

| TASK | Unit | Priority | Resolves |
|------|------|----------|----------|
| TASK-015 | U-057 | P0 | RISK-001 (API input validation) |
| TASK-016 | U-058 | P0 | FIND-004 (access control) |
| TASK-017 | U-059 | P0 | RISK-002 (race condition impl) |
| TASK-024 | U-066 | P1 | CSRF audit |
| TASK-029 | U-060 | P0 | FIND-001 (Stripe webhook impl) |
| TASK-030 | U-061 | P0 | FIND-002 (PayPal webhook impl) |
| TASK-031 | U-062 | P0 | FIND-005 (idempotency + atomic txn) |
| TASK-032 | U-063 | P1 | RISK-003, FIND-003 (AI rollback) |
| TASK-018 | U-064 | P2 | Static service cleanup |
| TASK-019 | U-065 | P1 | Entity access audit |
| TASK-020 | U-068 | P2 | Critical logging |
| TASK-021 | U-069 | P2 | Cache strategy (Redis) |
| TASK-022 | U-070 | P2 | Performance (N+1, lazy load) |
| TASK-025 | U-067 | P2 | Error handling standardization |

### FIND cleanup (newly added)

| TASK | Priority | Resolves |
|------|----------|----------|
| **TASK-054** | P1 | FIND-008 (Gemini API key URL → header) |
| **TASK-055** | P1 | FIND-009 (PayPalGateway dedup) |
| **TASK-056** | P1 | FIND-013 (notification anonymous endpoint) |
| **TASK-057** | P3 | FIND-006 (RatingController dead code) |
| **TASK-058** | P2 | FIND-007 (sitemap missing routes) |

### Commerce / Communication / UI-Polish (existing tasks)

- Commerce: TASK-001 (U-030), TASK-002 (U-031), TASK-003 (U-032), TASK-004 (U-033), TASK-005 (U-034)
- Communication: TASK-009 (U-042), TASK-011 (U-044)
- UI-Polish: TASK-026 (U-077), TASK-028 (U-079), TASK-033 (U-071), TASK-034 (U-072), TASK-035 (U-073), TASK-036 (U-074), TASK-037 (U-075), TASK-038 (U-078), TASK-039 (U-080), TASK-040 (U-081), TASK-041 (U-082), TASK-042 (U-083), TASK-043 (U-084), TASK-044 (learning paths), TASK-045 (instructor pages), TASK-046 (public content), TASK-047 (additional entity UI), TASK-048 (student portal pages)

### Public sitemap & frontend completion (newly added)

| TASK | Priority | Sitemap / Feature covered |
|------|----------|---------------------------|
| **TASK-059** | P1 | `/login`, `/register`, `/forgot-password`, email verify (WF-007); brand Drupal user-module forms with Bootstrap 5.3 dark |
| **TASK-060** | P2 | `/search` + Drupal Search API setup (DB backend v1, Solr-ready v2 per INT-008); index Course/Lesson/Blog/FAQ |
| **TASK-061** | P2 | sitemap.xml, robots.txt, metatag, JSON-LD Course schema (per C07 §SEO) |
| **TASK-062** | P2 | GDPR cookie consent banner + `/privacy`, `/terms`, `/cookies` legal pages |
| **TASK-071** | P2 | GA4 frontend tracking (8 events) — gated by TASK-062 consent |

### Management & admin completion (newly added)

| TASK | Priority | Sitemap entry covered |
|------|----------|----------------------|
| **TASK-063** | P2 | `/manage/badges` admin CRUD + award rules (extends U-054) |
| **TASK-064** | P2 | `/manage/notifications/send` broadcast/targeted UI |
| **TASK-065** | P1 | `/manage/grading` essay grading queue (extends U-020) |
| **TASK-066** | P2 | `/manage/settings/{payment,zoom,ai,email}` config UI |

### Quality / Test / Infra (newly added)

| TASK | Priority | Coverage |
|------|----------|----------|
| **TASK-067** | P1 | E03 test definitions for all 16 AC files (TEST-006..TEST-NN) |
| **TASK-068** | P1 | E2E browser test suite (Playwright + WF-001..004 golden flows) |
| **TASK-069** | P1 | GitLab CI `.gitlab-ci.yml` with G1-G4 quality gates |
| **TASK-074** | P2 | `lms_mobile_api` audit + hardening + OpenAPI spec at I01/05-mobile-api |

### Release & operations (newly added)

| TASK | Priority | Coverage |
|------|----------|----------|
| **TASK-070** | P2 | U-Userguide content completion (16 feature guides + 3 tutorials → U09-html) |
| **TASK-072** | P3 | Mobile app strategy decision (PWA vs React Native vs deferred) → PROTO-006 |
| **TASK-073** | P1 | F02 release checklist for v1.0 (pre + post release) |

### Test depth (newly added, TASK-075..086)

| TASK | Priority | Coverage |
|------|----------|----------|
| TASK-075 | P1 | Kernel test G-FOUNDATION + G-CONTENT entities |
| TASK-076 | P1 | Kernel test G-ASSESSMENT (8 question handlers) |
| TASK-077 | P1 | Kernel test G-PROGRESS (cascade + concurrency) |
| TASK-078 | P1 | Kernel test G-COMMERCE (mock gateways) |
| TASK-079 | P2 | Kernel test G-CERTIFICATE |
| TASK-080 | P2 | Kernel test G-COMMUNICATION |
| TASK-081 | P1 | Functional/Browser test top 20 controllers |
| TASK-082 | P2 | API contract test (I01 + lms_mobile_api) |
| TASK-083 | P2 | Visual regression baseline (BackstopJS) |
| TASK-084 | P1 | Performance benchmark (k6) |
| TASK-085 | P1 | Accessibility test (axe-core/Playwright WCAG 2.1 AA) |
| TASK-086 | P1 | Security regression (OWASP ZAP baseline) |

### Security expansion (newly added, TASK-087..094)

| TASK | Priority | Subject |
|------|----------|---------|
| TASK-087 | P0 | External penetration test |
| TASK-088 | P1 | Internal OWASP Top 10 audit |
| TASK-089 | P1 | CVE baseline + auto-monitoring |
| TASK-090 | P1 | Security headers (CSP + HSTS + X-Frame) |
| TASK-091 | P1 | Per-endpoint rate limiting |
| TASK-092 | P1 | Login brute-force protection |
| TASK-093 | P2 | 2FA opt-in (TOTP) |
| TASK-094 | P1 | Session hardening |

### DevOps essentials (newly added, TASK-095..106)

| TASK | Priority | Subject |
|------|----------|---------|
| TASK-095 | P0 | Production hosting provider selection |
| TASK-096 | P0 | SSL/HTTPS automation (Let's Encrypt) |
| TASK-097 | P1 | DNS + CloudFlare CDN |
| TASK-098 | P0 | DB backup automation (RPO ≤1h) |
| TASK-099 | P1 | DB restore drill (RTO ≤4h) |
| TASK-100 | P0 | Monitoring agent (Grafana) |
| TASK-101 | P1 | Log aggregation (Loki) |
| TASK-102 | P1 | PHP-FPM tuning |
| TASK-103 | P1 | Nginx config hardening |
| TASK-104 | P1 | Redis config tuning |
| TASK-105 | P1 | Healthcheck endpoints (/health/live, /health/ready) |
| TASK-106 | P0 | Zero-downtime deployment automation |

### A11y / i18n (newly added, TASK-107..114)

| TASK | Priority | Subject |
|------|----------|---------|
| TASK-107 | P1 | WCAG 2.1 AA full audit |
| TASK-108 | P1 | Screen reader pass (NVDA + VoiceOver) |
| TASK-109 | P2 | Color contrast tooling in CI |
| TASK-110 | P1 | Drupal i18n infrastructure |
| TASK-111 | P1 | Vietnamese translation pass |
| TASK-112 | P2 | Date/number locale (VND + d/m/Y) |
| TASK-113 | P2 | Email template i18n |
| TASK-114 | P3 | RTL support evaluation |

### Course content edge cases (newly added, TASK-115..122)

| TASK | Priority | Subject |
|------|----------|---------|
| TASK-115 | P2 | Course prerequisites enforcement |
| TASK-116 | P2 | Course access duration / expiry |
| TASK-117 | P2 | Course versioning (draft/published) |
| TASK-118 | P2 | Bulk enroll users |
| TASK-119 | P2 | Bulk essay grading |
| TASK-120 | P2 | Course import/export (JSON/YAML) |
| TASK-121 | P2 | Course archive (soft-delete) |
| TASK-122 | P3 | Course duplicate action |

### Commerce edge cases (newly added, TASK-123..130)

| TASK | Priority | Subject |
|------|----------|---------|
| TASK-123 | P1 | Refund flow (Stripe / PayPal / ZaloPay) |
| TASK-124 | P2 | Pro-rated membership upgrade/downgrade |
| TASK-125 | P2 | Failed payment retry + dunning |
| TASK-126 | P1 | Invoice PDF generation |
| TASK-127 | P1 | VAT calculation for VN (10%/5%/0%) |
| TASK-128 | P2 | Promo pricing (time-bound) |
| TASK-129 | P2 | Multi-currency v1 (VND + USD) |
| TASK-130 | P1 | Tax-inclusive pricing toggle |

### User management (newly added, TASK-131..135)

| TASK | Priority | Subject |
|------|----------|---------|
| TASK-131 | P2 | Bulk user import CSV |
| TASK-132 | P3 | User account merge tool |
| TASK-133 | P1 | GDPR data export (Art. 20) |
| TASK-134 | P1 | GDPR right to be forgotten (Art. 17) |
| TASK-135 | P2 | Role hierarchy + delegation |

### Communication enhancements (newly added, TASK-136..140)

| TASK | Priority | Subject |
|------|----------|---------|
| TASK-136 | P2 | Course Q&A section |
| TASK-137 | P2 | Lesson comments thread |
| TASK-138 | P2 | @mentions system |
| TASK-139 | P2 | Email digest (weekly) |
| TASK-140 | P2 | Notification preferences UI |

### Gamification (newly added, TASK-141..144)

| TASK | Priority | Subject |
|------|----------|---------|
| TASK-141 | P3 | Leaderboard (course + global) |
| TASK-142 | P3 | Learning streaks |
| TASK-143 | P3 | Points/XP system |
| TASK-144 | P3 | Achievements system |

### Marketing / content tools (newly added, TASK-145..150)

| TASK | Priority | Subject |
|------|----------|---------|
| TASK-145 | P1 | Coupon campaigns (extends TASK-003) |
| TASK-146 | P2 | Email campaign builder |
| TASK-147 | P1 | CKEditor 5 configuration |
| TASK-148 | P1 | Media library setup |
| TASK-149 | P1 | Image style presets |
| TASK-150 | P1 | Video handling decision + impl |

### Done (9 tasks)

TASK-006 (U-036), TASK-007 (U-037), TASK-008 (U-038), TASK-010 (U-043), TASK-012 (U-047), TASK-013 (U-048), TASK-014 (U-050), TASK-023 (U-049), TASK-027 (U-076).

---

## Coverage Summary (after this audit)

| Source artefact | Total | Covered by tasks | Gap |
|-----------------|-------|------------------|-----|
| C07 sitemap pages | 84 | 84 | 0 |
| B03 features (16 files) | 16 | 16 | 0 |
| C04 entities (29) | 29 | 29 | 0 |
| C06 APIs (7 specs) | 7 | 7 | 0 |
| B05 AC (16 files) | 16 | 16 | 0 |
| B06 integrations (8) | 8 | 8 | 0 |
| C10 prototypes (5 PROTO) | 5 | 5 | 0 |
| G02 RISKs + FINDs | 14 | 14 | 0 |
| Prototype HTML (35 pages) | 35 | 35 | 0 |
| H07 observability | 6 files | 6 | 0 |
| F02 release | TEMPLATE | v1.0.0 in TASK-073 | 0 |
| Test depth (kernel/functional/contract/visual/perf/a11y/security) | 12 dimensions | 12 (TASK-075..086) | 0 |
| DevOps (hosting/SSL/CDN/backup/restore/monitoring/logs/tuning/healthcheck/deploy) | 12 areas | 12 (TASK-095..106) | 0 |
| Security expansion (pen test, OWASP, CVE, headers, rate limit, brute force, 2FA, session) | 8 areas | 8 (TASK-087..094) | 0 |
| A11y / i18n (WCAG, screen reader, contrast, infra, VI, locale, email i18n, RTL) | 8 areas | 8 (TASK-107..114) | 0 |
| Course content edge cases (prereq, expiry, version, bulk, import, archive, dup) | 8 features | 8 (TASK-115..122) | 0 |
| Commerce edge cases (refund, pro-rate, dunning, invoice, VAT, promo, multi-currency, tax-inclusive) | 8 features | 8 (TASK-123..130) | 0 |
| User mgmt (CSV, merge, GDPR export, GDPR delete, role hierarchy) | 5 features | 5 (TASK-131..135) | 0 |
| Communication (Q&A, comments, mentions, digest, prefs) | 5 features | 5 (TASK-136..140) | 0 |
| Gamification (leaderboard, streaks, XP, achievements) | 4 features | 4 (TASK-141..144) | 0 |
| Marketing/content (coupon campaigns, email campaign, CKEditor, media library, image styles, video) | 6 areas | 6 (TASK-145..150) | 0 |

---

## Recommended Sprint Slicing (150 task plan)

Khuyến nghị chạy 6 luồng song song bằng 2-3 dev tracks. Mỗi sprint ~2 tuần.

### Track A — Foundations (chạy song song với mọi track)

1. **Sprint A1 — Spike sprint (~5d):** TASK-049 + TASK-050 song song → TASK-051, TASK-052, TASK-053 nối tiếp. Chốt DEC-003, mở khoá hardening.
2. **Sprint A2 — Quality infra (~5d):** TASK-068 (Playwright), TASK-069 (CI), TASK-067 (test specs).
3. **Sprint A3 — Test depth (~10d):** TASK-075..086 (kernel/functional/contract/visual/perf/a11y/security tests).

### Track B — Hardening + Security

4. **Sprint B1 — Security quick-wins (~3d):** TASK-054..058.
5. **Sprint B2 — Hardening impl (~10d):** TASK-015 → TASK-017 → TASK-029..032 → TASK-018..025 (theo dependency).
6. **Sprint B3 — Security expansion (~8d):** TASK-087..094 (pen test setup last, before release gate).

### Track C — DevOps & Operations (chạy sớm để không block release)

7. **Sprint C1 — Production infra (~10d):** TASK-095..097 (hosting, SSL, CDN), TASK-105 (healthcheck).
8. **Sprint C2 — Persistence & observability (~10d):** TASK-098..101 (backup, restore drill, monitoring, logs).
9. **Sprint C3 — Tuning + deploy (~6d):** TASK-102..104 (FPM/Nginx/Redis), TASK-106 (deployment automation).

### Track D — Feature completion

10. **Sprint D1 — Commerce completion (~10d):** TASK-001 → TASK-002 → TASK-004 → TASK-005 → TASK-003.
11. **Sprint D2 — Public surface (~8d):** TASK-059 → TASK-060 → TASK-061 → TASK-062 → TASK-071.
12. **Sprint D3 — Admin completion (~8d):** TASK-063..066.
13. **Sprint D4 — Mobile API (~5d):** TASK-074 → TASK-072.

### Track E — Edge cases & enhancements (P2/P3, defer if scope tight)

14. **Sprint E1 — Course edge cases (~10d):** TASK-115..122.
15. **Sprint E2 — Commerce edge cases (~10d):** TASK-123..130.
16. **Sprint E3 — User mgmt (~5d):** TASK-131..135 (GDPR export/delete là P1, ưu tiên).
17. **Sprint E4 — Communication enhancements (~8d):** TASK-136..140.
18. **Sprint E5 — Gamification (P3, optional):** TASK-141..144.
19. **Sprint E6 — Marketing/content (~10d):** TASK-145..150 (CKEditor + media library là P1, prioritize).

### Track F — A11y + i18n (chạy nền)

20. **Sprint F1 — i18n infra + VI translation (~10d):** TASK-110, TASK-111, TASK-112, TASK-113.
21. **Sprint F2 — A11y audit + fixes (~5d):** TASK-107..109.
22. **Sprint F3 — RTL eval (~1d, P3):** TASK-114.

### Track G — Release prep (cuối)

23. **Sprint G1 — UX polish (~10d):** Còn lại G-UI-POLISH (TASK-033..048).
24. **Sprint G2 — Documentation (~5d):** TASK-070 (userguide).
25. **Sprint G3 — Release (~5d):** TASK-073 (checklist) → CP6 → v1.0.0 release.

**Tổng workload ước tính:** ~1,200h dev work (P0+P1+P2+P3 weighted). Với 3-4 dev parallel + Sonnet agent assistance, ~3-4 tháng tới v1.0.

---

*End of D01-dev-overview.md*
