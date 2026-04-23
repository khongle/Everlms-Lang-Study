# A05 — Framework Structure Primer

> This project uses an AI-driven development framework. This primer distills the structural conventions so collaborators and new AI agents can work the project without access to `Guidelines/`. The authoritative source is held by the project owner.

---

## Layer Map

| Layer | Folder | Purpose | When to look here |
|-------|--------|---------|-------------------|
| A | `A-Project-Information/` | Per-project config, primers, living state | Start of every session |
| B | `B-Requirements/` | Business/technical requirements, features, acceptance criteria, integrations | Scope or requirements questions |
| C | `C-Design/` | Architecture, user roles, entities, modules, APIs, sitemap, wireframes, skins | Before touching structure or contracts |
| D | `D-Development/` | Build order, group/unit specs, session status, task kanban, handoff, code map | Daily development work |
| E | `E-Quality/` | Test strategy, issues, test definitions/results, traceability, patterns, NFR tracking | Test or quality work |
| F | `F-Release/` | Deployment config, release checklists, changelog | Release cycles |
| G | `G-Governance/` | ADRs, risk register, blockers, change requests | Decisions or risk management |
| H | `H-Operations/` | Incidents, runbooks, dependencies, tech debt, feature flags, capacity, observability | Post-release and maintenance |
| I | `I-External/` | Public API specs, SDK docs, webhook contracts, developer portal | External consumers |
| S | `S-System/` | Service catalog, contracts, topology, data flows, dependency matrix, SLOs | Runtime/deployed system view |
| K | `K-Knowledge/` | Domain glossary, coding conventions, architecture patterns, lessons learned | Onboarding and pattern recall |
| M | `M-Maintenance/` | Maintenance workflows, decision trees, impact templates, monitoring, evolution log | Maintenance events |

## EverLMS Layer Inventory (as of 2026-04-16)

| Layer | Files | Key contents |
|-------|-------|-------------|
| B | 54 | 16 features, 9 workflows, 16 acceptance criteria, 8 integrations |
| C | 76 | 10 entity files, 27 per-module files, 8 API specs, 18 wireframes |
| D | 132 | 84 unit specs (41 DONE + 43 SPEC), 11 group specs, code map (114 routes), kanban board |
| E | 23 | 5 issues, 5 test definitions, 2 solution patterns |
| F | 6 | Deployment config, pre/post-release checklist templates, changelog |
| G | 14 | 10 ADRs (8 accepted, 2 proposed), risk register (4 RISKs + 15 FINDs), 2 blockers |
| H | 23 | 7 runbooks, capacity model, 10 observability seeds (dashboards/alerts/SLOs) |
| I | 12 | Certificate verify + 3 planned endpoints, Stripe/PayPal webhook contracts |
| S | 17 | Service catalog, 7 outbound contracts, topology, 3 critical-path data flows |
| K | 4 | 70+ glossary terms, coding conventions, 8 architecture patterns, 10 lessons |
| M | 12 | 5 maintenance playbooks, 2 impact templates, decision trees |

## ID Convention

- Each layer uses sequential numbering starting at `01` (e.g. `B01`, `C04`, `D03`).
- Sub-files in folder slots use two-digit numbering within their folder.
- Numeric IDs are stable contracts — never renumber to close gaps.
- Layer letter progression: A, B, C, D, E, F, G, H, I, S, K, M.

## Tier 1 Always-Loaded Files

Load at every session start (target: 3–6K tokens):

1. `A-Project-Information/A01-CLAUDE.md` — AI config, commands, conventions, banned patterns
2. `A-Project-Information/A03-project-context.md` — living state, phase, blockers, findings
3. `D-Development/D04-session-status.md` — session log (latest first)
4. `D-Development/D06-handoff-protocol.md` — pending work for next session

## Tier 2 On-Demand Loading

| Phase | Load |
|-------|------|
| Ideation | B01, B02, B03 |
| Architecture | B01–B06, C01–C04, C08, G01, G02 |
| Development | D01, D02-{group}, D03-{unit}, D05, D07, E01, H05 |
| Development (G-HARDENING) | + G01/ADR-009, ADR-010; E02-issues/; S04-data-flow/ |
| Quality | B02, B03, B05, E01–E11, H01–H07 |
| Release | F01–F03, H01–H07 |

## File vs Folder Convention

- **Single file (.md)** when one canonical doc loaded whole: A01, A03, D01, D04, D06, F03, G02, G03, etc.
- **Folder** when items grow over time: B03-features/, C04-entities/, D03-unit-specs/, G01-adrs/, etc.
- When a folder slot currently has one file, keep it as a folder — future growth expected.

## Kanban Board — D05-tasks/

```
D05-tasks/
├── backlog/       # planned, not yet committed
├── todo/          # sprint-committed, ready to start
├── in-progress/   # agent actively working
├── blocked/       # dependency or SA decision needed
├── in-review/     # code done, SA review pending
├── closed/        # non-done closures (set closure_reason)
└── done/          # SA-approved, gate-passed
```

Valid `closure_reason`: `wont-fix`, `duplicate`, `requirement-dropped`, `descoped`.

## Sharing Boundary

- `Guidelines/` (if present) is SA-private framework reference — not required for day-to-day work.
- Everything needed is in layers A through M; this primer + `A06-workflow-primer.md` distills the conventions.

---

*End of A05-framework-primer.md*
