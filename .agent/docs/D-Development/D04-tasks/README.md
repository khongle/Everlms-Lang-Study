---
layer: D-Development
file: D04-tasks/README.md
purpose: Kanban board orientation — explains folder structure, status lifecycle, vocabulary rules, and how legacy TASK-NNN cards relate to canonical Stories
last_updated: 2026-06-20
---

# D04 — Task Kanban Board

**Layer:** D-Development / D04-tasks
**Total cards:** 241 (as of 2026-06-20)

This folder is the **kanban board** for EverLMS development. Every Task card is a scoped, executable work item that belongs to exactly one [Story](../D03-stories/). A Story collects all the Tasks that together complete it. Many Stories compose an [Epic](../D02-epics/).

---

## Vocabulary

| Level | Term | Do Not Use |
|-------|------|-----------|
| 1 | **Epic** (E-NN) | "Group", "Phase", "Category" |
| 2 | **Story** (module slug or cross-cutting slug) | "Unit", "Feature group" |
| 3 | **Task** (TASK-NNN) | "Unit", "Subtask" |

---

## Status Folders

| Folder | Meaning |
|--------|---------|
| `backlog/` | Planned but not yet Epic-committed. The bulk of the board lives here (226 cards). |
| `todo/` | Epic-committed and ready to start; SA has approved the Story spec. |
| `in-progress/` | An Agent_Implementer is actively working on the Task in an open session. |
| `blocked/` | A dependency or SA decision is needed before work can continue. |
| `in-review/` | Implementation is complete; awaiting SA review and approval. |
| `done/` | SA-approved; the Task's scope is implemented, reviewed, and accepted. The owning Story is marked DONE in D03 only after *all* its Tasks reach `done/`. |
| `closed/` | Non-done closure. Always has a `closure_reason` frontmatter field: `wont-fix`, `duplicate`, `requirement-dropped`, or `descoped`. |

### Status Transition Flow

```
backlog ──[Epic commit]──────────────── → todo
todo ──[SA opens session]────────────── → in-progress
in-progress ──[blocker hit]────────────→ blocked
in-progress ──[scope complete]────────── → in-review
in-review ──[SA approves]──────────────→ done
blocked ──[blocker resolved]────────── → (previous state)
Any ──[explicit non-action]────────── → closed (closure_reason required)
```

---

## Navigation

With 241 cards across 7 folders, use the **[_index.md](_index.md)** for navigation. It groups every Task by:

```
Epic (E-NN) → Story (D03 slug) → Task (TASK-NNN)
```

The index also records the reclassification log — tasks moved between folders because the draft's status label did not match the current code.

---

## Card Naming Convention

```
{task-id}-{kebab-name}.md
```

All existing cards use the `TASK-NNN` prefix. This is the stable identifier — do not renumber cards. The `{kebab-name}` part describes the scoped slice in ≤ 60 characters.

If future task families use a different prefix (e.g. `TK-NNN` for a new round), document it in [D01-dev-overview.md](../D01-dev-overview.md).

---

## Card Shape

Each Task card follows the template at [_template.md](_template.md). Key fields:

| Frontmatter field | Purpose |
|---|---|
| `id` | Stable TASK-NNN identifier |
| `story` | D03 Story this Task contributes to (required) |
| `epic` | E-NN parent Epic |
| `layer` | `service \| repository \| controller \| tests \| migration \| observability \| wiring \| page-family \| route-family \| convention-audit` |
| `estimated_hours` | SA estimate at creation time |
| `actual_hours` | Filled when moved to `done/` |
| `priority` | `high \| medium \| low` |
| `closure_reason` | Only for `closed/` cards |

---

## Legacy Cards: Original Detail Preserved

The 241 cards were migrated from the draft tree at `.agent/docs-draft/D-Development/D05-tasks/`. Each card retains its **original prose body** (description, acceptance criteria, technical notes, audit notes). Do not overwrite existing card bodies when moving a card between folders — only update frontmatter fields and add a session log row.

The draft used `Group` and `Unit` vocabulary (e.g. `G-COMMERCE`, `U-028`). These references appear in older card bodies as historical context but are **deprecated** — do not use them in new cards or prose.

---

## Reclassification (2026-06-20)

One card was reclassified from `done/` to `todo/` on initialization:

| Task | Reason |
|------|--------|
| [TASK-010-forum-ui.md](todo/TASK-010-forum-ui.md) | `lms_forum` Story = IN_PROGRESS in D03; `ForumController::viewTopic` throws a `"HARD DEBUG!"` exception in production code. The UI scaffolding exists but the module has an active, blocking bug. Card moved to `todo/` until the bug is fixed and the Story advances to DONE. |

---

## Board Maintenance Rules

1. Move a file between folders using `git mv` so history is preserved.
2. Add a row to the card's `## Session Log` table on every status change.
3. When moving to `done/`, fill `actual_hours` in frontmatter and ensure the owning Story's `Files Touched` table in [D03-stories/](../D03-stories/) is updated.
4. When moving to `closed/`, set `closure_reason` in frontmatter and add a one-line rationale note in the card body.
5. Update [_index.md](_index.md) when new cards are created or moved significantly (e.g. promoted from backlog to todo in a new Epic).
6. Do not add or rename status folders without updating `A02-agent-protocol.md` first (structure is locked per template authority).

---

## Related Documents

| Document | Purpose |
|----------|---------|
| [D02-epics/](../D02-epics/) | Epic definitions (E-01 through E-11) |
| [D03-stories/](../D03-stories/) | Story specs — one file per Story; `_index.md` lists all 36 |
| [D01-dev-overview.md](../D01-dev-overview.md) | Development overview, round notes, task ID scheme |
| [D05-session-status.md](../D05-session-status.md) | Active session tracker |
| [D06-handoff-protocol.md](../D06-handoff-protocol.md) | Session handoff rules |
| [C05-modules/](../../C-Design/C05-modules/) | Module design specs cross-linked from Story cards |
