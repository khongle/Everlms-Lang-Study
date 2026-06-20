# E11 — Chaos Results

This folder holds the outcome record of every chaos engineering experiment run against EverLMS. Each file documents a single experiment: hypothesis, fault injection, measurements, findings, and signoff.

## Status: empty — no chaos drills have been run

As of 2026-06-20, EverLMS has not yet reached the staging/pre-prod environment tier required to safely run chaos experiments. No automated test suite exists yet (see [E04-test-results/](../E04-test-results/)) and no observability stack (H07, planned) is in place. Chaos drills are planned as part of the pre-v1.0 hardening Epic E-10.

## What goes here

One file per chaos experiment, named:

```
chaos-{YYYY-MM}-{NNN}.md
```

Example: `chaos-2026-08-001.md` — first experiment in August 2026.

## When to create an entry

Create a new file **after** completing (or aborting) a chaos experiment. The file must be populated **before** running the experiment with the hypothesis, success criteria, and pre-experiment checklist — then completed with the actual measurements and findings.

### Qualifying experiments for EverLMS

Based on the current architecture ([C01-architecture-decision.md](../../C-Design/C01-architecture-decision.md), [C05-modules/](../../C-Design/C05-modules/)):

| Scenario | Hypothesis | Key module(s) | Priority |
|----------|------------|---------------|----------|
| DB connection exhaustion | `ProgressCascadeService` degrades gracefully under concurrent writes; no data corruption | `lms_progress` | High — see [D03 progress-race-condition](../../D-Development/D03-stories/progress-race-condition.md) |
| Cache (Redis/Memcache) failure | Catalog and course-detail pages fall back to DB reads; P99 < 2s | `lms_course`, `lms_lesson` | High |
| Payment gateway timeout (Stripe) | Checkout returns a recoverable error; no duplicate `PaymentIntent` created | `lms_stripe`, `lms_order` | High — see [C05-modules/lms_stripe.md](../../C-Design/C05-modules/lms_stripe.md) |
| AI provider failure (OpenAI/Gemini) | `CourseBuilder` queue worker fails cleanly; partial state rolled back or flagged | `lms_ai` | Medium — see [D03 ai-rollback-compensation](../../D-Development/D03-stories/ai-rollback-compensation.md) |
| Zoom API unavailable | Zoom lesson view degrades gracefully; error not fatal | `lms_zoom` | Medium — `generateSignature()` bug is a pre-existing risk |
| DDEV/nginx restart mid-request | In-flight quiz submissions are not double-counted | `lms_quiz`, `lms_progress` | Medium |

## Prerequisites before running any experiment

- A dedicated staging environment (not the developer DDEV instance) must exist.
- Observability dashboards (H07, planned) must be pre-loaded — chaos without monitoring is not a drill, it is an incident.
- The hypothesis must be written and agreed **before** fault injection.
- Runbooks (H02, planned) for the expected failure mode must be identified.
- Abort conditions must be defined upfront.

**Do NOT run chaos against the production environment.**

## Template

Each experiment file follows the shape in `.agent/00-Guidelines/A08-layer-templates/05-e-quality/E11-chaos-results.md`. Key sections:

- **Metadata** — `CHAOS-{YYYY}-{MM}-{NNN}`, hypothesis, blast radius
- **Pre-Experiment Checklist** — must be complete before fault injection
- **Hypothesis** — falsifiable statement (style: "If X fails, Y still works")
- **Success Criteria** — measurable metrics with targets (latency, error rate, recovery time)
- **Fault Injection** — exact commands / steps
- **Experiment Log** — timestamped observations
- **Findings** — severity-ranked, each linked to an action item
- **Signoff** — QA lead + SA

## Rules

- One file per experiment. Files are **immutable** after signoff.
- Every finding MUST link to an action item in `../E02-issues/` or `H04-tech-debt.md` (planned, layer H — not yet materialized).
- Hypothesis MUST be written before fault injection — no blind experiments.
- If the hypothesis is falsified, schedule a retrospective in [E07-retrospectives/](../E07-retrospectives/).
- Environment must be staging-tier or better isolated. Never run in production.

## Cross-links

- Chaos runbook (scheduling, cadence): `.agent/00-Guidelines/A09-operational-templates/05-chaos-runbook.md`
- Issues opened from findings: `../E02-issues/`
- Retrospectives (if hypothesis falsified): `../E07-retrospectives/`
- Relevant stories (known risk areas): [D03-stories/progress-race-condition.md](../../D-Development/D03-stories/progress-race-condition.md), [D03-stories/ai-rollback-compensation.md](../../D-Development/D03-stories/ai-rollback-compensation.md), [D03-stories/payment-webhooks.md](../../D-Development/D03-stories/payment-webhooks.md)
- Module specs: [C05-modules/lms_progress.md](../../C-Design/C05-modules/lms_progress.md), [C05-modules/lms_stripe.md](../../C-Design/C05-modules/lms_stripe.md), [C05-modules/lms_ai.md](../../C-Design/C05-modules/lms_ai.md)
- NFR tracking (SLA targets): `../E10-nfr-tracking.md`
- Performance results (related load data): `../E12-performance-results/`
