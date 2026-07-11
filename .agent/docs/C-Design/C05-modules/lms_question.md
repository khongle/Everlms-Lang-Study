# C05 Module - lms_question

**Name:** Questions (8 types) · **Phase:** 4 · **Epic:** [E04](../../D-Development/D02-epics/E04-assessment.md) · **Status:** done

## Responsibility
Sở hữu question bank: entity `lms_question` có thể bundle (8 bundle `lms_question_type`) cùng sub-entity answer (`lms_answer`, `lms_matching_pair`). Định nghĩa một question là gì theo từng type; việc grading nằm ở lms_quiz.

## Owned / related entities
- [lms_question](../C04-entities/lms_question.md) · [lms_question_type](../C04-entities/lms_question_type.md) · [lms_answer](../C04-entities/lms_answer.md) · [lms_matching_pair](../C04-entities/lms_matching_pair.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-006](../../B-Requirements/B03-features/F-006-quiz-and-assessment-engine.md) | Quiz & assessment engine |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-002](../../B-Requirements/B04-business-workflows/WF-002-quiz-attempt-and-grading.md) | Quiz attempt & grading |

## Integrations / APIs (B06 / C06)
- —

## Backing pages
| Label | URL |
| ----- | --- |
| Question bank | `/manage/questions` |
| Question edit (per type) | `/question/{question}/edit` |

## Cross references
- Entity [C04](../C04-entities/lms_question.md); được [lms_quiz](lms_quiz.md) sử dụng.

## Dependencies
- lms_base.

## Implementation notes
- 8 config bundle: single_choice, multiple_choice, true_false, short_answer, essay, fill_blank, matching, ordering. Field answer riêng theo từng type; sub-entity choice/correct được tạo inline.
