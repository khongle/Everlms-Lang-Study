# D06-handoff-protocol - Handoff Protocol

**Last updated:** 2026-06-21 (single-doc)

## At session start
1. Load Tier-1: [A01-CLAUDE](../../A-Project-Information/A01-CLAUDE/_index.md), [A03-project-context](../../A-Project-Information/A03-project-context/_index.md), [D05-session-status](../D05-session-status/_index.md), this file.
2. Check open blockers ([G03](../../G-Governance/G03-blockers/_index.md)) + latest Z-review verdict ([Z-Reviews](../../Z-Reviews/README.md)).
3. Confirm environment: `ddev start && ddev drush cr` (see A01 commands).

## During work
- Update [D07-code-map](../D07-code-map/README.md) on file create/modify.
- Keep changes within story scope; new cross-scope work → a `G04` change request.
- Honour gates G1→G4 and the editorial-usability invariant.

## At session end
- Update [D05-session-status](../D05-session-status/_index.md) (done / in-progress / next).
- Log session in [D08-sessions](../D08-sessions/README.md); append docs-structure changes to [A07-evolution-log](../../A-Project-Information/A07-evolution-log/_index.md).
- Commit only when the user asks; branch off `main` first.
