# F-006 - Quiz & assessment engine

**Status:** built
**Primary module:** [lms_quiz](../../C-Design/C05-modules/lms_quiz.md)
**Epic:** [E04 Assessment](../../D-Development/D02-epics/E04-assessment.md)
**Related workflows:** [WF-002 Quiz attempt & grading](../B04-business-workflows/WF-002-quiz-attempt-and-grading.md)
**Acceptance:** [B05 F-006](../B05-acceptance-criteria/F-006-quiz-and-assessment-engine.md)
**Source:** `web/modules/custom/lms_quiz/`, `lms_question/`

## Requirement
Instructors build quizzes (`lms_quiz`) from questions (`lms_question`) of **8 types** (`lms_question_type` config bundles: single_choice, multiple_choice, true_false, short_answer, essay, fill_blank, matching, ordering). Learners attempt a quiz; the engine auto-grades objective types via a `GradingStrategyFactory` (one strategy per type), records a `lms_quiz_result` with per-question `lms_question_response`, computes a score, and applies a pass threshold. Quizzes may have prerequisites (`field_prerequisite_quizzes`).

## Business value
Assessment validates learning, gates certification, and differentiates a real LMS from a content site.

## Scope notes
- Objective types auto-graded; essay/short_answer may require manual review.
- Passing a final quiz emits `QuizPassedEvent` → certificate issuance (F-007/WF-003).
- Question sub-entities: `lms_answer`, `lms_matching_pair`; quiz result holds `field_responses`.

## Cross references
- Design: [C04 lms_quiz](../../C-Design/C04-entities/lms_quiz.md), [C04 lms_question](../../C-Design/C04-entities/lms_question.md), [C04 lms_quiz_result](../../C-Design/C04-entities/lms_quiz_result.md), [C12 WF-002](../../C-Design/C12-workflows/WF-002-quiz-attempt-and-grading.md)
