# D02 — Epic Spec: Quality & Hardening

**Epic ID:** E-10
**Features contributed to:** NFR-001 (Security), NFR-002 (Data Integrity), NFR-003 (Code Quality), NFR-004 (Performance)
**Owner:** SA (primary) + Agent_Implementer (executes Stories); CP4 signed off by SA
**Services affected:** lms_payment, lms_stripe, lms_paypal (dead-code), lms_progress, lms_ai, lms_mobile_api, lms_forum, lms_rating, lms_notification, lms_quiz, lms_order, lms_membership, and all other modules (cross-cutting audit)
**Dependencies:** E-01 through E-09 (all prior Epics must be IN_PROGRESS or DONE before hardening begins)
**Estimated Duration:** 3 weeks
**Status:** NOT_STARTED (PLANNED)

## Purpose

Pre-production remediation sprint. No new features — only hardening code that already exists. Covers four waves: (1) security — access control, input validation, CSRF, entity access audit; (2) data integrity — progress race condition, webhook signature verification, payment idempotency; (3) code quality — AI rollback/compensation logic, DI cleanup, error-handling standardization, logging; (4) performance — Redis cache strategy, N+1 query elimination. This Epic must be fully DONE before E-11 UI-Polish begins.

## Stories

| Story ID | Title | Type | Primary source | Owning module(s) | Service(s) | Feature/AC refs | Dependencies | Est. Effort | Priority |
|---|---|---|---|---|---|---|---|---|---|
| S-10-01 | API input validation — all write endpoints | nfr | [../E-Quality/E02-issues/](../../E-Quality/E02-issues/) | lms_mobile_api, lms_progress, lms_note, lms_forum | all write routes | NFR-001 | None | 3d | Must |
| S-10-02 | Access control hardening — all routes | nfr | [../E-Quality/E02-issues/](../../E-Quality/E02-issues/) | all modules | all route controllers | NFR-001 | None | 3d | Must |
| S-10-03 | Entity access handler audit | nfr | [../E-Quality/E02-issues/](../../E-Quality/E02-issues/) | lms_forum, lms_rating, lms_progress, lms_note | ForumTopicAccessControlHandler, ForumReplyAccessControlHandler | NFR-001 | S-10-02 | 2d | Must |
| S-10-04 | CSRF protection audit | nfr | [../E-Quality/E02-issues/](../../E-Quality/E02-issues/) | all state-changing forms | form controllers | NFR-001 | None | 2d | Must |
| S-10-05 | Progress race condition fix (DB lock/transaction) | nfr | [../E-Quality/E02-issues/](../../E-Quality/E02-issues/) | lms_progress, lms_mobile_api | ProgressApiController, MobileLearningController | NFR-002 | None | 2d | Must |
| S-10-06 | Stripe webhook signature verification | nfr | [../E-Quality/E02-issues/](../../E-Quality/E02-issues/) | lms_stripe | StripeController (planned) | NFR-002 | None | 2d | Must |
| S-10-07 | PayPal webhook signature verification | nfr | [../E-Quality/E02-issues/](../../E-Quality/E02-issues/) | lms_paypal | (dead-code module — assess before implementing) | NFR-002 | S-10-06 | 1d | Should |
| S-10-08 | Payment callback idempotency keys | nfr | [../E-Quality/E02-issues/](../../E-Quality/E02-issues/) | lms_stripe, lms_payment | PaymentGatewayInterface | NFR-002 | S-10-06 | 2d | Must |
| S-10-09 | AI operation rollback + compensation | nfr | [../E-Quality/E02-issues/](../../E-Quality/E02-issues/) | lms_ai | CourseBuilderService, AiEngineUserForm | NFR-002 | None | 3d | Must |
| S-10-10 | Static service call cleanup (\\Drupal:: → DI) | maintenance | [D00-conventions/](../D00-conventions/) | all modules | service containers | NFR-003 | None | 2d | Must |
| S-10-11 | Error handling standardization | maintenance | [D00-conventions/](../D00-conventions/) | all API controllers | all modules | NFR-003 | S-10-01 | 2d | Must |
| S-10-12 | Logging for critical operations | maintenance | [D00-conventions/](../D00-conventions/) | lms_payment, lms_order, lms_quiz, lms_certificate | watchdog/logger.factory | NFR-003 | None | 1d | Must |
| S-10-13 | Redis cache strategy implementation | nfr | [D00-conventions/](../D00-conventions/) | lms_course, lms_progress, lms_notification | cache.backend.redis | NFR-004 | None | 2d | Must |
| S-10-14 | N+1 query elimination + lazy loading | nfr | [D00-conventions/](../D00-conventions/) | lms_progress, lms_analytics, lms_mobile_api | UserPortalController, AnalyticsService | NFR-004 | S-10-13 | 3d | Must |

## Build Order Within Epic

**Wave 1 — Security (parallel):**
1. S-10-01 — API input validation
2. S-10-02 — Access control hardening
3. S-10-04 — CSRF audit

**Wave 2 — Security (sequential after Wave 1):**
4. S-10-03 — Entity access handler audit (depends on S-10-02)

**Wave 3 — Data Integrity (parallel, after Wave 1):**
5. S-10-05 — Progress race condition fix
6. S-10-06 — Stripe webhook signatures

**Wave 4 — Data Integrity (sequential after Wave 3):**
7. S-10-07 — PayPal webhook signatures (depends on S-10-06; assess lms_paypal dead-code first)
8. S-10-08 — Payment idempotency keys (depends on S-10-06)

**Wave 5 — Code Quality (parallel, after Wave 2):**
9. S-10-09 — AI rollback/compensation
10. S-10-10 — DI cleanup
11. S-10-12 — Logging

**Wave 6 — Code Quality (sequential after Wave 5):**
12. S-10-11 — Error handling standardization (depends on S-10-01)

**Wave 7 — Performance (after Wave 6):**
13. S-10-13 — Redis cache strategy
14. S-10-14 — N+1 query elimination (depends on S-10-13)

## Acceptance Criteria (from B05)

| AC ID | Scenario | Feature | Contributing Story(ies) |
|-------|----------|---------|-------------------------|
| AC-1001 | All mobile API POST endpoints validate input and return 422 on missing/invalid fields | NFR-001 | S-10-01 |
| AC-1002 | All routes have explicit access checks (`_permission` or custom `_access`) — no route relies on `_access: TRUE` for protected data | NFR-001 | S-10-02 |
| AC-1003 | Entity access handlers return explicit NEUTRAL/FORBIDDEN — no implicit allow for unauthenticated | NFR-001 | S-10-03 |
| AC-1004 | All state-changing forms include CSRF token and verification | NFR-001 | S-10-04 |
| AC-1005 | Concurrent `markComplete` POSTs for the same lesson do not produce duplicate `lms_user_lesson` records | NFR-002 | S-10-05 |
| AC-1006 | Stripe webhook endpoint verifies `Stripe-Signature` header before processing payment event | NFR-002 | S-10-06 |
| AC-1007 | Repeated Stripe payment callback with same `payment_intent_id` processed only once | NFR-002 | S-10-08 |
| AC-1008 | Failed AI course generation after partial entity creation rolls back or marks entities for cleanup | NFR-002 | S-10-09 |
| AC-1009 | No `\Drupal::service()` calls inside service class constructors or methods — all dependencies injected | NFR-003 | S-10-10 |
| AC-1010 | All API error responses follow a consistent envelope (`{error, message, ...}`) | NFR-003 | S-10-11 |
| AC-1011 | Payment, order, quiz pass/fail, certificate issuance logged to watchdog at `notice` or higher | NFR-003 | S-10-12 |
| AC-1012 | Redis cache backend configured; course list and notification unread-count served from cache | NFR-004 | S-10-13 |
| AC-1013 | No N+1 queries on `/my-courses`, `/dashboard`, analytics list pages (verified by query log) | NFR-004 | S-10-14 |

## Integration Points (from B06)

| INT ID | Integration | Contributing Story(ies) | Feature Flag |
|--------|------------|--------------------------|-------------|
| INT-1001 | lms_stripe → Stripe webhook signature (HMAC-SHA256 using `STRIPE_WEBHOOK_SECRET`) | S-10-06 | — |
| INT-1002 | lms_payment → Redis cache for payment gateway plugin discovery | S-10-13 | — |
| INT-1003 | lms_ai → saga/rollback service (new service; no external dep) | S-10-09 | — |

## Test Requirements

- **Unit + integration tests per Story:** coverage >= 80% per Story (higher threshold for security stories)
- **Cross-Story integration tests:** (1) Full payment flow: Stripe webhook arrives → signature verified → idempotency checked → order completed (S-10-06 + S-10-08). (2) Concurrent progress marking under load (S-10-05). (3) Redis cache invalidation on course update (S-10-13)
- **Cross-Story Integration Test Owner:** S-10-06 — owner of `E03-test-definitions/cross-story/E-10-hardening/`
- **Acceptance tests:** AC-1001 through AC-1013

## Feature Flag Plan

| Flag ID | Story | Description | Risk Level | Rollout Strategy |
|---------|-------|-------------|-----------|-----------------|
| FF_FEAT_REDIS_CACHE | S-10-13 | Switch cache backend to Redis for hot data | Medium | 0%→10%→100% (requires Redis provisioned) |

## Known Decision Gates (resolve before starting Stories)

| Decision | Story Blocked | Options |
|----------|--------------|---------|
| DEC-001: API validation strategy (Symfony Validator vs custom assertions in controller layer) | S-10-01 | Recommend Symfony Validator via Drupal's `typed_data` layer |
| DEC-002: `lms_paypal` dead-code disposition (remove module vs fix interface + annotation) | S-10-07 | Recommend removal — `BankTransferGateway` + `lms_stripe` cover real gateways |
| DEC-003: Progress race condition fix (DB row-level lock vs queue-based serialization) | S-10-05 | Recommend DB `SELECT FOR UPDATE` transaction on `lms_user_course` row |

## CP4 Readiness Criteria

- [ ] Every Story in this Epic has status: DONE
- [ ] G1-G4 passed for every Story
- [ ] Cross-Story integration tests within Epic passing
- [ ] D07 code-map delta complete for all files touched in this Epic
- [ ] E05 traceability complete for every contributed NFR
- [ ] E07 epic retrospective written (E07-retrospectives/)
- [ ] Feature flags registered for FF_FEAT_REDIS_CACHE
- [ ] Open G4 Medium/Low issues logged in E02-issues/
- [ ] Decision gates DEC-001, DEC-002, DEC-003 resolved and documented
- [ ] Every Story that touched code/config/UI lists relevant `D00-conventions/` files in its D03 or D04 evidence
- [ ] E-11 UI-Polish NOT started until this Epic is fully DONE
