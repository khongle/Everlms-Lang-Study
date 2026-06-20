# D07 Code Map - lms_quiz

`web/modules/custom/lms_quiz/`

| Area | Location |
| ---- | -------- |
| Entities | `src/Entity/{Quiz,QuizResult,QuestionResponse}.php` |
| Grading | **`src/Grading/GradingStrategyFactory.php`** + `QuestionGradingInterface` (strategy per type) |
| Event | `QuizPassedEvent` (→ lms_certificate subscriber) |
| Routes | `/quiz/{quiz}/take`, `/quiz/{quiz}/submit`, `/quiz-result/{r}`, `/my-quiz-results` |

Story: [E04S02](../D03-stories/E04S02-lms_quiz.md) · Entity: [C04](../../C-Design/C04-entities/lms_quiz.md) · Module: [C05](../../C-Design/C05-modules/lms_quiz.md)
