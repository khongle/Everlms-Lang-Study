# A05-framework-primer.md — Framework Structure Primer

> This project uses an AI-driven development framework. This primer distills the structural conventions so project collaborators and new AI agents can work the project without access to `Guidelines/`. The authoritative source is held by the project owner.

**Last Updated:** 2026-06-20

---

## Layer Map

The framework defines 13 document layers (A through U). Each layer has a fixed letter, a canonical folder name, and a clear purpose. **In EverLMS, only layers A–E are materialized so far; layers F–U are planned but not yet created.**

| Layer | Folder | Purpose | When to look here |
|-------|--------|---------|-------------------|
| A | `A-Project-Information/` | Per-project configuration, primers, and living state | Start of every session |
| B | `B-Requirements/` | Business and technical requirements, features, acceptance criteria, integrations | When scope or requirements are in question |
| C | `C-Design/` | Architecture, user roles, entities, modules, APIs, sitemap, wireframes, skins, prototypes | Before touching structure or contracts |
| D | `D-Development/` | Development conventions, build order, Epic specs, Story specs, task kanban, session status, handoff, code map, per-session logs | Daily development work |
| E | `E-Quality/` | Test strategy, issues, test definitions and results, traceability, retrospectives, NFR tracking | Any test- or quality-related work |
| F | `F-Release/` | Deployment config, release checklists, changelog | Release cycles only (planned, layer F — not yet materialized) |
| G | `G-Governance/` | ADRs, risk register, blockers, change requests | When making or recording decisions (planned, layer G — not yet materialized) |
| H | `H-Operations/` | Incidents, runbooks, dependencies, tech debt, feature flags, capacity, observability-as-code | Post-release and maintenance (planned, layer H — not yet materialized) |
| I | `I-External/` | Public API specs, SDK docs, webhook contracts, developer portal, API changelog, rate limits | Public-facing surface and external consumers (planned, layer I — not yet materialized) |
| S | `S-System/` | Service catalog, service-to-service contracts, topology, data flows, dependency matrix, per-service SLOs | Runtime/deployed system view for on-call and DR (planned, layer S — not yet materialized) |
| K | `K-Knowledge/` | Domain glossary, coding conventions, architecture patterns, lessons learned | Onboarding and pattern recall (planned, layer K — not yet materialized) |
| M | `M-Maintenance/` | Maintenance workflows, decision trees, impact templates, post-release monitoring, evolution log | Maintenance events and framework evolution (planned, layer M — not yet materialized) |
| U | `U-Userguide/` | End-user documentation, getting-started, tutorials, FAQ, admin guide | End-user and admin documentation (planned, layer U — not yet materialized) |

> **Note on layer letter order:** the progression is A, B, C, D, E, F, G, H, I, then S (System), K (Knowledge), M (Maintenance), U (Userguide). The letters J, L, N, O, P, Q, R, T, V–Z are not used. This is intentional — S, K, M, and U were assigned their specific letters to match their purpose names.

---

## ID and Numbering Convention

- Each layer uses its own sequential numbering starting at `01` (e.g., `B01`, `B02`, `C04`, `D03`).
- Sub-files under a folder slot use two-digit sequential numbering within their folder (e.g., `B03-features/01-course-management.md`).
- Numeric IDs are stable contracts. When an artifact is removed, do not renumber to close the gap — reserve it for future use.
- The layer letter prefix (A–U) is fixed and never reassigned.

---

## File vs Folder Convention

- **Single file (`.md`):** used when the slot holds one canonical document that is read whole or updated in place — e.g., `A01-CLAUDE.md`, `D05-session-status.md`, `G02-risk-register.md`, `F03-changelog.md`.
- **Folder:** used when the slot holds a growing collection with one file per item — e.g., `B03-features/`, `C04-entities/`, `D03-stories/`, `G01-adrs/`, `E02-issues/`.
- When a folder slot currently holds only one file, keep it as a folder — future growth is expected.
- Do not collapse a canonical folder into a single file, or split a canonical single file into a folder, without updating the authoritative structure document.

---

## Tier 1 Always-Loaded Files

Load these four files at the start of every agent session (target: 3–6K tokens combined):

| # | File | Contents |
|---|------|----------|
| 1 | `A-Project-Information/A01-CLAUDE.md` | AI agent configuration: commands, conventions, thresholds, banned patterns, gate sequence |
| 2 | `A-Project-Information/A03-project-context.md` | Living state: current phase, active Epic/Story, recent decisions, blockers |
| 3 | `D-Development/D05-session-status.md` | Session log (append-only, latest first) |
| 4 | `D-Development/D06-handoff-protocol.md` | Load order and pending work for the next session |

Total context must stay under 80% of the model's context window. Load additional phase-specific files on demand, not upfront.

---

## Kanban Board — D04-tasks/

Task files live in status subfolders. Move files between status folders rather than editing state in place.

```
D04-tasks/
├── backlog/       # Planned — not yet committed to an Epic
├── todo/          # Epic-committed, ready to start
├── in-progress/   # Agent actively building
├── blocked/       # Dependency or SA decision needed
├── in-review/     # Code done, SA review pending
├── closed/        # Non-done closures — set closure_reason frontmatter
└── done/          # SA-approved, gate-passed
```

Valid `closure_reason` values (set in frontmatter on every `closed/` task): `wont-fix`, `duplicate`, `requirement-dropped`, `descoped`.

---

## Sharing Boundary

- `Guidelines/` is the authoritative framework reference read by SA and agent sessions. It is NOT shared with external collaborators.
- `.agent/docs/` (including `A-Project-Information/`) is the shareable surface — readable by SA, agent sessions, and external collaborators alike.
- This primer plus `A06-workflow-primer.md` provides enough context for an external collaborator to work the project without needing `Guidelines/` access.
- When the framework evolves, re-sync A05/A06 and log the change in [`A07-evolution-log.md`](A07-evolution-log.md).

---

*End of A05-framework-primer.md*
