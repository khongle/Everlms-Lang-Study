# D03 — Unit Spec: Question Entity (8 bundles)

**Unit ID:** U-014
**Group:** G-ASSESSMENT
**Title:** Question entity (8 bundles)
**Status:** DONE
**Priority:** P0

---

## Scope

Implements the `lms_question` custom content entity with a bundle-per-type architecture. Eight question types are defined via config install. Each bundle type drives the grading strategy in U-016. The `QuestionType` entity acts as the bundle config entity.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_question/lms_question.info.yml` | Module declaration |
| `web/modules/custom/lms_question/src/Entity/Question.php` | Entity class |
| `web/modules/custom/lms_question/src/Entity/QuestionType.php` | Bundle config entity |
| `web/modules/custom/lms_question/src/Access/QuestionAccessControlHandler.php` | Access handler |
| `web/modules/custom/lms_question/src/Form/QuestionForm.php` | Entity CRUD form |
| `web/modules/custom/lms_question/config/install/lms_question.question_type.essay.yml` | Essay bundle config |
| `web/modules/custom/lms_question/config/install/lms_question.question_type.fill_blank.yml` | Fill-in-blank bundle |
| `web/modules/custom/lms_question/config/install/lms_question.question_type.matching.yml` | Matching bundle |
| `web/modules/custom/lms_question/config/install/lms_question.question_type.multiple_choice.yml` | Multiple choice bundle |
| `web/modules/custom/lms_question/config/install/lms_question.question_type.ordering.yml` | Ordering bundle |
| `web/modules/custom/lms_question/config/install/lms_question.question_type.short_answer.yml` | Short answer bundle |
| `web/modules/custom/lms_question/config/install/lms_question.question_type.single_choice.yml` | Single choice bundle |
| `web/modules/custom/lms_question/config/install/lms_question.question_type.true_false.yml` | True/false bundle |
| `web/modules/custom/lms_question/config/schema/lms_question.schema.yml` | Config schema |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_question` (`Question extends ContentEntityBase`) — bundle type: `QuestionType`

**8 bundle types:** `essay`, `fill_blank`, `matching`, `multiple_choice`, `ordering`, `short_answer`, `single_choice`, `true_false`

Key fields: `title`, `field_question_text`, `field_answers` (ref: `lms_answer`), `field_points`, `field_explanation`

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | All 8 bundle configs install correctly |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Question create/edit for each bundle |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_question` entity type registered with bundle support
- [x] All 8 bundle types available and selectable
- [x] Questions can be created for each bundle
- [x] `field_answers` references `lms_answer` entities
- [x] `field_points` stores question weight
- [x] Access handler enforces permissions

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
| U-013 | Quiz references Question entities |
| U-015 | Answer and MatchingPair entities belong to Questions |
| U-016 | Grading strategies keyed by question bundle |

---

*End of U-014-question-entity.md*
