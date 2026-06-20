# M05 — Evolution Log: Format & Usage

> Append-only entry-set. Each file in this folder records one framework evolution event
> (a batch of changes to A-Guidelines documents driven by retros, incidents, or milestone reviews).

---

## When to Add an Entry

| Trigger | Action |
|---------|--------|
| Post-milestone CP retrospective | Create new entry file |
| 3+ similar incidents revealing a framework gap | Create new entry file |
| SA directive to change protocol or template | Create new entry file |
| P-FRAMEWORK_RETRO phase completed | Create new entry file |

**Do not** create an entry for routine code changes. Evolution entries are framework-level changes only — modifications to files in `00-Guidelines/`.

---

## Naming Convention

```
{YYYY-MM-DD}-{slug}.md
```

- Date: when the evolution decision was finalized (not when changes were merged)
- Slug: 2–5 words, kebab-case, describing the batch (e.g., `plugin-interface-rules`, `progress-concurrency-protocol`)

Examples:
- `2026-04-15-initial-materialization.md`
- `2026-05-01-hotfix-schema-gate.md`

---

## File Format

Each entry file follows the M05 template from `00-Guidelines/A08-layer-templates/12-m-maintenance.md` §M05:

```markdown
# Evolution Entry: {YYYY-MM-DD}-{slug}

## Source
- Retros consumed: {E07-retro-ids or —}
- Lessons consumed: {K04 lesson titles or —}
- Incidents consumed: {INC-NNN or —}

## Diff Plan
| File | Kind | Change | Version bump |
|------|------|--------|--------------|
| 00-Guidelines/A02-agent-protocol.md | protocol | {summary} | MINOR |
| 00-Guidelines/A08-layer-templates/{file} | template | {summary} | PATCH |

## Re-test Plan
- Prompts to re-run: {list from A16}
- Acceptance: {criteria}

## Outcome
- PR: {link or —}
- Merged: {YYYY-MM-DD or pending}
- A13 row added: yes / no
```

---

## Relationship to A13

Every M05 entry must have a corresponding row in `00-Guidelines/A-Project-Information/A07-evolution-log.md`.

- A13 is the **summary index** (one row per event, always-loaded, budget-friendly).
- M05 is the **detail record** (full diff plan, sources, re-test plan, outcome).

Rule: add the A13 row and the M05 file in the same commit. If a row appears in A13 without an M05 file, create the M05 file retroactively.

---

## Append-Only Rules

- Do not delete or edit completed entries.
- If an evolution entry turns out to be wrong: create a new entry that reverses or supersedes it, referencing the original.
- The set of files in this folder is a permanent audit trail.

---

## Current Entries

| File | Date | Summary |
|------|------|---------|
| *(none yet — first entry to be created after CP1 retrospective)* | — | — |

---

*Framework-level change protocol: `00-Guidelines/A04-maintenance-workflow.md`. A13 summary log: `00-Guidelines/A-Project-Information/A07-evolution-log.md`.*
