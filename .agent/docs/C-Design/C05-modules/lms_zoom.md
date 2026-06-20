# C05 Module - lms_zoom

**Name:** Live sessions (Zoom) · **Phase:** 8 · **Epic:** [E08](../../D-Development/D02-epics/E08-community.md) · **Status:** in-progress

## Responsibility
Integrates Zoom for live sessions tied to lessons/courses: create meetings, surface join links. ~3 routes.

## Owned / related entities
- None (uses `lms_lesson` zoom fields: `field_zoom_meeting_id`, `field_zoom_schedule`).

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
- Lesson fields [lms_lesson](lms_lesson.md); contract [INT-004](../C06-apis/INT-004-zoom.md).

## Dependencies
- lms_base, lms_lesson, lms_ai.

## Implementation notes
- Zoom creds in `lms_zoom.settings.yml` (R-001). ⚠️ As-built depth partial — scheduling/links present; attendance/recording likely limited (confirm in E06). Depends on lms_ai (likely for session summaries — verify).
