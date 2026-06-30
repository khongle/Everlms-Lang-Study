---
usecase_id: UC-WF-002-001
workflow: WF-002-quiz-attempt-and-grading
status: as-built
primary_actor: Student
modules: [lms_quiz, lms_question, lms_progress]
processes: [quiz-attempt-and-grading]
---

# UC-WF-002-001 - Take and grade a quiz

## Intent
A learner attempts a quiz and receives a trustworthy result state: auto-graded pass/fail when all items are objective, or pending review when any item requires instructor grading.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-002 |
| Business flow | attempt → grade → result/review → finalized result → (pass) event |
| Modules | lms_quiz, lms_question, lms_progress |
| Related features | F-006 |
| B04 source | WF-002 |

## Actors
Student (primary); instructor (manual grade for essays).

## Preconditions
Enrolled; prerequisites met; attempts remaining.

## Trigger
Learner submits the quiz.

## Main success scenario
1. Open quiz (P020); answer questions (8 types).
2. Submit → `GradingStrategyFactory` grades objective items.
3. `lms_quiz_result` + `lms_question_response` stored; pending-review flags are persisted per response.
4. If no manual review is required, the result is finalized as Passed/Failed against `field_threshold`.
5. If manual review is required, the result remains Pending/In review and the score is displayed as provisional.
6. On finalized pass of a final quiz → `QuizPassedEvent` (WF-003).

## Alternate and exception flows
- Time limit reached → auto-submit.
- Essay → pending manual grade by instructor.
- Fail → retry if attempts remain.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Quiz take | App | /quiz/{quiz}/take |
| Quiz result | App | /quiz-result/{r} |

## Acceptance and test contract
- Per-strategy grading correct; threshold pass/fail only after the result is finalized; pending attempts are not counted as passed; event fired only on finalized pass (B05 F-006).
