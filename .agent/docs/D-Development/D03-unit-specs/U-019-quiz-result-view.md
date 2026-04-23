# D03 — Unit Spec: Quiz Result View + Template

**Unit ID:** U-019
**Group:** G-ASSESSMENT
**Title:** Quiz result view + template
**Status:** DONE
**Priority:** P0

---

## Scope

Implements the post-quiz result display: score breakdown, per-question correctness feedback, pass/fail status, and a listing of all past attempts. Covers `QuizController::viewResult()` and `::myResults()` with their templates.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_quiz/src/Controller/QuizController.php` | `viewResult()`, `myResults()` methods |
| `web/modules/custom/lms_quiz/lms_quiz.routing.yml` | Result view and my-results routes |
| `web/modules/custom/lms_quiz/templates/lms-quiz-result.html.twig` | Single result detail template |
| `web/modules/custom/lms_quiz/templates/lms-my-quiz-results.html.twig` | My results listing template |

---

## Entities / Services / Controllers / Templates

**QuizController methods:**
- `viewResult(QuizResult $lms_quiz_result): array` — detailed result with per-question breakdown
- `myResults(): array` — list of all quiz results for current user

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Routes resolve; templates render |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Result page displays score and feedback |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] Result page shows total score and pass/fail status
- [x] Per-question breakdown shows user answer vs. correct answer
- [x] Essay/short-answer questions shown as "pending review"
- [x] "My Results" page lists all past attempts with scores
- [x] Access restricted to result owner or admin

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
| U-017 | QuizResult and QuestionResponse entities displayed |
| U-018 | Quiz take redirects here on completion |
| U-020 | Grading service may update result after manual grading |

---

*End of U-019-quiz-result-view.md*
