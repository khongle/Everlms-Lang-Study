# D03 — Unit Spec: Lesson Entity (9 types) + Handlers

**Unit ID:** U-007
**Group:** G-CONTENT
**Title:** Lesson entity (9 types) + handlers
**Status:** DONE
**Priority:** P0

---

## Scope

Implements the `lms_lesson` custom content entity with a `field_type` discriminator supporting 9 lesson types. Provides access handler and entity form. Each type stores type-specific fields (video URL/file, H5P embed, SCORM package, quiz reference, etc.).

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_lesson/lms_lesson.info.yml` | Module declaration |
| `web/modules/custom/lms_lesson/lms_lesson.module` | Hook implementations |
| `web/modules/custom/lms_lesson/src/Entity/Lesson.php` | Entity class |
| `web/modules/custom/lms_lesson/src/Access/LessonAccessControlHandler.php` | Access handler |
| `web/modules/custom/lms_lesson/src/Form/LessonForm.php` | Entity CRUD form |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_lesson` (`Lesson extends ContentEntityBase`)
- `field_type` allowed values: `video`, `text`, `quiz`, `h5p`, `scorm`, `pdf`, `pptx`, `zoom`, `mixed`
- Key fields: `title`, `field_content` (text_long), `field_type` (list_string), `field_video_url`, `field_video_file`, `field_h5p_embed`, `field_scorm_package`, `field_quiz` (ref: `lms_quiz`), `field_duration`, `status`

Note: D01 lists "7 types" but source has 9 (`video`, `text`, `quiz`, `h5p`, `scorm`, `pdf`, `pptx`, `zoom`, `mixed`).

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Entity schema installs; all 9 types stored |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Lesson create/edit for each type functional |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_lesson` entity type registered in Drupal
- [x] 9 lesson types selectable via `field_type`
- [x] Video lessons store URL or file reference
- [x] Quiz lessons reference a `lms_quiz` entity
- [x] SCORM lessons store package file reference
- [x] Access handler enforces permissions
- [x] Status field controls lesson visibility

---

## Known Debt / Issues

- D01 states "7 types"; actual implementation has 9 (`pdf`, `pptx` added beyond original spec).

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-006 | Chapter references lessons |
| U-013 | Quiz lessons reference Quiz entity |
| U-021 | UserLesson progress tracks this entity |
| U-023 | Lesson view rendered in dashboard |

---

*End of U-007-lesson-entity.md*
