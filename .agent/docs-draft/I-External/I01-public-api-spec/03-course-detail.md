# Course Detail Endpoint

**Status: PROPOSED** — Endpoint chưa được implement.
EverLMS hiện không expose JSON API cho course detail. Trang chi tiết hiện là server-rendered Drupal node.

**Spec format:** REST / JSON
**Owner:** hainn (lms_course module)
**Stability:** Alpha (proposed)
**Lifecycle:** Not yet introduced

---

## Summary

Endpoint trả về đầy đủ thông tin của một course cụ thể, identified by slug.
Authentication không bắt buộc.

Khi authenticated, response có thể bổ sung trường `enrollment_status` của user đó (PROPOSED — phase 2).

---

## Endpoint

**Status: PROPOSED**

```
GET /api/v1/courses/{slug}
```

| Parameter | In | Type | Required | Description |
|-----------|----|------|----------|-------------|
| `slug` | path | string | Yes | Course slug (URL-safe identifier), e.g. `advanced-javascript` |

**Authentication:** Optional (Bearer token). Unauthenticated requests receive public data only.

**Rate limit:** 300 requests/minute per IP (higher than catalog — single-item reads are cheaper).

---

## Success Response — 200 OK

```json
{
  "id": 42,
  "slug": "advanced-javascript",
  "title": "Advanced JavaScript",
  "short_description": "Master closures, async patterns, and performance.",
  "description": "<p>Full HTML course description...</p>",
  "thumbnail_url": "https://everlms.example.com/files/courses/js-thumb.jpg",
  "preview_video_url": "https://everlms.example.com/videos/js-preview.mp4",
  "level": "advanced",
  "language": "en",
  "duration_minutes": 480,
  "lesson_count": 32,
  "category": {
    "id": 5,
    "slug": "programming",
    "name": "Programming"
  },
  "tags": ["javascript", "es2024", "performance"],
  "instructor": {
    "id": 7,
    "display_name": "Nguyen Van A",
    "avatar_url": "https://everlms.example.com/files/avatars/nva.jpg",
    "bio": "Full-stack developer with 10 years experience."
  },
  "price": {
    "amount": 299000,
    "currency": "VND",
    "is_free": false
  },
  "requirements": [
    "Basic JavaScript knowledge",
    "Familiarity with ES6"
  ],
  "outcomes": [
    "Understand closures and scope deeply",
    "Write performant async code"
  ],
  "published_at": "2025-09-01T00:00:00Z",
  "updated_at": "2026-01-15T10:00:00Z",
  "enrollment_status": null
}
```

> **Note:** `enrollment_status` is `null` for unauthenticated requests.
> When authenticated (Bearer token), it will return `"enrolled"`, `"completed"`, or `null` (not enrolled).
> This field is **PROPOSED** for phase 2.

---

## Curriculum (Chapters + Lessons Preview)

To keep the response size manageable, lesson content (video URLs, SCORM packages) is NOT included.
Only a structural preview is returned:

```json
{
  "curriculum": [
    {
      "id": 10,
      "title": "Chapter 1: Closures",
      "lesson_count": 5,
      "lessons": [
        { "id": 101, "title": "What is a closure?", "type": "video", "duration_seconds": 540, "is_free_preview": true },
        { "id": 102, "title": "Practical closures", "type": "video", "duration_seconds": 720, "is_free_preview": false }
      ]
    }
  ]
}
```

---

## Not Found — 404

```json
{
  "error": {
    "code": "COURSE-001",
    "message": "No course found with slug 'unknown-slug'."
  }
}
```

---

## Error Codes

| Code | HTTP | Meaning | Retry? |
|------|------|---------|--------|
| `COURSE-001` | 404 | Course not found or unpublished | No |
| `COURSE-429` | 429 | Rate limited | Yes, with backoff |
| `COURSE-500` | 500 | Internal error | Yes, once |

---

## Caching Recommendation

- Full response cacheable for up to 300 seconds for anonymous requests.
- Cache-Control: `public, max-age=300, s-maxage=300`
- When `Authorization` header is present, cache must be private (or bypass CDN).

---

## Breaking-Change Policy

- New optional top-level fields → allowed without version bump.
- Removal of fields, slug changes, or structural changes to `curriculum` → requires `/api/v2/` + 90-day notice in I05.
