# A06-workflow-primer.md — Workflow Primer

> Distilled snapshot of the development and maintenance process for EverLMS. The authoritative source is held by the project owner in `Guidelines/`.

**Last Updated:** 2026-06-20

---

## Development Phases

The framework organizes work into six sequential phases plus a continuous post-release Maintenance phase. Each phase exits through a named checkpoint requiring SA approval.

| # | Phase | Key outputs | Exit gate |
|---|-------|-------------|-----------|
| 1 | **Ideation** | Requirements (B01–B02), feature list (B03), acceptance criteria sketch | CP1 |
| 2 | **Architecture** | C-layer design (entities, modules, APIs, workflows, skins), risks identified | CP2 |
| 3 | **Framework Setup** | Repo scaffold, A01-CLAUDE validated, D01 build order approved, first modules bootable | CP3 |
| 4 | **Development** | Stories implemented Epic by Epic; G1–G4 gates pass per Story; CP4 per Epic | CP4 (per Epic) |
| 5 | **Quality** | E-layer complete, NFRs validated, H-Operations baseline ready | CP5 |
| 6 | **Release** | Deployment verified, H-Operations moves to operational, chaos/capacity/observability confirmed | CP6 → Maintenance |

---

## Gate Sequence (G1–G4)

Gates run per Story during Development. Every Story must pass all four gates in order before it is marked DONE. No skipping.

| Gate | Name | Pass criterion | Max retries |
|------|------|----------------|-------------|
| G1 | Build | `ddev exec drush cr` exits 0, zero errors | 3 |
| G2 | Lint | `ddev exec vendor/bin/phpcs --standard=Drupal,DrupalPractice web/modules/custom/` exits 0 | 3 |
| G3 | Tests | `ddev exec vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/` passes, coverage meets threshold | 3 |
| G4 | Adversarial Self-Review | All 7 categories examined; Critical/High findings fixed; Medium+ logged to E02-issues/ | 3 |

### G4 Review Categories

G4 is a structured self-audit covering seven areas. "Looks good" is forbidden — either surface a specific finding with severity and resolution, or cite the exact files and checks examined.

1. **Security** — SQL injection, XSS, CSRF, auth bypass, secrets exposure, input validation, `_access: TRUE` routes
2. **Error Handling** — unhandled exceptions, silent failures, missing retry/backoff for external calls (Stripe, OpenAI, Zoom)
3. **Performance** — N+1 queries, unbounded result sets, missing cache tags, synchronous blocking in hooks
4. **Edge Cases** — null/empty inputs, race conditions (e.g., concurrent quiz submissions, progress cascade), timezone handling
5. **Architecture** — static `\Drupal::` calls outside migration context, circular module dependencies, oversized controllers, forbidden patterns from D00-conventions
6. **Data Integrity** — missing database transactions (especially order + enrollment pairs), orphaned entities, cascade correctness
7. **Maintainability** — function/file/complexity within A01-CLAUDE thresholds, no dead code, no unlinked TODOs, test coverage maintained

All Medium+ findings must be closed or explicitly SA-deferred before CP5.

---

## Checkpoints (CP1–CP6)

Checkpoints are phase-transition review points. SA must approve before work proceeds to the next phase.

| CP | Exits from | Approver | Key criteria |
|----|-----------|----------|--------------|
| CP1 | Ideation | SA | Requirements are clear and scope is bounded; B01–B03 frozen |
| CP2 | Architecture | SA | Design is sound, risks are identified and logged; C-layer substantially complete |
| CP3 | Framework Setup | SA | Scaffold verified, A01-CLAUDE validated, D01 approved; first modules boot without errors |
| CP4 | Per-Epic (within Development) | SA | All Stories in the Epic are DONE, G1–G4 passed per Story, cross-Story integration tests pass |
| CP5 | Quality + Ops Readiness | SA | E-layer complete; NFRs met; H-Operations baseline (H01–H07); all Medium+ G4 findings closed or SA-deferred |
| CP6 | Release | SA | Deployment verified; H-Operations moves from baseline to operational; chaos/capacity/observability ready; go/no-go signed off |

CP4 repeats for each Epic — it is not a single milestone. CP5 and CP6 each occur once per release scope.

---

## Personas

| Persona | Active in | Responsibility |
|---------|-----------|----------------|
| **SA** (human) | All phases | Final approval at every checkpoint; sets architecture, module boundaries, and acceptance criteria |
| **Agent_BA** | Ideation | Defines requirements and features; hands off to Architect once B01–B03 are frozen at CP1 |
| **Agent_Architect** | Architecture, Framework Setup | Designs C-layer, entity/module map, integrations; hands to Implementer once Story specs (D03) are ready |
| **Agent_Implementer** | Development | Builds entire Stories end-to-end under SA direction; hands to QA once G1–G4 pass for the Story |
| **Agent_QA** | Quality | Validates NFRs, test coverage, E-layer completeness; hands to PM once NFRs are met (CP5); hands back to Implementer on G4 Critical/High failures |
| **Agent_PM** | Release | Coordinates release readiness; hands back to Architect (rare) on scope-changing change requests |

Every handoff requires a named artifact (checkpoint record, spec file, or gate report). The receiver acknowledges before starting work.

---

## Maintenance Types

After CP6, changes flow through a typed maintenance process. The type is locked at triage — scope expansion during implementation requires stopping and splitting into a new ticket.

| Type | Priority indicator | SLA | Feature flag required |
|------|--------------------|-----|-----------------------|
| HOTFIX | Critical (production down) | Under 4 hours | Yes, if high-risk rollback needed |
| BUG_FIX | High/Medium | Under 1 sprint | Optional |
| ENHANCEMENT | Planned | Scheduled sprint | Yes, if high-risk |
| REFACTOR | Internal quality | Scheduled sprint | Not typically required |
| DEPENDENCY_UPDATE | CVE-driven | Critical CVE: same day; High: 1 week; Medium/Low: next sprint | Yes, if API changes |

Maintenance state machine: `TRIAGE → IMPACT_ANALYSIS → PLANNING → IMPLEMENTATION → GATES → DEPLOY → VERIFY`.

Playbooks for each type: `M-Maintenance/M01-maintenance-workflows/` (planned, layer not yet materialized) (planned, layer M — not yet materialized).

---

## Session Protocol

### At session start

1. Load the four Tier 1 files: `A01-CLAUDE.md`, `A03-project-context.md`, `D05-session-status.md`, `D06-handoff-protocol.md`.
2. Read `D06-handoff-protocol.md` for pending work; identify the current phase and active Story.
3. Load phase-specific context on demand. Keep total context under 80% of the model's window.
4. Announce: current phase, active Story, owning modules, approximate context usage.

### During the session

- Update `D07-code-map/` whenever files are created or significantly modified.
- Wrap high-risk changes in a feature flag (`FF_{TYPE}_{ID}`, default OFF).
- Log issues discovered into `E02-issues/`; G4 Medium/Low findings go there too.

### At session end

1. Append an entry to `D05-session-status.md` (latest first).
2. Update `D06-handoff-protocol.md`: what was completed, what remains, any gotchas.
3. Update `A03-project-context.md` if the phase or a major state changed.
4. Report to SA: what was done, what is next, any blockers.

---

*End of A06-workflow-primer.md*
