# B01 — Project Requirements

> Business context, stakeholders, constraints, and success criteria for EverLMS.
> Layer authority: B-Requirements. Cross-links use relative paths within `.agent/docs/`.
> Status as of: 2026-06-20.

---

## Business Context

**Problem statement.** Organizations running training programs face fragmented tooling: content lives in file shares, assessments in spreadsheets, certificates issued manually, and payments handled outside the system. This creates high administrative overhead, no audit trail, and poor learner experience.

**What EverLMS solves.** EverLMS is a self-hosted, white-label Learning Management System built on Drupal 11. It provides a single platform for the full training lifecycle: structured course authoring (Course → Chapter → Lesson hierarchy), multi-format content delivery, automated assessment and grading, certificate issuance with PDF generation, and commerce (individual course sales, bundles, membership tiers). A companion Flutter mobile app (Android + iOS) extends learning to mobile devices.

**Market context.** The primary target tenant is a Vietnamese B2B training organization (seed demo: construction safety training for "Công ty Cổ phần Xây dựng Thành Đạt"). The platform is architected for multi-tenant/white-label use: the mobile app supports runtime domain switching (`SwitchDomainScreen`), and the `open_switcher` utility module enables user impersonation for operations staff.

**Business value.**
- Revenue: course sales (single / bundle / membership), payment via bank transfer (BankTransferGateway, as-built) and Stripe PaymentIntent (as-built, test mode); PayPal stub not production-ready.
- Cost reduction: AI course builder (`lms_ai`, OpenAI gpt-4o / Gemini 2.5-flash) accelerates content creation; automated certificate issuance eliminates manual PDF work.
- Learner engagement: progress tracking, forum, ratings, in-app notifications, and gamification foundation (badges — near-stub; award logic not yet implemented).

---

## Stakeholders

| ID | Stakeholder | Role | Interest | Communication |
|----|-------------|------|----------|---------------|
| SH-001 | Product / Tech Owner (Hai Nguyen) | Solution Architect & lead developer | Technical direction, architecture quality, delivery pace | Every development session |
| SH-002 | Administrator | System operator | Full site configuration, role/permission management, system health | As needed |
| SH-003 | Manager | Operations manager | User management, order/payment oversight, reporting dashboards, course approval | Weekly review |
| SH-004 | Instructor | Content creator | Course/chapter/lesson authoring, quiz design, student progress monitoring | Sprint review / UAT |
| SH-005 | Student (Learner) | End user | Course discovery, enrollment, learning progress, quiz results, certificate download, mobile app | UAT |
| SH-006 | Content Editor | CMS operator | Blog, FAQ, and node content management (article/page); pathauto, metatag | As needed |

---

## Business Requirements

| ID | Requirement | Priority | Rationale | Stakeholder |
|----|-------------|----------|-----------|-------------|
| BR-001 | Course management — CRUD for Course → Chapter → Lesson hierarchy with category/tag taxonomy | Must | Foundation of the platform; all other features depend on structured content | SH-001, SH-004 |
| BR-002 | Multi-format lesson content delivery (video, text/H5P, Zoom live session, PDF, SCORM) | Must | Diverse training formats required for corporate compliance and skills courses | SH-004 |
| BR-003 | Assessment engine with 8 question types and automatic grading (binary pass/fail; essay → pending review) | Must | Validate learner competency; required for certificate eligibility | SH-004, SH-005 |
| BR-004 | Cascading progress tracking (lesson → chapter → course) with "continue learning" UX | Must | Learners must be able to resume; managers need completion reporting | SH-005, SH-003 |
| BR-005 | Commerce — order lifecycle, coupon codes, course bundles, membership tiers | Must | Primary revenue mechanism for training organizations | SH-003 |
| BR-006 | Payment processing — bank transfer (BankTransferGateway) and Stripe PaymentIntent | Must | Accept real payments; Stripe is production-capable (test keys currently); bank transfer is live | SH-003 |
| BR-007 | Automated PDF certificate issuance triggered by quiz pass event; public verification URL | Must | Regulatory/compliance requirement for corporate training completions | SH-005, SH-003 |
| BR-008 | In-app notification system (notification bell block, unread badge); email stub for future | Should | Engagement and operational alerts; email channel not yet functional (as-built: stub) | SH-005 |
| BR-009 | Forum and community features (topics, replies); course ratings and reviews | Should | Peer learning and social proof for course quality | SH-005 |
| BR-010 | Analytics and reporting dashboards for admin, manager, and instructor roles | Should | Business insights: enrollment trends, revenue, learner performance | SH-002, SH-003 |
| BR-011 | AI course builder — AI-assisted curriculum generation and lesson content drafting (OpenAI / Gemini) | Could | Dramatically accelerates content creation; implemented but no rollback on partial failure | SH-004 |
| BR-012 | Zoom live session integration within lessons | Could | Synchronous learning capability; implemented but `generateSignature()` has a known bug (wrong service ID) | SH-004 |
| BR-013 | Gamification foundation — badge entity and award logic | Could | Learner motivation; badge entities exist but award logic is a stub (not yet implemented) | SH-005 |
| BR-014 | Mobile app (Flutter, Android + iOS) with offline content download | Must | Mobile learning is expected by modern learners; app v1.0.10+33 is production-shaped | SH-005 |
| BR-015 | White-label / multi-tenant capability — runtime domain switch, homepage skin switcher (15 skins) | Could | Commercial product differentiator; 15 skins implemented in `lms_theme`; mobile domain switch implemented | SH-001 |
| BR-016 | Security & performance hardening — secrets management, input validation, webhook signature verification, access control, caching, and NFR compliance | Must | Production readiness; current gaps: secrets committed in config (no Key module), no payment webhook verification, ~0% automated test coverage | SH-001, SH-002 |

---

## Constraints

| ID | Type | Constraint | Impact |
|----|------|-----------|--------|
| CON-001 | Technical | Drupal 11.3.5 + PHP 8.3 as the application framework | Architecture must follow Drupal's Entity API, Plugin system, config management, and Twig rendering pipeline; no non-Drupal PHP frameworks |
| CON-002 | Technical | MariaDB 10.11 (InnoDB, utf8mb4_general_ci) as the only data store | Relational schema only; no NoSQL, no Redis cache layer currently deployed; 202 DB tables as-built |
| CON-003 | Technical | Plugin-based payment gateway architecture (`PaymentGatewayManager`, `@PaymentGateway` annotation) | New payment providers must implement `PaymentGatewayInterface`; lms_paypal is dead code and cannot be the model |
| CON-004 | Technical | Bootstrap 5.3.3 loaded from CDN (jsdelivr); SCSS compiled via Dart Sass; Twig templates | No React/Vue frontend; all UI is server-rendered Twig with progressive enhancement JS; CDN dependency requires internet access for page rendering |
| CON-005 | Technical | No secrets management module in use (`key` module installed but unconfigured) | All third-party credentials currently in plaintext config YAML committed to VCS — this is a production blocker (see NFR-009 in B02) |
| CON-006 | Integration | DDEV (`everlms.ddev.site`, nginx-fpm, PHP 8.3, MariaDB 10.11) is the only supported local development environment | Collaborators must use DDEV; no Vagrant, bare-metal, or alternative Docker setups are documented |
| CON-007 | Integration | Flutter app requires a live Drupal backend at a configurable base URL | Mobile development requires backend to be running; no mock server or offline fixture layer exists in the API layer |
| CON-008 | Regulatory | No explicit data residency or GDPR configuration at this stage | Password policy module is installed; no data export/deletion automation; GDPR compliance is a target-state concern |
| CON-009 | Budget / resource | Single developer (SH-001) building the full stack | Scope must be prioritized ruthlessly; AI-assisted tooling (`lms_ai`, agent framework) is used to multiply output |

---

## Success Metrics

| ID | Metric | Target | Baseline | Measurement Method | Frequency |
|----|--------|--------|----------|-------------------|-----------|
| SM-001 | Course completion rate (enrolled → quiz passed) | > 40% | Not yet measured | `lms_analytics` dashboard + DB query on `lms_progress` / `lms_quiz_result` | Monthly |
| SM-002 | Time-to-first-lesson after enrollment | < 30 seconds (UX) | Not yet measured | Manual UX timing; future: Drupal performance logs | Per release |
| SM-003 | Payment success rate (Stripe PaymentIntent completed) | > 95% | Not yet measured (test mode) | Stripe dashboard + `lms_order` status audit | Weekly (when live) |
| SM-004 | System uptime | >= 99.5% | Not yet measured (local dev only) | Uptime monitoring (planned, environment TBD) | Daily (when deployed) |
| SM-005 | Homepage page load time (p95, cold cache) | < 2 seconds | Not yet measured | Browser DevTools + planned load test | Per release |
| SM-006 | Mobile API response time (p95, `/api/v1/` endpoints) | < 500 ms | Not yet measured | Dio interceptor timing; future: APM tool | Per release |
| SM-007 | Certificate generation time (quiz pass → PDF available) | < 5 seconds | Not yet measured | Manual timing of event subscriber + mPDF render | Per release |

---

## Assumptions

| ID | Assumption | Risk if Wrong | Validation Plan |
|----|-----------|---------------|-----------------|
| ASM-001 | Learners use modern browsers (Chrome 120+, Firefox 120+, Safari 17+) that support `backdrop-filter` (glassmorphism CSS) | Homepage glassmorphism renders broken on older browsers; `light_mode` skins degrade gracefully | Browser compatibility matrix test (Playwright / BrowserStack) before public launch |
| ASM-002 | Stripe and third-party payment APIs are available and stable | Checkout flow broken; orders stuck in `pending` | Integration smoke tests + provider status monitoring; bank transfer gateway as manual fallback |
| ASM-003 | Initial content volume < 10,000 courses with < 100,000 enrolled users | Performance degrades; 202 DB tables without Redis cache may show N+1 query issues at scale | Load test with realistic data volume before each major release |
| ASM-004 | OpenAI and Google Gemini API availability for AI course builder | AI content generation fails; `CourseBuilder` queue worker accumulates failed jobs without rollback | Implement provider-level fallback in `lms_ai`; validate API availability before batch generation |
| ASM-005 | DDEV-based local environment is available on developer machines (Docker runtime required) | Onboarding friction; setup steps in [A01-CLAUDE.md](../A-Project-Information/A01-CLAUDE.md) become invalid | Validate setup sequence on a clean machine; document minimum Docker/DDEV version requirements |
| ASM-006 | Config sync (`config/sync/`, 396 YAML files) is the authoritative site configuration source | Environment drift; features present in DB but not in code, or vice versa | `drush config:status` runs clean (no diffs) before every release |

---

## NFR Seed

The following non-functional dimensions are confirmed to matter for EverLMS. Full targets and compliance detail live in [B02-technical-requirements.md](./B02-technical-requirements.md).

| Dimension | Priority | Notes |
|-----------|----------|-------|
| Performance (page load, API latency) | Must | CDN Bootstrap dependency; no server-side cache currently active |
| Security / secrets management | Must (blocker) | Plaintext secrets in VCS is a production blocker |
| Availability / uptime | Must | No production hosting configured yet |
| Data integrity | Must | Payment, grading, certificate atomicity |
| Accessibility (a11y) | Should | Bootstrap 5 provides baseline; no formal WCAG audit done |
| Internationalization (i18n) | Should | English primary; Vietnamese in mobile app (ARB l10n); backend locale: `en` + `Asia/Bangkok` timezone |
| Observability | Should | Drupal watchdog active; structured APM not yet deployed |

---

## Adequacy at CP1 — Ideation Output Self-Check

| # | Check | Status |
|---|-------|--------|
| 1 | Every `BR-NNN` has a measurable outcome in at least one `SM-NNN`. | BR-001 → SM-002 (time-to-first-lesson); BR-003/BR-007 → SM-001 (completion), SM-007 (cert time); BR-005/BR-006 → SM-003 (payment rate); BR-004 → SM-001; BR-014 → SM-006. PASS |
| 2 | Every `SM-NNN` has a concrete Measurement Method and Frequency. | All 7 SM rows specify method and frequency. PASS |
| 3 | Every `CON-NNN` of type Technical/Regulatory/Integration has an impact-on-architecture note. | All 9 constraints carry an impact note. PASS |
| 4 | Every `ASM-NNN` has a Validation Plan with a real mechanism. | All 6 assumptions name a validation mechanism. PASS |
| 5 | NFR dimensions noted (performance, security, availability, capacity, data residency, observability). | NFR Seed table above covers all 7 dimensions. PASS |
| 6 | At least one named stakeholder per BR; no orphan BR. | All 15 BR rows reference at least one SH-NNN. PASS |
