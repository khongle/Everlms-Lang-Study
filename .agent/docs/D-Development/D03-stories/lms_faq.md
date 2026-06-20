# D03 — Story Spec: LMS FAQ

**Story ID:** lms_faq
**Story type:** module
**Title:** FAQ entity with 3-scope (global/course/lesson) taxonomy and delegated routing
**Primary source:** `../../C-Design/C05-modules/lms_faq.md`
**Owning module(s):** lms_faq
**Service(s):** _(none)_
**Epic:** E-02 (Content Layer)
**Contributes to Feature(s):** F-001 (Course Management — FAQ content), F-016 (Essential Enhancements — FAQ management)
**Design source(s):** `../../C-Design/C05-modules/lms_faq.md`
**Round:** N/A
**Driver:** SA directive — FAQ/knowledge base entity
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** pre-2026-04-15 (pre-framework)
**Tasks:** see `../D04-tasks/` filtered by `Story ID: lms_faq`

---

## Session Strategy

**Estimated session count:** 1 (small — smallest custom module in EverLMS; only 2 files)
**Splitting strategy:** N/A — single session: one entity class + info.yml.

---

## Scope (from primary source)

The smallest custom module in EverLMS — defines only the `lms_faq` ContentEntity with a 3-scope discriminator; all CRUD routing and frontend rendering is delegated to `lms_course`:

1. **`lms_faq` entity** — ContentEntityBase (no EntityOwnerTrait, no `status` field); 7 base fields (question, field_answer, field_scope, field_course, field_category, weight); translatable; auto-generated routes via `AdminHtmlRouteProvider`.
2. **`field_scope`** — list_string discriminator with 3 values: `global`, `course`, `lesson`; default=global.
3. **Access control** — core `EntityAccessControlHandler` (no custom handler); governed by `administer lms_course` admin_permission.
4. **Auto-generated routes** — `/manage/faq/add`, `/manage/faq/{lms_faq}/edit`, `/manage/faq/{lms_faq}` (canonical); no delete-form link declared in annotation.
5. **Workspace CRUD** — `/workspace/content/faqs/add`, `/workspace/content/faqs/{lms_faq}/edit`, `/workspace/content/faqs/{lms_faq}/view` — declared in `lms_course.routing.yml`, handled by `WorkspaceContentController`.
6. **Frontend rendering** — no dedicated public route; FAQs rendered inline by `lms_course` controllers:
   - `CoursePublicController::homepage()` loads `field_scope=global` FAQs
   - `CoursePublicController::detail()` loads `field_scope=course` FAQs filtered by `field_course`

No routing.yml, no services.yml, no permissions.yml, no .module file, no .install file, no templates, no config directories.

---

## Source Documents

| Source | Why loaded |
|--------|-----------|
| `../../C-Design/C05-modules/lms_faq.md` | Owning module design (as-built) |
| `../../C-Design/C05-modules/lms_course.md` | Host of workspace routes + frontend FAQ loading |
| `../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md` | FAQ content delivery AC |

---

## Requirements

| Requirement | Source | Type |
|-------------|--------|------|
| lms_faq entity registered and CRUD functional | AC-01-course-management.md | Acceptance |
| field_scope discriminator supports global, course, lesson | AC-01-course-management.md | Acceptance |
| Global-scope FAQs displayed on homepage | AC-01-course-management.md | Acceptance |
| Course-scope FAQs displayed on course detail page filtered by field_course | AC-01-course-management.md | Acceptance |
| Admin can create/edit FAQs via workspace content routes | AC-01-course-management.md | Acceptance |

---

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_faq/lms_faq.info.yml` | Create | deps: lms_base | U-055 | pre-2026-04-15 |
| `web/modules/custom/lms_faq/src/Entity/Faq.php` | Create | lms_faq ContentEntity; 7 base fields; translatable | U-055 | pre-2026-04-15 |

---

## Dependencies

| Dependency | Type | Source |
|------------|------|--------|
| `lms_base` | Intra-Epic | Base module requirement; faq_category vocab installed by lms_base |
| `lms_course` | Cross-Story (runtime) | Admin permission borrowed; workspace CRUD routes declared in lms_course; frontend rendering in CoursePublicController |

---

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|------------|-----------|---------|
| field_scope default=global | Faq::baseFieldDefinitions() | Confirmed in entity class |
| weight field for sort order | FAQ ordering within scope | Confirmed in base fields |

---

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission |
|--------------------|----------------|-----------------|-----------|-------------------|
| Homepage FAQ section | Global-scope FAQs (question + answer) | lms_faq (field_scope=global) | `/workspace/content/faqs/add` | administer lms_course |
| Course detail FAQ section | Course-scope FAQs filtered by field_course | lms_faq (field_scope=course, field_course=current course) | `/workspace/content/faqs/add` | administer lms_course |

**Hardcoded content exceptions:** None — FAQ content is fully entity-driven.

---

## Feature Flags Introduced or Read

None.

---

## What Was Built (from facts base)

All code is **as-built**. Status confirmed by `modules-content.md`:

- `Faq.php` — 7 base fields verified; translatable; label key = `question`; NO `status` field; core EntityAccessControlHandler (no custom).
- Auto-generated CRUD routes via AdminHtmlRouteProvider confirmed (add, edit, canonical).
- Workspace CRUD routes (`/workspace/content/faqs/*`) confirmed in `lms_course.routing.yml`.
- Frontend FAQ loading confirmed in `CoursePublicController::homepage()` (global scope) and `::detail()` (course scope).

**Known issues / debt:**
- **No status (published) field:** FAQs cannot be drafted or unpublished — any saved FAQ is immediately visible. Adding status requires a schema update + update hook.
- **No lesson-scope consumer (potential dead field):** `field_scope=lesson` is declared but no controller is confirmed to load FAQs with this scope. The lesson scope appears to be target-state / unimplemented.
- **No field_lesson reference field:** Despite declaring a lesson scope, the entity has no reference back to the specific lesson.
- **No delete-form link in annotation:** Delete is unavailable through the workspace UI; only accessible via the auto-generated Drupal entity admin routes.
- **Core EntityAccessControlHandler:** No custom access handler — all FAQ operations gate on `administer lms_course` only. No granular per-editor control.
- **Routing delegation to lms_course violates separation of concerns:** All workspace FAQ CRUD routes are declared in `lms_course.routing.yml` and handled by `WorkspaceContentController`, creating a hard coupling.

---

## Tasks (draft unit references)

| Task / Draft ID | Description | Status |
|----------------|-------------|--------|
| U-055 | FAQ entity (3-scope: global/course/lesson) + workspace CRUD integration | DONE |

---

## Acceptance Link

See [`../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md`](../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md).

---

## DONE Evidence

| Check | Result |
|-------|--------|
| lms_faq entity type registered | PASS — entity class verified |
| 3-scope field (global/course/lesson) | PASS — field_scope base field confirmed |
| Global FAQs appear on homepage | PASS — CoursePublicController::homepage() loads global-scope FAQs |
| Course-scope FAQs on course detail, filtered by field_course | PASS — CoursePublicController::detail() confirmed |
| Admin can create/edit FAQs via /workspace/content/faqs/* | PASS — WorkspaceContentController routes confirmed |
| faq_category taxonomy reference | PASS — field_category base field (bundle: faq_category) confirmed |
| weight field controls sort order | PASS — base field confirmed |

---

## Gate Results (per Story, see A02 §3)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Entity schema installs; auto-generated routes resolve | 1/3 | pre-2026-04-15 |
| G2 Lint | PASS | No violations | 1/3 | pre-2026-04-15 |
| G3 Tests | PASS | FAQ CRUD via workspace routes functional; scope filtering verified | 1/3 | pre-2026-04-15 |
| G4 Adversarial | PASS | No status field, no lesson-scope consumer, no delete-form link noted as debt | 1/3 | pre-2026-04-15 |

---

## Notes

- This is the smallest custom module in EverLMS: exactly 2 files (`lms_faq.info.yml` + `src/Entity/Faq.php`). All other functionality is delegated to lms_course.
- The `field_scope=lesson` value is declared but has no frontend consumer as of the current codebase — it is target-state or abandoned. Any future lesson-FAQ feature requires: (1) a `field_lesson` entity_reference field on lms_faq, (2) a consumer in the lesson view controller (lms_progress).
- The `faq_category` vocabulary is installed by `lms_base` (one of the 8 vocabs in hook_install). lms_faq depends on lms_base to ensure the vocabulary exists before FAQ entities reference it.
