# C06 — API: Progress (Mark-Complete, Dashboard, Notes)

> **Documentation mode:** as-built — all endpoints exist in the codebase as of 2026-06-20.
>
> Covers the JSON API endpoints for lesson progress tracking, note management, and the mobile learning/dashboard endpoints. HTML learner portal pages (`/dashboard`, `/my-courses`, `/learn/{course}/{lesson}`) are excluded from this file — see [C07-sitemap](../C07-sitemap.md).
>
> **Modules:** [lms_progress](../C05-modules/lms_progress.md), [lms_note](../C05-modules/lms_note.md), [lms_mobile_api](../C05-modules/lms_mobile_api.md)
> **Entities:** [lms_user_course, lms_user_lesson, lms_note](../C04-entities/01-content-entities.md)

---

## API Overview

| Layer | Prefix | Auth | Consumer |
|-------|--------|------|----------|
| Web progress API | `/api/lms/progress/` | Session (logged_in) | Browser AJAX (lesson view page) |
| Web note API | `/api/lms/note/` | Session (logged_in) | Browser AJAX (in-lesson note widget) |
| Mobile learning API | `/api/v1/courses/{id}/lessons/{id}`, `/api/v1/progress/`, `/api/v1/dashboard`, `/api/v1/my-courses` | Bearer token (controller-level) | Flutter app v1.0.10+33 |

**Note on data model:** Progress is stored in two entities:
- `lms_user_course` — per-user per-course enrollment record with `field_progress` (decimal 0–100), `field_status` (enrolled / in_progress / completed).
- `lms_user_lesson` — per-user per-lesson record with `field_status` (not_started / in_progress / completed) and `field_video_position` (seconds).

There is **no** `lms_user_chapter` entity — chapter-level progress is not explicitly stored; it is derived by the client or inferred from lesson completion counts.

---

## Endpoint: POST /api/lms/progress/mark-complete

**Route name:** `lms_progress.api_mark_complete`
**Module:** lms_progress
**Controller:** `web/modules/custom/lms_progress/src/Controller/ProgressApiController.php` → `markComplete(Request $request)`
**Authentication:** `_user_is_logged_in: TRUE` (session)
**Methods:** POST

### Request

**Headers:**
| Header | Value | Required |
|--------|-------|----------|
| Content-Type | application/json | Yes |
| X-CSRF-Token | {token from /session/token} | Recommended (Drupal CSRF protection) |

**Body:**
```json
{
  "lesson_id": 123,
  "course_id": 45
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `lesson_id` | integer | Yes | `lms_lesson` entity ID |
| `course_id` | integer | Yes | `lms_course` entity ID |

### Response

**Success (200):**
```json
{
  "success": true,
  "lesson_completed": true,
  "course_completed": false,
  "progress": 45.5,
  "next_lesson_url": "/learn/45/124"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Operation succeeded |
| `lesson_completed` | boolean | The `lms_user_lesson` record now has `field_status = completed` |
| `course_completed` | boolean | Course progress reached 100% — `lms_user_course.field_status = completed` |
| `progress` | float | Updated course completion percentage (0–100, 2 decimal places) |
| `next_lesson_url` | string or null | Internal URL of the next incomplete lesson in curriculum order; null if course is complete |

**Error (400/403):**
```json
{
  "success": false,
  "message": "Access denied or lesson not found."
}
```

### Business Logic (as-built)

1. Reads `lesson_id` and `course_id` from request JSON body.
2. Looks up existing `lms_user_lesson` for `(uid, lesson_id)` — upserts to `field_status = completed`, sets `field_completed_at`.
3. Recalculates course progress: counts all lessons in the course (via Chapter→Lesson traversal), counts lessons with `lms_user_lesson.field_status = completed` for this user, computes percentage.
4. Updates `lms_user_course.field_progress` with the new percentage.
5. If percentage == 100: sets `lms_user_course.field_status = completed` and `field_completed_at`.
6. Determines `next_lesson_url` by finding the first `lms_lesson` in curriculum order that does not have a completed `lms_user_lesson` record.
7. Returns JSON.

**Note:** `markComplete()` does NOT trigger certificate issuance directly. Certificate issuance is triggered by `QuizPassedEvent` in the quiz submit flow (lms_certificate event subscriber), not by lesson completion.

### Known Issues

| Issue | Description |
|-------|-------------|
| No input validation | `lesson_id` and `course_id` are read without type/range validation |
| Race condition | Concurrent calls can cause progress recalculation to run in parallel against stale data — no DB transaction or row-level lock |
| No enrollment check | Controller does not verify the user is enrolled in the course before marking complete |

### Caching

No cache headers set. Each call performs live DB reads/writes. No Drupal cache tags invalidated (lesson view page caches are user-specific and vary by session).

---

## Endpoint: POST /api/lms/note/save

**Route name:** `lms_note.save`
**Module:** lms_note
**Controller:** `web/modules/custom/lms_note/src/Controller/NoteController.php` → `save(Request $request)`
**Authentication:** `_user_is_logged_in: TRUE` (session)
**Methods:** POST

### Request

**Headers:**
| Header | Value | Required |
|--------|-------|----------|
| Content-Type | application/json | Yes |
| X-CSRF-Token | {token} | Recommended |

**Body:**
```json
{
  "lesson_id": 123,
  "course_id": 45,
  "note_text": "string — note body content",
  "video_timestamp": 142
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `lesson_id` | integer | Yes | `lms_lesson` entity ID |
| `course_id` | integer | Yes | `lms_course` entity ID (stored in `lms_note.field_course`) |
| `note_text` | string | Yes | Text content of the note (stored in `field_body`) |
| `video_timestamp` | integer | No | Video position in seconds at the time of note (stored in `field_video_timestamp`; default 0) |

### Response

**Success (200):**
```json
{
  "status": "created",
  "id": 77
}
```

or

```json
{
  "status": "updated",
  "id": 77
}
```

| Field | Type | Description |
|-------|------|-------------|
| `status` | string | `"created"` for new note, `"updated"` for existing note update |
| `id` | integer | `lms_note` entity ID |

**Business logic:** Upsert — queries for existing `lms_note` where `uid = current_user` AND `field_lesson = lesson_id`. If found, updates `field_body` and `field_video_timestamp`. If not found, creates new `lms_note` entity. Invalidates cache tag `lms_note:{lesson_id}:{uid}` after save.

---

## Endpoint: POST /api/lms/note/delete

**Route name:** `lms_note.delete`
**Module:** lms_note
**Controller:** `NoteController::delete(Request $request)`
**Authentication:** `_user_is_logged_in: TRUE` (session)
**Methods:** POST

### Request

**Headers:**
| Header | Value | Required |
|--------|-------|----------|
| Content-Type | application/json | Yes |

**Body:**
```json
{
  "lesson_id": 123
}
```

### Response

**Success (200):**
```json
{
  "status": "deleted"
}
```

or

```json
{
  "status": "not_found"
}
```

**Business logic:** Deletes all `lms_note` entities for `(current_user, lesson_id)`. Invalidates cache tag. Returns `not_found` if no note existed (not an error status).

---

## Endpoint: GET /api/lms/note/get

**Route name:** `lms_note.get`
**Module:** lms_note
**Controller:** `NoteController::getNote(Request $request)`
**Authentication:** `_user_is_logged_in: TRUE` (session)
**Methods:** GET

### Request

**Query Parameters:**
| Param | Type | Required | Description |
|-------|------|----------|-------------|
| `lesson_id` | integer | Yes | `lms_lesson` entity ID |

### Response

**Success (200):**
```json
{
  "status": "found",
  "note_text": "string — note body content"
}
```

or

```json
{
  "status": "not_found",
  "note_text": null
}
```

**Business logic:** Queries `lms_note` storage for `(current_user, lesson_id)`. Returns first matching note's `field_body` value. `status = "not_found"` is a 200 response, not a 404.

---

## Mobile Learning Endpoints

### GET /api/v1/courses/{courseId}/lessons/{lessonId}

**Route name:** `lms_mobile_api.lesson.detail`
**Module:** lms_mobile_api
**Controller:** `web/modules/custom/lms_mobile_api/src/Controller/MobileLearningController.php` → `getLessonDetail()`
**Authentication:** Bearer token (controller-enforced)

**Path Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `courseId` | integer | `lms_course` entity ID |
| `lessonId` | integer | `lms_lesson` entity ID |

**Request Headers:**
| Header | Value | Required |
|--------|-------|----------|
| Authorization | Bearer {token} | Yes |

**Response (200):**
```json
{
  "id": 123,
  "title": "string",
  "type": "video | text | quiz | h5p | scorm | pdf | pptx | zoom | mixed",
  "content": "string — field_content (HTML) | null",
  "video_url": "string — field_video_url (YouTube/Vimeo URL) | null",
  "video_file_url": "string — absolute URL for field_video_file | null",
  "zoom_meeting_id": "string | null",
  "zoom_schedule": "ISO8601 timestamp | null",
  "quiz_id": 10,
  "duration": 15,
  "free_preview": false,
  "attachments": [
    {
      "filename": "string",
      "url": "string — absolute file URL",
      "mime": "string"
    }
  ],
  "user_progress": {
    "status": "not_started | in_progress | completed",
    "video_position": 0,
    "completed_at": "ISO8601 | null"
  },
  "existing_note": {
    "id": 77,
    "text": "string | null",
    "video_timestamp": 142
  }
}
```

**Notes:**
- `quiz_id` is populated only when `field_type = quiz` or `mixed` and `field_quiz` is set on the lesson entity.
- `video_url` and `video_file_url` are mutually exclusive in most cases, but both can be present for `mixed` type.
- `user_progress` reflects the current user's `lms_user_lesson` record. If no record exists, `status = "not_started"`.
- `existing_note` is `null` if no note exists for this user+lesson.
- H5P embed (`field_h5p_embed`) and SCORM (`field_scorm_package`) are not included in the mobile response — these lesson types have limited mobile support.

**Error Responses:**
| Status | When |
|--------|------|
| 401 | Missing/invalid bearer token |
| 403 | User not enrolled in course or lesson is behind drip schedule (`field_drip_days`) |
| 404 | Course or lesson not found |

---

### GET /api/v1/dashboard

**Route name:** `lms_mobile_api.dashboard`
**Module:** lms_mobile_api
**Controller:** `MobileCourseController::getDashboard()`
**Authentication:** Bearer token (controller-enforced)

**Request Headers:**
| Header | Value | Required |
|--------|-------|----------|
| Authorization | Bearer {token} | Yes |

**Response (200):**
```json
{
  "stats": {
    "enrolled": 5,
    "in_progress": 3,
    "completed": 2,
    "certificates": 1
  },
  "continue_learning": {
    "course_id": 45,
    "course_title": "string",
    "lesson_id": 124,
    "lesson_title": "string",
    "progress": 45.5,
    "url": "/learn/45/124"
  },
  "recent_courses": [
    {
      "id": 45,
      "title": "string",
      "thumbnail": "string | null",
      "progress": 45.5,
      "status": "in_progress"
    }
  ]
}
```

**Notes:**
- `continue_learning` is the first incomplete lesson in the most-recently-accessed enrolled course. Null if all courses are complete.
- `recent_courses` lists up to 5 enrolled courses ordered by most recently accessed.
- Stats are computed live from `lms_user_course` entity queries for the current user.

**Known issue:** `dashboard()` and `myCourses()` in `UserPortalController` (web) have near-identical "find next lesson" logic — duplicated code (not a mobile-specific issue, but affects consistency).

---

### GET /api/v1/my-courses

**Route name:** `lms_mobile_api.my_courses`
**Module:** lms_mobile_api
**Controller:** `MobileCourseController::getMyCourses()`
**Authentication:** Bearer token (controller-enforced)

**Request Headers:**
| Header | Value | Required |
|--------|-------|----------|
| Authorization | Bearer {token} | Yes |

**Query Parameters:**
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `status` | string | — | Filter: `enrolled`, `in_progress`, `completed` |
| `page` | integer | 1 | Page number |
| `per_page` | integer | 20 | Items per page |

**Response (200):**
```json
{
  "courses": [
    {
      "id": 45,
      "title": "string",
      "thumbnail": "string | null",
      "progress": 45.5,
      "status": "enrolled | in_progress | completed",
      "enrolled_at": "ISO8601",
      "completed_at": "ISO8601 | null"
    }
  ],
  "total": 5,
  "page": 1,
  "per_page": 20
}
```

**Notes:**
- `progress` is from `lms_user_course.field_progress` (decimal 0–100).
- `enrolled_at` is from `lms_user_course.field_enrolled_at` (created timestamp).
- `completed_at` is from `lms_user_course.field_completed_at` (timestamp; null if not completed).

---

### POST /api/v1/progress/mark-complete

**Route name:** `lms_mobile_api.progress.mark_complete`
**Module:** lms_mobile_api
**Controller:** `MobileLearningController::markLessonComplete()`
**Authentication:** Bearer token (controller-enforced)
**Methods:** POST

**Path Parameters:** None.

**Request Headers:**
| Header | Value | Required |
|--------|-------|----------|
| Authorization | Bearer {token} | Yes |
| Content-Type | application/json | Yes |

**Request Body:**
```json
{
  "lesson_id": 123,
  "course_id": 45
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `lesson_id` | integer | Yes | `lms_lesson` entity ID |
| `course_id` | integer | Yes | `lms_course` entity ID |

**Response (200):**
```json
{
  "success": true,
  "lesson_completed": true,
  "course_completed": false,
  "progress": 45.5,
  "next_lesson_url": "/api/v1/courses/45/lessons/124"
}
```

**Business logic:** Same as the web `POST /api/lms/progress/mark-complete` — upserts `lms_user_lesson`, recalculates `lms_user_course.field_progress`, determines next lesson. The `next_lesson_url` in the mobile response points to the mobile API path (`/api/v1/courses/{id}/lessons/{id}`) rather than the web path.

**Error Responses:**
| Status | When |
|--------|------|
| 400 | Missing `lesson_id` or `course_id` |
| 401 | Missing/invalid bearer token |
| 403 | User not enrolled in course |
| 404 | Course or lesson not found |

---

## API Route Summary

| Method | Path | Route Name | Auth | Module | Description |
|--------|------|------------|------|--------|-------------|
| POST | `/api/lms/progress/mark-complete` | `lms_progress.api_mark_complete` | Session | lms_progress | Web: mark lesson complete, recalculate progress |
| POST | `/api/lms/note/save` | `lms_note.save` | Session | lms_note | Web: create or update in-lesson note |
| POST | `/api/lms/note/delete` | `lms_note.delete` | Session | lms_note | Web: delete note for a lesson |
| GET | `/api/lms/note/get` | `lms_note.get` | Session | lms_note | Web: retrieve existing note for a lesson |
| GET | `/api/v1/courses/{courseId}/lessons/{lessonId}` | `lms_mobile_api.lesson.detail` | Bearer | lms_mobile_api | Mobile: lesson detail with user progress and note |
| GET | `/api/v1/dashboard` | `lms_mobile_api.dashboard` | Bearer | lms_mobile_api | Mobile: learner dashboard stats and continue-learning |
| GET | `/api/v1/my-courses` | `lms_mobile_api.my_courses` | Bearer | lms_mobile_api | Mobile: enrolled courses list with progress |
| POST | `/api/v1/progress/mark-complete` | `lms_mobile_api.progress.mark_complete` | Bearer | lms_mobile_api | Mobile: mark lesson complete, recalculate progress |

---

## Known Issues

| Issue | Endpoint(s) | Description |
|-------|-------------|-------------|
| No input validation | `mark-complete` (web + mobile) | `lesson_id` and `course_id` not validated for type or range before entity queries |
| Race condition on progress recalc | `mark-complete` (web + mobile) | Concurrent requests recalculate from same stale DB state — no transaction or lock; progress can be understated |
| No enrollment check | `mark-complete` (web) | `ProgressApiController::markComplete()` does not verify `lms_user_course` exists before creating `lms_user_lesson` |
| No cross-lesson ownership validation | `GET /api/lms/note/get` | Query filters by user ID so ownership is implicit; no explicit entity access check |
| Comment form side effect | `/learn/{course}/{lesson}` (HTML) | `lms_progress.module` hook_form_alter grants all text formats to the `authenticated` role unconditionally at form-alter time (saves the Role entity on every page load containing a lesson comment form — performance and security concern) |
| Undefined `$response` variable | `lms_progress_comment_form_submit_redirect()` | Returns undefined `$response` on non-AJAX path when no lesson context found — PHP warning in production |
| H5P / SCORM no tracking endpoints | All | H5P and SCORM lesson types have no xAPI or SCORM 1.2 tracking endpoint — completion cannot be auto-tracked for these types |
| No progress read endpoint | Web API | There is no `GET /api/lms/progress/{course_id}` endpoint — web clients cannot read course progress as JSON; must load the full HTML dashboard or my-courses page |
