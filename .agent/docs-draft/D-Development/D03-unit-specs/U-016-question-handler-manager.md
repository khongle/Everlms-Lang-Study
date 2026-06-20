# D03 — Unit Spec: QuestionHandlerManager (8 strategies)

**Unit ID:** U-016
**Group:** G-ASSESSMENT
**Title:** QuestionHandlerManager (8 strategies)
**Status:** DONE
**Priority:** P0

---

## Scope

Implements the strategy pattern for grading different question types. The `GradingStrategyFactory` resolves the correct `QuestionGradingInterface` implementation based on question bundle. Eight strategy classes cover all question bundles defined in U-014.

Note: D01 names this "QuestionHandlerManager" but the actual implementation uses `GradingStrategyFactory` + `QuestionGradingInterface` strategy pattern located in `lms_quiz` (not `lms_question`).

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_quiz/src/Grading/GradingStrategyFactory.php` | Factory resolving strategy by bundle |
| `web/modules/custom/lms_quiz/src/Grading/QuestionGradingInterface.php` | Grading strategy interface |
| `web/modules/custom/lms_quiz/src/Grading/Strategy/SingleChoiceStrategy.php` | Single choice grading |
| `web/modules/custom/lms_quiz/src/Grading/Strategy/MultipleChoiceStrategy.php` | Multiple choice grading |
| `web/modules/custom/lms_quiz/src/Grading/Strategy/TrueFalseStrategy.php` | True/false grading |
| `web/modules/custom/lms_quiz/src/Grading/Strategy/ShortAnswerStrategy.php` | Short answer grading |
| `web/modules/custom/lms_quiz/src/Grading/Strategy/EssayStrategy.php` | Essay (manual) grading |
| `web/modules/custom/lms_quiz/src/Grading/Strategy/FillBlankStrategy.php` | Fill-in-blank grading |
| `web/modules/custom/lms_quiz/src/Grading/Strategy/MatchingStrategy.php` | Matching pair grading |
| `web/modules/custom/lms_quiz/src/Grading/Strategy/OrderingStrategy.php` | Ordering grading |

---

## Entities / Services / Controllers / Templates

**`GradingStrategyFactory::getStrategy(string $bundle): ?QuestionGradingInterface`**

**`QuestionGradingInterface` methods:**
- `grade(Question $question, mixed $user_submission): array`
- `getMaxScore(Question $question): float`

8 strategy implementations: `SingleChoiceStrategy`, `MultipleChoiceStrategy`, `TrueFalseStrategy`, `ShortAnswerStrategy`, `EssayStrategy`, `FillBlankStrategy`, `MatchingStrategy`, `OrderingStrategy`

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Factory resolves all 8 strategies |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Each strategy grades correctly for its type |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `GradingStrategyFactory::getStrategy()` returns correct implementation for each of 8 bundles
- [x] All strategies implement `QuestionGradingInterface`
- [x] Auto-gradeable types (single, multiple, true_false, fill_blank, matching, ordering) return score immediately
- [x] Essay and short_answer strategies flag for manual review
- [x] `getMaxScore()` returns configured point value

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
| U-014 | Question bundles keyed to strategies |
| U-015 | Answer entities read by strategies |
| U-020 | Grading service calls factory |

---

*End of U-016-question-handler-manager.md*
