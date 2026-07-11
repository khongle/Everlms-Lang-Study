# C05 Module - lms_zoom

**Name:** Live sessions (Zoom) · **Phase:** 8 · **Epic:** [E08](../../D-Development/D02-epics/E08-community.md) · **Status:** in-progress

## Responsibility
Tích hợp Zoom cho live session gắn với lesson/course: tạo meeting, hiển thị link join. ~3 route.

## Owned / related entities
- Không có (dùng field zoom của `lms_lesson`: `field_zoom_meeting_id`, `field_zoom_schedule`).

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-022](../../B-Requirements/B03-features/F-022-live-sessions-zoom.md) | Live sessions (Zoom) |

## Business workflows (B04)
- —

## Integrations / APIs (B06 / C06)
- [INT-004 Zoom](../C06-apis/INT-004-zoom.md)

## Backing pages
| Label | URL |
| ----- | --- |
| Live session / join | `/zoom/*` |

## Cross references
- Field lesson ở [lms_lesson](lms_lesson.md); contract [INT-004](../C06-apis/INT-004-zoom.md).

## Dependencies
- lms_base, lms_lesson, lms_ai.

## Implementation notes
- Credential Zoom nằm trong `lms_zoom.settings.yml` (R-001). ⚠️ Độ sâu as-built còn một phần — scheduling/link đã có; attendance/recording có khả năng còn hạn chế (xác nhận ở E06). Phụ thuộc lms_ai (có thể để tóm tắt session — cần verify).
