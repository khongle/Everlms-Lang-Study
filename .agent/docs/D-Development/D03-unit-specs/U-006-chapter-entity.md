# D03 — Unit Spec: Chapter Entity + Handlers

**Unit ID:** U-006
**Group:** G-CONTENT
**Title:** Chapter entity + handlers
**Status:** DONE
**Priority:** P0

---

## Scope

Implements the `lms_chapter` custom content entity representing a logical grouping of lessons within a course. Includes access control handler and entity form. Chapters are referenced from Course via `field_chapters`.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_chapter/lms_chapter.info.yml` | Module declaration |
| `web/modules/custom/lms_chapter/src/Entity/Chapter.php` | Entity class |
| `web/modules/custom/lms_chapter/src/Access/ChapterAccessControlHandler.php` | Access handler |
| `web/modules/custom/lms_chapter/src/Form/ChapterForm.php` | Entity CRUD form |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_chapter` (`Chapter extends ContentEntityBase`)
- Key fields: `title`, `field_lessons` (entity_reference to `lms_lesson`), `status`, `weight`

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Entity schema installs; CRUD works |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Chapter create/edit/delete functional |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_chapter` entity type registered in Drupal
- [x] Chapter can be created and attached to a Course
- [x] `field_lessons` references `lms_lesson` entities
- [x] Access handler enforces permissions
- [x] `status` field controls chapter visibility

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
| U-005 | Course references chapters via `field_chapters` |
| U-007 | Lesson entity referenced by Chapter |
| U-022 | Progress cascade traverses chapters → lessons |

---

*End of U-006-chapter-entity.md*
