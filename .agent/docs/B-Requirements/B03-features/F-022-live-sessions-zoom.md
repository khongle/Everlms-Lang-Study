# F-022 - Live sessions (Zoom)

**Status:** partial
**Primary module:** [lms_zoom](../../C-Design/C05-modules/lms_zoom.md)
**Epic:** [E08 Community](../../D-Development/D02-epics/E08-community.md)
**Related workflows:** —
**Acceptance:** [B05 F-022](../B05-acceptance-criteria/F-022-live-sessions-zoom.md)
**Source:** `web/modules/custom/lms_zoom/`

## Requirement
Instructor lên lịch (schedule) session **Zoom** trực tiếp gắn với một course; learner thấy các session sắp tới và join link. Tích hợp với Zoom API (INT-004) tạo meeting và hiển thị join URL.

## Business value
Live session kết hợp giữa self-paced và instructor-led delivery — có giá trị cho đào tạo theo cohort hoặc đào tạo compliance yêu cầu tham dự trực tiếp.

## Scope notes
- `lms_zoom` (3 route, 3 dependency) tích hợp với Zoom API (INT-004).
- ⚠️ Độ sâu as-built còn partial — scheduling/link có thể đã có; attendance tracking/recording nhiều khả năng còn hạn chế (cần xác nhận ở E06).
- Credential của Zoom là SENSITIVE (theo pattern R-001).

## Cross references
- Design: [C05 lms_zoom](../../C-Design/C05-modules/lms_zoom.md), [C06 INT-004 Zoom](../../C-Design/C06-apis/INT-004-zoom.md)
