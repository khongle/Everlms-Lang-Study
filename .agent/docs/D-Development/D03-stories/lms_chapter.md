# D03 — Story Spec: LMS Chapter

**Story ID:** lms_chapter
**Story type:** module
**Title:** Chapter entity — middle tier of the Course → Chapter → Lesson curriculum hierarchy
**Primary source:** `../../C-Design/C05-modules/lms_chapter.md`
**Owning module(s):** lms_chapter
**Service(s):** _(none)_
**Epic:** E-02 (Content Layer)
**Contributes to Feature(s):** F-001 (Course Management — curriculum structure)
**Design source(s):** `../../C-Design/C05-modules/lms_chapter.md`
**Round:** N/A
**Driver:** SA directive — content hierarchy entity
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** pre-2026-04-15 (pre-framework)
**Tasks:** see `../D04-tasks/` filtered by `Story ID: lms_chapter`

---

## Session Strategy

**Estimated session count:** 1 (small)
**Splitting strategy:** N/A — thin entity module: 3 PHP classes, no routing, no services.

---

## Scope (from primary source)

Minimal entity module defining the `lms_chapter` ContentEntity — the middle tier of the Course → Chapter → Lesson curriculum hierarchy:

1. **`lms_chapter` entity** — ContentEntityBase (no EntityOwnerTrait); 8 base fields (name, field_description, field_lessons ref, weight, status, created, changed); translatable; auto-generated routes via `AdminHtmlRouteProvider`.
2. **`ChapterForm`** — ContentEntityForm; `save()` redirects to collection (`/manage/chapters`).
3. **`ChapterAccessControlHandler`** — delegates to `lms_course` permissions (view: `view lms_course`; update/delete: `edit lms_course`; create: `create lms_course`).
4. **admin_edit form mode** — registered externally by `lms_course.module::hook_entity_type_alter` (not declared in annotation).
5. **Workspace admin-edit route** — `/workspace/chapters/{lms_chapter}/admin-edit` declared in `lms_course.routing.yml`, uses `_entity_form: lms_chapter.admin_edit`.

No routing.yml, no services.yml, no permissions.yml, no .module file, no .install file, no templates, no config directories.

---

## Source Documents

| Source | Why loaded |
|--------|-----------|
| `../../C-Design/C05-modules/lms_chapter.md` | Owning module design (as-built) |
| `../../C-Design/C05-modules/lms_course.md` | Host of workspace route and hook_entity_type_alter |
| `../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md` | Curriculum structure AC |

---

## Requirements

| Requirement | Source | Type |
|-------------|--------|------|
| lms_chapter entity registered and CRUD functional | AC-01-course-management.md | Acceptance |
| Chapters referenceable from Course via field_chapters | AC-01-course-management.md | Acceptance |
| field_lessons references lms_lesson entities (UNLIMITED) | AC-01-course-management.md | Acceptance |
| weight field controls sort order within course | AC-01-course-management.md | Acceptance |
| Access delegated to lms_course permissions | Internal design | Acceptance |

---

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_chapter/lms_chapter.info.yml` | Create | deps: lms_base, lms_course | U-006 | pre-2026-04-15 |
| `web/modules/custom/lms_chapter/src/Entity/Chapter.php` | Create | lms_chapter ContentEntity; 8 base fields; translatable | U-006 | pre-2026-04-15 |
| `web/modules/custom/lms_chapter/src/Form/ChapterForm.php` | Create | ContentEntityForm; save() → collection redirect | U-006 | pre-2026-04-15 |
| `web/modules/custom/lms_chapter/src/Access/ChapterAccessControlHandler.php` | Create | Delegates to lms_course permissions | U-006 | pre-2026-04-15 |

---

## Dependencies

| Dependency | Type | Source |
|------------|------|--------|
| `lms_base` | Intra-Epic | Base module requirement; declared in info.yml |
| `lms_course` | Cross-Story | Borrows administer lms_course admin_permission; workspace route declared in lms_course.routing.yml; admin_edit form mode registered via hook_entity_type_alter |

---

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|------------|-----------|---------|
| Forward-reference parent→children | field_chapters on lms_course → lms_chapter | Consistent with lms_course entity design |
| No back-reference field | lms_chapter has no field_course | Confirmed — course→chapter only |

---

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission |
|--------------------|----------------|-----------------|-----------|-------------------|
| Course detail curriculum (within /courses/{id}) | Chapter name, description, lesson list | lms_chapter entity | `/workspace/chapters/{id}/admin-edit` or `/manage/chapters/{id}/edit` | administer lms_course / edit lms_course |

**Hardcoded content exceptions:** None.

---

## Feature Flags Introduced or Read

None.

---

## What Was Built (from facts base)

All code is **as-built** (minimal module). Status confirmed by `modules-content.md`:

- `Chapter.php` entity — 8 base fields verified; translatable; AdminHtmlRouteProvider generates CRUD routes.
- `ChapterForm.php` — ContentEntityForm with collection redirect after save.
- `ChapterAccessControlHandler.php` — permission delegation to lms_course confirmed.
- No list_builder defined in annotation (not needed for a child entity managed inline or via workspace).
- admin_edit form mode registered externally by lms_course.module — confirmed as intentional coupling.

**Known issues / debt:**
- No back-reference to parent course (no `field_course` field on lms_chapter). Traversal from chapter to course requires reverse entity query. This is an architectural decision documented in [`../../C-Design/C05-modules/lms_chapter.md`](../../C-Design/C05-modules/lms_chapter.md) §Known Issues.
- admin_edit form mode coupling: lms_chapter's edit form behavior depends on lms_course being installed.
- No chapter ordering/prerequisite logic exists beyond `weight` field.

---

## Tasks (draft unit references)

| Task / Draft ID | Description | Status |
|----------------|-------------|--------|
| U-006 | Chapter entity + handlers (entity, form, access handler) | DONE |

---

## Acceptance Link

See [`../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md`](../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md).

---

## DONE Evidence

| Check | Result |
|-------|--------|
| lms_chapter entity type registered in Drupal | PASS — entity class verified |
| Chapter can be created and attached to Course via field_chapters | PASS — field_chapters on Course entity verified |
| field_lessons references lms_lesson entities | PASS — field definition verified in Chapter.php |
| weight field present for sort order | PASS — base field verified |
| Access handler enforces lms_course permissions | PASS — ChapterAccessControlHandler verified |
| CRUD routes auto-generated at /manage/chapters/* | PASS — AdminHtmlRouteProvider confirmed |
| Workspace admin-edit at /workspace/chapters/{id}/admin-edit | PASS — route in lms_course.routing.yml verified |

---

## Gate Results (per Story, see A02 §3)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Entity schema installs; CRUD routes resolve | 1/3 | pre-2026-04-15 |
| G2 Lint | PASS | No violations | 1/3 | pre-2026-04-15 |
| G3 Tests | PASS | Chapter create/edit/delete cycle functional | 1/3 | pre-2026-04-15 |
| G4 Adversarial | PASS | No back-reference noted as design debt; admin_edit coupling noted | 1/3 | pre-2026-04-15 |

---

## Notes

- Intentionally thin module — no routing, no services, no hooks. All cross-cutting concerns (workspace routes, admin_edit registration) are handled by the parent module `lms_course`.
- The absence of a back-reference field (`field_course` on lms_chapter) is a design choice that simplifies the entity schema but requires reverse queries for chapter-to-course traversal in business logic (e.g., progress cascade, certificate issuance).
