# F-022 Acceptance Criteria - Live sessions (Zoom)

> ⚠️ As-built depth chỉ partial; confirm trong E06.

## Core criteria
1. **Given** một instructor, **when** họ schedule một Zoom session cho một course, **then** một meeting được tạo qua INT-004 và một join link được lưu lại.
2. **Given** một session đã được schedule, **when** một learner đã enroll xem course, **then** họ thấy session sắp diễn ra và một join link.
3. **Given** Zoom API lỗi, **when** đang schedule, **then** lỗi được surface và không có session bị lỗi nào được hiển thị.

## Evidence expected
- Module: [lms_zoom](../../C-Design/C05-modules/lms_zoom.md); contract [INT-004](../../C-Design/C06-apis/INT-004-zoom.md).
- Test: schedule-meeting test với Zoom client được stub; access-to-link test (E03).
