# D03 — Story Spec: Quality Test Suite

**Story ID:** quality-test-suite
**Story type:** nfr
**Title:** Quality Test Suite — kernel, functional, e2e, a11y, security tests
**Primary source:** `E-Quality/E01-test-strategy.md`; draft D01-dev-overview.md §TASK-067..086
**Owning module(s):** all lms_* modules (tests live in `{module}/tests/`)
**Service(s):** PHPUnit (kernel + functional), Playwright (e2e + a11y), OWASP ZAP (security baseline)
**Epic:** E-10 (Quality & Hardening)
**Contributes to Feature(s):** F-012 (Security & Performance)
**Design source(s):** `B05-acceptance-criteria/`, `B06-integration-requirements/`, `B02-technical-requirements.md`
**Round:** N/A
**Driver:** Pre-v1.0 quality gate; G2/G3/G4 gate requirements (A02 §3); draft TASK-067..086
**Status:** SPEC
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `story: quality-test-suite` — maps to draft TASK-067, TASK-068, TASK-069, TASK-075..086

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 4+
**Splitting strategy:**
- S1: Kernel tests for G-FOUNDATION + G-CONTENT entities (TASK-075) + G-ASSESSMENT 8 question handlers (TASK-076)
- S2: Kernel tests G-PROGRESS (cascade + concurrency — TASK-077) + G-COMMERCE mock gateways (TASK-078) + G-CERTIFICATE (TASK-079) + G-COMMUNICATION (TASK-080)
- S3: Functional/browser tests top 20 controllers (TASK-081) + API contract tests lms_mobile_api (TASK-082) + CI pipeline setup (TASK-069)
- S4: Visual regression baseline (TASK-083) + Performance benchmark k6 (TASK-084) + a11y Playwright (TASK-085) + Security OWASP ZAP (TASK-086)

## Scope (from primary source)

Implement a comprehensive, layered test suite covering the entire EverLMS codebase. No test infrastructure currently exists. Tests are written to Drupal PHPUnit conventions for kernel/functional tests, and to Playwright conventions for e2e/a11y.

**Test layers:**

1. **Kernel tests (PHPUnit + Drupal KernelTestBase):**
   - G-FOUNDATION: lms_base entity definitions, taxonomy vocabulary creation, LmsHelper utilities (TASK-075)
   - G-CONTENT: lms_course/chapter/lesson entity CRUD, field validation, entity access handlers (TASK-075)
   - G-ASSESSMENT: all 8 GradingStrategy implementations — verify correct score calculation, binary grading, essay → `pending_review`, `QuizPassedEvent` dispatch on pass (TASK-076)
   - G-PROGRESS: `ProgressCascadeService` cascade correctness + concurrency (requires Story `progress-race-condition` to be complete) (TASK-077)
   - G-COMMERCE: mock PaymentGateway plugin — `PaymentGatewayManager` discovers and invokes gateways; BankTransferGateway real; Stripe mock (TASK-078)
   - G-CERTIFICATE: auto-issuance event subscriber fires on `QuizPassedEvent`; traverses Quiz→Lesson→Chapter→Course; mPDF integration (TASK-079)
   - G-COMMUNICATION: notification dispatch, forum entity creation, rating entity (TASK-080)

2. **Functional/browser tests (PHPUnit + Drupal BrowserTestBase or WebDriverTestBase):**
   - Top 20 controllers by traffic: HomepageController, CourseCatalogController, CourseDetailController, LessonViewController, QuizTakeController, QuizResultController, DashboardController, CheckoutController, OrderController, CertificateController, ForumController, NotificationController, ProfileController, SearchController, LoginForm, RegisterForm, MembershipController, WishlistController, BadgeController, AnalyticsDashboardController (TASK-081)
   - API contract tests for all `/api/v1/*` mobile endpoints (TASK-082): verify request/response shapes against OpenAPI spec (planned at `C-Design/C06-apis/`); test bearer token auth, OTP flow

3. **E2E tests (Playwright):**
   - Golden flows WF-001 (enrollment → lesson → quiz → certificate), WF-002 (quiz grading), WF-003 (certificate issuance), WF-004 (order management) against a running DDEV instance (TASK-068)
   - Run against `everlms.ddev.site` with seeded test data

4. **Accessibility tests (Playwright + axe-core):**
   - WCAG 2.1 AA audit on: homepage, course catalog, course detail, lesson view, quiz take, dashboard, checkout, profile (TASK-085)
   - Zero critical axe violations required for G3 pass on each page

5. **Security regression (OWASP ZAP baseline):**
   - ZAP baseline scan against running DDEV instance (TASK-086)
   - Zero HIGH findings required for G4 pass

6. **Performance benchmark (k6):**
   - Load test: 100 concurrent users on catalog page, course detail, quiz submit (TASK-084)
   - Target: P95 response time < 2s under 100 concurrent users

7. **CI pipeline (TASK-069):**
   - `.gitlab-ci.yml` (or GitHub Actions equivalent) with stages: G1-build, G2-lint (PHPCS Drupal), G3-tests (kernel + functional), G4-adversarial (ZAP baseline + k6 smoke)
   - Run on every MR/PR; fail fast on G1

8. **Visual regression baseline (BackstopJS — TASK-083):**
   - Capture baseline screenshots of 10 key pages at 375/768/1440px
   - Run on every UI-touching MR; diff threshold 0.1%

**Real implementation status (as-built):**
- No test files exist in any `lms_*/tests/` directory.
- No CI pipeline exists.
- No visual regression tooling configured.
- `GradingStrategyFactory` (static PHP factory, not Drupal plugin manager) implements 8 question-type strategies — these are priority targets for kernel tests.
- Certificate auto-issuance via `lms_quiz.passed` event subscriber — priority integration test target.

## Source Documents

| Source | Why loaded |
|---|---|
| `C-Design/C05-modules/lms_quiz.md` | GradingStrategyFactory + 8 strategies |
| `C-Design/C05-modules/lms_certificate.md` | Event subscriber + mPDF |
| `C-Design/C05-modules/lms_progress.md` | ProgressCascadeService concurrency |
| `C-Design/C05-modules/lms_payment.md` | PaymentGatewayManager plugin discovery |
| `C-Design/C05-modules/lms_mobile_api.md` | /api/v1/* contract |
| `B05-acceptance-criteria/` | Source for test AC derivation |
| `B04-business-workflows/WF-001..004` | Golden e2e flow definitions |
| `B02-technical-requirements.md` | NFR coverage requirements |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| All 8 GradingStrategy implementations have kernel tests with correct score assertions | B02 §Quality | Non-Functional |
| ProgressCascadeService concurrency test: 10 parallel requests → correct final state | RISK-002 | Non-Functional |
| Golden e2e flows WF-001..004 pass in Playwright against DDEV | B04-business-workflows/ | Acceptance |
| WCAG 2.1 AA: zero critical axe violations on 8 key pages | B02 §Accessibility | Non-Functional |
| OWASP ZAP baseline: zero HIGH findings | B02 §Security | Non-Functional |
| k6 load test: P95 < 2s at 100 concurrent users on catalog | B02 §Performance | Non-Functional |
| CI pipeline runs on every MR and fails on G1 build failure | B02 §DevOps | Non-Functional |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_quiz/tests/src/Kernel/GradingStrategyTest.php` | Create | 8 strategy × correct/incorrect/boundary cases | TASK-076 | — |
| `web/modules/custom/lms_progress/tests/src/Kernel/ProgressCascadeTest.php` | Create | Cascade correctness + concurrency simulation | TASK-077 | — |
| `web/modules/custom/lms_certificate/tests/src/Kernel/CertificateIssuanceTest.php` | Create | Event subscriber fires; cert entity created; mPDF mock | TASK-079 | — |
| `web/modules/custom/lms_payment/tests/src/Kernel/PaymentGatewayManagerTest.php` | Create | Plugin discovery; BankTransfer gateway invocation | TASK-078 | — |
| `web/modules/custom/lms_course/tests/src/Functional/CourseCatalogControllerTest.php` | Create | Catalog page renders; enrolled indicator for logged-in user | TASK-081 | — |
| `web/modules/custom/lms_quiz/tests/src/Functional/QuizTakeControllerTest.php` | Create | Quiz take form submits; grading runs; result page loads | TASK-081 | — |
| `tests/e2e/wf-001-enrollment-learning.spec.ts` | Create | Playwright: enroll → lesson → quiz → certificate (WF-001) | TASK-068 | — |
| `tests/e2e/wf-004-order-management.spec.ts` | Create | Playwright: checkout → payment → order confirmation (WF-004) | TASK-068 | — |
| `tests/a11y/axe-homepage.spec.ts` | Create | Playwright + axe-core WCAG 2.1 AA on homepage | TASK-085 | — |
| `tests/perf/k6-catalog.js` | Create | k6 load test: 100 VUs on /courses 5 min | TASK-084 | — |
| `tests/security/zap-baseline.yml` | Create | OWASP ZAP baseline scan config | TASK-086 | — |
| `tests/visual/backstop.json` | Create | BackstopJS config: 10 pages × 3 viewports | TASK-083 | — |
| `.gitlab-ci.yml` | Create | CI pipeline: G1-G4 stages | TASK-069 | — |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| Story `progress-race-condition` DONE before `ProgressCascadeTest.php` concurrency tests | Intra-Epic | this D03-stories/ |
| Story `hardening-security` DONE before access control functional tests | Intra-Epic | this D03-stories/ |
| Story `payment-webhooks` DONE before PaymentGateway integration tests | Intra-Epic | this D03-stories/ |
| DDEV running with seeded test data (drush seed) | Infrastructure | setup-infra.md |
| Playwright npm package installed (`npm i -D playwright @axe-core/playwright`) | External | package.json |
| k6 binary available in CI environment | External | CI environment |
| OWASP ZAP Docker image in CI | External | `.gitlab-ci.yml` |
| BackstopJS npm package | External | package.json |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| `D00-conventions/php-drupal.md` | All test PHP files | PHPCS Drupal standard |
| Drupal PHPUnit test base class selection (KernelTestBase vs BrowserTestBase) | Test files | Per test type |
| Test data: use entity builders, not raw DB inserts | All kernel tests | Code review |

## Editorial Usability / Entity-First Evidence

Not applicable — this Story creates test infrastructure only; no visible content changes.

## Feature Flags Introduced or Read

| Flag ID | Introduced / Read | Purpose | Rollout plan link |
|---------|-------------------|---------|-------------------|
| FF_FEAT_AI_PROVIDER | Read | AI provider tests must run with flag ON and OFF | H05-feature-flags.md entry |

## Test Plan

The Story itself IS the test suite. Gate criteria are met when the new test infrastructure achieves coverage targets and all tests pass.

| Test ID | Type | Coverage Target |
|---------|------|-----------------|
| T-QT-K01 | Kernel | All 8 GradingStrategy implementations: PASS |
| T-QT-K02 | Kernel | ProgressCascadeService: 10 parallel requests → correct final state |
| T-QT-K03 | Kernel | Certificate issuance event subscriber: cert entity created after QuizPassedEvent |
| T-QT-K04 | Kernel | PaymentGatewayManager discovers BankTransferGateway |
| T-QT-F01 | Functional | Top 20 controllers return correct HTTP codes for authenticated/anonymous roles |
| T-QT-F02 | Functional | /api/v1/* mobile endpoints return correct response shapes |
| T-QT-E01 | E2E | WF-001: enroll → complete lesson → pass quiz → certificate issued (Playwright) |
| T-QT-E02 | E2E | WF-004: checkout → Stripe payment form → order completed (Playwright) |
| T-QT-A01 | Accessibility | Homepage: zero axe critical violations (WCAG 2.1 AA) |
| T-QT-S01 | Security | ZAP baseline: zero HIGH findings against DDEV |
| T-QT-P01 | Performance | k6: catalog P95 < 2s at 100 VUs |
| T-QT-V01 | Visual | BackstopJS: no regression vs baseline (threshold 0.1%) |

## Gate Results (per Story, see A02 §3)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PENDING | Test infrastructure builds (composer install, npm install) | 0/3 | — |
| G2 Lint | PENDING | PHPCS on test files; Playwright TypeScript type-check | 0/3 | — |
| G3 Tests | PENDING | All tests listed above PASS | 0/3 | — |
| G4 Adversarial | PENDING | ZAP baseline zero HIGH; test suite itself reviewed for false-positive risk | 0/3 | — |

## Notes

- **`GradingStrategyFactory` note:** it is a static PHP factory (not a Drupal plugin manager). Kernel tests can instantiate it directly without the full Drupal container. This is simpler than plugin-based testing.
- **Essay grading:** essays result in `pending_review` status, not a score. Kernel tests verify this path separately (no score assertion; verify status field = `pending_review`).
- **`QuizPassedEvent`:** dispatched only when score ≥ passing_score AND quiz type is not essay-only. Kernel tests must mock the event dispatcher to capture the event.
- **mPDF in certificate tests:** mPDF generates actual PDF bytes. Kernel tests should mock the PDF generation service and verify it was called with the correct arguments (not assert PDF binary content).
- **lms_paypal dead code:** do not write tests for `lms_paypal` PaymentGateway — it is confirmed dead code (wrong interface, no annotation). File it as a known issue in `E02-issues/` instead.
- **`lms_mobile_api` bearer token:** uses custom bearer token via `keyvalue.expirable` + OTP (NOT OAuth2/JWT despite config key names). API contract tests must use the custom auth flow, not OAuth2.
- **CI pipeline note:** the `.gitlab-ci.yml` assumes GitLab CI. If the repo uses GitHub Actions, use `.github/workflows/ci.yml` equivalent. The stage/job names (G1-build, G2-lint, G3-tests, G4-adversarial) mirror the protocol gate IDs.
