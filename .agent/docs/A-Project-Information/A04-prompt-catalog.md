# A04 — Prompt Catalog

> Index of prompts used in this project. Source files live in `Guidelines/A10-prompts/`.
> Update when prompts are added, tested, or retired.

---

## Phase Prompts

| ID | Phase | Prompt | Source | Status |
|----|-------|--------|--------|--------|
| P-IDEA | 1-Ideation | Requirements elicitation | Guidelines/A10-prompts/01-ideation.md | done (CP1 passed) |
| P-ARCH | 2-Architecture | Entity + module design | Guidelines/A10-prompts/02-architecture-planning.md | done (CP2 passed) |
| P-SETUP | 3-Framework | DDEV + module scaffold | Guidelines/A10-prompts/03-framework-setup.md | done (CP3 passed) |
| P-DEV | 4-Development | Unit implementation flow | Guidelines/A10-prompts/04-development.md | active |
| P-QUAL | 5-Quality | Test strategy + E-layer gates | Guidelines/A10-prompts/05-quality.md | ready |
| P-REL | 6-Release | Deployment + F-layer | Guidelines/A10-prompts/06-release.md | ready |
| P-MAINT | 7-Maintenance | Classify + dispatch | Guidelines/A10-prompts/07-maintenance.md | ready |
| P-POST | 7-Maintenance | 7-day monitoring | Guidelines/A10-prompts/08-post-release.md | ready |
| P-INCIDENT | 7-Maintenance | IC dispatch | Guidelines/A10-prompts/09-incident-response.md | ready |
| P-FRAMEWORK_RETRO | Meta | Framework retrospective | Guidelines/A10-prompts/10-framework-retro.md | ready |

## Operational Templates

| ID | Template | Source | Use when |
|----|----------|--------|----------|
| T-SESSION | Session log | Guidelines/A09-operational-templates/01-session.md | Every session — write to D08-sessions/ |
| T-ADVERSARIAL | G4 adversarial review | Guidelines/A09-operational-templates/02-adversarial.md | Pre-CP review of high-risk units |
| T-CHECKPOINT | Checkpoint gate | Guidelines/A09-operational-templates/03-checkpoint.md | CP1-CP6 transitions |
| T-IMPACT | Impact analysis | Guidelines/A09-operational-templates/04-impact.md | Each maintenance change (see M03 for variants) |
| T-CHAOS | Chaos runbook | Guidelines/A09-operational-templates/05-chaos-runbook.md | Drill scheduling — results to E11 |
| T-CAPACITY | Capacity planning | Guidelines/A09-operational-templates/06-capacity-planning.md | Annual + scale-trigger fires — H06 |
| T-OBSERVABILITY | Observability-as-code | Guidelines/A09-operational-templates/07-observability-as-code.md | New SLO/dashboard/alert in H07 |

## Quick-Pick by Current Phase (4-Development)

1. **New unit** → P-DEV + T-SESSION + T-CHECKPOINT (gate at G4)
2. **Bug discovered** → P-MAINT → BUG_FIX playbook (M01) + T-IMPACT abbreviated
3. **G-HARDENING work** → P-DEV + T-ADVERSARIAL + ADR check (G01/ADR-009, ADR-010)

---

*End of A04-prompt-catalog.md*
