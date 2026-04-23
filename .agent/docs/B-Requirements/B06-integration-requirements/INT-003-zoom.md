# INT-003 — Zoom Video Conferencing

**ID:** INT-003
**Integration:** Zoom
**Owner Module:** `lms_zoom`
**Direction:** Outbound (API calls)
**Protocol:** REST API + OAuth 2.0
**Status:** API stub implemented

---

## Scope

Tạo và quản lý Zoom meetings cho live lesson type. Students nhận join link khi lesson được schedule.

## Connection

| Property | Value |
|----------|-------|
| API Base URL | `https://api.zoom.us/v2` |
| Auth | OAuth 2.0 (Server-to-Server OAuth app) |
| Token Endpoint | `https://zoom.us/oauth/token` |
| Protocol | HTTPS REST |

## Data Exchanged

| Direction | Data |
|-----------|------|
| Outbound | Create Meeting: topic, start_time, duration, settings (host video, participant video) |
| Inbound | Meeting object: id, join_url, start_url, password |
| Outbound | List/Update/Delete Meeting |
| Inbound | Webhook (optional): meeting.started, meeting.ended, participant.joined |

## Authentication

- **Server-to-Server OAuth**: Account ID + Client ID + Client Secret.
- Token refresh: access token expires 1 hour; refresh automatically.
- Credentials stored as Drupal config/key entity; không hardcode.

## Rate Limits & Quotas

| Limit | Value |
|-------|-------|
| API rate limit | 10 create meetings/s (Zoom default) |
| Daily meeting limit | Per Zoom account plan |

## Failure Modes

| Scenario | Behavior |
|----------|----------|
| Meeting creation fails | Lesson saves without meeting link; instructor alerted |
| Meeting deleted on Zoom side | Lesson shows "Meeting cancelled"; admin notified |
| Join link expired | Student clicks link → Zoom shows expired; lesson marked for review |
| Zoom API rate limit | Retry với backoff |

## Data Stored in Lesson

| Field | Value |
|-------|-------|
| `field_zoom_meeting_id` | Zoom meeting ID |
| `field_zoom_join_url` | Student join URL |
| `field_zoom_start_url` | Host start URL (instructor only) |
| `field_zoom_password` | Meeting password |

## Related Docs

- [WF-001 — Enrollment & Learning](../B04-business-workflows/WF-001-enrollment-learning.md) — Zoom lesson type
- [AC-H03](../B05-acceptance-criteria/AC-10-integrations.md) — Zoom AC
- B03: [F-001 Course Management](../B03-features/01-course-management.md) — lesson types
