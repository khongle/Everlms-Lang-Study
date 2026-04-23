# Course Catalog Browse Endpoint

**Status: PROPOSED** — Endpoint chưa được implement.
EverLMS hiện không expose JSON API cho course catalog. Trang catalog hiện là server-rendered Drupal views.

**Spec format:** REST / JSON
**Owner:** hainn (lms_course module)
**Stability:** Alpha (proposed)
**Lifecycle:** Not yet introduced

---

## Summary

Endpoint công khai cho phép external consumers (mobile app, third-party portals, headless frontend)
lấy danh sách course có phân trang và filter.

Authentication không bắt buộc. Trả về chỉ những course đã published và public.

---

## Endpoint

**Status: PROPOSED**

```
GET /api/v1/courses
```

**Authentication:** None required (public endpoint). Authenticated requests may return additional fields in future versions.

**Rate limit:** 120 requests/minute per IP. See I06.

---

## Query Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `page` | integer | No | Page number, 1-indexed (default: 1) | `?page=2` |
| `per_page` | integer | No | Results per page, max 50 (default: 20) | `?per_page=10` |
| `category` | string | No | Filter by category slug | `?category=programming` |
| `level` | string | No | Filter by level: `beginner`, `intermediate`, `advanced` | `?level=beginner` |
| `language` | string | No | Filter by course language (ISO 639-1) | `?language=vi` |
| `q` | string | No | Keyword search in title and description | `?q=javascript` |
| `sort` | string | No | Sort order: `newest`, `popular`, `price_asc`, `price_desc` (default: `newest`) | `?sort=popular` |

---

## Success Response — 200 OK

```json
{
  "data": [
    {
      "id": 42,
      "slug": "advanced-javascript",
      "title": "Advanced JavaScript",
      "short_description": "Master closures, async patterns, and performance.",
      "thumbnail_url": "https://everlms.example.com/files/courses/js-thumb.jpg",
      "level": "advanced",
      "language": "en",
      "duration_minutes": 480,
      "lesson_count": 32,
      "category": {
        "id": 5,
        "slug": "programming",
        "name": "Programming"
      },
      "instructor": {
        "display_name": "Nguyen Van A"
      },
      "price": {
        "amount": 299000,
        "currency": "VND",
        "is_free": false
      },
      "published_at": "2025-09-01T00:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total_items": 148,
    "total_pages": 8
  },
  "links": {
    "self":  "/api/v1/courses?page=1",
    "next":  "/api/v1/courses?page=2",
    "prev":  null
  }
}
```

---

## Error Responses

| HTTP | Error code | Meaning | Retry? |
|------|-----------|---------|--------|
| 400 | `CATALOG-400` | Invalid query parameter value (e.g., `per_page=999`) | No |
| 429 | `CATALOG-429` | Rate limit exceeded | Yes, with backoff |
| 500 | `CATALOG-500` | Internal error | Yes, once |

```json
{
  "error": {
    "code": "CATALOG-400",
    "message": "per_page must be between 1 and 50."
  }
}
```

---

## Caching Recommendation

- Response may be cached at CDN / Varnish level for up to 60 seconds for anonymous requests.
- Vary by query string.
- No `Authorization` header means no per-user cache contamination risk.

---

## Breaking-Change Policy

- Adding new optional fields or new sort options → allowed without version bump.
- Removing fields, changing field types, or removing sort options → requires `/api/v2/` + 90-day notice in I05.
