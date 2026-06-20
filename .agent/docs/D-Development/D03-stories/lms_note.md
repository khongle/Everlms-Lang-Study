# D03 — Story Spec: lms_note

**Story ID:** lms_note
**Story type:** module
**Title:** Student Notes — per-lesson note-taking with video timestamp, JSON API, grouped My Notes page
**Primary source:** `C-Design/C05-modules/lms_note.md`
**Owning module(s):** lms_note
**Service(s):** note
**Epic:** E-04 (Progress & Learner Portal)
**Contributes to Feature(s):** Learning Progress, Content & Media
**Design source(s):** `../C-Design/C05-modules/lms_note.md`
**Round:** N/A
**Driver:** SA directive
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** (pre-2026-04-15; implemented before D03 layer was materialized)
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_note`

---

## Session Strategy

**Estimated session count:** 1
**Splitting strategy:** N/A — single small module; entity + controller + template fit in one session.

---

## Scope

Provides per-user, per-lesson note-taking with optional video timestamp anchoring:

- `lms_note` content entity: stores lesson reference, course reference, note body, and video position in seconds. `field_course` was added post-release via `lms_note_update_10001`.
- `NoteController`: three JSON API endpoints (save/upsert, delete, get) and one portal page (my-notes, grouped by course).
- Cache invalidation via `lms_note:{lesson_id}:{uid}` cache tag on save and delete.
- `lms-my-notes.html.twig`: renders notes grouped by course.
- Integration point: `UserPortalController::lessonView()` (in `lms_progress`) queries `lms_note` storage and passes `existing_note` to lesson view template.

Full design: [`../C-Design/C05-modules/lms_note.md`](../../C-Design/C05-modules/lms_note.md)

---

## Source Documents

| Source | Why loaded |
|---|---|
| `../C-Design/C05-modules/lms_note.md` | Owning module design — authoritative reference |
| `../C-Design/C05-modules/lms_progress.md` | Consumer: lessonView queries lms_note storage for existing_note |

---

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Learners can save and retrieve notes per lesson | B03-features/03-learning-progress.md | Acceptance |
| Notes support optional video timestamp to link note to a moment in a video | B03-features/05-content-media.md | Acceptance |
| My Notes page groups notes by course | B03-features/03-learning-progress.md | Acceptance |
| One note per user per lesson (upsert, not multi-create) | B03-features/03-learning-progress.md | Acceptance |
| Note save/delete invalidates per-lesson cache | B02-technical-requirements.md | Technical |

---

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_note/lms_note.info.yml` | Create | Module declaration; deps: lms_base, lms_lesson | — | S1 |
| `web/modules/custom/lms_note/lms_note.routing.yml` | Create | 4 routes: /api/lms/note/save, /delete, /get [POST/GET]; /my-notes [GET] | — | S1 |
| `web/modules/custom/lms_note/lms_note.module` | Create | hook_theme() for lms_my_notes | — | S1 |
| `web/modules/custom/lms_note/lms_note.install` | Create | lms_note_update_10001(): adds field_course to lms_note entity | — | S1 |
| `web/modules/custom/lms_note/src/Entity/Note.php` | Create | lms_note content entity; 8 base fields; EntityOwnerTrait | — | S1 |
| `web/modules/custom/lms_note/src/Controller/NoteController.php` | Create | save(), delete(), getNote(), myNotes() | — | S1 |
| `web/modules/custom/lms_note/templates/lms-my-notes.html.twig` | Create | vars: grouped_notes (keyed by course ID) | — | S1 |

---

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| lms_base | Internal module | Shared entity traits |
| lms_lesson | Internal module | field_lesson entity reference; lesson context in save/get |
| lms_course (runtime, not declared in info.yml) | Internal module | field_course added by update_10001; grouping in myNotes() |
| lms_progress (runtime consumer) | Internal module | lessonView() queries lms_note storage and passes existing_note to template |

---

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| Upsert pattern (one note per user+lesson) | NoteController::save() | Entity query by uid + field_lesson; update if found, create if not |
| Cache tag invalidation | NoteController::save(), delete() | Invalidates `lms_note:{lesson_id}:{uid}` tag |
| Schema migration via update hook | field_course | lms_note_update_10001(); pre-update records have NULL field_course |

---

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission |
|---|---|---|---|---|
| My Notes page | notes grouped by course; body text; video timestamp | lms_note entity | `/my-notes` | authenticated (owner) |
| Lesson note widget | existing_note body pre-filled in lesson view | lms_note entity | `/learn/{course}/{lesson}` inline | authenticated (owner) |

**Hardcoded content exceptions:** None.

---

## Feature Flags Introduced or Read

None. No feature flags in this module.

---

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-NOTE-001 | Unit | save() creates note on first call for user+lesson | lms_note entity created; status=created |
| T-NOTE-002 | Unit | save() updates note on second call for same user+lesson | lms_note entity updated; status=updated |
| T-NOTE-003 | Unit | delete() removes note for user+lesson; returns not_found if absent | status=deleted or not_found |
| T-NOTE-004 | Unit | getNote() returns note_text for given lesson_id | Correct body returned |
| T-NOTE-005 | Integration | myNotes() groups notes by course; sorted by changed DESC | grouped_notes keyed by course_id |
| T-NOTE-006 | Integration | save() invalidates lms_note:{lesson_id}:{uid} cache tag | Cache properly invalidated |

---

## Gate Results

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Entity schema installs; routes resolve; update hook runs | — | pre-2026-04-15 |
| G2 Lint | PASS | No coding standard violations | — | pre-2026-04-15 |
| G3 Tests | PASS | Save/delete/get/myNotes functional; grouping correct | — | pre-2026-04-15 |
| G4 Adversarial | PASS | No blockers at close; open debt logged below | — | pre-2026-04-15 |

---

## Notes

### Known Issues / Debt

| ID | Severity | Description |
|----|----------|-------------|
| BUG-NOTE-01 | Medium | `delete()` queries by `uid + field_lesson` using current_user; verify entity query filters enforce ownership and cannot be spoofed via POST body |
| DEBT-NOTE-01 | Low | No full-text search on `/my-notes`; large note collections require client-side filtering |
| DEBT-NOTE-02 | Low | API endpoints use session-cookie auth (Drupal default); if called from a cross-origin SPA, CSRF token review is needed |

### Schema Note

`field_course` was added post-initial-release via `lms_note_update_10001`. Notes created before the update have `NULL` for `field_course`. The `myNotes()` grouping silently handles this (notes with no course appear ungrouped or under a NULL key depending on array handling).

These issues feed [E02 Issues](../../E-Quality/E02-issues/).
