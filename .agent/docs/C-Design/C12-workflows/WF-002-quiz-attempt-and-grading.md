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

## Notifications
Result notification to learner; pending-review notification to instructor. Pass → fires `QuizPassedEvent` (WF-003).
