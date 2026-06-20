# E12 — Performance Results

This folder holds the outcome record of every load, stress, soak, endurance, and spike test run against EverLMS. Each file captures measured throughput, latency, error rate, resource usage, and the SLA verdict for a single run. This folder is distinct from [E04-test-results/](../E04-test-results/) (functional test runs) and [E11-chaos-results/](../E11-chaos-results/) (chaos experiments).

## Status: empty — no performance runs have been executed

As of 2026-06-20, no performance test tooling (k6, JMeter, Locust) is configured and no dedicated staging/perf environment exists. Performance testing is planned as part of the pre-v1.0 hardening work described in [D03 Story: quality-test-suite](../../D-Development/D03-stories/quality-test-suite.md) (Task TASK-084, Epic E-10).

## What goes here

One file per performance run, named:

```
perf-{YYYY-MM-DD}-{HHMM}-{kind}.md
```

where `kind` is one of: `load` | `stress` | `soak` | `endurance` | `spike`.

Example: `perf-2026-09-15-1400-load.md` — load test run on 2026-09-15 at 14:00.

## When to create an entry

| Trigger | Kind | Minimum cadence |
|---------|------|-----------------|
| Pre-release for any change touching hot-path routes | `load` | Every release |
| New module added that serves public traffic | `load` | Once per new module |
| Finding a throughput regression in E04 functional tests | `stress` | On demand |
| Soak/memory-leak investigation | `soak` | Min 4 hours; before major releases |
| Autoscaler validation or burst-capacity test | `spike` | Before capacity changes |

A run is also required at **CP6** (pre-release checkpoint) for any Service whose `H06-capacity.md` entry declares `capacity_sensitivity: HIGH` or `MEDIUM` (planned, layer H — not yet materialized). Since H06 is not yet materialized, refer to the module specs below for the candidate list.

## Planned performance targets (NFR source: `../E10-nfr-tracking.md`)

Based on [D03 quality-test-suite](../../D-Development/D03-stories/quality-test-suite.md) and [B02-technical-requirements.md](../../B-Requirements/B02-technical-requirements.md):

| Endpoint / scenario | Tool | Target | Notes |
|---------------------|------|--------|-------|
| `GET /courses` — course catalog | k6 | P95 < 2s at 100 concurrent users, sustained 5 min | High-traffic page; uses Views + taxonomy filters |
| `GET /courses/{slug}` — course detail | k6 | P95 < 2s at 100 VUs | Includes enrollment status check |
| `POST /quiz/{id}/submit` — quiz submit | k6 | P95 < 3s at 50 VUs | Triggers `GradingStrategyFactory` + `ProgressCascadeService` |
| `GET /api/v1/*` — mobile API (all 31 routes) | k6 | P95 < 500ms at 200 VUs | Bearer-token auth via custom keyvalue.expirable |
| `POST /api/v1/quiz/submit` — mobile quiz submit | k6 | P95 < 3s at 100 VUs | Same grading path as web |
| Full checkout flow (`/checkout` → Stripe PaymentIntent) | k6 | P95 < 5s at 20 VUs | Note: Stripe PaymentIntent created on form BUILD (known bug — see [C05-modules/lms_stripe.md](../../C-Design/C05-modules/lms_stripe.md)) |

## High-risk modules to include in early runs

| Module | Performance concern | Spec |
|--------|---------------------|------|
| `lms_progress` | `ProgressCascadeService` — concurrent writes to progress tables; known race-condition risk | [C05-modules/lms_progress.md](../../C-Design/C05-modules/lms_progress.md) |
| `lms_quiz` | `GradingStrategyFactory` called synchronously on submit; 8 strategy classes | [C05-modules/lms_quiz.md](../../C-Design/C05-modules/lms_quiz.md) |
| `lms_stripe` | `PaymentIntent` created on form BUILD (one per page load, not per submit) — duplicate API calls under load | [C05-modules/lms_stripe.md](../../C-Design/C05-modules/lms_stripe.md) |
| `lms_mobile_api` | 31 `/api/v1/*` routes; custom auth token lookup via `keyvalue.expirable` on every request | [C05-modules/lms_mobile_api.md](../../C-Design/C05-modules/lms_mobile_api.md) |
| `lms_ai` | `CourseBuilder` queue worker — async but may back up under concurrent AI content generation; OpenAI/Gemini rate limits | [C05-modules/lms_ai.md](../../C-Design/C05-modules/lms_ai.md) |
| `lms_certificate` | mPDF generation on certificate issuance — CPU-intensive; not async | [C05-modules/lms_certificate.md](../../C-Design/C05-modules/lms_certificate.md) |

## Performance test script location (once created)

Per [D03 quality-test-suite](../../D-Development/D03-stories/quality-test-suite.md), scripts will live at:

```
tests/perf/k6-catalog.js          # catalog load test (TASK-084)
tests/perf/k6-quiz-submit.js      # quiz submit load test
tests/perf/k6-mobile-api.js       # mobile API contract + load
```

## Template

Each run file follows the shape in `.agent/00-Guidelines/A08-layer-templates/05-e-quality/E12-performance-results.md`. Key fields:

- **Run ID** — `PERF-{YYYY-MM-DD}-{NNN}`
- **Kind** — `load | stress | soak | endurance | spike` (one kind per file; do not mix)
- **Verdict** — `PASS | FAIL | INCONCLUSIVE`
- **Targets** come from `../E10-nfr-tracking.md` — this file records actuals only
- FAIL verdicts must link to `../E02-issues/` entries

## Rules

- One file per run. Files are **immutable** once written.
- A `soak`/`endurance` run must be at least 4 hours to qualify for leak/drift detection. Shorter runs are labelled `load`.
- Never run write-load tests against production. Use staging or a dedicated perf environment.
- Failing runs MUST link to `../E02-issues/` entries. A FAIL without an issue entry is incomplete.
- Any new bottleneck found triggers a review of H06 capacity model and H07 alerts (planned, layer H — not yet materialized).

## Cross-links

- NFR targets: `../E10-nfr-tracking.md`
- Functional test runs: `../E04-test-results/`
- Chaos experiments: `../E11-chaos-results/`
- Issues opened from findings: `../E02-issues/`
- Story driving perf test creation: [D03-stories/quality-test-suite.md](../../D-Development/D03-stories/quality-test-suite.md)
- Module specs: [C05-modules/](../../C-Design/C05-modules/)
- Technical requirements (NFR source): [B02-technical-requirements.md](../../B-Requirements/B02-technical-requirements.md)
