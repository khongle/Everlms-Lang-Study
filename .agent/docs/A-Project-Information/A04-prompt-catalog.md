# A04 — Prompt Catalog

> Index of prompts used in this project. Source files live in `../../../00-Guidelines/A10-prompts/`.
> Each row mirrors the frontmatter of the corresponding prompt file.
> Update when prompts are added, tested, or retired. Remove the row when a prompt is archived (per A02 §19.6 step 2).
>
> **Complexity column:** all prompts in this project use `4` (level-4-advanced tier, Cấp Độ 5 delivery).

---

## Phase Prompts

| ID | Phase | Prompt file | One-line purpose | When used | Version | Complexity | Last Tested | Last-Tested Model | Deprecation |
|----|-------|-------------|-----------------|-----------|---------|------------|-------------|-------------------|-------------|
| P-IDEA | 1 — Ideation | `01-ideation.md` | Elicit project vision, stakeholder goals, and seed B01–B03 | Phase 1 start; before CP1 | 1.0 | 4 | 2026-04-18 | claude-opus-4-7 | — |
| P-ARCHITECTURE | 2 — Architecture | `02-architecture-planning.md` | Design entity/module structure, ADRs, risk register, and C-layer artifacts | Phase 2; before CP2 | 1.0 | 4 | 2026-04-18 | claude-opus-4-7 | — |
| P-FRAMEWORK_SETUP | 3 — Setup | `03-framework-setup.md` | Scaffold repo, DDEV, A01-CLAUDE.md, D00 conventions, D01 overview | Phase 3 bootstrap (pre-scaffold); before CP3 | 1.0 | 4 | 2026-04-18 | claude-opus-4-7 | — |
| P-DEVELOPMENT | 4 — Development | `04-development.md` | Implement one Story (Epic ⊇ Story ⊇ Task), run G1–G4 gates, update D07 | Each Story session in Phase 4; CP4 per Epic | 1.0 | 4 | 2026-04-18 | claude-opus-4-7 | — |
| P-QUALITY | 5 — Quality | `05-quality.md` | Verify E-layer completeness, NFRs, H-Operations baseline readiness | Phase 5; before CP5 | 1.0 | 4 | 2026-04-18 | claude-opus-4-7 | — |
| P-RELEASE | 6 — Release | `06-release.md` | Execute deployment, verify H-Operations operational, obtain CP6 go/no-go | Phase 6; before CP6 | 1.0 | 4 | 2026-04-18 | claude-opus-4-7 | — |
| P-MAINTENANCE | Maintenance | `07-maintenance.md` | Triage, classify, and dispatch post-release changes (HOTFIX/BUG_FIX/ENHANCEMENT/REFACTOR/DEPENDENCY_UPDATE) | Ongoing — any maintenance ticket after CP6 | 1.0 | 4 | 2026-04-18 | claude-opus-4-7 | — |
| P-POST_RELEASE | Post-CP6 | `08-post-release.md` | Monitor first 7 days in production; daily SLO burn check and feature-flag ramp | First week after CP6 | 1.0 | 4 | 2026-04-18 | claude-opus-4-7 | — |
| P-INCIDENT_RESPONSE | Production | `09-incident-response.md` | Activate IC war-room, execute runbooks, draft comms, write PIR | Any SEV1/SEV2 incident | 1.0 | 4 | 2026-04-18 | claude-opus-4-7 | — |
| P-FRAMEWORK_RETRO | Meta | `10-framework-retro.md` | Surface framework improvements, sync A05/A06 primers, log changes in A07 | After each CP2/CP4/CP5/CP6 or post-incident cluster; semi-annual safety net | 1.0 | 4 | 2026-04-18 | claude-opus-4-7 | — |

---

## Operational Templates

Source files live in `../../../00-Guidelines/A09-operational-templates/`.

| ID | Template file | One-line purpose | When used |
|----|---------------|-----------------|-----------|
| T-SESSION | `01-session.md` | Structured session log — what was done, what's next, blockers | Every session; write to `D-Development/D08-sessions/` |
| T-ADVERSARIAL | `02-adversarial.md` | G4 adversarial self-review checklist (7 categories) | Pre-CP review of high-risk Stories; mandatory at G4 gate |
| T-CHECKPOINT | `03-checkpoint.md` | Checkpoint gate record (CP1–CP6) | Each checkpoint transition; SA approval required |
| T-IMPACT | `04-impact.md` | Impact analysis for maintenance changes | Every maintenance ticket at TRIAGE → IMPACT_ANALYSIS step |
| T-CHAOS | `05-chaos-runbook.md` | Chaos experiment runbook; results go to E11 | Chaos drills scheduled in H-Operations |
| T-CAPACITY | `06-capacity-planning.md` | Capacity planning worksheet; output to H06 | Annual review or scale-trigger event |
| T-OBSERVABILITY | `07-observability-as-code.md` | SLO/dashboard/alert definition; output to H07 | When adding a new SLO, alert, or dashboard |
| T-CONTENT_TOOLS | `08-content-tools-module.md` | Module implementation scaffold for content-tools pattern | New module that follows the content-tools scaffold |
| T-SECRETS | `09-secrets-module.md` | Secrets/credentials module pattern | Secrets refactor or new secrets integration |
| T-FEATURE_FLAG | `10-feature-flag-manager.md` | Feature-flag setup and lifecycle | `FF_*` flag creation or rollout change |
| T-DATA_MIGRATION | `11-data-migration-protocol.md` | Safe data migration checklist | DB schema change or data backfill |
| T-CREDENTIAL_LEAK | `12-credential-leak-response.md` | Credential-leak incident response | On discovery of a committed secret (see security risk in A01) |
| T-STORY_SPEC | `13-story-spec-adequacy-checklist.md` | Story Spec Adequacy Checklist (A09/13) | SA review before opening any Phase 4 session |

---

## Quick-Pick by Current Phase

Project is currently in **Phase 4 — Development** (lms_* modules being implemented).

| Situation | Prompts / Templates to use |
|-----------|----------------------------|
| Start a new Story session | P-DEVELOPMENT + T-SESSION + T-STORY_SPEC (SA pre-check) |
| End of an Epic (CP4 gate) | P-DEVELOPMENT (G4 pass) → T-CHECKPOINT (CP4) |
| Bug discovered mid-development | P-MAINTENANCE (BUG_FIX playbook) + T-IMPACT |
| Security hardening pass | P-DEVELOPMENT + T-ADVERSARIAL + ADR check in `G-Governance/G01-adrs/` |
| Committed secret found | T-CREDENTIAL_LEAK (immediate); log issue in `E-Quality/E02-issues/` |
| Framework rules change (Guidelines/) | P-FRAMEWORK_RETRO → update A05/A06 → log in A07 |

---

## Prompt Metadata Schema (A02 §19.6 contract)

Each prompt file in `A10-prompts/` must carry this frontmatter:

```yaml
prompt-id: P-{NAME}
name: {human description}
version: 1.0
complexity-level: 4
delivery-model: cap-do-5
lifecycle-step: Phase {N} — {name}
persona: {Agent_BA | Agent_Architect | Agent_Implementer | Agent_QA | Agent_PM | SA | IC}
last-tested: {YYYY-MM-DD}
last-tested-model: {model-id}
last-tested-evidence: {path to D08-sessions/dry-run-{prompt-id}-{YYYY-MM-DD}.md}
deprecation-date: null
cadence-refs: [{A11 section refs}]
known-limitations:
  - {limitation}
```

**Rules:**
- Bump `version` and update `last-tested` + `last-tested-model` when a prompt changes semantics (not just prose).
- A row with `deprecation-date` in the past must NOT be invoked; remove it at the next framework retro cleanup cycle (A02 §19 Dead-Artifact Cleanup).
- `last-tested-evidence` is REQUIRED for FRESH state; a missing or null value downgrades the prompt to STALE after 180 days.

---

## Model Lifecycle Registry (A02 §19.6)

> Refreshed by Agent_PM on every framework retro. Used to compute STALE / EXPIRED status for `last-tested-model` values above.

| Model ID | Vendor | Vendor source URL | Vendor-published support end | Last verified on | Status |
|----------|--------|-------------------|------------------------------|------------------|--------|
| claude-opus-4-7 | Anthropic | https://docs.anthropic.com/en/docs/about-claude/models | — (active) | 2026-06-20 | active |
| claude-sonnet-4-6 | Anthropic | https://docs.anthropic.com/en/docs/about-claude/models | — (active) | 2026-06-20 | active |
| claude-haiku-4-5 | Anthropic | https://docs.anthropic.com/en/docs/about-claude/models | — (active) | 2026-06-20 | active |

**Rules:**
1. Every `last-tested-model` value in the Prompt Catalog above MUST appear as an `active` row in this registry.
2. When a vendor publishes a retirement date, update the row's `Vendor-published support end` and flip `Status` to `retired` once the date passes — all prompts citing that model become EXPIRED.
3. This registry is per-project; refresh it at each framework retro (P-FRAMEWORK_RETRO).

---

*End of A04-prompt-catalog.md*
