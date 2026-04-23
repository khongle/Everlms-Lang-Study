# D06 — Handoff Protocol

> **Tier 1** — always loaded at session start. Load order + handoff checklist.
> Update: at the end of every session if next actions change.

---

## Load Order (Next Session)

### Step 1 — Tier 1 (ALWAYS, ~5K tokens)
1. `A-Project-Information/A01-CLAUDE.md` — AI config, commands, conventions
2. `A-Project-Information/A03-project-context.md` — living state (phase, progress, decisions)
3. `A-Project-Information/A05-framework-primer.md` — distilled framework map (budget-safe)
4. `D-Development/D04-session-status.md` — last session outcomes
5. **`D-Development/D06-handoff-protocol.md`** (this file) — what's next

### Step 2 — Phase-specific Tier 2 (~10-15K tokens)
Current phase = **4-Development**. Load:
- `D-Development/D01-dev-overview.md` — group/unit map, progress dashboard
- `D-Development/D03-unit-specs/U-{XXX}-*.md` for the active unit(s) only
- `D-Development/D07-code-map/` — slice relevant to active module
- `E-Quality/E01-test-strategy.md` — how tests run
- `H-Operations/H05-feature-flags.md` — for flagged changes

### Step 3 — Work-type specific (load as needed)

| If working on… | Also load |
|----------------|-----------|
| **Commerce / checkout** | `B04-business-workflows/WF-004-order-management.md`, `WF-005-membership-subscription.md`, `B05-acceptance-criteria/AC-04-*.md`, `B06-integration-requirements/INT-001-stripe.md`, `INT-002-paypal.md` |
| **Certificate** | `B04/WF-003-certificate-issuance.md`, `C04-entities/05-certificate-entities.md` |
| **Assessment / quiz** | `C04-entities/02-assessment-entities.md`, ADR-005 (GradingStrategyFactory) |
| **Progress cascade** | `C04-entities/03-progress-entities.md`, ADR-008, ADR-010 (pending), RISK-002 in G02 |
| **Hardening (security)** | `B02-technical-requirements.md` §Security, `E01-test-strategy.md`, G02 RISK + FIND list |
| **AI provider** | ADR-006, `B06-integration-requirements/INT-004-openai.md`, `INT-005-gemini.md`, `H05-feature-flags.md` §AI flags |
| **Zoom integration** | `B06-integration-requirements/INT-003-zoom.md` |
| **Deployment / release** | `F01-deployment.md`, `F02-release-checklist/` |

---

## Next Actions (Priority Queue)

### P0 — Blocks production readiness

| Task | Why P0 | Related |
|------|--------|---------|
| **TASK-015** API input validation | RISK-001, NFR-008 | DEC-001, ADR-009 (pending SA sign-off) |
| **TASK-016** Access-control hardening | FIND-004, FIND-013, NFR-007 | `_access: TRUE` routes audit |
| **TASK-017** Progress atomicity | RISK-002, NFR-015 | DEC-003, ADR-010 (pending SA sign-off) |
| **TASK-002** Stripe webhook verification | FIND-001, RISK-004, NFR-009 | U-060, requires `STRIPE_WEBHOOK_SECRET` env |
| **U-060** Stripe webhook impl | Blocks payment launch | See TASK-002 |
| **TASK-001** Checkout flow real integration | G-COMMERCE critical path | Depends on U-060 |
| **TASK-006** Certificate issuance logic | G-CERTIFICATE | Depends on progress cascade fix |
| **TASK-007** Certificate PDF generation | G-CERTIFICATE | dompdf font cache setup |
| **TASK-008** Public cert verify | G-CERTIFICATE | `/verify/{code}` route |

### P1 — Feature completion

- **TASK-004** Membership lifecycle (expire/renew/upgrade)
- **TASK-009** Notification dispatch (10 types actual delivery)
- **TASK-010** Forum UI (topic create, reply, mention)
- **TASK-014** Analytics dashboards (admin + instructor)
- **TASK-018** Audit `\Drupal::` static calls across all modules

### P2 — Polish

- G-UI-POLISH group (14 units) — accessibility, responsive edge cases, error UX

---

## Gotchas (must know before touching code)

### Payment
- **Stripe handler is a stub** (`FIND-001`): `handleWebhook()` returns 200 without signature verify. Do not enable live mode until U-060 merges.
- **PayPal entirely TODO** (`FIND-002`, `FIND-009`): two duplicate `PayPalGateway` classes exist (`lms_payment` + `lms_paypal`); resolve to one.
- **Anon checkout** (`FIND-004`): `/checkout` routes use `_access: TRUE`. Tracked by TASK-015 — do not launch paid tier before fix.

### Progress
- **Race condition** (`RISK-002`): two concurrent lesson-complete requests from different tabs can produce inconsistent `UserCourse.progress_pct`. Fix pending ADR-010 acceptance.
- **No transaction between order + enrollment** (`FIND-005`): possible edge case where payment succeeds but enrollment row never inserts.

### AI
- **OpenAI no `max_tokens` cap** (`FIND-003`): risk of runaway cost. Admin **must** set via config before enabling `FF_FEAT_AI_BUILDER`.
- **Gemini key in URL query** (`FIND-008`): leaks to access logs. Move to header.
- **AI providers feature-flagged off** by default; enable only in dev.

### Architecture
- **`\Drupal::` static calls still present** in multiple modules. Tracked by TASK-018; G4 should flag new instances.
- **`readonly` properties in AJAX form classes break form rebuild**. Do not add `readonly` to such classes.
- **Service IDs follow `lms_{module}.{service_name}`**; declaring differently breaks discovery.

### Decisions pending SA
- **DEC-001** input validation strategy (ADR-009 proposal)
- **DEC-003** race condition handling (ADR-010 proposal)
- Blockers: `BLK-001`, `BLK-002` in A03.

---

## Current State Snapshot

| Dimension | Value | Source |
|-----------|-------|--------|
| Phase | 4-Development | A03 |
| Active persona | Agent_PM (docs) → Agent_Coder next for G-HARDENING | A03 |
| Progress | 41/84 units DONE (49%) | A03 Progress Summary |
| Open Critical/High findings | 8 FINDs + 2 RISKs (payment + progress) | G02 |
| Test coverage | Not measured (pre-test strategy fully wired) | E01 |
| Health | WARNING — 43 SPEC units, 5 PARTIAL DONE units | A03 Health Check |

Groups by status:
- **PASSED**: G-FOUNDATION, G-CONTENT, G-ASSESSMENT, G-PROGRESS, G-ADDITIONAL
- **PARTIAL**: G-COMMERCE (5/10 DONE), G-CERTIFICATE (1/4), G-COMMUNICATION (3/6), G-INTEGRATION (2/6)
- **NOT_STARTED**: G-HARDENING (0/14), G-UI-POLISH (0/14)

---

## Handoff Checklist (what sender owes receiver)

When ending a session, verify before writing this file:

- [ ] D04 session status updated with what was done
- [ ] D03 unit specs updated for state changes (SPEC → IN_PROGRESS → DONE)
- [ ] D07 code-map updated if new files created or modules restructured
- [ ] A03 project context: phase, blockers, decisions refreshed
- [ ] G4 adversarial proof attached if a unit reached REVIEW
- [ ] E02 issues logged for any Medium/Low findings
- [ ] This file (D06) updated: next actions, blockers, gotchas
- [ ] Feature flags in H05 updated if any were added/toggled
- [ ] If checkpoint approved → E07 retro entry + CP report in D08-sessions/

## Health Check Triggers

Run a health check (xem `A02-agent-protocol.md` §12) at:
- Session start
- Before any CP1-CP6 submission
- Before release
- Monthly during maintenance

---

## Escalation

Escalate to SA when:
- Gate fails 3 times (BLOCKED state)
- Any new scope requires G04 change-request
- Pending DEC-001/002/003 blocks implementation
- NFR threshold can't be met (see E10)
- G4 finds Critical security issue without an approved mitigation
- Feature flag rollback needed
- Dependency has Critical CVE (see H03)

Escalation channel: hainn (SA). Log via `G03-blockers.md` entry.

---

*End of D06-handoff-protocol.md — Tier 1 artifact*
