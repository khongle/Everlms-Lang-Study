# A06-workflow-primer - Workflow Snapshot

**Last updated:** 2026-06-21

The distilled how-we-work snapshot. Full machinery: `.agent/00-Guidelines/A02-agent-operating-protocol.md` (personas/phases) and `.agent/00-Guidelines/A06-validation-and-adequacy-gates.md` (gates).

## Phases
1. **Ideation / Requirements** (Agent_BA) → CP1
2. **Architecture** (Agent_Architect) → CP2
3. **Framework setup** (Agent_Architect) → CP3
4. **Development** (Agent_Implementer) → CP4 per Epic
5. **Quality** (Agent_QA) → CP5
6. **Release** (Agent_PM) → CP6
7. **Maintenance / Post-release** (Agent_PM, IC) → continuous

> For EverLMS (a rebuild), Phases 1-3 are executed **as-built** — requirements/architecture/framework are reverse-documented from the existing code rather than designed forward; Phases 4-7 slots are scaffolded for ongoing work (gap-closure, hardening, release).

## Gate sequence
- **G1 Build → G2 Lint → G3 Tests → G4 Adversarial.** Strict order; max 3 retries/gate; 3 fails ⇒ BLOCKED + notify SA + STOP.
- Checkpoints **CP1-CP6** are SA-approved exit gates; **CP4** runs per Epic.
- G4 forbids "Looks good" without proof across 7 categories: SECURITY, ERROR_HANDLING, PERFORMANCE, EDGE_CASES, ARCHITECTURE, DATA_INTEGRITY, MAINTAINABILITY.
- ⚠️ As-built: no automated test suites exist yet, so G3 is effectively manual — see `E-Quality/E01-test-strategy/`.

## Maintenance work types
`HOTFIX | BUG_FIX | ENHANCEMENT | REFACTOR | DEPENDENCY_UPDATE` — mirrored by `M-Maintenance/M01-maintenance-workflows/`.
