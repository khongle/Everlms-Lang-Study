---
story_id: E05S02
story_type: module-data
primary_source: C05-modules/lms_badge.md
owning_modules: [lms_badge]
adequacy: FAIL
---

# E05S02 - lms_badge build spine

Badge definition + instance đã awarded. **Status:** blocked (thiếu award logic — BLK-003/R-003).

## Tasks
| Task | Status |
| ---- | ------ |
| Badge + user_badge entities | resolved |
| Admin define badges | resolved |
| Award trigger/evaluator | blocked → [E05S02T01](../D04-tasks/todo/E05S02T01-implement-badge-award-subscriber.md) |

## Links
[C05](../../C-Design/C05-modules/lms_badge.md) · [F-008](../../B-Requirements/B03-features/F-008-badges-and-gamification.md) · [G02 R-003](../../G-Governance/G02-risk-register/_index.md)

## Source Trace
- **Module:** `lms_badge` — [C05](../../C-Design/C05-modules/lms_badge.md) · [D07](../D07-code-map/lms_badge.md)
- **Entity:** [lms_badge](../../C-Design/C04-entities/lms_badge.md), [lms_user_badge](../../C-Design/C04-entities/lms_user_badge.md)
- **Feature:** [F-008](../../B-Requirements/B03-features/F-008-badges-and-gamification.md) badges-and-gamification
- **Workflow:** n/a (award trigger dự kiến ăn theo QuizPassedEvent, xem WF-002/WF-003)

## WBS Tasks
| Task ID | Mô tả | Trạng thái |
| ---- | ----- | ---------- |
| `E05S02T02` | Badge + user_badge entities | resolved (as-built, không có D04 card riêng) |
| `E05S02T03` | Admin define badges | resolved (as-built, không có D04 card riêng) |
| E05S02T01 | Award trigger/evaluator | blocked → [task file](../D04-tasks/todo/E05S02T01-implement-badge-award-subscriber.md) |

## Adequacy Checklist
- [x] Entity: `lms_badge` + `lms_user_badge` (C04)
- [x] Route: admin define-badge route đã resolved
- [ ] Access/permission: award trigger chưa hoạt động → F-008 non-functional (R-003)
- [ ] Test: chưa có automated test coverage riêng — gap chung R-006/X01
