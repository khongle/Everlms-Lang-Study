# E01 — Test Strategy

**Layer:** E-Quality
**Purpose:** Testing approach, coverage targets, gate definitions, and per-layer guidance for EverLMS (Drupal 11 LMS).
**Last updated:** 2026-06-20
**Status:** target-state — no automated tests exist yet; this document defines the target strategy.

---

## 1. Testing Approach

EverLMS targets a **classic Drupal test pyramid** with an additional browser/E2E tier and a performance baseline:

```
         /‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾\
        /    E2E / Playwright   \      (fewest, most realistic)
       /‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾\
      / Functional (BrowserTest) \
     /‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾\
    /    Kernel (KernelTestBase)   \
   /‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾\
  /  Unit (PHPUnit — no Drupal boot) \   (most, fastest)
 ‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾
```

**Current reality (as-built as of 2026-06-20):** There are **no automated test files** in any `lms_*/tests/` directory. No CI pipeline exists. No visual regression or performance baseline has been run. This document is entirely a target strategy; all items below are planned.

The strategy is grounded in:
- 27 custom `lms_*` modules + `open_switcher` (28 total) at `web/modules/custom/`
- 137 routes (19 public, 21 learn/app, 47 workspace/manage, 17 admin, 31 API)
- Priority critical logic: `GradingStrategyFactory` (8 strategies), `ProgressCascadeService`, certificate auto-issuance, `PaymentGatewayManager`
- Known bugs and stubs that must be covered by negative tests or deferred (see [E02-issues/](E02-issues/) — planned layer)

---

## 2. Coverage Targets

| Phase | PHPUnit line coverage | Gate required |
|---|---|---|
| MVP (pre-v1.0) | >= 60% on custom modules | G3 |
| Stable (post-v1.0) | >= 80% on custom modules | G3 |

Coverage is measured by `phpunit --coverage-text` over `web/modules/custom/lms_*/src/`.

| Test Type | Target | Tool | Frequency |
|---|---|---|---|
| Unit (no Drupal container) | >= 60% MVP → 80% stable | PHPUnit 11 (ships with Drupal 11.3.5) | Every Story gate (G3) |
| Kernel (DB + container, no HTTP) | All entity types + critical services | PHPUnit + `KernelTestBase` | Per Epic completion |
| Functional / Browser | Top 20 controllers; role matrix | PHPUnit + `BrowserTestBase` or `WebDriverTestBase` | Per Epic completion |
| API contract | All `/api/v1/*` mobile endpoints | PHPUnit + HTTP client or Playwright | Pre-release |
| E2E (golden flows) | WF-001 → WF-004 critical paths | Playwright | Pre-release, per Epic checkpoint |
| Accessibility | 8 key pages, WCAG 2.1 AA | Playwright + axe-core | Pre-release |
| Performance | p95 targets (see §5) | k6 | Pre-release (E12 — planned) |
| Security regression | Zero HIGH findings | OWASP ZAP baseline | Pre-release |
| Visual regression | 10 key pages × 3 viewports | BackstopJS | Every UI-touching Story |
| Static analysis | PHPCS Drupal + DrupalPractice | phpcs, phpstan | Every commit (G2) |
| Dependency scan | Zero CRITICAL CVEs | `composer audit` | Pre-release |

---

## 3. Gate Sequence

```
G1 (Build) → G2 (Lint) → G3 (Tests) → G4 (Adversarial Self-Review)
```

Every Story in [D03-stories/](../D-Development/D03-stories/) must pass all four gates before moving to DONE status.

### Gate Definitions

| Gate | Command | Pass Criteria | Max Retries |
|---|---|---|---|
| G1 | `ddev exec drush cr` (or `ddev exec drush site:install --existing-config -y && drush cr` for fresh env) | Exit code 0; zero PHP fatal errors | 3 |
| G2 | `ddev exec vendor/bin/phpcs --standard=Drupal,DrupalPractice web/modules/custom/` | Exit code 0; zero errors or warnings | 3 |
| G3 | `ddev exec vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/ --coverage-text` | All tests pass; coverage >= threshold | 3 |
| G4 | Manual adversarial self-review using the 7 categories below | All Critical/High findings fixed before merging | 3 |

**PHPUnit configuration note:** There is no `phpunit.xml` at repo root (it is listed in `.gitignore` as absent). Tests will be bootstrapped via Drupal core's `web/core/phpunit.xml.dist`. A project-level `phpunit.xml` should be created when setting up the test suite (Story [quality-test-suite](../D-Development/D03-stories/quality-test-suite.md)).

---

## 4. G4 Adversarial Self-Review Categories

Every Story must have G4 applied before DONE. Seven categories (source: framework protocol):

| # | Category | EverLMS Focus |
|---|---|---|
| 1 | **Security** | SQL injection, XSS, CSRF, auth bypass, plaintext secrets in config (`«secret in config — see security risk»`), static `\Drupal::` calls in service layer |
| 2 | **Error Handling** | Unhandled exceptions, silent failures (especially in queue workers, AI generation), missing retry logic for payment/webhook calls |
| 3 | **Performance** | N+1 queries in catalog/progress/analytics, unbounded `entityQuery()` results, missing cache tags, mPDF generation blocking request thread |
| 4 | **Edge Cases** | Null quiz score, zero-lesson chapter, concurrent enrollment, Unicode in user-supplied content, essay quiz with no manual grader |
| 5 | **Architecture** | `\Drupal::` static calls in service classes (must use DI), circular module dependencies, god-controller anti-pattern, non-plugin grading factory |
| 6 | **Data Integrity** | Missing DB transactions on cascade progress updates, orphaned `lms_question_response` rows, certificate issued without complete course traversal |
| 7 | **Maintainability** | Function/file length, cyclomatic complexity, naming clarity, dead code (`lms_paypal` stub, `"HARD DEBUG!"` exception in `ForumController::viewTopic()`), coverage regression |

---

## 5. What to Test Per Layer

### 5.1 Unit Tests (PHPUnit, no Drupal container)

The primary target is **`GradingStrategyFactory`** and its 8 strategy classes in `lms_quiz`. These are static PHP (not Drupal plugins), making them ideal pure-unit targets.

| Target | Strategy classes / scenarios | Source module |
|---|---|---|
| `MultipleChoiceStrategy` | All correct → full score; partial → 0 (binary); none → 0 | `lms_quiz` |
| `SingleChoiceStrategy` | Correct/incorrect binary | `lms_quiz` |
| `TrueFalseStrategy` | Boolean scoring | `lms_quiz` |
| `FillBlankStrategy` | Case-sensitive match; case-insensitive match | `lms_quiz` |
| `ShortAnswerStrategy` | Text match; keyword scoring | `lms_quiz` |
| `MatchingStrategy` | All pairs correct; partial pairs | `lms_quiz` |
| `OrderingStrategy` | Correct sequence; wrong sequence | `lms_quiz` |
| `EssayStrategy` | Returns `pending_review` status; no score assertion | `lms_quiz` |

Additional unit targets:

| Service / Class | Test focus |
|---|---|
| `LmsException` hierarchy | `ValidationException`, `AccessDeniedException`, `NotFoundException` instantiate with correct HTTP code and message (planned — Story [hardening-security](../D-Development/D03-stories/hardening-security.md)) |
| `ValidationService` (planned) | Required-field check → structured `{"errors":[…]}`; HTML stripping for XSS |
| `LoggingService` (planned) | Critical event writes watchdog entry with correct context; no PII beyond `user_id` |
| `AiProviderPlugin` (OpenAI/Gemini) | Provider selection; API key redacted in logs |

### 5.2 Kernel Tests (PHPUnit + `KernelTestBase`, real DB + container)

| Entity / Service | Scenarios |
|---|---|
| `lms_course` | CRUD; field validation; status transition (draft → published) |
| `lms_chapter` | CRUD; course reference; ordering field |
| `lms_lesson` | CRUD; 9 lesson-type variants; chapter reference |
| `lms_quiz` | CRUD; config validation (threshold, timer); `passing_score` boundary |
| `lms_question` (8 bundles) | CRUD per bundle; score field; answer/matching-pair sub-entities |
| `lms_quiz_result` | CRUD; score persistence; attempt counter; `pending_review` for essay |
| `lms_order` | CRUD; status lifecycle (pending → paid → cancelled); item management |
| `lms_user_certificate` | CRUD; `verify_code` uniqueness; `field_expiry_date` (note: stored as varchar — see [E02-issues/](E02-issues/)) |
| `lms_notification` | CRUD; type validation; recipient targeting |
| `lms_user_membership` | CRUD; status field; date range management |
| `ProgressCascadeService` | Lesson completion → chapter progress → course progress cascade correctness; 10 parallel requests → correct final state (requires Story [progress-race-condition](../D-Development/D03-stories/progress-race-condition.md) complete first) |
| `CertificateIssuanceSubscriber` | `QuizPassedEvent` fires → subscriber traverses Quiz→Lesson→Chapter→Course → `lms_user_certificate` created; no duplicate on retry |
| `PaymentGatewayManager` | Plugin discovery: discovers `BankTransferGateway`; `StripeGateway`; does NOT discover `lms_paypal` (dead code) |

### 5.3 Functional / Browser Tests (PHPUnit + `BrowserTestBase`)

Target: top 20 controllers by expected traffic, covering all 7 roles.

| Controller | Key assertions |
|---|---|
| `HomepageController` | 200 for anonymous; homepage skins render |
| `CourseCatalogController` | Catalog lists published courses; filter by category/level |
| `CourseDetailController` | Detail page renders; enroll button visible for eligible users |
| `LessonViewController` | Enrolled student can view lesson; non-enrolled gets 403 |
| `QuizTakeController` | Quiz form renders for enrolled student; submission triggers grading |
| `QuizResultController` | Result page shows score; certificate link shown on pass |
| `DashboardController` | Authenticated user sees own progress; unauthenticated → 403/redirect |
| `CheckoutController` | Payment form renders; gateway options present |
| `OrderController` | Order detail visible to owner; 403 for other users |
| `CertificateController` | Public verify URL works; certificate PDF downloadable by owner |
| `ForumController` | `viewTopic()` does NOT throw `"HARD DEBUG!"` exception (regression test) |
| `NotificationController` | Bell count correct; mark-all-read works |
| `ProfileController` | Own profile editable; other profile not editable |
| `MembershipController` | Active membership grants expected access |
| `WishlistController` | Toggle add/remove; wishlist page lists items |
| `AnalyticsDashboardController` | Admin/manager can access; student gets 403 |
| `LoginForm` | Invalid credentials → error message; valid → redirect |
| `RegisterForm` | Missing fields → validation error; valid → account created |
| `BadgeController` | Badges list renders (stub — no award logic yet) |
| Role matrix (`/manage/*` routes) | `student` role returns 403 on all `/manage/*` routes |

### 5.4 API Contract Tests (`/api/v1/*` — `lms_mobile_api`)

All 31 `/api/v1/*` routes must be tested against the OpenAPI spec (planned at [C06-apis/](../C-Design/C06-apis/) — not yet materialized).

**Authentication method:** Custom bearer token via `keyvalue.expirable` + OTP (NOT OAuth2/JWT despite any `oauth_*` config key names). Tests must use the custom auth flow.

| Endpoint group | Contracts to assert |
|---|---|
| `/api/v1/auth/*` | OTP request → token issuance; expired token → 401 |
| `/api/v1/courses/*` | List courses; detail; enrollment status |
| `/api/v1/learning/*` | Start lesson; mark complete; progress state |
| `/api/v1/quiz/*` | Take quiz; submit answers; result with score |
| `/api/v1/certificates/*` | List user certificates; verify code |
| `/api/v1/notes/*` | CRUD; timestamp attachment |
| `/api/v1/notifications/*` | List; mark read |

### 5.5 E2E Tests (Playwright, against `everlms.ddev.site`)

E2E tests run against a live DDEV instance with seeded data (`.agent/generated/` JSON imported in order per `manifest.json`).

#### Student Flows

| ID | Scenario | Priority |
|---|---|---|
| E2E-001 | Browse catalog → filter by category/level → view course detail | P0 |
| E2E-002 | Enroll free course → start lesson → mark complete | P0 |
| E2E-003 | Purchase paid course → checkout (BankTransfer) → enrollment granted | P0 |
| E2E-004 | Take quiz (multiple choice) → submit → view result with score | P0 |
| E2E-005 | Complete all lessons → pass quiz → receive certificate (auto-issued) | P0 |
| E2E-006 | Resume learning from last-viewed lesson (continue button) | P0 |
| E2E-007 | View student dashboard → progress overview across enrolled courses | P1 |
| E2E-008 | Add course to wishlist → view wishlist page → remove | P1 |
| E2E-009 | Rate completed course → submit review text | P1 |
| E2E-010 | Verify certificate via public verify URL (no login required) | P0 |

#### Instructor Flows

| ID | Scenario | Priority |
|---|---|---|
| E2E-011 | Create course → add chapters → add lessons in workspace | P0 |
| E2E-012 | Create quiz → add 3 question types → configure passing score | P0 |
| E2E-013 | Grade submitted essay question → update score manually | P1 |
| E2E-014 | View own course analytics (enrollment count, completion rate) | P1 |

#### Admin / Manager Flows

| ID | Scenario | Priority |
|---|---|---|
| E2E-015 | Manage users → assign `instructor` role → verify route access | P0 |
| E2E-016 | Manage orders → view order detail → mark bank transfer paid | P1 |
| E2E-017 | Manage certificate templates → create template → preview | P1 |
| E2E-018 | View admin analytics dashboard (platform-wide stats) | P1 |
| E2E-019 | Manage course categories → create/edit/delete taxonomy term | P1 |

#### Commerce Flows

| ID | Scenario | Priority |
|---|---|---|
| E2E-020 | Checkout → Stripe payment form (test mode) → order completed → enrollment | P0 |
| E2E-021 | Checkout → payment failure simulation → retry flow | P0 |
| E2E-022 | Purchase bundle → all bundled courses unlocked | P1 |

#### Golden Business Workflows (WF-001 to WF-004)

These are the highest-priority E2E tests, mapped to business workflows in [B04-business-workflows/](../B-Requirements/B04-business-workflows/):

| Playwright spec | Workflow | Scope |
|---|---|---|
| `tests/e2e/wf-001-enrollment-learning.spec.ts` | WF-001 | Enroll → lessons → quiz → certificate end-to-end |
| `tests/e2e/wf-002-quiz-grading.spec.ts` | WF-002 | Quiz submit → auto-grade (7 strategies) + essay → manual grade |
| `tests/e2e/wf-003-certificate-issuance.spec.ts` | WF-003 | Quiz pass event → cert entity created → PDF downloadable |
| `tests/e2e/wf-004-order-management.spec.ts` | WF-004 | Cart → checkout → payment → order confirmation → enrollment |

### 5.6 Accessibility Tests (Playwright + axe-core)

WCAG 2.1 AA on 8 key pages. Zero critical axe violations required for G3 pass.

| Page | Route |
|---|---|
| Homepage | `/homepage` |
| Course catalog | `/courses` |
| Course detail | `/courses/{slug}` |
| Lesson view | `/learn/lesson/{id}` |
| Quiz take | `/learn/quiz/{id}` |
| Student dashboard | `/dashboard` |
| Checkout | `/checkout/{order}` |
| User profile | `/user/{uid}/edit` |

Bootstrap 5.3.3 is loaded via CDN (note: CDN availability is an external reliability dependency — see [E10-nfr-tracking.md](E10-nfr-tracking.md) NFR-B06). Twig templates must use semantic HTML and ARIA landmarks.

### 5.7 Performance Tests (k6)

Target state. No baseline measured yet. Results will be tracked in E12-performance-results (planned layer — not yet materialized).

| Scenario | VUs | Duration | p95 Target | Tool |
|---|---|---|---|---|
| Homepage (cold cache) | 50 | 5 min | < 2 s | k6 + Lighthouse |
| Course catalog (`/courses`) | 100 | 5 min | < 3 s | k6 |
| API: `/api/v1/courses` | 100 | 5 min | < 500 ms | k6 |
| Quiz submit (POST) | 50 | 5 min | < 1 s | k6 |
| Certificate PDF generation | 10 | 5 min | < 5 s | Custom timed test |

**Known performance risks (from facts base):**
- Page cache is disabled (`max_age: 0`) in current config — must be enabled before production performance tests are meaningful.
- No Redis backend configured (no Redis DDEV add-on).
- Bootstrap 5.3.3 loaded via CDN — adds external round-trip latency.

### 5.8 Security Regression (OWASP ZAP)

Zero HIGH findings from ZAP baseline scan against running DDEV instance. Scan config at `tests/security/zap-baseline.yml` (to be created in Story [quality-test-suite](../D-Development/D03-stories/quality-test-suite.md)).

Known security issues that tests must flag or track (not cure — cures belong to [hardening-security](../D-Development/D03-stories/hardening-security.md) story):
- Plaintext secrets in committed config YAML (`«secret in config — see security risk»` — see [E10-nfr-tracking.md](E10-nfr-tracking.md) NFR-007)
- Missing webhook signature verification (Stripe, PayPal) — Story [payment-webhooks](../D-Development/D03-stories/payment-webhooks.md)
- Stripe `PaymentIntent` created on form BUILD (not submit) — see [E10-nfr-tracking.md](E10-nfr-tracking.md) NFR-014

---

## 6. Test File Conventions and Naming

### File Locations

```
web/modules/custom/{module}/tests/src/Unit/         ← Unit tests
web/modules/custom/{module}/tests/src/Kernel/       ← Kernel tests
web/modules/custom/{module}/tests/src/Functional/   ← Browser tests
tests/e2e/                                          ← Playwright E2E
tests/a11y/                                         ← Playwright + axe-core
tests/perf/                                         ← k6 scripts
tests/security/                                     ← ZAP config
tests/visual/                                       ← BackstopJS config
```

### Test Class Naming

```
Drupal\Tests\{module}\Unit\{ClassName}Test
Drupal\Tests\{module}\Kernel\{ClassName}Test
Drupal\Tests\{module}\Functional\{ControllerName}Test
```

### Test Method Naming Convention

```
{module}.{feature}.{scenario}
Example: lms_quiz.grading.multiple_choice_all_correct_returns_full_score
Example: lms_progress.cascade.parallel_requests_converge_to_correct_state
```

PHPUnit method names: `testMultipleChoiceAllCorrectReturnsFullScore()` (camelCase of the scenario above).

### Base Class Selection

| Layer | Base class | When to use |
|---|---|---|
| Unit | `PHPUnit\Framework\TestCase` | Pure logic, no Drupal services |
| Kernel | `Drupal\KernelTests\KernelTestBase` | Services, entity API, DB queries |
| Functional | `Drupal\Tests\BrowserTestBase` | Form submission, HTTP, session |
| Functional (JS) | `Drupal\Tests\WebDriverTestBase` | AJAX, JavaScript interactions |

---

## 7. Test Data Strategy

- **Factory / entity builder pattern:** create entities via entity API inside tests (`\Drupal::entityTypeManager()->getStorage()->create([...])->save()`), never raw SQL inserts.
- **Per-test isolation:** each kernel test creates its own entity data and transactions are rolled back (or test DB is reset) on teardown via `KernelTestBase`.
- **Shared read-only fixtures:** taxonomy vocabularies and roles set up in `setUp()` via config install or `installEntitySchema()`.
- **Seed data for E2E:** the `.agent/generated/` JSON files (5 courses, 22 chapters, 79 lessons, 5 quizzes, 75 questions; generated 2026-03-29) are the canonical E2E seed. Import order: instructors → students → courses 1–5 → quizzes 1–5 → certificates → membership plans → forum topics → badges (as per `.agent/generated/manifest.json`).
- **Secret data in tests:** never use real API keys. Use `STRIPE_TEST_KEY` environment variables or mock gateway implementations. The committed `.env` (test Stripe/PayPal credentials) should never be loaded in CI — use CI environment secrets instead.

---

## 8. CI Pipeline (target-state)

No CI pipeline exists yet. Target structure (to be created in Story [quality-test-suite](../D-Development/D03-stories/quality-test-suite.md)):

```yaml
# .gitlab-ci.yml stages (target)
stages:
  - G1-build     # composer install; drush site:install --existing-config
  - G2-lint      # phpcs --standard=Drupal,DrupalPractice
  - G3-tests     # phpunit kernel + functional; playwright e2e
  - G4-adversarial  # ZAP baseline; k6 smoke test
```

Trigger: every Merge Request / Pull Request. Fail fast on G1.

---

## 9. Known Test Gaps (as of 2026-06-20)

| Gap | Severity | Related Story |
|---|---|---|
| Zero test files in any `lms_*/tests/` directory | Critical | [quality-test-suite](../D-Development/D03-stories/quality-test-suite.md) |
| No CI pipeline | Critical | [quality-test-suite](../D-Development/D03-stories/quality-test-suite.md) |
| `lms_paypal` is dead code — no tests warranted; file as known issue | High | [lms_paypal](../D-Development/D03-stories/lms_paypal.md) |
| `lms_badge` is near-stub (zero award logic) — award logic must be written before meaningful badge tests | High | [lms_badge](../D-Development/D03-stories/lms_badge.md) |
| `lms_notification` email/push channels are stubs — test only in-app channel | Medium | [lms_notification](../D-Development/D03-stories/lms_notification.md) |
| No phpunit.xml at repo root | Medium | [quality-test-suite](../D-Development/D03-stories/quality-test-suite.md) |
| `lms_user_certificate.field_expiry_date` stored as varchar not int — schema bug affects date assertions | Low | E02-issues/ (planned) |
| `ForumController::viewTopic()` throws `"HARD DEBUG!"` — functional test will fail until fixed | High | [hardening-security](../D-Development/D03-stories/hardening-security.md) |
| `lms_rating` missing 3 runtime routes — API contract tests will 404 until fixed | High | [hardening-security](../D-Development/D03-stories/hardening-security.md) |
