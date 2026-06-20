# M01 - BUG_FIX workflow

1. Reproduce; capture in [E02 issues](../../E-Quality/E02-issues/README.md) (`ISSUE-NNNN`).
2. Locate via [D07 code-map](../../D-Development/D07-code-map/README.md); assess impact ([E09](../../E-Quality/E09-impact-analysis/README.md)).
3. Fix on a branch within story scope (CR if out-of-scope, [G04](../../G-Governance/G04-change-requests/README.md)).
4. Gates G1→G4; add a regression test (E03).
5. Update issue → resolved; changelog ([F03](../../F-Release/F03-changelog/_index.md)).
