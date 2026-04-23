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

*End of D01-dev-overview.md*
