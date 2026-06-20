# C05 Module - lms_quiz

**Name:** Quizzes & grading · **Phase:** 4 · **Epic:** [E04](../../D-Development/D02-epics/E04-assessment.md) · **Status:** done

## Responsibility
Owns quiz assembly, attempts, and **automated grading**. Hosts `GradingStrategyFactory` (one `QuestionGradingInterface` strategy per question type) and the result entities. Emits `QuizPassedEvent` on pass (consumed by lms_certificate).

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
- Quiz attempt/results available via mobile API.

## Backing pages
| Label | URL |
| ----- | --- |
| Take quiz | `/quiz/{quiz}/take` |
| Quiz result | `/quiz-result/{result}` |
| Quiz manage | `/quiz/{quiz}/edit` |

## Cross references
- Questions [lms_question](lms_question.md); certificate trigger [lms_certificate](lms_certificate.md); [WF-002](../C12-workflows/WF-002-quiz-attempt-and-grading.md).

## Dependencies
- lms_base, lms_question.

## Implementation notes
- **`src/Grading/GradingStrategyFactory.php`** + `QuestionGradingInterface`: a strategy per question type (8). Static factory selects strategy by bundle; objective types auto-graded, essay/short_answer → `field_pending_review` for manual grading. Pass vs `field_threshold` → `QuizPassedEvent`.
