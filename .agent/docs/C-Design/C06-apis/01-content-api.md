# C06 — API: Content (Course Catalog, Search, Detail, Enroll)

> **Documentation mode:** as-built — all endpoints exist in the codebase as of 2026-06-20.
>
> Covers JSON endpoints for course discovery and enrollment. HTML page routes (`/courses`, `/courses/{id}`, etc.) are excluded — see [C07-sitemap](../C07-sitemap.md).
>
> **Modules:** [lms_course](../C05-modules/lms_course.md), [lms_mobile_api](../C05-modules/lms_mobile_api.md)
> **Entities:** [lms_course](../C04-entities/01-content-entities.md), [taxonomy_term (course_category, course_level, course_language)](../C04-entities/01-content-entities.md)

---

## API Overview

| Attribute | Web API | Mobile API |
|-----------|---------|------------|
| Base path | `/api/courses/` | `/api/v1/` |
| Authentication | Public (`access content`) | Bearer token (controller-level) |
| Consumers | Browser AJAX (catalog search widget) | Flutter app v1.0.10+33 |
| Response format | JSON (bare object, no envelope) | JSON (bare object) |

**Note on response envelope:** Neither the web API nor the mobile API wraps responses in a standard `{data, error, meta}` envelope. Each controller returns its own flat JSON shape. The shapes documented below reflect the actual controller output grounded in the facts base.

---

## Endpoint: GET /api/courses/search

**Route name:** `lms_course.api_search`
**Module:** lms_course
**Controller:** `web/modules/custom/lms_course/src/Controller/CoursePublicController.php` → `searchApi()`
**Authentication:** Public — `_permission: 'access content'`
**Authorization:** None (public catalog)
**Rate Limit:** Not configured (Drupal default — no per-route throttle)

### Request

**Headers:**
| Header | Value | Required |
|--------|-------|----------|
| Accept | application/json | Recommended |

**Query Parameters:**
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `q` | string | — | Free-text search against course name |
| `category` | integer | — | Taxonomy term ID from `course_category` vocabulary |
| `level` | string | — | Taxonomy term name from `course_level` (e.g., "Beginner") |
| `price` | string | — | Filter: `free` or `paid` (maps to `field_price_type`) |
| `rating` | float | — | Minimum `field_rating` threshold |
| `sort` | string | `default` | Sort order: `newest` (by `created` DESC), `price` (by `field_price` ASC), `default` |
| `page` | integer | 0 | Zero-based page index |

**Path Parameters:** None.

**Body:** None (GET).

### Response

**Success (200):**
```json
{
  "courses": [
    {
      "id": 1,
      "title": "string — course name",
      "thumbnail": "string — absolute file URL or null",
      "category": "string — term name or null",
      "level": "string — term name or null",
      "price": "0.00",
      "price_type": "free | paid | membership",
      "rating": "5.0",
      "instructor": "string — display name"
    }
  ],
  "total": 42,
  "page": 0,
  "per_page": 12
}
```

**Notes on shape:**
- `thumbnail` is an absolute URL built by the controller using Drupal's file URL generator. May be `null` if no image is set.
- `price` is a string-formatted decimal from `field_price` (precision 10, scale 2).
- `rating` is a string-formatted decimal from `field_rating` (precision 3, scale 1, default `5.0`).
- Pagination: 12 items per page (hardcoded in `catalog()` method; `searchApi()` mirrors this).

**Error Responses:**
| Status | When |
|--------|------|
| 200 with empty `courses: []` | No results; not a 404 |
| 500 | Drupal unhandled exception (e.g., database error) |

### Caching

No explicit cache headers set by this endpoint. Drupal's page cache does not apply (JSON response). Responses are not cached — each request queries the database.

---

## Endpoint: GET /api/v1/courses/search (Mobile)

**Route name:** `lms_mobile_api.courses.search`
**Module:** lms_mobile_api
**Controller:** `web/modules/custom/lms_mobile_api/src/Controller/MobileCourseController.php` → `searchCourses()`
**Authentication:** Bearer token validated at controller level (routing.yml declares `_access: 'TRUE'`)
**Authorization:** Any authenticated mobile user

### Request

**Headers:**
| Header | Value | Required |
|--------|-------|----------|
| Authorization | Bearer {token} | Yes (controller-enforced) |
| Accept | application/json | Yes |

**Query Parameters:**
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `q` | string | — | Search query |
| `category` | integer | — | Category term ID |
| `page` | integer | 1 | Page number (1-based in mobile API) |
| `per_page` | integer | 20 | Items per page |

**Body:** None (GET).

### Response

**Success (200):**
```json
{
  "courses": [
    {
      "id": 1,
      "title": "string",
      "thumbnail": "string | null",
      "category": "string | null",
      "price": "0.00",
      "price_type": "free | paid | membership",
      "rating": "5.0"
    }
  ],
  "total": 42,
  "page": 1,
  "per_page": 20
}
```

**Error Responses:**
| Status | Code | When |
|--------|------|------|
| 401 | — | Missing or invalid bearer token |
| 500 | — | Server error |

---

## Endpoint: GET /api/v1/categories (Mobile)

**Route name:** `lms_mobile_api.categories`
**Module:** lms_mobile_api
**Controller:** `MobileCourseController::listCategories()`
**Authentication:** Bearer token (controller-enforced)

### Request

**Headers:**
| Header | Value | Required |
|--------|-------|----------|
| Authorization | Bearer {token} | Yes |

**Query Parameters:** None.

### Response

**Success (200):**
```json
{
  "categories": [
    {
      "id": 1,
      "name": "string — term name from course_category vocabulary",
      "thumbnail": "string | null"
    }
  ]
}
```

**Note:** Categories are taxonomy terms from the `course_category` vocabulary. See [entities: taxonomy vocabularies](../C04-entities/01-content-entities.md).

---

## Endpoint: GET /api/v1/courses/{courseId} (Mobile)

**Route name:** `lms_mobile_api.course.detail`
**Module:** lms_mobile_api
**Controller:** `MobileCourseController::getCourseDetail()`
**Authentication:** Bearer token (controller-enforced)

### Request

**Headers:**
| Header | Value | Required |
|--------|-------|----------|
| Authorization | Bearer {token} | Yes |

**Path Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `courseId` | integer | `lms_course` entity ID |

**Query Parameters:** None.

### Response

**Success (200):**
```json
{
  "id": 1,
  "title": "string",
  "description": "string — field_description (HTML)",
  "short_description": "string — field_short_description",
  "what_learn": "string — field_what_learn",
  "prerequisites": "string — field_prerequisites",
  "thumbnail": "string | null",
  "banner": "string | null",
  "category": "string | null",
  "level": "string | null",
  "language": "string | null",
  "price": "0.00",
  "price_type": "free | paid | membership",
  "rating": "5.0",
  "duration": 120,
  "is_enrolled": false,
  "instructor": {
    "id": 5,
    "name": "string",
    "avatar": "string | null"
  },
  "chapters": [
    {
      "id": 10,
      "title": "string",
      "lessons": [
        {
          "id": 100,
          "title": "string",
          "type": "video | text | quiz | h5p | scorm | pdf | pptx | zoom | mixed",
          "duration": 15,
          "is_free_preview": false,
          "is_completed": false
        }
      ]
    }
  ]
}
```

**Notes:**
- `duration` is in minutes (from `field_duration` on `lms_course`, integer).
- `is_enrolled` is `true` when a `lms_user_course` record exists for the authenticated user.
- `is_completed` per lesson requires a `lms_user_lesson` record with `field_status = completed`.
- Chapters ordered by `weight` on `lms_chapter`; lessons ordered by `weight` on `lms_lesson`.

**Error Responses:**
| Status | When |
|--------|------|
| 401 | Missing/invalid bearer token |
| 404 | Course not found or unpublished |

---

## Endpoint: POST /api/v1/courses/{courseId}/enroll-free (Mobile)

**Route name:** `lms_mobile_api.course.enroll_free`
**Module:** lms_mobile_api
**Controller:** `MobileCourseController::enrollFree()`
**Authentication:** Bearer token (controller-enforced)

### Request

**Headers:**
| Header | Value | Required |
|--------|-------|----------|
| Authorization | Bearer {token} | Yes |
| Content-Type | application/json | Yes |

**Path Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `courseId` | integer | `lms_course` entity ID with `field_price_type = free` |

**Body:** Empty or `{}`.

### Response

**Success (200/201):**
```json
{
  "success": true,
  "message": "Enrolled successfully.",
  "enrollment_id": 55
}
```

**Error Responses:**
| Status | When |
|--------|------|
| 400 | Course is not free (price_type != free) |
| 401 | Not authenticated |
| 409 | Already enrolled |
| 404 | Course not found |

**Business Logic (as-built):**
1. Validates bearer token → resolves user.
2. Loads `lms_course` entity; checks `field_price_type == 'free'`.
3. Checks for existing `lms_user_course` record (duplicate check).
4. Creates `lms_user_course` with `field_status = 'enrolled'`, `field_progress = 0`.
5. Optionally creates `lms_notification` of type `enrollment`.

**Web equivalent:** `GET /courses/{lms_course}/enroll-free` (`lms_course.enroll_free`) — HTML redirect; controller is `CourseEnrollmentController::enrollFree`. Note: web route declares `_permission: 'access content'` (public) but requires login at controller level — this is a routing-layer auth gap.

---

## API Route Summary

| Method | Path | Route Name | Auth | Module | Description |
|--------|------|------------|------|--------|-------------|
| GET | `/api/courses/search` | `lms_course.api_search` | Public | lms_course | Web: course catalog JSON search |
| GET | `/api/v1/courses/search` | `lms_mobile_api.courses.search` | Bearer | lms_mobile_api | Mobile: course search |
| GET | `/api/v1/categories` | `lms_mobile_api.categories` | Bearer | lms_mobile_api | Mobile: list course categories |
| GET | `/api/v1/courses/{courseId}` | `lms_mobile_api.course.detail` | Bearer | lms_mobile_api | Mobile: course detail with curriculum |
| POST | `/api/v1/courses/{courseId}/enroll-free` | `lms_mobile_api.course.enroll_free` | Bearer | lms_mobile_api | Mobile: enroll in free course |

---

## Known Issues

| Issue | Endpoint | Description |
|-------|----------|-------------|
| Auth gap at routing layer | `GET /courses/{lms_course}/enroll-free` (web) | Route declares `_permission: 'access content'` (public) but controller enforces login — not a JSON endpoint but worth noting for parity with mobile |
| No response envelope | All web/mobile API endpoints | No standard `{data, error, meta}` wrapper — each controller returns bespoke JSON shapes; makes client-side error handling inconsistent |
| No rate limiting | All endpoints | No Drupal-level throttle configured on any API route |
| `categories()`, `categoryDetail()`, `glossary()` methods | `/categories`, `/categories/{term}`, `/glossary` (web HTML) | Methods referenced in routing.yml; implementation confirmed present but not fully verified from facts base |
