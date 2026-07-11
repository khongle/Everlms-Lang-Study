# C13 Usecases - WF-002 Quiz attempt & grading

## Workflow
- C12: [WF-002](../../C12-workflows/WF-002-quiz-attempt-and-grading.md) · B04: [WF-002](../../../B-Requirements/B04-business-workflows/WF-002-quiz-attempt-and-grading.md)
- Modules: lms_quiz, lms_question
- Business flow: attempt → auto-grade → result hoặc manual review → finalized result → event (pass).

## Usecase index
| Usecase | Title | Primary actor | Process | C11 pages |
| ------- | ----- | ------------- | ------- | --------- |
| [UC-WF-002-001](UC-WF-002-001-take-and-grade-quiz.md) | Take and grade a quiz | Student | Quiz attempt & grading | P020, P021 |

## Completion rule
Đóng khi learner submit một quiz và nhận được hoặc một finalized pass/fail result cho quiz chỉ có câu hỏi objective, hoặc một pending-review result được đánh dấu rõ ràng cho quiz có phần chấm tay (manual grading).
