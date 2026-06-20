---
id: INT-003
name: Zoom Video Conferencing
type: REST
direction: Bidirectional
status: LIVE
---

# INT-003 — Zoom Video Conferencing

**Owner:** `lms_zoom` module
**Documentation:** https://developers.zoom.us/docs/api/

---

## Purpose

Creates and manages Zoom meetings for Lessons of type `zoom`. Handles the full meeting lifecycle: create on lesson save, delete on lesson delete, join via Web SDK in-browser, and AI-powered meeting summary via recordings. One Zoom meeting = one Zoom-type lesson.

**Implementation status:** as-built. The full API layer, meeting lifecycle management, Web SDK signature generation, and AI agenda generation are real working code. One minor stub: `processSummaries()` uses a hardcoded simulated string instead of a real VTT HTTP download.

---

## Connection

| Property | Value |
|----------|-------|
| Endpoint (API) | `https://api.zoom.us/v2` |
| Protocol | HTTPS/REST |
| Authentication | Server-to-Server OAuth (Account Credentials grant) |
| Token Endpoint | `https://zoom.us/oauth/token` |
| Client-side SDK | Zoom Web SDK JS (loaded via `lms_zoom/websdk` library) |
| Rate Limit | ~10 create meetings/s (Zoom default; varies by plan) |

### Credentials (config keys)

Stored in `lms_zoom.settings` config YAML (`config/sync/lms_zoom.settings.yml`):

| Config key | Description | Status |
|------------|-------------|--------|
| `account_id` | Zoom Account ID (for token exchange) | «secret in config — see security risk» |
| `client_id` | OAuth App Client ID | «secret in config — see security risk» |
| `client_secret` | OAuth App Client Secret | «secret in config — see security risk» |
| `sdk_key` | Web SDK App Key (client-side) | «secret in config — see security risk» |
| `sdk_secret` | Web SDK App Secret (HS256 JWT signing) | «secret in config — see security risk» |

**SECURITY RISK:** All five Zoom credentials are stored in plaintext in the committed config YAML. See `config-inventory.md` R4.

---

## SLA

| Metric | Target | Measurement |
|--------|--------|------------|
| Latency (p95) | < 3000ms (meeting create) | APM monitoring |
| Availability | Zoom SLA | Health check |
| Throughput | ~10 creates/s | Zoom rate limit |
| Error Rate | < 1% | Error logging |

---

## Authentication Flow

**Server-to-Server OAuth (Account Credentials grant):**

1. `POST https://zoom.us/oauth/token?grant_type=account_credentials&account_id={account_id}` with `Authorization: Basic base64(client_id:client_secret)`.
2. Response: `access_token` (valid ~1 hour).
3. Token cached in Drupal State (`lms_zoom_access_token`, `lms_zoom_token_expires`). Refreshed automatically when expired before each API call.
4. All subsequent API calls include `Authorization: Bearer {access_token}`.

---

## Request/Response Contract

### Create Meeting

**Endpoint:** `POST https://api.zoom.us/v2/users/{userId}/meetings`

`userId` = instructor email (resolved from Lesson → Chapter → Course → `field_instructor`) or `'me'` as fallback.

**Request:**

```json
{
  "topic": "string — lesson name",
  "type": 2,
  "start_time": "string — ISO 8601 UTC, e.g. '2026-06-20T10:00:00Z'",
  "duration": "integer — minutes",
  "timezone": "string — e.g. 'Asia/Bangkok'",
  "settings": {
    "host_video": true,
    "participant_video": true,
    "join_before_host": false
  }
}
```

**Response (success — 201):**

```json
{
  "id": "integer — Zoom meeting ID (stored in lesson field_zoom_meeting_id)",
  "join_url": "string — student join URL (field_zoom_join_url)",
  "start_url": "string — host start URL (field_zoom_start_url)",
  "password": "string — meeting password (field_zoom_password)",
  "topic": "string",
  "start_time": "string",
  "duration": "integer"
}
```

**Response (error):**

```json
{
  "code": "integer — Zoom error code",
  "message": "string"
}
```

### Get Meeting

**Endpoint:** `GET https://api.zoom.us/v2/meetings/{meetingId}`

**Response (success):** Same shape as create response above.

### Delete Meeting

**Endpoint:** `DELETE https://api.zoom.us/v2/meetings/{meetingId}`

**Response:** 204 No Content on success.

### Get Recordings

**Endpoint:** `GET https://api.zoom.us/v2/meetings/{meetingId}/recordings`

**Response (success):**

```json
{
  "meeting_id": "string",
  "recording_files": [
    {
      "file_type": "string — e.g. 'TRANSCRIPT', 'TIMELINE', 'MP4'",
      "download_url": "string — URL to download the file",
      "status": "string — 'completed'"
    }
  ]
}
```

### Get ZAK Token (host start)

**Endpoint:** `GET https://api.zoom.us/v2/users/{userId}/token?type=zak`

**Response (success):**

```json
{
  "token": "string — ZAK token for Web SDK host start"
}
```

### Web SDK Signature

**Internal endpoint:** `POST /api/lms/websdk/signature` (route `lms_zoom.generate_signature`, requires `_user_is_logged_in: TRUE`)

Generates an HS256 JWT using `firebase/php-jwt` library with `sdk_key` and `sdk_secret`.

**Request body:**

```json
{
  "meeting_id": "string — Zoom meeting ID",
  "role": "integer — 0 (attendee) or 1 (host)"
}
```

**Response (success):**

```json
{
  "signature": "string — HS256 JWT for Zoom Web SDK initialization",
  "zak": "string — ZAK token (only if role=1/host)",
  "sdk_key": "string — for Web SDK init"
}
```

**Known bug:** `generateSignature()` in `ZoomController` references a wrong service ID when resolving the ZoomService — this may cause a runtime error. Verify before production use.

---

## Lesson Data Stored

| Field | Content |
|-------|---------|
| `field_zoom_meeting_id` | Zoom meeting ID (integer as string) |
| `field_zoom_join_url` | Student join URL |
| `field_zoom_start_url` | Host start URL (instructor only) |
| `field_zoom_password` | Meeting password |

These fields are populated by `ZoomMeetingManager::syncMeeting()` triggered from `hook_entity_presave()` when a Lesson with `field_type = 'zoom'` is saved.

---

## Meeting Lifecycle Hooks

| Event | Trigger | Action |
|-------|---------|--------|
| Lesson presave (type=zoom) | `hook_entity_presave` | `ZoomMeetingManager::syncMeeting()` — creates or updates Zoom meeting |
| Lesson delete (type=zoom) | `hook_entity_delete` | `ZoomMeetingManager::deleteMeeting()` — deletes Zoom meeting |
| Cron | `hook_cron` | `ZoomAiService::processSummaries()` — fetches recordings and generates AI summaries for completed meetings (VTT download is stub) |

---

## AI Agenda Generation (lms_zoom + lms_ai)

`ZoomAiService::generateAgenda(string $topic)` calls the OpenAI provider (`createInstance('openai')`) to generate an HTML agenda. Surfaced via AJAX button "Generate Zoom Agenda with AI" in the lesson edit form (`hook_form_lms_lesson_form_alter`).

---

## Retry Policy

| Property | Value |
|----------|-------|
| Max Retries | None explicit (Guzzle client default) |
| Backoff | N/A |
| Initial Delay | N/A |
| Max Delay | N/A |
| Timeout | Guzzle default (~30s) |
| Retry On | N/A — errors are caught and logged; lesson saves without meeting link on failure |

---

## Fallback Behavior

| Scenario | Behavior | Duration |
|----------|----------|----------|
| Meeting creation fails | Error logged; lesson saves without `field_zoom_meeting_id`; no alert to instructor | Permanent until instructor re-saves lesson |
| OAuth token expired | Token refreshed automatically from State cache before API call | Transparent |
| Meeting not found on Zoom | `getMeeting()` returns NULL; join page shows degraded state | Until meeting is recreated |
| VTT download in processSummaries | Uses hardcoded simulated transcript string instead of real download (stub) | Bug |

---

## Circuit Breaker

Not implemented.

---

## Data Flow

- **Frequency:** Event-driven (lesson save/delete); cron for AI summaries; real-time for Web SDK signature requests
- **Volume:** One Zoom meeting per Zoom-type lesson; signature request per join
- **Data Sensitivity:** Internal — meeting IDs and join URLs are internal; ZAK token is host-only
- **Encryption:** In-transit: TLS 1.2+

---

## Feature Flag

- **Flag ID:** not required
- **Rationale:** Zoom integration is active whenever `lms_zoom` module is enabled and credentials are configured in `lms_zoom.settings`.

---

## Related Docs

- [B03 — F-001 Course Management](../B03-features/01-course-management.md) — lesson types including zoom
- [B04 — WF-001 Enrollment & Learning](../B04-business-workflows/WF-001-enrollment-learning.md)
- [INT-004 — OpenAI](./INT-004-openai.md) — used by ZoomAiService for agenda and summary generation
- Source: `web/modules/custom/lms_zoom/src/Service/ZoomService.php`
- Source: `web/modules/custom/lms_zoom/src/Service/ZoomMeetingManager.php`
- Source: `web/modules/custom/lms_zoom/src/Controller/ZoomController.php`
