# D03 — Unit Spec: Grading Service (auto + essay route)

**Unit ID:** U-020
**Group:** G-ASSESSMENT
**Title:** Grading service (auto + essay route)
**Status:** DONE
**Priority:** P0

---

## Scope

Orchestrates quiz grading after submission: iterates `QuestionResponse` records, calls the appropriate grading strategy via `GradingStrategyFactory`, accumulates scores, computes pass/fail, and saves the final `QuizResult`. Essay and short-answer responses are flagged for manual instructor review rather than auto-scored.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_quiz/src/Controller/QuizController.php` | `submitQuiz()` contains inline grading logic |
| `web/modules/custom/lms_quiz/src/Grading/GradingStrategyFactory.php` | Strategy resolution (see U-016) |
| `web/modules/custom/lms_quiz/src/Grading/Strategy/EssayStrategy.php` | Essay manual-grade flag |
| `web/modules/custom/lms_quiz/src/Grading/Strategy/ShortAnswerStrategy.php` | Short-answer manual-grade flag |

---

## Entities / Services / Controllers / Templates

**Grading flow (in `submitQuiz`):**
1. For each question in quiz: call `GradingStrategyFactory::getStrategy($bundle)->grade()`
2. Save score to `lms_question_response`
3. Sum scores, compute percentage
4. Set `field_passed` on `lms_quiz_result` based on `field_pass_percentage`
5. Dispatch `QuizPassedEvent` if passing

**Manual review path:** Essay/short-answer responses set `field_needs_manual_grading = TRUE`.

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Grading executes on submit |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Auto-graded types scored correctly |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] Auto-gradeable types (6 of 8) scored without instructor input
- [x] Essay and short-answer set `field_needs_manual_grading = TRUE`
- [x] Total score and pass/fail computed correctly
- [x] `QuizPassedEvent` dispatched on pass
- [x] Grading is idempotent (re-grading same result produces same score)

---

## Known Debt / Issues

- Grading logic is inline in `QuizController::submitQuiz()` rather than extracted to a dedicated service class — considered technical debt (relates to G-HARDENING refactor).

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-016 | GradingStrategyFactory called here |
| U-017 | QuizResult and QuestionResponse written here |
| U-018 | Quiz take controller calls grading flow |
| U-019 | Result view shows grading output |

---

*End of U-020-grading-service.md*
