---
story_id: E08S07
story_type: module-foundation
primary_source: C05-modules/lms_zoom.md
owning_modules: [lms_zoom]
adequacy: FAIL
---

# E08S07 - lms_zoom build spine

Live session Zoom. **Status:** in-progress (scheduling/link; attendance mới một phần).

## Tasks
| Task | Status |
| ---- | ------ |
| Zoom meeting create + join links | resolved |
| Attendance/recording | todo |
| Secrets → Key/env | todo (R-001) |

## Links
[C05](../../C-Design/C05-modules/lms_zoom.md) · [C06 INT-004](../../C-Design/C06-apis/INT-004-zoom.md) · [F-022](../../B-Requirements/B03-features/F-022-live-sessions-zoom.md)

## Source Trace
- **Module:** `lms_zoom` — [C05](../../C-Design/C05-modules/lms_zoom.md) · [D07](../D07-code-map/lms_zoom.md)
- **Entity:** n/a — không có entity C04 riêng, lưu session/meeting qua field trên `lms_lesson`/config
- **Feature:** [F-022](../../B-Requirements/B03-features/F-022-live-sessions-zoom.md) live-sessions-zoom
- **Workflow:** [C06 INT-004](../../C-Design/C06-apis/INT-004-zoom.md) tích hợp Zoom

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E08S07T01` Zoom meeting create + join links | resolved |
| `E08S07T02` Attendance/recording | todo |
| `E08S07T03` Secrets → Key/env | todo (R-001) |

## Adequacy Checklist
- [x] Entity: n/a
- [x] Route: create/join meeting route đã resolved
- [ ] Access/permission: attendance tracking mới một phần → chưa xác nhận đủ ai đã tham dự
- [ ] Test: Zoom API key vẫn ở config YAML (R-001); chưa có automated test — R-006/X01
