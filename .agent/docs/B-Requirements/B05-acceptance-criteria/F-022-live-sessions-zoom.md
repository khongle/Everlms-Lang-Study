# F-022 Acceptance Criteria - Live sessions (Zoom)

> ⚠️ As-built depth partial; confirm in E06.

## Core criteria
1. **Given** an instructor, **when** they schedule a Zoom session for a course, **then** a meeting is created via INT-004 and a join link is stored.
2. **Given** a scheduled session, **when** an enrolled learner views the course, **then** they see the upcoming session and a join link.
3. **Given** Zoom API failure, **when** scheduling, **then** the error is surfaced and no broken session is shown.

## Evidence expected
- Module: [lms_zoom](../../C-Design/C05-modules/lms_zoom.md); contract [INT-004](../../C-Design/C06-apis/INT-004-zoom.md).
- Test: schedule-meeting test with a stubbed Zoom client; access-to-link test (E03).
