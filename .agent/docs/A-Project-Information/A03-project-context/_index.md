# A03-project-context - Living Context

**Last updated:** 2026-06-21

The handoff anchor for any persona resuming work. The one A-slot that legitimately changes often. Mirrors live blockers from `G-Governance/G03-blockers/` and the latest readiness verdict from `Z-Reviews/`.

## Current state
The `.agent/docs/` tree is being **rebuilt fresh from source** (live code > config export > DB) into the full A-U + Z structure per the updated `00-Guidelines`. The product itself (EverLMS) is a working Drupal 11 LMS with seeded demo data; the documentation — not the code — is the active work. Phase posture for the *product* is **post-build / maintenance** (most modules built, several gaps and risks open); for the *docs* it is **Phase 1-3 regeneration** (requirements/architecture/framework captured as-built).

## Recent decisions
| Date | Decision | Rationale | Linked docs |
| ---- | -------- | --------- | ----------- |
| 2026-06-21 | Rebuild `.agent/docs/` fresh from source into full A-U+Z (not synthesise drafts) | Drafts predate the current `00-Guidelines` (folder-per-slot, new slots); source is the only reliable truth for a rebuild | [G01-adrs/ADR-001](../../G-Governance/G01-adrs/ADR-001-rebuild-docs-fresh-from-source.md) |
| 2026-06-21 | Document as-built; mark stubs/dead-code/target explicitly | Honest build contract beats aspirational docs | [E06-implementation-status](../../E-Quality/E06-implementation-status/_index.md) |
| 2026-06-21 | Reference raw sources in-place (do not copy DB/keys into docs) | Avoid duplicating 21 MB DB + leaking secrets into a shareable tree | [00-project-input/README](../../00-project-input/README.md) |
| 2026-06-20 | Quiz grading via static `GradingStrategyFactory` (8 strategies) | Captured as the assessment architecture | [C01-architecture-decision](../../C-Design/C01-architecture-decision/_index.md) |

## Active blockers
| ID | Description | Owner | Since |
| -- | ----------- | ----- | ----- |
| BLK-001 | Third-party secrets committed in config YAML (no Key module) — blocks safe public release | Agent_Architect | 2026-06-20 |
| BLK-002 | No payment webhooks / signature verification — orders can desync from gateway truth | Agent_Implementer | 2026-06-20 |
| BLK-003 | `lms_badge` award logic absent — badges defined but never granted | Agent_Implementer | 2026-06-20 |
| BLK-004 | ~0% automated test coverage — G3 passes vacuously | Agent_QA | 2026-06-20 |

<!-- Mirror G03-blockers/_index.md; clear rows when resolved (log resolution in G03/E02). -->

## Next steps
Driven by the latest readiness verdict — `Z-Reviews/2026-06-21-initial-build-readiness-review/` → **GO-WITH-FIXES** (P0: BLK-001 secrets, BLK-002 webhooks; P1: BLK-003 badges, BLK-004 tests).
- [ ] Complete the A-U+Z documentation rebuild (Agent_Architect, this pass)
- [ ] Move third-party secrets to Key module / env (BLK-001, P0)
- [ ] Add payment webhook endpoints + signature verification for Stripe (BLK-002, P0)
- [ ] Implement `lms_badge` award triggers or mark formally deferred (BLK-003, P1)
- [ ] Establish a PHPUnit kernel/functional baseline for core flows (BLK-004, P1)
