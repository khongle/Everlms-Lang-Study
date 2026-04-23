# D03 — Unit Spec: Note Entity (video timestamp)

**Unit ID:** U-053
**Group:** G-ADDITIONAL
**Title:** Note entity (video timestamp)
**Status:** DONE
**Priority:** P2

---

## Scope

Implements the `lms_note` custom content entity allowing users to create timestamped notes while watching video lessons. Includes `NoteController` with AJAX save/delete/get endpoints and a "My Notes" listing page.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_note/lms_note.info.yml` | Module declaration |
| `web/modules/custom/lms_note/lms_note.install` | Schema install |
| `web/modules/custom/lms_note/lms_note.module` | Hook implementations |
| `web/modules/custom/lms_note/lms_note.routing.yml` | Note routes |
| `web/modules/custom/lms_note/src/Entity/Note.php` | Note entity class |
| `web/modules/custom/lms_note/src/Controller/NoteController.php` | Note controller |
| `web/modules/custom/lms_note/templates/lms-my-notes.html.twig` | My notes template |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_note` (`Note extends ContentEntityBase implements EntityOwnerInterface`)
- Key fields: `uid`, `field_lesson` (ref: `lms_lesson`), `field_course` (ref: `lms_course`), `field_content` (text), `field_timestamp` (integer — video seconds), `created`

**NoteController methods:**
- `save(Request $request): JsonResponse` — create or update note
- `delete(Request $request): JsonResponse` — delete note
- `getNote(Request $request): JsonResponse` — retrieve note by lesson/timestamp
- `myNotes(): array` — listing of all user notes

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Entity schema installs; AJAX endpoints work |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Note save/delete/retrieve functional |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_note` entity type registered
- [x] Note saves with video timestamp in seconds
- [x] AJAX save/delete/get endpoints functional
- [x] `/my-notes` page lists all notes grouped by course/lesson
- [x] Notes scoped to authenticated user

---

## Known Debt / Issues

None identified.

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-007 | Lesson entity referenced by Note |
| U-023 | Lesson view template triggers note AJAX calls |

---

*End of U-053-note-entity.md*
