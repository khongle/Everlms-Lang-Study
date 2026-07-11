---
story_id: E06S05
story_type: module-data
primary_source: C05-modules/lms_bundle.md
owning_modules: [lms_bundle]
adequacy: PASS
---

# E06S05 - lms_bundle build spine

Entity course bundle; mua sẽ fan-out ra thành enrollment. **Status:** done.

## Tasks
| Task | Status |
| ---- | ------ |
| Bundle entity + pricing | resolved |
| Purchase fan-out | resolved |

## Links
[C05](../../C-Design/C05-modules/lms_bundle.md) · [F-009](../../B-Requirements/B03-features/F-009-course-bundles.md)

## Source Trace
- **Module:** `lms_bundle` — [C05](../../C-Design/C05-modules/lms_bundle.md) · [D07](../D07-code-map/lms_bundle.md)
- **Entity:** [lms_course_bundle](../../C-Design/C04-entities/lms_course_bundle.md)
- **Feature:** [F-009](../../B-Requirements/B03-features/F-009-course-bundles.md) course-bundles
- **Workflow:** n/a (mua bundle fan-out ra `lms_order_item`/entitlement, xem WF-004)

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E06S05T01` Bundle entity + pricing | resolved |
| `E06S05T02` Purchase fan-out (bundle → enrollment nhiều course) | resolved |

## Adequacy Checklist
- [x] Entity: `lms_course_bundle` (C04)
- [x] Route: catalog/detail bundle route (C05/D07)
- [x] Access/permission: entitlement fan-out theo state paid, đã resolved
- [ ] Test: chưa có automated test coverage riêng — gap chung R-006/X01
