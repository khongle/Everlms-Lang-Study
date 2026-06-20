# C04 Entity - lms_quiz_result

**Display name:** Quiz Result
**Owning module(s):** [lms_quiz](../C05-modules/lms_quiz.md)
**Mode:** as-built (Content Entity)

## Purpose
One learner's quiz attempt: aggregate score, pass/fail result, timing, and the per-question responses.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | learner |
| field_quiz | ref → lms_quiz |
| field_score | total score |
| field_result | pass/fail (`quiz_results` taxonomy or status) |
| field_start_time, field_end_time | attempt timing |
| field_responses | **multi-ref → lms_question_response** |
| created | timestamp |

## Relationships
- N→1 `lms_quiz`, N→1 `user`; 1→N `lms_question_response`.
- A passing result feeds `QuizPassedEvent` → `lms_user_certificate` (WF-003) and may reference from `lms_user_certificate.field_quiz_result`.

## Editorial/data management
Created by the engine on submit; not editor-authored. Read on learner dashboard + analytics. See [WF-002](../C12-workflows/WF-002-quiz-attempt-and-grading.md).
