# D03 — Unit Spec: Answer + MatchingPair Entities

**Unit ID:** U-015
**Group:** G-ASSESSMENT
**Title:** Answer + MatchingPair entities
**Status:** DONE
**Priority:** P0

---

## Scope

Implements the `lms_answer` and `lms_matching_pair` custom content entities that store answer options for choice-type questions and key-value pairs for matching questions respectively. Both entities are child records of a `lms_question`.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_question/src/Entity/Answer.php` | Answer entity class |
| `web/modules/custom/lms_question/src/Entity/MatchingPair.php` | MatchingPair entity class |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_answer` (`Answer extends ContentEntityBase`)
- Key fields: `field_answer_text`, `field_is_correct` (boolean), `field_question` (ref: `lms_question`)

**Entity:** `lms_matching_pair` (`MatchingPair extends ContentEntityBase`)
- Key fields: `field_left_text`, `field_right_text`, `field_question` (ref: `lms_question`)

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Both entity schemas install correctly |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Answer/MatchingPair create/edit functional |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_answer` entity type registered in Drupal
- [x] `lms_matching_pair` entity type registered in Drupal
- [x] Answer entities reference their parent question
- [x] `field_is_correct` marks correct answers for auto-grading
- [x] MatchingPair stores left/right text for matching questions

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
| U-014 | Question entity references Answer/MatchingPair |
| U-016 | Grading strategies read `field_is_correct` |
| U-018 | Quiz take controller renders answer options |

---

*End of U-015-answer-matching-entities.md*
