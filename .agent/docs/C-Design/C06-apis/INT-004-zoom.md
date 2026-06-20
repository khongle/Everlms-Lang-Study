# C06 API/Contract - INT-004 Zoom

**Requirement:** [B06 INT-004](../../B-Requirements/B06-integration-requirements/INT-004-zoom.md)
**Participating modules:** [lms_zoom](../C05-modules/lms_zoom.md)

## Purpose
Create Zoom meetings for course live sessions and surface join links to enrolled learners.

## Proposed contract
- **REST namespace:** outbound to Zoom API (`api.zoom.us/v2/...`); internal routes `/zoom/*`.
- **Auth:** Zoom OAuth (server-to-server) / JWT credentials in `lms_zoom.settings.yml` (R-001).
- **Authorization:** instructors/staff may create meetings; enrolled learners may view join links.
- **Response envelope:** `{ data: {meeting_id, join_url, schedule}, error|null, meta: {provider: "zoom"} }`; stored on `lms_lesson.field_zoom_*`.
- **Audit:** log create/update meeting calls; do not expose host keys to learners.

## Test requirements
- CI: mocked Zoom client returning canned meeting payloads.
- Auth+authz: only authorised creators; learners read-only.
- Failure/fallback: Zoom API error → surfaced, no broken link shown.
- Contract test: create-meeting + access-to-link by role.
