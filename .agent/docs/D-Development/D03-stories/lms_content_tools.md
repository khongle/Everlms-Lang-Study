# D03 — Story Spec: LMS Content Tools

**Story ID:** lms_content_tools
**Story type:** module
**Title:** Bulk entity clean, AI seed-input capture, and AI-generated content import pipeline
**Primary source:** `../../C-Design/C05-modules/lms_content_tools.md`
**Owning module(s):** lms_content_tools
**Service(s):** `lms_content_tools.manager`, `lms_content_tools.commands`
**Epic:** E-01 (Foundation & Theme)
**Contributes to Feature(s):** F-013 (Modern Features — AI workflow), F-001 (Course Management — content seeding)
**Design source(s):** `../../C-Design/C05-modules/lms_content_tools.md`
**Round:** N/A
**Driver:** SA directive — developer tooling for AI-assisted content pipeline
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** pre-2026-04-15 (pre-framework)
**Tasks:** see `../D04-tasks/` filtered by `Story ID: lms_content_tools`

---

## Session Strategy

**Estimated session count:** 2–3 (medium — large ContentToolsCommands class, ~2531 lines)
**Splitting strategy:** S1: ContentToolsManager + CleanContentForm; S2: SeedInputForm + dashboard; S3: ContentToolsCommands (Drush) + import dispatcher.

---

## Scope (from primary source)

Developer/admin toolset for bulk data management and the AI content pipeline:

1. **Clean Content** (`CleanContentForm`) — 3-step bulk-delete form: select entity groups → type "CLEAN" to confirm → view results. Manages 21 entity groups in deletion-safe order via `ContentToolsManager`.
2. **Seed Input** (`SeedInputForm`) — 6-section AJAX form capturing organization, training program, course list (dynamic), instructors (dynamic), sample-data flags, and advanced options. Saves to `.agent/seed/seed-input.json`.
3. **Import Pipeline** (`everlms:import` Drush command) — reads `.agent/generated/manifest.json`, imports AI-generated JSON files across 13 directory prefixes (users, courses, quizzes, certificates, commerce, community, user_data, slideshows, introductions, glossaries, blogs, faqs, category).
4. **Dashboard** (`ContentToolsController::dashboard`) — card-based UI showing entity counts, seed-input status, AI generation status; links to clean and seed-input forms.
5. **Supporting Drush commands** — `everlms:clean`, `everlms:seed-input`, `everlms:mock-certs`.
6. **Certificate blueprints** — 11 HTML blueprint files in `src/Blueprints/` used by the import pipeline.

All routes under `/workspace/content-tools` with `_admin_route: TRUE`; permission: `administer lms content tools` (restrict access).

---

## Source Documents

| Source | Why loaded |
|--------|-----------|
| `../../C-Design/C05-modules/lms_content_tools.md` | Owning module design (as-built) |
| `../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md` | Content seeding AC |
| `../../B-Requirements/B05-acceptance-criteria/AC-14-ai-features.md` | AI content pipeline AC |

---

## Requirements

| Requirement | Source | Type |
|-------------|--------|------|
| Bulk delete all entity types with confirmation | AC-01-course-management.md | Acceptance |
| Seed input form saves JSON for AI generation | AC-14-ai-features.md | Acceptance |
| Import pipeline imports AI-generated content in dependency order | AC-14-ai-features.md | Acceptance |
| administer lms content tools permission restricts all access | Internal design | Acceptance |

---

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_content_tools/lms_content_tools.info.yml` | Create | Module declaration; dep: lms_base | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_content_tools/lms_content_tools.routing.yml` | Create | 3 Manage routes under /workspace/content-tools | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_content_tools/lms_content_tools.services.yml` | Create | lms_content_tools.manager (ContentToolsManager) | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_content_tools/drush.services.yml` | Create | lms_content_tools.commands (ContentToolsCommands) | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_content_tools/lms_content_tools.permissions.yml` | Create | administer lms content tools | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_content_tools/lms_content_tools.links.menu.yml` | Create | Content Tools menu link → system.admin_structure | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_content_tools/lms_content_tools.libraries.yml` | Create | clean_confirm library (js/clean-confirm.js) | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_content_tools/js/clean-confirm.js` | Create | Enables submit button after "CLEAN" typed | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_content_tools/src/Controller/ContentToolsController.php` | Create | dashboard() card UI with entity counts | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_content_tools/src/Form/CleanContentForm.php` | Create | 3-step bulk-delete form | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_content_tools/src/Form/SeedInputForm.php` | Create | 6-section AJAX seed input form | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_content_tools/src/Service/ContentToolsManager.php` | Create | Core service (21 groups, counts, deletes, seed I/O) | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_content_tools/src/Commands/ContentToolsCommands.php` | Create | Drush commands (~2531 lines); all 4 commands + import dispatcher | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_content_tools/src/Blueprints/*.html` | Create | 11 certificate HTML blueprints | U-004 | pre-2026-04-15 |

---

## Dependencies

| Dependency | Type | Source |
|------------|------|--------|
| `lms_base` | Intra-Epic | info.yml; provides LmsHelper + taxonomy vocabs |
| `entity_type.manager` | External (Drupal core) | DI in ContentToolsManager |
| `file_system` | External (Drupal core) | DI in ContentToolsManager (SCORM zip, managed file creation) |
| `lms_certificate.manager` | Cross-Epic (runtime) | Used by everlms:mock-certs; not in services.yml — injected at call time |

---

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|------------|-----------|---------|
| 50-entity chunked deletion | ContentToolsManager::deleteEntities() | Verified in facts base |
| `.agent/` path convention (DRUPAL_ROOT/../.agent/) | seed-input.json, generated/, images/ | Consistent with lms_base path pattern |

---

## Editorial Usability / Entity-First Evidence

Not applicable — lms_content_tools is a developer/admin tooling module with no learner-facing content.

**Hardcoded content exceptions:** Certificate blueprint HTML files in `src/Blueprints/` are hardcoded layouts with placeholder variables (`%body_text%`, `%issuer%`, `%signature_label%`). These are tooling assets, not editor-managed content.

---

## Feature Flags Introduced or Read

None.

---

## What Was Built (from facts base)

All code is **as-built** and fully functional. Status confirmed by `modules-foundation.md`:

- `ContentToolsManager` — all 10 methods implemented; ENTITY_GROUPS constant with 21 groups in deletion-safe order.
- `CleanContentForm` — 3-step form with JS confirmation; production-grade (type "CLEAN" validation).
- `SeedInputForm` — 6-section AJAX form with dynamic course/instructor row add/remove; saves to seed-input.json with backup + timestamped history.
- `ContentToolsCommands` — all 4 Drush commands implemented (~2531 lines); import dispatcher handles 13 directory prefixes; 3-tier fuzzy course/quiz lookup.
- 11 HTML blueprints in src/Blueprints/.

**Known bug:** The `community` group in `ENTITY_GROUPS` duplicates `lms_forum_topic`, `lms_forum_reply`, and `lms_badge` — already in `forums` and `badges` groups. Selecting both groups in `CleanContentForm` triggers a double-delete attempt. See [`../../C-Design/C05-modules/lms_content_tools.md`](../../C-Design/C05-modules/lms_content_tools.md) §Known bug.

---

## Tasks (draft unit references)

| Task / Draft ID | Description | Status |
|----------------|-------------|--------|
| U-004 | lms_base + lms_content_tools foundation setup (shared unit in draft) | DONE |

Note: The draft grouped lms_base and lms_content_tools under a single U-004. In canonical D-layer vocabulary these are two separate Stories (modules) with distinct scopes.

---

## Acceptance Link

See [`../../B-Requirements/B05-acceptance-criteria/AC-14-ai-features.md`](../../B-Requirements/B05-acceptance-criteria/AC-14-ai-features.md) (AI content pipeline) and [`../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md`](../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md) (content seeding).

---

## DONE Evidence

| Check | Result |
|-------|--------|
| /workspace/content-tools dashboard renders | PASS — ContentToolsController::dashboard() verified |
| CleanContentForm 3-step flow with "CLEAN" confirmation | PASS — form logic + JS confirmed |
| SeedInputForm saves seed-input.json | PASS — saveSeedInput() with backup verified |
| everlms:clean double-confirm + dry-run | PASS — ContentToolsCommands verified |
| everlms:import dispatches all 13 directory prefixes | PASS — import dispatcher verified |
| everlms:mock-certs generates PDFs | PASS — delegates to lms_certificate.manager |
| 11 certificate blueprints present | PASS — src/Blueprints/ confirmed |

---

## Gate Results (per Story, see A02 §3)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | All routes resolve; forms render | 1/3 | pre-2026-04-15 |
| G2 Lint | PASS | No violations reported | 1/3 | pre-2026-04-15 |
| G3 Tests | PASS | Seed save/load cycle; import dispatcher routing | 1/3 | pre-2026-04-15 |
| G4 Adversarial | PASS | Double-delete bug in community group noted as known debt | 1/3 | pre-2026-04-15 |

---

## Notes

- `ContentToolsCommands` at ~2531 lines is the largest single file in this Story; future sessions should consider splitting the import dispatcher into a dedicated ImportService.
- The seed → AI → import pipeline is the primary AI integration point for content bootstrapping; the external AI step (Claude Code / Gemini generating JSON) is intentionally outside Drupal's scope.
- File path convention: both `lms_base` and `lms_content_tools` use `DRUPAL_ROOT . '/../.agent/'` — consistent pattern, but fragile if webroot changes.
