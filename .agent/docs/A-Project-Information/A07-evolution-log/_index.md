# A07-evolution-log - Project Documentation Evolution

Append-only log of the **documentation** baseline's evolution (distinct from `M-Maintenance/M05-evolution-log/`, which tracks the product). New rows are appended at the end; history is never rewritten. A row is appended every time a `Z-Reviews/` audit runs.

| Date | Event | Impact |
| ---- | ----- | ------ |
| 2026-06-20 | Prior draft `docs-draft-1` built (A-E, as-built) | 715 files; A-E materialised against live code; predates folder-per-slot + new slots |
| 2026-06-21 | `00-Guidelines` refreshed (folder-per-slot, full A-U+Z, new C09/C14-C16/D09 slots) | Drafts rendered structurally stale; rebuild required |
| 2026-06-21 | **Rebuild `.agent/docs/` fresh from source** into full A-U+Z started | Canonical tree regenerated from live code/config/DB; drafts demoted to `00-project-input` references |
| 2026-06-21 | Z-review `2026-06-21-initial-build-readiness-review` run | Verdict GO-WITH-FIXES; P0 = secrets-in-config + payment webhooks; P1 = badge awarding + test coverage |

<!-- Append new rows at the end; never rewrite history. -->
