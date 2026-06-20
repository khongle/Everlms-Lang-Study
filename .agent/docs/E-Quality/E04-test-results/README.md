# E04 — Test Results

This folder holds the outcome record of every test, QA, visual regression, route-smoke, or screenshot run executed against EverLMS. Each file is immutable once written and represents a single run.

## Status: empty — no automated suite exists yet

As of 2026-06-20, **no test files exist** in any `lms_*/tests/` directory, no CI pipeline is configured, and no automated test suite has been run. This folder will remain empty until the work described in [D03 Story: quality-test-suite](../../D-Development/D03-stories/quality-test-suite.md) (Epic E-10) is executed.

## What goes here

One file per test run, named:

```
run-{YYYY-MM-DD}-{HHMM}-{scope}.md
```

where `scope` is one of: `module` | `epic` | `full` | `nightly` | `ci-{short_sha}` | `visual` | `route-smoke` | `screenshot` | `editor-usability`.

## When to create an entry

| Trigger | Scope label | Notes |
|---------|-------------|-------|
| Any PHPUnit kernel/functional run against a module or Epic | `module-{name}` or `epic-{id}` | Even partial or failing runs must be recorded |
| Full suite run (all lms_* modules) | `full` | Nightly CI run or pre-release |
| CI pipeline run on a merge request | `ci-{short_sha}` | Include commit SHA in the run ID |
| Playwright e2e / a11y run | `epic` or `full` | Reference golden flows WF-001..004 from [B04-business-workflows/](../../B-Requirements/B04-business-workflows/) |
| Visual regression (BackstopJS) | `visual` | Compare against C10 prototype baselines |
| Route smoke (137 routes, see [C06-apis/](../../C-Design/C06-apis/)) | `route-smoke` | Expected status codes per route group |
| Editor usability check on a C11 component | `editor-usability` | Verify non-developer can edit business-owned content |

## Planned test targets (when the suite is built)

Based on [D03 quality-test-suite](../../D-Development/D03-stories/quality-test-suite.md) and the module specs in [C05-modules/](../../C-Design/C05-modules/):

| Layer | Tool | Priority modules / scope |
|-------|------|--------------------------|
| Kernel (PHPUnit) | `KernelTestBase` | `lms_quiz` (8 GradingStrategy classes), `lms_certificate` (event subscriber), `lms_progress` (ProgressCascadeService concurrency), `lms_payment` (PaymentGatewayManager) |
| Functional/browser (PHPUnit) | `BrowserTestBase` | Top 20 controllers (HomepageController, QuizTakeController, CourseCatalogController …) |
| API contract | PHPUnit / REST client | `lms_mobile_api` `/api/v1/*` — 31 mobile routes, custom bearer-token auth |
| E2E | Playwright | Golden flows WF-001 (enrollment → quiz → certificate), WF-004 (order management) |
| Accessibility | Playwright + axe-core | WCAG 2.1 AA on 8 key pages; zero critical violations required |
| Security | OWASP ZAP baseline | Zero HIGH findings (G4 gate) |
| Performance | k6 | Catalog P95 < 2s at 100 VUs — results go to [E12-performance-results/](../E12-performance-results/) |
| Visual regression | BackstopJS | 10 pages × 375/768/1440 px viewports |

## Template

Each run file follows the shape defined in `.agent/00-Guidelines/A08-layer-templates/05-e-quality/E04-test-results.md`. Key fields:

- **Run ID** — `RUN-{YYYY-MM-DD}-{NNN}`
- **Triggered By** — `agent | CI | SA | scheduled`
- **Result** — `PASS | FAIL | PARTIAL`
- Failing tests must link to `../E02-issues/` entries.
- Coverage thresholds are declared in `A01-CLAUDE.md`; this file records actuals only.

## Rules

- Files are **immutable** once written. Do not edit a past run record.
- A FAIL result without a corresponding `E02-issues/` entry is incomplete.
- The latest run per scope is the authoritative "current test state" for checkpoints (CP5).
- Regression detection: compare against the previous run of the same scope; flag any test that flipped PASS → FAIL.

## Cross-links

- Test strategy (planned): `../E01-test-strategy.md`
- Issues tracker: `../E02-issues/`
- Test definitions: `../E03-test-definitions/`
- Story driving test creation: [D03 quality-test-suite](../../D-Development/D03-stories/quality-test-suite.md)
- Module specs referenced by test targets: [C05-modules/](../../C-Design/C05-modules/)
- Business workflows (e2e golden flows): [B04-business-workflows/](../../B-Requirements/B04-business-workflows/)
- Performance runs (separate folder): `../E12-performance-results/`
