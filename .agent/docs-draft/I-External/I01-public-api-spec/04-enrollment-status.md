# Enrollment Status Endpoint

**Status: PROPOSED** — Endpoint chưa được implement.
EverLMS hiện không expose JSON API cho enrollment data. Enrollment hiện chỉ accessible qua server-rendered Drupal pages.

**Spec format:** REST / JSON
**Owner:** hainn (lms_enrollment module)
**Stability:** Alpha (proposed)
**Lifecycle:** Not yet introduced

---

## Summary

Endpoint trả về danh sách tất cả enrollments của authenticated user, bao gồm progress và completion status.
Dùng để headless frontend, mobile app, hoặc external dashboard hiển thị learning history.

**Authentication is required.** Unauthenticated requests return 401.

---

## Endpoint

**Status: PROPOSED**

```
GET /api/v1/users/me/enrollments
```

**Authentication:** Required — Bearer token (OAuth2 / Drupal session token).

| Header | Value |
|--------|-------|
| `Authorization` | `Bearer {access_token}` |

**Rate limit:** 60 requests/minute per authenticated user.

---

## Query Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `status` | string | No | Filter by status: `active`, `completed`, `expired` | `?status=completed` |
| `page` | integer | No | Page number, 1-indexed (default: 1) | `?page=2` |
| `per_page` | integer | No | Results per page, max 50 (default: 20) | `?per_page=10` |

---

## Success Response — 200 OK

```json
{
  "data": [
    {
      "enrollment_id": 501,
      "status": "active",
      "enrolled_at": "2025-10-01T09:00:00Z",
      "expires_at": null,
      "progress_percent": 65,
      "last_accessed_at": "2026-04-10T14:30:00Z",
      "course": {
        "id": 42,
        "slug": "advanced-javascript",
        "title": "Advanced JavaScript",
        "thumbnail_url": "https://everlms.example.com/files/courses/js-thumb.jpg"
      },
      "certificate": null
    },
    {
      "enrollment_id": 488,
      "status": "completed",
      "enrolled_at": "2025-07-15T08:00:00Z",
      "expires_at": null,
      "progress_percent": 100,
      "last_accessed_at": "2025-09-20T11:15:00Z",
      "course": {
        "id": 38,
        "slug": "intro-to-python",
        "title": "Introduction to Python",
        "thumbnail_url": "https://everlms.example.com/files/courses/python-thumb.jpg"
      },
      "certificate": {
        "code": "X9Y8Z7W6V5",
        "issued_at": "2025-09-20T11:20:00Z",
        "verify_url": "/api/v1/certificate/verify/X9Y8Z7W6V5"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total_items": 2,
    "total_pages": 1
  },
  "links": {
    "self": "/api/v1/users/me/enrollments?page=1",
    "next": null,
    "prev": null
  }
}
```

---

## Enrollment Status Values

| Status | Meaning |
|--------|---------|
| `active` | Enrolled and within access window; in progress |
| `completed` | Finished all required lessons/quizzes |
| `expired` | Access period ended without completion |

---

## Error Responses

| HTTP | Error code | Meaning | Retry? |
|------|-----------|---------|--------|
| 401 | `ENROLL-401` | Not authenticated (missing or invalid token) | No — re-authenticate |
| 403 | `ENROLL-403` | Token valid but user lacks permission (e.g. banned) | No |
| 429 | `ENROLL-429` | Rate limited | Yes, with backoff |
| 500 | `ENROLL-500` | Internal error | Yes, once |

```json
{
  "error": {
    "code": "ENROLL-401",
    "message": "Authentication required. Provide a valid Bearer token."
  }
}
```

---

## Token Acquisition (PROPOSED)

No dedicated token endpoint is specified yet. Options:
1. **Drupal simple_oauth module** — OAuth2 PKCE flow (recommended for headless frontend).
2. **Drupal JSON:API + Basic Auth** — simpler but less secure for production.

Token endpoint would be: `POST /oauth/token` (standard OAuth2 flow).

---

## Privacy / Data Notes

- Response is scoped strictly to `me` — no user can query another user's enrollments.
- `display_name` / PII of other users is never included.
- Certificate code in response is public (same as printed on certificate), so safe to return here.

---

## Breaking-Change Policy

- New optional fields → allowed without version bump.
- Removal of fields, changes to status values → requires `/api/v2/` + 90-day notice in I05.
