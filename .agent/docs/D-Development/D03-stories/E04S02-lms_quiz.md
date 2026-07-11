---
story_id: E04S02
story_type: module-data
primary_source: C05-modules/lms_quiz.md
owning_modules: [lms_quiz]
adequacy: PASS
---

# E04S02 - lms_quiz build spine

Lắp ráp Quiz + chấm điểm tự động (GradingStrategyFactory) + result. **Status:** done.

## Tasks
| Task | Status |
| ---- | ------ |
| Quiz entity + attempts | resolved |
| Grading strategies (8) | resolved |
| QuizPassedEvent | resolved |
| Grading unit tests | todo (R-006) |

## Links
[C05](../../C-Design/C05-modules/lms_quiz.md) · [C04](../../C-Design/C04-entities/lms_quiz.md) · [WF-002](../../C-Design/C12-workflows/WF-002-quiz-attempt-and-grading.md) · [D07](../D07-code-map/lms_quiz.md)

## Source Trace
- **Module:** `lms_quiz` — [C05](../../C-Design/C05-modules/lms_quiz.md) · [D07](../D07-code-map/lms_quiz.md)
- **Entity:** [lms_quiz](../../C-Design/C04-entities/lms_quiz.md), [lms_quiz_result](../../C-Design/C04-entities/lms_quiz_result.md), [lms_question_response](../../C-Design/C04-entities/lms_question_response.md)
- **Feature:** [F-006](../../B-Requirements/B03-features/F-006-quiz-and-assessment-engine.md) quiz-and-assessment-engine
- **Workflow:** [WF-002](../../C-Design/C12-workflows/WF-002-quiz-attempt-and-grading.md) quiz-attempt-and-grading

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E04S02T01` Quiz entity + attempts | resolved |
| `E04S02T02` Grading strategies (8, GradingStrategyFactory) | resolved |
| `E04S02T03` QuizPassedEvent | resolved |
| `E04S02T04` Grading unit tests | todo (R-006) |

## Adequacy Checklist
- [x] Entity: `lms_quiz`/`lms_quiz_result`/`lms_question_response` (C04)
- [x] Route: attempt/result route (C05/D07)
- [x] Access/permission: attempt gating theo enrollment đã resolved
- [ ] Test: grading unit test còn `todo` (R-006) — chưa cover 8 grading strategy
