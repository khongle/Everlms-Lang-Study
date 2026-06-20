# E09 — Impact Analysis Index

This folder is the registry of per-change impact analyses for EverLMS. Each row in this index corresponds to a materialized impact analysis instance stored in `M-Maintenance/M03-impact-templates/` (planned, layer M — not yet materialized).

## Status: empty — no impact analyses have been commissioned yet

As of 2026-06-20, the codebase is in active initial development. No maintenance changes, hotfixes, or high-risk enhancements requiring a formal impact analysis have been processed. This registry will be populated as changes of the qualifying type are proposed.

## What goes here

This file (`README.md`) serves as the index. When an impact analysis is commissioned:

1. A new row is appended to the **File Registry** table below (once entries exist).
2. A corresponding instance file is created at `M-Maintenance/M03-impact-templates/{variant}-{NNN}.md` (planned, layer M — not yet materialized), following the canonical instance shape in `.agent/00-Guidelines/A09-operational-templates/04-impact.md`.

**This file is the index only.** On conflict, the M03 instance is authoritative; this registry is regenerated from M03 instances when drift is detected.

## When to commission an impact analysis

An impact analysis is required before executing any of the following:

| Change type | Variant | Notes |
|-------------|---------|-------|
| Hotfix to a live system (any severity) | abbreviated | Must complete before deploying |
| Enhancement touching payment, auth, or data-integrity paths | full | e.g., changes to `lms_payment` / `lms_stripe` / `lms_order` — see [C05-modules/lms_payment.md](../../C-Design/C05-modules/lms_payment.md) |
| Drupal core or critical contrib update | full | Drupal 11.3.5 → next minor/major |
| New module dependency added to `composer.json` | abbreviated | Check for conflict with 27 custom modules |
| Schema migration (new field, table change) | full | Reference [D07-code-map/](../../D-Development/D07-code-map/) and `db-schema.md` facts |
| Removal or refactor of a shared service (e.g., `LmsHelper`, `GradingStrategyFactory`) | full | Blast radius across all 27 lms_* modules |
| Secrets migration (e.g., moving plaintext config YAML secrets to Key module) | full | «secret in config — see security risk» — affects all third-party integration modules |

Variant choice (full vs. abbreviated) follows the decision tree in M02 §3 (planned, layer M — not yet materialized).

## Known high-risk areas requiring impact analysis when addressed

Based on the current codebase facts:

| Area | Risk | Relevant module specs |
|------|------|-----------------------|
| No webhook signature verification | RISK — Stripe/PayPal webhook spoofing possible | [C05-modules/lms_stripe.md](../../C-Design/C05-modules/lms_stripe.md), [C05-modules/lms_payment.md](../../C-Design/C05-modules/lms_payment.md) |
| `lms_paypal` dead code | Any attempt to enable it will fail silently | [C05-modules/lms_paypal.md](../../C-Design/C05-modules/lms_paypal.md) |
| `lms_badge` near-stub | Enabling award logic requires entity + event changes across modules | [C05-modules/lms_badge.md](../../C-Design/C05-modules/lms_badge.md) |
| `lms_ai` no rollback on partial failure | CourseBuilder queue worker can leave partial state | [C05-modules/lms_ai.md](../../C-Design/C05-modules/lms_ai.md), [D03-stories/ai-rollback-compensation.md](../../D-Development/D03-stories/ai-rollback-compensation.md) |
| `lms_notification` email/push stubs | Enabling channels requires external service integration | [C05-modules/lms_notification.md](../../C-Design/C05-modules/lms_notification.md) |
| Secrets in committed config YAML | Migrating to Key module touches all integration configs | «secret in config — see security risk» |

## File Registry

_No entries yet. First entry will be appended here when the first impact analysis is commissioned._

| Impact ID | Change set | Date | Author persona | Variant | M03 instance file | Status |
|-----------|------------|------|----------------|---------|-------------------|--------|
| — | — | — | — | — | — | — |

## Rules

- Do NOT use E09 for retrospectives. Use [E07-retrospectives/](../E07-retrospectives/) for Epic, incident, or framework retrospectives.
- Every maintenance or high-risk change requiring impact analysis MUST create a row in this registry AND populate the matching M03 instance file.
- The registry is the index; the M03 instance is the canonical content.
- Change requests (governance trigger) live in `G-Governance/G04-change-requests/` (planned, layer G — not yet materialized).

## Cross-links

- Module specs (blast-radius source): [C05-modules/](../../C-Design/C05-modules/)
- Code map (reverse-trace support): [D07-code-map/](../../D-Development/D07-code-map/)
- Stories for known high-risk items: [D03-stories/](../../D-Development/D03-stories/)
- Issues tracker: `../E02-issues/`
- Retrospectives (change-triggered): `../E07-retrospectives/`
- M03 instance template location: `M-Maintenance/M03-impact-templates/` (planned, layer M — not yet materialized)
- Canonical instance shape: `.agent/00-Guidelines/A09-operational-templates/04-impact.md`
