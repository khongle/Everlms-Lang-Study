# C05 Module - lms_quiz

**Name:** Quizzes & grading · **Phase:** 4 · **Epic:** [E04](../../D-Development/D02-epics/E04-assessment.md) · **Status:** done

## Responsibility
Sở hữu việc lắp ráp (assembly) quiz, attempt, và **automated grading**. Host `GradingStrategyFactory` (một strategy `QuestionGradingInterface` cho mỗi question type) và các entity result. Phát ra `QuizPassedEvent` khi đậu (được lms_certificate sử dụng).

## Owned / related entities
- [lms_quiz](../C04-entities/lms_quiz.md) · [lms_quiz_result](../C04-entities/lms_quiz_result.md) · [lms_question_response](../C04-entities/lms_question_response.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-006](../../B-Requirements/B03-features/F-006-quiz-and-assessment-engine.md) | Quiz & assessment engine |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-002](../../B-Requirements/B04-business-workflows/WF-002-quiz-attempt-and-grading.md) | Quiz attempt & grading |

## Integrations / APIs (B06 / C06)
- Quiz attempt/result có sẵn qua mobile API.

## Backing pages
| Label | URL |
| ----- | --- |
| Take quiz | `/quiz/{quiz}/take` |
| Quiz result | `/quiz-result/{result}` |
| Quiz manage | `/quiz/{quiz}/edit` |

## Cross references
- Question ở [lms_question](lms_question.md); trigger certificate ở [lms_certificate](lms_certificate.md); [WF-002](../C12-workflows/WF-002-quiz-attempt-and-grading.md).

## Dependencies
- lms_base, lms_question.

## Implementation notes
- **`src/Grading/GradingStrategyFactory.php`** + `QuestionGradingInterface`: một strategy cho mỗi question type (8 loại). Static factory chọn strategy theo bundle; type khách quan (objective) được auto-grade, essay/short_answer → `field_pending_review` để grading thủ công. So sánh với `field_threshold` để xác định pass → `QuizPassedEvent`.
