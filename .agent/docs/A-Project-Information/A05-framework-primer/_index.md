# A05-framework-primer - Framework Snapshot

**Last updated:** 2026-06-21

The distilled, shareable snapshot of the documentation contract EverLMS docs follow. Authoritative structure: `.agent/00-Guidelines/A01-target-docs-structure.md` (kept with the SA). This primer carries the bare contract into the shared tree.

## Documentation contract
The `.agent/docs/` tree is a **build contract**, not a report: every page, entity, workflow and API is specified so an implementer can build it and a QA persona can verify it. For this rebuild the source-of-truth chain is **live code → config export (`config/sync/`) → database (`database/db.sql`) → A-U docs → (re)implementation**; the optional `02-xlsx/03-md` normalization layer is **not** used. If a doc and the code disagree, the code wins and the doc records the discrepancy.

## Required layers
All canonical layers ship (folder-per-slot; a slot may be a stub with a CP1 ADR recording any skip):
- **A** Project-Information · **B** Requirements · **C** Design · **D** Development · **E** Quality · **F** Release · **G** Governance · **H** Operations · **I** External · **K** Knowledge · **M** Maintenance · **S** System · **U** Userguide · **Z** Reviews.
- Every level-1 slot is a FOLDER `<SlotCode>-<slug>/` with a primary `_index.md` (or `README.md` for human-facing folders). No bare `<SlotCode>-<slug>.md` files under a layer.

## Naming conventions
The ID registry (must agree byte-for-byte with `A01-target-docs-structure.md`):
- **Features + Acceptance** (shared slug): `F-NNN-<slug>` — `B03 ↔ B05 ↔ E05 ↔ U03`
- **Workflows:** `WF-NNN-<slug>` — `B04 ↔ C12 ↔ C13 folder ↔ S04`
- **Integrations:** `INT-NNN-<slug>` — `B06 ↔ C06`
- **Epics:** `E##-<slug>` (hierarchical WBS, D02) · **Stories:** `E##S##-<slug>` (D03) · **Tasks:** `E##S##T##-<slug>` (D04)
- **ADRs:** `ADR-NNN-<slug>` (G01) · **Risks:** `R-NNN` (G02) · **CRs:** `CR-NNN` (G04) · **Issues:** `ISSUE-NNNN` (E02) · **Incidents:** `INC-NNN` (H01)
- **Modules / entities:** snake_case, project prefix **`lms_*`** (+ `open_switcher`) — files `<module_id>.md` / `<entity_id>.md`
- **Pages:** `P###-<slug>` (C11/pages) · **Blocks:** `<block_id>.md` (C11/blocks) · **Usecases:** `UC-WF-NNN-NNN-<slug>`
- **Service contracts:** `<from>-to-<to>.md` (S02) · **Feature flags:** `FF_UPPER_SNAKE` (H05)
- Numeric IDs are stable contracts: never renumber to close a gap; reserve removed IDs.

## Editorial usability rule
Every business-owned visible content surface (course text, lesson body, certificate template, notification copy, FAQ, blog, homepage skin content) must be editable by a non-developer through a management UI — **no hardcoded editorial content** in PHP/Twig/JS/CSS. A story that renders content but cannot answer *"where does an editor change this and how do we verify it appears?"* is **not** implementation-ready. Enforced in `C04`, `C11`, `D03/D04`, G4, and CP4.
