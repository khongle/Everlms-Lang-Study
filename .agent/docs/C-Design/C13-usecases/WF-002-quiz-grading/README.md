# WF-002 — Quiz Grading — Use Case Folder

**Workflow:** WF-002 Quiz Grading  
**Source workflow doc:** [`../../C12-workflows/`](../../C12-workflows/)  
**Business workflow:** [`../../../../B-Requirements/B04-business-workflows/WF-002-quiz-grading.md`](../../../B-Requirements/B04-business-workflows/WF-002-quiz-grading.md)  
**Implementing modules:** `lms_quiz`, `lms_question`, `lms_notification`  

---

## Use cases in this folder

| File | ID | Type | Summary |
|------|----|------|---------|
| [UC-002-01-auto-grade-pass.md](UC-002-01-auto-grade-pass.md) | UC-002-01 | happy | Student submits objective quiz and passes; `QuizPassedEvent` dispatched |
| [UC-002-02-essay-pending.md](UC-002-02-essay-pending.md) | UC-002-02 | edge | Quiz contains essay question; result is `Pending` regardless of other scores |
| [UC-002-03-prerequisite-blocked.md](UC-002-03-prerequisite-blocked.md) | UC-002-03 | permission | Student attempts quiz without passing prerequisite; attempt is blocked |

---

## Architecture notes

| Component | Implementation |
|-----------|---------------|
| Grading | Static PHP `GradingStrategyFactory` + 8 `QuestionGradingInterface` strategy classes (NOT a Drupal Plugin) |
| Grading model | Strictly binary — all-or-nothing per question, no partial credit |
| Essay grading | Always `pending_review=true`; must be manually graded (no manual grading UI exists — blocked) |
| Pass event | `QuizPassedEvent` dispatched by `QuizController::submitQuiz()` on Passed result |
| `field_start_time` | Hardcoded to `$now - 60` (1-min estimate) — not real tracking |
| Quiz result taxonomy | `quiz_results` vocabulary; terms: `Passed`, `Failed`, `Pending` (auto-created if Pending term missing) |
