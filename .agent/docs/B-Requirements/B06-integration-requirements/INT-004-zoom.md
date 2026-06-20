# INT-004 - Zoom

## Requirement
Create and manage live Zoom meetings tied to courses (F-022): generate meeting + join URL, surface to enrolled learners.

## Participating modules
[lms_zoom](../../C-Design/C05-modules/lms_zoom.md)

## Contract expectations
1. **Auth/authz:** Zoom API credentials (OAuth/JWT) — SENSITIVE (R-001); server-side.
2. **Failure/retry/fallback:** on Zoom API failure, scheduling errors are surfaced; no broken/empty session link shown to learners.
3. **CI test fixture/stub:** mocked Zoom client returning canned meeting payloads; no live Zoom calls in CI.
4. **Operational owner + alerting:** Instructor-experience owner; alert on repeated Zoom API failures. ⚠️ As-built depth partial.

## Design link
[C06 INT-004](../../C-Design/C06-apis/INT-004-zoom.md)
