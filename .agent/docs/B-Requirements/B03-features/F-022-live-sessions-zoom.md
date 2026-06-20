# F-022 - Live sessions (Zoom)

**Status:** partial
**Primary module:** [lms_zoom](../../C-Design/C05-modules/lms_zoom.md)
**Epic:** [E08 Community](../../D-Development/D02-epics/E08-community.md)
**Related workflows:** —
**Acceptance:** [B05 F-022](../B05-acceptance-criteria/F-022-live-sessions-zoom.md)
**Source:** `web/modules/custom/lms_zoom/`

## Requirement
Instructors schedule live **Zoom** sessions tied to a course; learners see upcoming sessions and a join link. Integration with the Zoom API (INT-004) creates meetings and surfaces join URLs.

## Business value
Live sessions blend self-paced and instructor-led delivery — valuable for cohort-based or compliance training requiring live attendance.

## Scope notes
- `lms_zoom` (3 routes, 3 deps) integrates with Zoom API (INT-004).
- ⚠️ As-built depth partial — scheduling/links may exist; attendance tracking/recording likely limited (confirm in E06).
- Zoom credentials are SENSITIVE (R-001 pattern).

## Cross references
- Design: [C05 lms_zoom](../../C-Design/C05-modules/lms_zoom.md), [C06 INT-004 Zoom](../../C-Design/C06-apis/INT-004-zoom.md)
