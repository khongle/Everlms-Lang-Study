---
page: my-notes
mode: as-built
owner_module: lms_note
route_group: app
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: My Notes

**Canonical URL:** `/my-notes` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_note.my_notes`
**Solution:** Custom controller (`NoteController::myNotes`) queries all `lms_note` entities for the current user, groups by course, returns `lms_my_notes` theme render array.
**Owner module:** [`lms_note`](../../C05-modules/lms_note.md)
**Primary source:** `entity:lms_note` (all notes owned by current user)
**Editable source:** `lms_note` entities — created/updated via AJAX API at `/api/lms/note/save` (inline in lesson view); not directly editable on this page
**Editor role:** Learner (note owner); notes are created in-lesson via the notes sidebar
**Editor management UI:** In-lesson note panel at `/learn/{course}/{lesson}` — AJAX save via `lms_note.save` (`POST /api/lms/note/save`)
**Revision / moderation:** Not revisionable
**Forms:** — (read-only listing; no create/edit form on this page)
**Theme template:** `web/modules/custom/lms_note/templates/lms-my-notes.html.twig` *(see [`../../C09-skins.md`](../../C09-skins.md))*
**Access:** Authenticated (`_user_is_logged_in: TRUE`)
**Cache:** Per-user — cache tags `lms_note:{lesson_id}:{uid}` (invalidated on save/delete via `NoteController`)
**Translation:** Monolingual EN
**Related entities:** [`lms_note`](../../C04-entities/README.md), [`lms_course`](../../C04-entities/README.md), [`lms_lesson`](../../C04-entities/README.md)
**Prototype reference:** — (no dedicated prototype; referenced layout implied by account area)
**Notes:** Notes are grouped by course (keyed by `field_course` entity ID) then listed per lesson within each course. Sort: `changed DESC`. Each note entry links back to the originating lesson via `field_lesson` reference. `field_video_timestamp` stores the video position when the note was saved — shown as a timestamp link.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined once in [`../_common.md`](../_common.md) and not repeated here.

### 1. Page Title / Empty State

- **Scope:** Page-specific
- **Implementation mechanism:** Twig conditional: if `grouped_notes` is empty, shows "No notes yet" empty-state; otherwise shows page heading
- **Plugin / Module:** `lms_note` — `lms-my-notes.html.twig`
- **Source content type / Entity:** Static chrome
- **Backed by fields / config keys:** Static text
- **Editable source:** computed/read-only
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** Heading + optional empty-state block
- **Region / Placement:** Top of page content area
- **Notes:** Empty state should include a CTA linking to `/my-courses`.
- **How to implement (EN):** `{% if grouped_notes is empty %}` renders empty-state with link to my-courses. Otherwise renders "My Notes" heading.
- **How to implement (VI):** `{% if grouped_notes is empty %}` hiển thị trạng thái trống với link đến my-courses. Ngược lại hiển thị tiêu đề "Ghi chú của tôi".
- **Solution Architect Request:** —

### 2. Course-Grouped Note List

- **Scope:** Page-specific
- **Implementation mechanism:** Twig nested loop: outer loop over `grouped_notes` (keyed by course ID), inner loop over notes per course/lesson
- **Plugin / Module:** `lms_note` — `NoteController::myNotes` + `lms-my-notes.html.twig`
- **Source content type / Entity:** `lms_note`, `lms_course`, `lms_lesson`
- **Backed by fields / config keys:** `field_body` (note text), `field_course` (course ref), `field_lesson` (lesson ref), `field_video_timestamp`, `changed`
- **Editable source:** `lms_note` entities — editable in-lesson via AJAX API
- **Editor role / permission:** Authenticated user (note owner)
- **Editor management UI:** `/learn/{course_id}/{lesson_id}` (notes panel in lesson view)
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Create a note in lesson view → navigate to `/my-notes` → confirm note appears under correct course
- **View mode / Projection:** Grouped list; note cards with course header, lesson link, note text, timestamp, video timestamp badge (if `field_video_timestamp > 0`)
- **Region / Placement:** Main content area
- **Notes:** Controller groups: `$grouped[$course_id][] = $note_data`. Each course group shows course title as section header. Each note shows: lesson title as link, note body, `changed` date, video position badge (when `field_video_timestamp > 0`). No pagination — all notes loaded at once for current user.
- **How to implement (EN):** Controller: query `lms_note` by `uid=current_user` ORDER BY `changed DESC`; load course+lesson for each note; build `grouped_notes[course_id][]` array with course_title, lesson_url, body, timestamp, video_timestamp. Template: `{% for course_id, group in grouped_notes %}`.
- **How to implement (VI):** Controller: truy vấn `lms_note` theo `uid=current_user` ORDER BY `changed DESC`; load course+lesson; xây dựng `grouped_notes[course_id][]` với course_title, lesson_url, body, timestamp, video_timestamp. Template: `{% for course_id, group in grouped_notes %}`.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Page Title / Empty State | "My Notes" heading or "No notes yet" empty state with CTA | Static chrome | N/A | computed/read-only | N/A | N/A | N/A | N/A | N/A |
| Course-Grouped Note List | Course section headers, lesson links, note text, save timestamp, video timestamp badge | `lms_note`, `lms_course`, `lms_lesson` | `field_body`, `field_course`, `field_lesson`, `field_video_timestamp`, `changed` | `/learn/{course}/{lesson}` notes panel | Authenticated (owner) | N/A | Monolingual EN | `lms_note:{lesson_id}:{uid}` per-user | Create note in lesson → confirm on this page |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| "My Notes" page title | Static UI chrome | Developer convention | — |
| "No notes yet" empty state copy | Static developer string | Developer convention | — |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Page Title / Empty State | DONE | — | As-built |
| Course-Grouped Note List | DONE | — | As-built; grouping by course, lesson links, video timestamp |
| Note AJAX create/update (in-lesson) | DONE | — | As-built via `lms_note.save` + `lms_note.delete` AJAX endpoints |
| Pagination | NOT IMPLEMENTED | — | All notes loaded at once; no pager; acceptable for typical user scale |
