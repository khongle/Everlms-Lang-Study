# D03 — Story Spec: LMS Base

**Story ID:** lms_base
**Story type:** module
**Title:** Foundation utilities, taxonomy bootstrap, email-login, and demo-content Drush command
**Primary source:** `../../../C-Design/C05-modules/lms_base.md`
**Owning module(s):** lms_base
**Service(s):** `lms_base.helper`, `lms_base.commands`
**Epic:** E-01 (Foundation & Theme)
**Contributes to Feature(s):** F-007 (User Management), F-001 (Course Management — taxonomy prereq)
**Design source(s):** `../../C-Design/C05-modules/lms_base.md`
**Round:** N/A
**Driver:** ADR — foundation module; dependency-graph root
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** pre-2026-04-15 (pre-framework)
**Tasks:** see `../D04-tasks/` filtered by `Story ID: lms_base`

---

## Session Strategy

**Estimated session count:** 1 (small)
**Splitting strategy:** N/A — single cohesive install + service module.

---

## Scope (from primary source)

Foundation module for all EverLMS custom modules. Responsibilities:

1. **Taxonomy bootstrap** — `hook_install` creates 8 taxonomy vocabularies (`course_category`, `course_level`, `course_language`, `course_tags`, `quiz_difficulty`, `quiz_results`, `faq_category`, `badge_type`) with seeded default terms including a nested `course_category` hierarchy (Programming → Frontend/Backend/Mobile).
2. **`LmsHelper` service** — injectable utility: UID lookup, role check, term tree load, term name lookup, duration formatting, slug generation.
3. **Email-login** — `hook_form_alter` relabels username field to "Email address"; `lms_base_user_login_validate` resolves email to Drupal username before core validation.
4. **Auto-student role** — `hook_user_insert` assigns `student` role to newly registered standard users.
5. **Demo-content Drush command** — `lms:generate-demo` (`lms-gen`) creates N courses × M chapters × K lessons with Vietnamese demo content.

No web routes, no permissions.yml, no templates, no config/install.

---

## Source Documents

| Source | Why loaded |
|--------|-----------|
| `../../C-Design/C05-modules/lms_base.md` | Owning module design (as-built) |
| `../../B-Requirements/B05-acceptance-criteria/AC-07-user-management.md` | User role AC |
| `../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md` | Taxonomy prereq AC |

---

## Requirements

| Requirement | Source | Type |
|-------------|--------|------|
| 8 taxonomy vocabularies present after module install | AC-01-course-management.md | Acceptance |
| Login with email address supported | AC-07-user-management.md | Acceptance |
| New registrants auto-assigned `student` role | AC-07-user-management.md | Acceptance |
| `LmsHelper` injectable via DI in all downstream modules | Internal design | Non-Functional |

---

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_base/lms_base.info.yml` | Create | Module declaration; no custom deps | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_base/lms_base.services.yml` | Create | Registers `lms_base.helper` (LmsHelper) | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_base/drush.services.yml` | Create | Registers `lms_base.commands` (LmsDemoContentCommand) | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_base/lms_base.module` | Create | hook_user_insert, hook_form_alter, lms_base_user_login_validate, hook_user_login, hook_entity_presave (stub) | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_base/lms_base.install` | Create | hook_install (8 vocabs + seed terms), hook_uninstall, _lms_base_create_terms() | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_base/src/Service/LmsHelper.php` | Create | Utility service (6 methods) | U-004 | pre-2026-04-15 |
| `web/modules/custom/lms_base/src/Commands/LmsDemoContentCommand.php` | Create | Drush lms:generate-demo | U-004 | pre-2026-04-15 |

---

## Dependencies

| Dependency | Type | Source |
|------------|------|--------|
| `entity_type.manager` | External (Drupal core service) | DI in LmsHelper, LmsDemoContentCommand |
| `current_user` | External (Drupal core service) | DI in LmsHelper |
| _(none — custom)_ | — | lms_base is the dependency-graph root; no upstream custom deps |

---

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|------------|-----------|---------|
| Service registration via services.yml | lms_base.helper, lms_base.commands | Verified in facts base |
| No circular dependencies | All lms_* may depend on lms_base; lms_base must not depend on any | Design rule, confirmed in info.yml |

---

## Editorial Usability / Entity-First Evidence

Not applicable — lms_base defines no entities and renders no visible content.

**Hardcoded content exceptions:** Default taxonomy seed terms (course_category hierarchy, quiz_difficulty, etc.) are hardcoded in `lms_base.install::hook_install()` — intentional bootstrap data, not editor-managed content.

---

## Feature Flags Introduced or Read

None.

---

## What Was Built (from facts base)

All code is **as-built** and fully functional. Status confirmed by `modules-foundation.md`:

- `hook_install` creates and seeds all 8 vocabularies; `hook_uninstall` removes them cleanly.
- `LmsHelper` — 6 methods implemented, no stubs, no TODOs.
- `LmsDemoContentCommand` — functional `lms:generate-demo` with `--courses`, `--chapters`, `--lessons` options; creates Vietnamese demo content.
- Email-login validator operational (`lms_base_user_login_validate`).
- `hook_entity_presave` is an intentional stub (comment confirms: relies on standard Drupal logic).

**Known debt (not a bug):** `LmsDemoContentCommand` writes `field_chapter` on `lms_lesson` (lesson→chapter reference) — inverse of the forward-reference pattern used by `lms_content_tools` importer. Schema inconsistency; tracked in [`../../E-Quality/E02-issues/`](../../E-Quality/E02-issues/).

---

## Tasks (draft unit references)

| Task / Draft ID | Description | Status |
|----------------|-------------|--------|
| U-004 | lms_base: taxonomies + LmsHelper + email-login + Drush demo command | DONE |

---

## Acceptance Link

See [`../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md`](../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md) (taxonomy prereqs) and [`../../B-Requirements/B05-acceptance-criteria/AC-07-user-management.md`](../../B-Requirements/B05-acceptance-criteria/AC-07-user-management.md) (email login, auto-student role).

---

## DONE Evidence

| Check | Result |
|-------|--------|
| All 8 vocabularies created on install | PASS — confirmed in hook_install; `modules-foundation.md` |
| Default seed terms present | PASS — _lms_base_create_terms() recursive helper verified |
| LmsHelper injectable via DI | PASS — services.yml constructor DI; used by lms_content_tools and lms_course |
| Email login functional | PASS — lms_base_user_login_validate + hook_form_alter verified |
| Auto-student role assignment | PASS — hook_user_insert verified |
| Drush lms:generate-demo executes | PASS — LmsDemoContentCommand verified |

---

## Gate Results (per Story, see A02 §3)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Module installs; vocabularies created; service container resolves | 1/3 | pre-2026-04-15 |
| G2 Lint | PASS | No coding standard violations reported | 1/3 | pre-2026-04-15 |
| G3 Tests | PASS | LmsHelper methods return expected values; install/uninstall cycle clean | 1/3 | pre-2026-04-15 |
| G4 Adversarial | PASS | No known blockers; demo-content field direction inconsistency noted as debt | 1/3 | pre-2026-04-15 |

---

## Notes

- `lms_base` is the **dependency-graph root** — no other EverLMS custom module is allowed to be a dependency of lms_base.
- The `hook_entity_presave` stub is intentional (comment in code); safe to leave as-is.
- Demo-content command uses `field_chapter` on `lms_lesson` (child→parent ref) vs. `field_chapters` on `lms_course` (parent→children ref). Inconsistency should be resolved when the lesson entity's field schema is audited.
- 11 total taxonomy vocabularies exist in the system; lms_base installs 8. The remaining 3 (`blog_tags`, `forum_category`, `tags`) are installed by other modules.
