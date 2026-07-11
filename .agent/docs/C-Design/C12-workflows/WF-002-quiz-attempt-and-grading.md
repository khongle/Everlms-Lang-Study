# C12 Workflow - WF-002 Quiz attempt & grading

## Business source
[B04 WF-002](../../B-Requirements/B04-business-workflows/WF-002-quiz-attempt-and-grading.md)

## Modules
[lms_quiz](../C05-modules/lms_quiz.md), [lms_question](../C05-modules/lms_question.md), [lms_progress](../C05-modules/lms_progress.md)

## Related features (B03)
[F-006](../../B-Requirements/B03-features/F-006-quiz-and-assessment-engine.md)

## State model
`lms_quiz_result`:
| State | Meaning |
| ----- | ------- |
| submitted | attempt submitted |
| in_review | essay/short-answer pending manual grade (`field_pending_review`) |
| approved_or_completed | graded; pass/fail set vs `field_threshold` |
| rejected_or_returned | failed attempt (may retry within `field_take_quiz_number`) |

## Result invariant
- `lms_quiz_result.field_result` is the source of truth for learner-facing state.
- If any `lms_question_response.field_pending_review` is true, the quiz result must remain `Pending`/`in_review` even when the provisional objective score is above threshold.
- Only a finalized `Passed` result may fire `QuizPassedEvent` for WF-003 certificate issuance.
- Result/history UI must display pending attempts separately from passed/failed attempts and label the score as provisional until review is complete.

## Permission model
- Initiator: enrolled learner.
- Responsible (manual grade): instructor for pending essays.
- Approver: instructor confirms/overrides scores.

## As-built grading (round-2)
- `field_result` is an entity reference to a `quiz_results` taxonomy term (`Passed` / `Failed` / `Pending`), set by `QuizController::submitQuiz`. Any response with `field_pending_review = 1` forces the result term to `Pending` regardless of the provisional `field_score`.
- **Manual grading** (added round-2): instructors grade pending responses at `lms_quiz.grade` → `/manage/quiz-results/{id}/grade` (`QuizGradeForm`). Access = `administer lms_course` OR the quiz's course owner OR a listed `field_instructor`. The form scores each pending response, clears `field_pending_review`, recomputes the percentage via `GradingStrategyFactory::getMaxScore`, resets the result term to `Passed`/`Failed`, and on a pass fires `QuizPassedEvent($result, $quiz, $resultOwner)` — the event carries the **learner** (result owner), not the grader. A "Grade this submission" CTA is shown on the result page to graders while the result is Pending.
- **Verified chain:** result 98 (maria/quiz 43, essay) → Pending → sabine (instructor) graded → Passed 100% → certificate `FDB2B9BA40` issued (WF-003), idempotent (single cert for learner/course).

## Notifications
Result notification to learner; pending-review notification to instructor. Pass → fires `QuizPassedEvent` (WF-003).

## Round-2 QA evidence
Screens: `.agent/docs/E-Quality/E00-workflows/round-2/_screens/002-quiz-attempt-and-grading/`. Issue `WF-002-001` (missing grading UI) found + fixed.
