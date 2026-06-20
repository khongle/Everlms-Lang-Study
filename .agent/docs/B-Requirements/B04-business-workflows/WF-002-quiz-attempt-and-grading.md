# WF-002 - Quiz attempt & grading

## Business flow
A learner opens a quiz attached to a lesson/course. The system presents questions (8 types) and the learner submits answers. On submit, the `GradingStrategyFactory` selects a grading strategy per question type, scores objective questions automatically, stores per-question `lms_question_response` and an overall `lms_quiz_result`, and compares the score to the pass threshold. Objective quizzes return a result immediately; essay/short-answer items may be flagged for manual review. A pass on a course's final quiz emits `QuizPassedEvent`, consumed by WF-003.

## Participating modules
[lms_quiz](../../C-Design/C05-modules/lms_quiz.md), [lms_question](../../C-Design/C05-modules/lms_question.md), [lms_progress](../../C-Design/C05-modules/lms_progress.md)

## Related features (B03)
[F-006](../B03-features/F-006-quiz-and-assessment-engine.md), [F-008](../B03-features/F-008-badges-and-gamification.md)

## Trigger
Learner submits a quiz attempt.

## Outcome
`lms_quiz_result` + `lms_question_response` records persisted; score and pass/fail computed; `QuizPassedEvent` fired on pass.

## Design link
[C12 WF-002](../../C-Design/C12-workflows/WF-002-quiz-attempt-and-grading.md)
