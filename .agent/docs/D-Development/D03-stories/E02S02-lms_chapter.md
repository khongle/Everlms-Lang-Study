---
story_id: E02S02
story_type: module-data
primary_source: C05-modules/lms_chapter.md
owning_modules: [lms_chapter]
adequacy: PASS
---

# E02S02 - lms_chapter build spine

Entity Chapter (tầng giữa course→chapter→lesson). **Status:** done.

## Tasks
| Task | Status |
| ---- | ------ |
| Chapter entity + ref to lessons | resolved |
| Ordering (weight) | resolved |

## Links
[C05](../../C-Design/C05-modules/lms_chapter.md) · [C04](../../C-Design/C04-entities/lms_chapter.md) · [D07](../D07-code-map/lms_chapter.md)

## Source Trace
- **Module:** `lms_chapter` — [C05](../../C-Design/C05-modules/lms_chapter.md) · [D07](../D07-code-map/lms_chapter.md)
- **Entity:** [lms_chapter](../../C-Design/C04-entities/lms_chapter.md)
- **Feature:** thuộc [F-002](../../B-Requirements/B03-features/F-002-course-authoring-and-structure.md) course-authoring-and-structure (tầng course→chapter→lesson)
- **Workflow:** n/a

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E02S02T01` Chapter entity + ref to lessons | resolved |
| `E02S02T02` Ordering (weight) | resolved |

## Adequacy Checklist
- [x] Entity: `lms_chapter` định nghĩa base field + tham chiếu lesson (C04)
- [x] Route: quản lý qua form authoring của lms_course (không route độc lập)
- [x] Access/permission: kế thừa quyền authoring của `lms_course`
- [ ] Test: chưa có automated test coverage riêng — gap chung R-006/X01
