# D03 — Unit Spec: Quiz Entity

**Unit ID:** U-013
**Group:** G-ASSESSMENT
**Title:** Quiz entity
**Status:** DONE
**Priority:** P0

---

## Scope

Implements the `lms_quiz` custom content entity representing a quiz associated with a lesson or standalone. Includes access control handler and entity form. Quizzes reference Question entities and store pass threshold, time limit, and attempt configuration.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_quiz/lms_quiz.info.yml` | Module declaration |
| `web/modules/custom/lms_quiz/lms_quiz.install` | Schema install |
| `web/modules/custom/lms_quiz/lms_quiz.module` | Hook implementations |
| `web/modules/custom/lms_quiz/src/Entity/Quiz.php` | Entity class |
| `web/modules/custom/lms_quiz/src/Access/QuizAccessControlHandler.php` | Access handler |
| `web/modules/custom/lms_quiz/src/Form/QuizForm.php` | Entity CRUD form |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_quiz` (`Quiz extends ContentEntityBase`)
- Key fields: `title`, `field_questions` (entity_reference to `lms_question`), `field_pass_percentage`, `field_time_limit`, `field_max_attempts`, `field_difficulty` (ref: `quiz_difficulty`), `status`

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Entity schema installs; CRUD works |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Quiz create/edit/delete functional |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_quiz` entity type registered in Drupal
- [x] Quiz references question entities
- [x] Pass percentage and time limit fields functional
- [x] Access handler enforces permissions
- [x] Quiz difficulty references `quiz_difficulty` taxonomy

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
| U-004 | `quiz_difficulty` taxonomy required |
| U-007 | Lesson `quiz` type references this entity |
| U-014 | Question entities referenced by Quiz |
| U-017 | QuizResult entity references Quiz |
| U-018 | Quiz take controller uses this entity |

---

*End of U-013-quiz-entity.md*
