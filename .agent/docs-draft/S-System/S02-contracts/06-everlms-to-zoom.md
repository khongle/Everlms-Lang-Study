# Contract: EverLMS → Zoom API

**Caller:** `everlms-web` (module: `lms_zoom`)
**Callee:** Zoom REST API v2 + Zoom Web SDK (client-side)
**Protocol:** HTTPS REST (server-to-server) + JWT embedded in browser (client SDK)
**Auth:** Server-to-Server OAuth2 (account_credentials grant) for API calls; JWT HS256 for Web SDK signature
**Handler classes:**
- `\Drupal\lms_zoom\Service\ZoomService` — API calls
- `\Drupal\lms_zoom\Controller\ZoomController` — Web SDK signature generation
**Status:** ACTIVE

---

## Endpoints Used

| Zoom Endpoint | Method | Purpose | Implemented? |
|--------------|--------|---------|--------------|
| `https://zoom.us/oauth/token?grant_type=account_credentials&account_id={id}` | POST | Obtain Bearer access token | Yes |
| `https://api.zoom.us/v2/users/{userId}/meetings` | POST | Create a scheduled meeting | Yes |
| `https://api.zoom.us/v2/meetings/{meetingId}` | GET | Get meeting details (topic, password, host_email) | Yes |
| `https://api.zoom.us/v2/meetings/{meetingId}` | DELETE | Delete a meeting | Yes |
| `https://api.zoom.us/v2/meetings/{meetingId}/recordings` | GET | Get cloud recordings | Yes |
| `https://api.zoom.us/v2/users/{userId}/token?type=zak` | GET | Get ZAK token for host Web SDK start | Yes |

---

## Auth — Server-to-Server OAuth2

Token is obtained with HTTP Basic Auth (`clientId:clientSecret`) and cached in Drupal State API:

```
POST https://zoom.us/oauth/token?grant_type=account_credentials&account_id={accountId}
Authorization: Basic base64({clientId}:{clientSecret})
```

**Token cache:** Stored in Drupal State (`lms_zoom_access_token`, `lms_zoom_token_expires`).
Cache expires 60 seconds before actual expiry as buffer.
All subsequent calls use: `Authorization: Bearer {access_token}`.

**Config path:** `lms_zoom.settings` → `account_id`, `client_id`, `client_secret`
**Admin UI:** `/admin/config/everlms/zoom` (ZoomSettingsForm)

---

## Auth — Web SDK Signature (JWT HS256)

The browser-embedded Zoom Web SDK requires a signed JWT to join or host a meeting:

```php
$token_payload = [
  'sdkKey'    => $sdk_key,
  'mn'        => $meeting_number,
  'role'      => 0 | 1,   // 0 = attendee, 1 = host/instructor
  'iat'       => time() - 30,
  'exp'       => time() + 7200,  // 2-hour validity
  'appKey'    => $sdk_key,       // backward-compat field
  'tokenExp'  => $exp
];
// Signed with HS256 using $sdk_secret
```

The signature is generated server-side at `POST /zoom/signature` (ZoomController::generateSignature).
Role elevation to `1` (host) is granted automatically if the logged-in user is the course instructor.

**SDK credentials:** `lms_zoom.settings` → `sdk_key`, `sdk_secret` (separate from API credentials)

---

## Create Meeting — Payload

```json
{
  "topic": "{lesson title}",
  "type": 2,
  "start_time": "2024-03-24T18:00:00Z",
  "duration": 60,
  "timezone": "UTC",
  "settings": {
    "host_video": true,
    "participant_video": true,
    "join_before_host": false,
    "mute_upon_entry": true,
    "waiting_room": true,
    "approval_type": 0
  }
}
```

Meeting `id` is stored in `field_zoom_meeting_id` on the `lms_lesson` entity.

---

## Retry / Timeout

| Setting | Value |
|---------|-------|
| HTTP timeout | Not explicitly set (Guzzle default: 0 = no timeout) |
| Retry on 5xx | Not implemented (single attempt) |
| Token refresh | Automatic on expiry (cached) |

> **Gap:** No explicit HTTP timeout is set for Zoom API calls. A slow or hanging Zoom API response
> could tie up a PHP-FPM worker indefinitely. Recommend setting `timeout: 15` on all Zoom requests.

---

## Rate Limits

Zoom REST API rate limits (per account):
- Standard: 30 requests/second per token (varies by endpoint)
- Meeting creation: 100 meetings/day per user (Pro plan)
- No rate-limit handling is implemented in EverLMS — 429 responses are treated as generic errors.

---

## Failure Mode

| Scenario | Behaviour |
|----------|-----------|
| Token fetch fails | `getAccessToken()` returns `null`; all API calls skip silently |
| `createMeeting()` fails for instructor | Falls back to admin account (`me`) automatically |
| `getMeeting()` fails | `ZoomController::joinMeeting()` throws generic `\Exception` |
| SDK credential missing | `generateSignature()` returns HTTP 500 JSON error |

**Criticality:** Tier 2 — Zoom-type lessons are unavailable when the Zoom API is down.
Non-Zoom lessons are unaffected.
