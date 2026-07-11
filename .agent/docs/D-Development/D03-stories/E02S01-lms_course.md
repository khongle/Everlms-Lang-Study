---
story_id: E02S01
story_type: module-data
primary_source: C05-modules/lms_course.md
owning_modules: [lms_course]
adequacy: PASS
---

# E02S01 - lms_course build spine

Entity Course, catalog, trang detail, authoring (~37 route). **Status:** done.

## Tasks
| Task | Status |
| ---- | ------ |
| Course entity + fields | resolved |
| Catalog + filters + detail | resolved |
| Authoring forms | resolved |

## Links
[C05](../../C-Design/C05-modules/lms_course.md) · [C04](../../C-Design/C04-entities/lms_course.md) · [F-001](../../B-Requirements/B03-features/F-001-course-catalog-and-discovery.md)/[F-002](../../B-Requirements/B03-features/F-002-course-authoring-and-structure.md) · [D07](../D07-code-map/lms_course.md)

## Source Trace
- **Module:** `lms_course` — [C05](../../C-Design/C05-modules/lms_course.md) · [D07](../D07-code-map/lms_course.md)
- **Entity:** [lms_course](../../C-Design/C04-entities/lms_course.md)
- **Feature:** [F-001](../../B-Requirements/B03-features/F-001-course-catalog-and-discovery.md) course-catalog-and-discovery, [F-002](../../B-Requirements/B03-features/F-002-course-authoring-and-structure.md) course-authoring-and-structure
- **Workflow:** n/a — không có WF-xxx riêng cho course CRUD

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E02S01T01` Course entity + fields | resolved |
| `E02S01T02` Catalog + filters + detail | resolved |
| `E02S01T03` Authoring forms | resolved |

## Adequacy Checklist
- [x] Entity: `lms_course` định nghĩa đầy đủ base field (C04)
- [x] Route: ~37 route catalog/detail/authoring (C05/D07)
- [x] Access/permission: permission theo role (instructor/admin) đã resolved
- [ ] Test: chưa có automated test coverage riêng — gap chung R-006/X01
