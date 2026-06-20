---
documentation-mode: as-built
generated: 2026-06-20
---

# D08 — Sessions Index

> Per-session log files. One file per session. **Immutable once written.**
> Corrections are made by appending a `## Addendum` section with a date stamp; original content is never edited.
> Roll-up summaries live in [`D05-session-status.md`](../D05-session-status.md) (one compact entry per session, loaded Tier 1).
> This folder holds the per-session full transcripts — loaded only when reconstructing a specific session.

---

## Naming Conventions

| File pattern | Use |
|---|---|
| `session-{YYYY-MM-DD}-{N}.md` | Standard session (N = daily counter, 1-indexed) |
| `{YYYY-MM-DD}-{slug}.md` | Named session (documentation rebuild, cross-team sync, etc.) |
| `story-adequacy-{story-id}-{YYYY-MM-DD}.md` | A09/A13 Story Spec Adequacy evidence per Story |
| `cp3-smoke-test.md` | CP3 smoke-test evidence (singleton) |
| `sync-{YYYY-MM-DD}.md` | Cross-team sync notes |

---

## Session Index

| # | File | Date | Type | Summary |
|---|------|------|------|---------|
| 1 | [2026-06-20-docs-rebuild.md](2026-06-20-docs-rebuild.md) | 2026-06-20 | Documentation rebuild | A–E layers rebuilt from code ground truth. 28 C05 module specs, 37 Stories, 11 Epics, 8 conventions. |

---

## Rules

- Files in this folder are **immutable once written**. No editing past sessions — append `## Addendum` if a correction is needed.
- Every session in D05 must have a matching file here.
- A D05 entry without a matching D08 file is incomplete — create the D08 file before closing context.
- Session files are **not** loaded at Tier 1 — they are loaded only when reconstructing a specific session. Keep D05 as the always-loaded roll-up.
