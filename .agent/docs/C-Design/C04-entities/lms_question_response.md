# C04 Entity - lms_question_response

**Display name:** Question Response
**Owning module(s):** [lms_quiz](../C05-modules/lms_quiz.md)
**Mode:** as-built (Content Entity)

## Purpose
A learner's answer to one question within a quiz attempt, plus its grading outcome (auto or teacher-reviewed).

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| field_question | ref → lms_question |
| field_quiz | ref → lms_quiz |
| field_answers | **multi** chosen answer refs |
| field_correct | graded correct/incorrect |
| field_score | points awarded |
| field_text_answer | free-text answer (essay/short) |
| field_pending_review | awaiting manual grading |
| field_teacher_feedback | reviewer feedback (formatted) |

## Relationships
- Belongs to a `lms_quiz_result` (`field_responses`). Refs `lms_question` + `lms_quiz`.

## Editorial/data management
Created by the quiz engine on submit (not editor-authored). Teacher feedback editable by instructors for pending-review items. See [WF-002](../C12-workflows/WF-002-quiz-attempt-and-grading.md).
