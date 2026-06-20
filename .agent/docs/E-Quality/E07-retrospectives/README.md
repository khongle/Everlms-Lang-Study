# E07 — Retrospectives

This folder holds Epic close-out retrospectives, incident postmortems, and framework retrospectives for EverLMS. Each file is a materialized retro artifact, immutable once written.

## Status: empty — no Epics have been closed, no incidents logged, no framework retro run

As of 2026-06-20, all Epics defined in [D02-epics/](../../D-Development/D02-epics/) are in progress or pending. No SEV1–SEV3 incidents have been formally recorded. This folder remains empty until the first Epic reaches CP4 (close) or the first incident is declared.

## What goes here

One file per retrospective event. Three retro types are supported:

| `retro_type` | Trigger | Required fields |
|---|---|---|
| `epic` | Closing an Epic at CP4 | Stories closed, G1–G4 gate outcomes, NFR posture, H-layer readiness, carry-over list |
| `incident` | Any SEV1–SEV3 incident (production or staging) | `INC-{NNN}` ID, severity, detection→mitigation→resolution timeline, 5-whys root cause, re-occurrence guard |
| `framework` | Periodic framework retro pass | Retro window, prompt SLAs, A07 evolution-log entries produced, diff plan |

File naming: use a descriptive slug, e.g., `epic-E01-foundation-close.md`, `incident-INC-001-forum-debug.md`, `framework-2026-Q3.md`.

## When to create an entry

### Epic retro (`retro_type: epic`)

Create at CP4 (Epic close) for each Epic in [D02-epics/](../../D-Development/D02-epics/). Inputs:

- All D03 Story IDs delivered in the Epic (from [D03-stories/](../../D-Development/D03-stories/))
- Gate pass/fail/retry summary across the Epic's Stories
- E10 NFRs touched (from `../E10-nfr-tracking.md`)
- H-layer (H01–H07) baseline check (planned, layer H — not yet materialized)
- Velocity and scope-drift analysis
- Carry-over items with G04 CR or G03 blocker links

### Incident postmortem (`retro_type: incident`)

Create within 48 hours of resolving any SEV1–SEV3 incident. Known bugs that may trigger incidents once traffic hits production:

| Known bug | Module | Risk | Issue |
|-----------|--------|------|-------|
| `ForumController::viewTopic` throws `"HARD DEBUG!"` | `lms_forum` | SEV2 if triggered by end users | `../E02-issues/` |
| `lms_rating` missing 3 runtime routes (HTTP 404) | `lms_rating` | SEV3 | `../E02-issues/` |
| Stripe `PaymentIntent` created on form BUILD (one per page load) | `lms_stripe` | SEV2 — duplicate charges risk | `../E02-issues/` |
| No webhook signature verification (Stripe/PayPal) | `lms_payment`, `lms_stripe` | SEV1 if webhooks exposed | `../E02-issues/` |

See [C05-modules/lms_forum.md](../../C-Design/C05-modules/lms_forum.md), [C05-modules/lms_stripe.md](../../C-Design/C05-modules/lms_stripe.md), [C05-modules/lms_payment.md](../../C-Design/C05-modules/lms_payment.md).

### Framework retro (`retro_type: framework`)

Run periodically (suggested quarterly). Input: all Epic retros and incident postmortems since the previous framework retro, plus prompt SLA checks per A07.

## Template

Each file follows the shape in `.agent/00-Guidelines/A08-layer-templates/05-e-quality/E07-retrospectives.md`. Required frontmatter:

```yaml
---
retro_type: epic | incident | framework
date: YYYY-MM-DD
scope: {Epic ID / INC-NNN / framework-retro-YYYY-MM-DD}
---
```

Key sections: Metrics Summary, What Went Well, What Could Improve, Action Items, Patterns to Keep (→ K03, planned), Lessons Learned (→ K04, planned).

## Rules

- One file per retro event. Files are immutable once written.
- Incident retros MUST be completed within 48 hours of resolution.
- Epic retros are required at CP4 — a missing epic retro blocks Epic close.
- Lessons that surface a reusable rule go to K04 (planned, layer K — not yet materialized). If no reusable rule is surfaced, record `K04: NONE — no reusable rule surfaced`.
- Action items from retros that require code changes link back to `../E02-issues/` or D04-tasks.

## Cross-links

- Epics list: [D02-epics/](../../D-Development/D02-epics/)
- Stories: [D03-stories/](../../D-Development/D03-stories/)
- Issues tracker: `../E02-issues/`
- NFR tracking: `../E10-nfr-tracking.md`
- Impact analysis (for change-triggered retros): `../E09-impact-analysis/`
- Chaos drill retros (if hypothesis falsified): `../E11-chaos-results/`
- Module specs (for incident context): [C05-modules/](../../C-Design/C05-modules/)
- Evolution log: [A07-evolution-log.md](../../A-Project-Information/A07-evolution-log.md)
