---
story_id: E04S01
story_type: module-data
primary_source: C05-modules/lms_question.md
owning_modules: [lms_question]
adequacy: PASS
---

# E04S01 - lms_question build spine

Entity Question dạng bundleable (8 type) + sub-entity answer. **Status:** done.

## Tasks
| Task | Status |
| ---- | ------ |
| Question entity + 8 type bundles | resolved |
| Answer/matching-pair sub-entities | resolved |

## Links
[C05](../../C-Design/C05-modules/lms_question.md) · [C04](../../C-Design/C04-entities/lms_question.md) · [F-006](../../B-Requirements/B03-features/F-006-quiz-and-assessment-engine.md) · [D07](../D07-code-map/lms_question.md)

## Source Trace
- **Module:** `lms_question` — [C05](../../C-Design/C05-modules/lms_question.md) · [D07](../D07-code-map/lms_question.md)
- **Entity:** [lms_question](../../C-Design/C04-entities/lms_question.md), [lms_question_type](../../C-Design/C04-entities/lms_question_type.md) (config), [lms_answer](../../C-Design/C04-entities/lms_answer.md), [lms_matching_pair](../../C-Design/C04-entities/lms_matching_pair.md)
- **Feature:** [F-006](../../B-Requirements/B03-features/F-006-quiz-and-assessment-engine.md) quiz-and-assessment-engine
- **Workflow:** n/a

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E04S01T01` Question entity + 8 type bundles | resolved |
| `E04S01T02` Answer/matching-pair sub-entities | resolved |

## Adequacy Checklist
- [x] Entity: `lms_question` (bundleable, 8 type) + `lms_answer`/`lms_matching_pair` (C04)
- [x] Route: authoring route qua lms_course/quiz admin (C05/D07)
- [x] Access/permission: kế thừa quyền authoring instructor/admin
- [ ] Test: chưa có automated test coverage riêng — gap chung R-006/X01
