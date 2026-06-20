# D03 — Unit Spec: Quiz Take Controller + Template

**Unit ID:** U-018
**Group:** G-ASSESSMENT
**Title:** Quiz take controller + template
**Status:** DONE
**Priority:** P0

---

## Scope

Implements the quiz-taking user flow: render the quiz form (`takeQuiz`), process submission (`submitQuiz`), enforce time limit and attempt limits, and redirect to results. The template renders all question types with appropriate input widgets.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_quiz/src/Controller/QuizController.php` | `takeQuiz()`, `submitQuiz()` methods |
| `web/modules/custom/lms_quiz/lms_quiz.routing.yml` | Quiz take and submit routes |
| `web/modules/custom/lms_quiz/templates/lms-quiz-take.html.twig` | Quiz taking template |

---

## Entities / Services / Controllers / Templates

**QuizController methods:**
- `takeQuiz(Quiz $lms_quiz): array` — renders quiz form
- `submitQuiz(Quiz $lms_quiz, Request $request)` — processes POST, creates QuizResult/QuestionResponse

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Routes resolve; template renders |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Quiz take and submit flow functional |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] Quiz form renders all question types with correct widgets
- [x] Timer displayed when `field_time_limit` is set
- [x] Submission blocked if max attempts exceeded
- [x] POST submission creates `lms_quiz_result` and per-question `lms_question_response` records
- [x] User redirected to result page after submission
- [x] Unauthenticated users redirected to login

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
| U-013 | Quiz entity rendered |
| U-014 | Question entities and bundles rendered |
| U-015 | Answer options rendered for choice types |
| U-016 | Grading strategies called on submit |
| U-017 | QuizResult/QuestionResponse created on submit |
| U-020 | Grading service orchestrates scoring |

---

*End of U-018-quiz-take-controller.md*
