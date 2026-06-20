---
module: lms_note
service: note
route-groups: [App, API]
status: DONE
---

# C05 — Module: LMS Note

**Directory:** `web/modules/custom/lms_note/`
**Responsibility:** Provides per-user, per-lesson note-taking with optional video timestamp anchoring, exposed via lightweight JSON API endpoints and a grouped "My Notes" portal page.

> **Documentation mode:** as-built — matches current code (2026-06-20).

---

## Route Bindings

| Route Group | Prefix | Controller | Paths |
|-------------|--------|------------|-------|
| API (legacy) | `/api/lms` | `NoteController` | `/api/lms/note/save` [POST], `/api/lms/note/delete` [POST], `/api/lms/note/get` [GET] |
| App | `/` | `NoteController` | `/my-notes` [GET] |

All routes require `_user_is_logged_in: TRUE`.

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `NoteController` | Controller | JSON CRUD API for notes; renders My Notes page |
| `lms_note` | Entity | Student note record (lesson, course, body, video timestamp) |

No `lms_note.services.yml` — no exported services.

---

## Internal Structure

```
web/modules/custom/lms_note/
+-- lms_note.info.yml           # package: EverLMS; deps: lms_base, lms_lesson
+-- lms_note.routing.yml        # 4 routes (3 API + 1 App)
+-- lms_note.module             # hook_theme() only
+-- lms_note.install            # lms_note_update_10001() — adds field_course
+-- src/
|   +-- Controller/
|   |   +-- NoteController.php      # save(), delete(), getNote(), myNotes()
|   +-- Entity/
|   |   +-- Note.php                # lms_note content entity
+-- templates/
|   +-- lms-my-notes.html.twig      # vars: grouped_notes
```

---

## Entity: `lms_note` — Student Note

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| id | auto | — | PK |
| uuid | auto | — | |
| uid | entity_reference (user) | — | via `EntityOwnerTrait` |
| field_course | entity_reference (lms_course) | TRUE | added by `lms_note_update_10001` |
| field_lesson | entity_reference (lms_lesson) | TRUE | |
| field_body | text_long | TRUE | |
| field_video_timestamp | integer | — | seconds into video; default 0 |
| created | created | — | |
| changed | changed | — | |

Table: `lms_note`. Admin permission: `administer lms_course`. Access handler: core default `EntityAccessControlHandler`.

> **Schema note:** `field_course` was added post-initial-release via database update `lms_note_update_10001`. Pre-update records have no course linkage.

---

## Controllers

### `NoteController` — as-built

Dependencies: `entity_type.manager`, `current_user`.

| Method | Route | Description |
|--------|-------|-------------|
| `save(Request)` | `lms_note.save` [POST] | Accepts JSON `{lesson_id, course_id, note_text, video_timestamp}`; upserts `lms_note` (one note per user+lesson); invalidates cache tag `lms_note:{lesson_id}:{uid}`; returns JSON `{status: created\|updated, id}`. |
| `delete(Request)` | `lms_note.delete` [POST] | Accepts JSON `{lesson_id}`; deletes all notes for current user + lesson; invalidates cache; returns JSON `{status: deleted\|not_found}`. |
| `getNote(Request)` | `lms_note.get` [GET] | Query param `?lesson_id=`; returns JSON `{status, note_text}`. |
| `myNotes()` | `lms_note.my_notes` [GET] | Loads all notes for current user sorted by `changed` DESC; groups by course ID; renders `lms_my_notes` template with `grouped_notes` array keyed by course ID. |

---

## Hook Implementations

| Hook | File | Description |
|------|------|-------------|
| `hook_theme()` | `lms_note.module` | Registers `lms_my_notes` template with variable `grouped_notes`. |

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_base` | Internal | Shared base traits |
| `lms_lesson` | Internal | `field_lesson` entity reference; lesson context in save/get |
| `lms_course` | Internal (runtime, not declared in info.yml) | `field_course` added by update_10001; grouping in myNotes() |
| `lms_progress` | Internal (runtime, not declared) | `UserPortalController::lessonView()` queries lms_note storage and passes `existing_note` to template |

---

## Known Issues / Bugs

| ID | Severity | Description |
|----|----------|-------------|
| BUG-NOTE-01 | Medium | `delete()` deletes by `uid + field_lesson` — no ownership check against the note's own `uid` field; relies solely on `current_user`. Works correctly but verify entity query filters are enforced. |
| DEBT-NOTE-01 | Low | No full-text search on `/my-notes`; large note collections require client-side filtering. |
| DEBT-NOTE-02 | Low | API endpoints use session-cookie auth (Drupal default); no explicit CSRF token mechanism beyond Drupal's standard form API. If called from a cross-origin SPA, CSRF review is needed. |

> These feed [E02 Issues](../../E-Quality/E02-issues/).

---

## Cross-Links

- Entity detail: [C04 Content Entities](../C04-entities/01-content-entities.md)
- Note displayed in lesson view: [lms_progress module](lms_progress.md) (`lessonView()` passes `existing_note`)
- Lesson reference: [lms_lesson module](lms_lesson.md)
