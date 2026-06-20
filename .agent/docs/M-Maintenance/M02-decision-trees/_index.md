# M02-decision-trees - Maintenance Decision Trees

## Which workflow?
- Production down / security exposure → **HOTFIX** (+ incident).
- Wrong behaviour, not urgent → **BUG_FIX**.
- New/changed capability → **ENHANCEMENT**.
- Internal cleanup, no behaviour change → **REFACTOR**.
- Bump a library/core → **DEPENDENCY_UPDATE**.

## Is a Change Request needed?
- Editing outside current story scope, or changing a canonical ID/path/mirror, or a docs-structure change → yes ([G04](../../G-Governance/G04-change-requests/README.md); update `A01-target-docs-structure.md` first).

## Build vs flag vs defer?
- Release-blocking (P0) → build now ([D09 W01](../../D-Development/D09-queue/_index.md)).
- Risky/partial rollout → feature flag (when FF manager exists, [H05](../../H-Operations/H05-feature-flags/_index.md)).
- Low value (P2) → backlog.
