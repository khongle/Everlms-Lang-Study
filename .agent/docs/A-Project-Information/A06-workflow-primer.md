# A06 — Workflow Primer

> Distilled snapshot of the development and maintenance process for EverLMS. The authoritative source is held by the project owner in `Guidelines/`.

---

## Gate Sequence

Every unit must pass gates in order. No skipping.

| Gate | Name | Pass criterion | Max retries |
|------|------|----------------|-------------|
| G1 | Build | `ddev exec drush cr` exits 0, zero errors | 3 |
| G2 | Lint | `ddev exec vendor/bin/phpcs --standard=Drupal,DrupalPractice web/modules/custom/` exits 0 | 3 |
| G3 | Tests | `ddev exec vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/` passes, coverage >= threshold | 3 |
| G4 | Adversarial Self-Review | 7 categories examined, Critical/High fixed | 3 |

### G4 Categories

1. **Security** — SQL injection, XSS, CSRF, auth bypass, secrets exposure, input validation, `_access: TRUE` routes
2. **Error Handling** — unhandled exceptions, silent failures, retry/backoff for OpenAI/Stripe/PayPal
3. **Performance** — N+1 queries, unbounded results, missing cache tags, blocking SCORM unzip in hooks
4. **Edge Cases** — null/empty inputs, race conditions (progress cascade), concurrent quiz submit, timezone
5. **Architecture** — `\Drupal::` static calls, circular module deps, oversized controllers, no `#markup`
6. **Data Integrity** — missing transactions (order+enrollment), orphaned entities, cascade correctness
7. **Access Control** — EntityAccessControlHandler missing, route permissions vs controller-only auth

"Looks good" is forbidden and auto-fails G4. Either surface a finding with severity and fix, or cite specific files/checks examined.

### EverLMS G4 High-Risk Areas

| Area | What to check | Related FIND |
|------|---------------|-------------|
| Payment webhooks | Signature verification, idempotency | FIND-001, FIND-002 |
| Progress cascade | Race condition, atomic updates | RISK-002 |
| AI provider calls | max_tokens cap, API key exposure | FIND-003, FIND-008 |
| Checkout routes | Authentication at routing layer | FIND-004 |
| Order+enrollment | Atomic transaction | FIND-005 |
| Notification API | Anonymous access | FIND-013 |

## Checkpoints

| CP | Exit from | Approver | Key criteria | EverLMS status |
|----|-----------|----------|-------------|----------------|
| CP1 | Ideation | SA | Requirements clear, scope bounded | PASSED |
| CP2 | Architecture | SA | Design sound, risks identified, C-layer complete | PASSED |
| CP3 | Framework Setup | SA | Scaffold verified, A01-CLAUDE validated, D01 approved | PASSED |
| CP4 | Per-group (Development) | SA | All group units DONE, G1-G4 passed | IN PROGRESS (5/9 groups passed) |
| CP5 | Quality + Ops Readiness | SA | E-layer complete, NFRs met, H-layer operational | BLOCKED by G-HARDENING |
| CP6 | Release | SA | Deployment verified, H-layer ready | NOT STARTED |

## Development Phases

1. **Ideation** — define scope, requirements, features (exits CP1)
2. **Architecture** — design C-layer, integrations, risks (exits CP2)
3. **Framework Setup** — scaffold repo, validate A01, write D01 (exits CP3)
4. **Development** — implement units group by group, pass G1-G4, CP4 per group
5. **Quality + Ops Readiness** — verify E-layer, NFRs, H-layer operational (exits CP5)
6. **Release** — deploy, verify (exits CP6 → Maintenance)

## Personas

| Persona | Phase(s) | Handoff hint |
|---------|----------|--------------|
| Agent_BA | Ideation | → Architect once B01-B03 frozen at CP1 |
| Agent_Architect | Architecture, Setup | → Coder once unit specs (D03) ready |
| Agent_Coder | Development | → QA once G1-G4 pass for the unit |
| Agent_QA | Quality | → PM once NFRs met (CP5); back to Coder on G4 Critical/High |
| Agent_PM | Release | → Architect (rare) on scope-changing CR |

Every handoff requires a named artifact (checkpoint, spec, gate report).

## Maintenance Types

| Type | Indicator | SLA | Feature flag |
|------|-----------|-----|-------------|
| HOTFIX | `RED` | < 4 hours | Required if high-risk |
| BUG_FIX | `YELLOW` | < 1 sprint | Optional |
| ENHANCEMENT | `BLUE` | Planned sprint | Required if high-risk |
| REFACTOR | `PURPLE` | Planned sprint | Not typically required |
| DEPENDENCY_UPDATE | `ORANGE` | Per CVE severity | Required if API changes |

Maintenance state machine: `TRIAGE → IMPACT_ANALYSIS → PLANNING → IMPLEMENTATION → GATES → DEPLOY → VERIFY`.
Classification locked at TRIAGE. Scope expansion mid-flight = STOP + split into new ticket.

Playbooks: `M-Maintenance/M01-maintenance-workflows/` (one per type).

## Session Protocol

### At session start:

1. Load Tier 1 files (A01-CLAUDE, A03-project-context, D04-session-status, D06-handoff-protocol)
2. Read D06 for pending work; identify current phase and active unit(s)
3. Load phase-specific context on demand (see A05 Tier 2 table)
4. Keep total context under 80% of model window
5. Announce: phase, active units, context usage

### During session:

- Update `D07-code-map/` whenever files are created or modified
- Wrap high-risk changes in a feature flag (`FF_{TYPE}_{ID}`, default OFF)
- Log issues into `E02-issues/`; Medium/Low from G4 also land there

### At session end:

1. Append entry to `D04-session-status.md`
2. Update `D06-handoff-protocol.md` with what was completed, what remains, gotchas
3. Update `A03-project-context.md` if phase or major state changed
4. Report: what was done, what's next, any blockers

---

*End of A06-workflow-primer.md*
