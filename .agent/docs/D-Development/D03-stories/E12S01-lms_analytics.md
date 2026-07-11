---
story_id: E12S01
story_type: module-foundation
primary_source: C05-modules/lms_analytics.md
owning_modules: [lms_analytics]
adequacy: FAIL
---

# E12S01 - lms_analytics build spine

Report/KPI (enrollment/completion/revenue) + CSV. **Status:** in-progress (KPI mới một phần).

## Tasks
| Task | Status |
| ---- | ------ |
| Course/quiz/user reports + export | resolved |
| Complete KPI depth + scoping | todo → [E12S01T01](../D04-tasks/todo/E12S01T01-complete-analytics-kpis.md) |

## Links
[C05](../../C-Design/C05-modules/lms_analytics.md) · [F-020](../../B-Requirements/B03-features/F-020-analytics-and-reporting.md)

## Source Trace
- **Module:** `lms_analytics` — [C05](../../C-Design/C05-modules/lms_analytics.md) · [D07](../D07-code-map/lms_analytics.md)
- **Entity:** n/a — báo cáo đọc (read-only) qua `lms_user_course`/`lms_quiz_result`/`lms_order`, không có entity riêng
- **Feature:** [F-020](../../B-Requirements/B03-features/F-020-analytics-and-reporting.md) analytics-and-reporting
- **Workflow:** n/a

## WBS Tasks
| Task ID | Mô tả | Trạng thái |
| ---- | ----- | ---------- |
| `E12S01T02` | Course/quiz/user reports + CSV export | resolved (as-built, không có D04 card riêng) |
| E12S01T01 | Complete KPI depth + scoping | todo → [task file](../D04-tasks/todo/E12S01T01-complete-analytics-kpis.md) |

## Adequacy Checklist
- [x] Entity: n/a
- [x] Route: report dashboard + export route đã resolved
- [x] Access/permission: chỉ admin/instructor xem report, đã resolved
- [ ] Test: KPI depth mới một phần (in-progress); chưa có automated test — R-006/X01
