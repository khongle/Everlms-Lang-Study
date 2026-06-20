# C06 — API: Mobile API (`/api/v1`)

**Documentation mode:** as-built — 22 routes fully implemented; custom bearer token auth (NOT OAuth2/JWT library); OTP via email

**Module:** `lms_mobile_api`
**Module path:** `web/modules/custom/lms_mobile_api/`
**Consumer:** Flutter mobile app (`app/` — v1.0.10+33, Riverpod/GoRouter/Dio, 33 screens)
**Controllers:** `MobileAuthController`, `MobileCourseController`, `MobileLearningController`
**Service:** `OtpService` (keyvalue-backed OTP + session tokens)

---

## API Overview

**Base URL:** `/api/v1`
**Authentication:** Custom bearer token — 32-char hex stored in `keyvalue.expirable` collection `lms_mobile_api.tokens`; NOT OAuth2, NOT JWT library, NOT Drupal session/cookie
**Token TTL:** 604800 s (7 days) configurable via `lms_mobile_api.settings:token_expiry_seconds`
**Token acquisition:** Via `POST /api/v1/oauth/token` (password grant) or post-OTP verification flows
**Header precedence:** `X-Authorization: Bearer <token>` checked first; falls back to `Authorization: Bearer <token>` (non-standard workaround for HTTP Basic Auth server gates)
**Consumers:** Flutter app (Dio HTTP client); no browser/SPA
**Response Envelope:** Endpoints do NOT wrap in a standard envelope — they return flat JSON objects. Error responses use `{error, message}` shape. No `data`/`meta` wrapper.

**Route access:** All 22 routes declare `_access: TRUE` and `_format: json` in routing.yml. No CSRF token. Auth is enforced entirely in-controller via bearer token resolution.

**No refresh token mechanism:** Token expires after TTL; user must re-authenticate. `oauth_client_id` / `oauth_client_secret` fields in `lms_mobile_api.settings.yml` are unused config artifacts from an earlier design.

**CORS:** No CORS headers configured in this module. Requires `sites/default/services.yml` CORS config on the server.

---

## Authentication Flow

```
Registration:
  POST /api/v1/user/register
    → account created (status=0, role=student)
    → OTP email sent (6-digit code, 15-min TTL)
    → returns otp_token (64-char hex)
  POST /api/v1/user/register/verify-otp
    → activates account
    → returns access_token (32-char hex, 7-day TTL)

Login:
  POST /api/v1/oauth/token
    → email + password
    → returns access_token

Password Reset:
  POST /api/v1/user/forgot-password → otp_token
  POST /api/v1/user/forgot-password/verify-otp → reset_token (30-min TTL)
  POST /api/v1/user/reset-password → access_token (auto-login)
```

---

## Endpoint: POST /api/v1/user/register

**Route:** `lms_mobile_api.user.register`
**Controller:** `MobileAuthController::register`
**Authentication:** Public

### Request
```json
{
  "name": "Nguyen Van A",
  "email": "user@example.com",
  "password": "secretpass123",
  "password_confirm": "secretpass123"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|-----------|
| `name` | string | No | Used as display name |
| `email` | string | Yes | Valid email; no existing active account |
| `password` | string | Yes | Min 8 chars |
| `password_confirm` | string | Yes | Must match `password` |

### Response 200
```json
{
  "status": "otp_sent",
  "message": "Registration OTP sent to your email.",
  "otp_token": "4a9f...64chars"
}
```

### Response 422
```json
{
  "error": "validation_error",
  "message": "Email already in use",
  "violations": [{"field": "email", "message": "An active account already exists..."}]
}
```

**Logic:** Validates input; rejects if active account exists; deletes stale blocked registrations; creates user `status=0`, role `student`; generates OTP (6-digit) + session token (64-char); stores OTP session in `keyvalue.expirable` collection `lms_mobile_api.otp` (TTL 900s); sends OTP email.

---

## Endpoint: POST /api/v1/user/register/verify-otp

**Route:** `lms_mobile_api.user.register.verify_otp`
**Controller:** `MobileAuthController::verifyRegisterOtp`
**Authentication:** Public

### Request
```json
{ "otp_token": "4a9f...64chars", "otp_code": "123456" }
```

### Response 200
```json
{
  "status": "verified",
  "message": "Account activated successfully.",
  "uid": 42,
  "access_token": "b3e1...32chars",
  "token_type": "Bearer",
  "expires_in": 604800
}
```

### Response 400
```json
{ "error": "invalid_otp", "message": "Invalid or expired OTP code." }
```

---

## Endpoint: POST /api/v1/user/register/resend-otp

**Route:** `lms_mobile_api.user.register.resend_otp`
**Controller:** `MobileAuthController::resendRegisterOtp`
**Authentication:** Public

### Request
```json
{ "otp_token": "4a9f...64chars" }
```

### Response 200
```json
{
  "status": "otp_resent",
  "message": "New OTP sent.",
  "otp_token": "c7d2...64chars"
}
```

**Note:** New `otp_token` is issued — the old token is invalidated.

---

## Endpoint: POST /api/v1/user/forgot-password

**Route:** `lms_mobile_api.user.forgot_password`
**Controller:** `MobileAuthController::forgotPassword`
**Authentication:** Public

### Request
```json
{ "email": "user@example.com" }
```

### Response 200
```json
{
  "status": "otp_sent",
  "message": "If the email exists, a reset code has been sent.",
  "otp_token": "4a9f...64chars"
}
```

**Security note:** Always returns 200 to prevent email enumeration. Returns empty `otp_token` when email not found.

---

## Endpoint: POST /api/v1/user/forgot-password/verify-otp

**Route:** `lms_mobile_api.user.forgot_password.verify_otp`
**Controller:** `MobileAuthController::verifyForgotPasswordOtp`
**Authentication:** Public

### Request
```json
{ "otp_token": "4a9f...64chars", "otp_code": "654321" }
```

### Response 200
```json
{ "reset_token": "e8f1...64chars" }
```

### Response 400
```json
{ "error": "invalid_otp", "message": "Invalid or expired OTP code." }
```

---

## Endpoint: POST /api/v1/user/reset-password

**Route:** `lms_mobile_api.user.reset_password`
**Controller:** `MobileAuthController::resetPassword`
**Authentication:** Public (reset_token in body; TTL 1800s)

### Request
```json
{
  "reset_token": "e8f1...64chars",
  "password": "newpass456",
  "password_confirm": "newpass456"
}
```

### Response 200
```json
{
  "status": "success",
  "message": "Password reset successfully.",
  "uid": 42,
  "access_token": "b3e1...32chars",
  "token_type": "Bearer",
  "expires_in": 604800
}
```

---

## Endpoint: POST /api/v1/oauth/token

**Route:** `lms_mobile_api.oauth.token`
**Controller:** `MobileAuthController::oauthToken`
**Authentication:** Public (credentials in body)

### Request
```json
{ "username": "user@example.com", "password": "secretpass123" }
```

### Response 200
```json
{
  "uid": 42,
  "email": "user@example.com",
  "name": "Nguyen Van A",
  "access_token": "b3e1...32chars",
  "token_type": "Bearer",
  "expires_in": 604800
}
```

### Response 401
```json
{ "error": "invalid_grant", "message": "Invalid email or password." }
```

**Logic:** Looks up active user by email; verifies password via Drupal `password` service (`password->check()`); issues access token via `issueAccessToken(uid)`.

---

## Endpoint: GET /api/v1/user/profile

**Route:** `lms_mobile_api.user.profile`
**Controller:** `MobileAuthController::getProfile`
**Authentication:** Bearer token required

### Request Headers
| Header | Required | Value |
|--------|----------|-------|
| `Authorization` or `X-Authorization` | Yes | `Bearer <token>` |

### Response 200
```json
{ "uid": 42, "email": "user@example.com", "name": "Nguyen Van A" }
```

### Response 403
`AccessDeniedHttpException` — missing/invalid/expired token.

---

## Endpoint: GET /api/v1/user/me

**Route:** `lms_mobile_api.user.me`
**Controller:** `MobileAuthController::getUserMe`
**Authentication:** Bearer token required

### Response 200
```json
{
  "id": 42,
  "uuid": "550e8400-e29b-...",
  "name": "Nguyen Van A",
  "email": "user@example.com",
  "first_name": "Van A",
  "last_name": "Nguyen",
  "avatar_url": "https://everlms.ddev.site/sites/default/files/...",
  "phone": "+84901234567",
  "gender": "male",
  "date_of_birth": "1995-03-15",
  "roles": ["student"]
}
```

**Notes:** `avatar_url` generated via `file_url_generator` service. `authenticated` role stripped from `roles` array. User fields: `user_picture`, `field_first_name`, `field_last_name`, `field_phone`, `field_gender`, `field_date_of_birth`.

---

## Endpoint: POST /api/v1/user/change-password

**Route:** `lms_mobile_api.user.change_password`
**Controller:** `MobileAuthController::changePassword`
**Authentication:** Bearer token required

### Request
```json
{
  "current_password": "oldpass",
  "new_password": "newpass456",
  "new_password_confirm": "newpass456"
}
```

### Response 200
```json
{ "status": "success", "message": "Password changed successfully." }
```

### Error Responses
| Status | Code | When |
|--------|------|------|
| 401 | — | Invalid current password |
| 422 | `validation_error` | Passwords don't match or too short |
| 403 | — | Invalid bearer token |

---

## Endpoint: GET /api/v1/courses/search

**Route:** `lms_mobile_api.courses.search`
**Controller:** `MobileCourseController::searchCourses`
**Authentication:** Public (no bearer required)

### Query Parameters
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `search` | string | — | Full-text search on course name |
| `category` / `category[]` | int[] | — | Filter by category term ID(s) |
| `level` / `level[]` | int[] | — | Filter by level term ID(s) |
| `price` / `price[]` | string[] | — | `free` or `paid` |
| `featured` | bool | — | Filter featured courses |
| `sort` | string | `newest` | `newest`, `popular`, `rating`, `price_asc`, `price_desc` |
| `page` | integer | 0 | 0-based page index |
| `limit` | integer | 20 | Items per page (max 100) |

### Response 200
```json
{
  "total": 150,
  "page": 0,
  "limit": 20,
  "items": [
    {
      "id": 7,
      "uuid": "550e8400-...",
      "title": "JavaScript Fundamentals",
      "short_description": "Learn JS from scratch",
      "thumbnail_url": "https://...",
      "price": 199000,
      "price_type": "paid",
      "level": {"id": 3, "name": "Beginner"},
      "category": {"id": 5, "name": "Programming"},
      "instructor": {"id": 12, "name": "Tran Van B", "avatar_url": "https://...", "bio": "..."},
      "rating": 4.7,
      "rating_count": 234,
      "student_count": 1023,
      "duration_minutes": 480,
      "bestseller": true,
      "featured": false,
      "created": "2025-12-01T08:00:00Z"
    }
  ]
}
```

---

## Endpoint: GET /api/v1/categories

**Route:** `lms_mobile_api.categories`
**Controller:** `MobileCourseController::listCategories`
**Authentication:** Public

### Response 200
```json
{
  "items": [
    {
      "id": 5,
      "uuid": "...",
      "name": "Programming",
      "description": "Software development courses",
      "thumbnail_url": "https://...",
      "course_count": 42
    }
  ]
}
```

**Logic:** Queries `taxonomy_term` vid=`course_category`, status=1, sorted by weight then name. Counts published courses per category.

---

## Endpoint: GET /api/v1/courses/{courseId}

**Route:** `lms_mobile_api.course.detail`
**Controller:** `MobileCourseController::getCourseDetail`
**Authentication:** Optional — enrollment and wishlist data included only when authenticated
**Path Parameters:**

| Param | Type | Constraint | Description |
|-------|------|-----------|-------------|
| `courseId` | integer | `\d+` | `lms_course` entity ID |

### Response 200
```json
{
  "id": 7,
  "uuid": "...",
  "title": "JavaScript Fundamentals",
  "description": "Full HTML description",
  "short_description": "Learn JS from scratch",
  "what_learn": ["Variables", "Functions", "Async"],
  "prerequisites": ["HTML basics"],
  "who_for": "Beginners",
  "thumbnail_url": "https://...",
  "banner_url": "https://...",
  "price": 199000,
  "price_type": "paid",
  "level": {"id": 3, "name": "Beginner"},
  "category": {"id": 5, "name": "Programming"},
  "tags": ["javascript", "web"],
  "instructors": [{"id": 12, "name": "Tran Van B", "avatar_url": "...", "bio": "..."}],
  "rating": 4.7,
  "rating_count": 234,
  "student_count": 1023,
  "duration_minutes": 480,
  "bestseller": true,
  "featured": false,
  "created": "2025-12-01T08:00:00Z",
  "curriculum": [
    {
      "id": 20,
      "title": "Chapter 1: Getting Started",
      "description": "...",
      "weight": 1,
      "lessons": [
        {
          "id": 45,
          "title": "Introduction to Variables",
          "type": "video",
          "duration_minutes": 12,
          "free_preview": true,
          "is_completed": false,
          "is_locked": false
        }
      ]
    }
  ],
  "enrollment": {
    "enrolled": true,
    "progress": 35.5,
    "status": "in_progress",
    "last_lesson_id": 47,
    "last_lesson_url": "/api/v1/courses/7/lessons/47"
  },
  "is_wishlisted": true,
  "certificate_template_id": 3
}
```

**Note:** `enrollment` is `null` when not enrolled or anonymous. Drip-lock for lessons calculated from `field_drip_days` vs `field_enrolled_at` timestamp. `is_wishlisted` requires `lms_wishlist` entity storage (implicit dependency — not in `lms_mobile_api.info.yml`).

### Response 404
```json
{ "error": "not_found", "message": "Course not found." }
```

---

## Endpoint: POST /api/v1/courses/{courseId}/enroll-free

**Route:** `lms_mobile_api.course.enroll_free`
**Controller:** `MobileCourseController::enrollFree`
**Authentication:** Bearer token required
**Path Parameters:** `courseId` (`\d+`)

### Response 200
```json
{
  "status": "enrolled",
  "course_id": 7,
  "enrollment_id": 88,
  "message": "Successfully enrolled in the course.",
  "first_lesson_id": 45
}
```

### Error Responses
| Status | Code | When |
|--------|------|------|
| 403 | — | Course is not free (`field_price_type !== 'free'`) |
| 400 | `already_enrolled` | Existing `lms_user_course` record found |
| 404 | — | Course not found or not published |

**Logic:** Checks course exists, published, and free; checks no existing enrollment; finds first published lesson; creates `lms_user_course` with `field_progress = 0.0`, `field_status = 'enrolled'`.

---

## Endpoint: GET /api/v1/dashboard

**Route:** `lms_mobile_api.dashboard`
**Controller:** `MobileCourseController::getDashboard`
**Authentication:** Bearer token required

### Response 200
```json
{
  "in_progress_courses": [
    {
      "id": 7,
      "title": "JavaScript Fundamentals",
      "thumbnail_url": "https://...",
      "progress": 35.5,
      "last_lesson_id": 47,
      "last_lesson_title": "Functions Deep Dive"
    }
  ],
  "completed_courses_count": 2,
  "certificates_count": 2,
  "enrolled_courses_count": 5
}
```

**Logic:** Loads all `lms_user_course` for user sorted by `field_enrolled_at DESC`; computes progress via `calculateProgress()`; up to 5 in-progress courses returned; counts `lms_user_certificate` entries.

---

## Endpoint: GET /api/v1/my-courses

**Route:** `lms_mobile_api.my_courses`
**Controller:** `MobileCourseController::getMyCourses`
**Authentication:** Bearer token required

### Query Parameters
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `status` | string | `all` | `all`, `active`, `completed` |
| `page` | integer | 0 | 0-based |
| `limit` | integer | 20 | Max 100 |

### Response 200
```json
{
  "total": 5,
  "page": 0,
  "limit": 20,
  "items": [
    {
      "enrollment_id": 88,
      "course_id": 7,
      "course_title": "JavaScript Fundamentals",
      "course_thumbnail_url": "https://...",
      "instructor_name": "Tran Van B",
      "progress": 35.5,
      "status": "in_progress",
      "enrolled_at": "2026-01-15T10:00:00Z",
      "completed_at": null,
      "last_lesson_id": 47,
      "last_lesson_title": "Functions Deep Dive",
      "total_lessons": 24,
      "completed_lessons": 8
    }
  ]
}
```

---

## Endpoint: GET /api/v1/courses/{courseId}/lessons/{lessonId}

**Route:** `lms_mobile_api.lesson.detail`
**Controller:** `MobileLearningController::getLessonDetail`
**Authentication:** Optional — required unless `field_free_preview = TRUE`
**Path Parameters:**

| Param | Type | Constraint | Description |
|-------|------|-----------|-------------|
| `courseId` | integer | `\d+` | `lms_course` entity ID |
| `lessonId` | integer | `\d+` | `lms_lesson` entity ID |

### Response 200
```json
{
  "id": 45,
  "uuid": "...",
  "title": "Introduction to Variables",
  "type": "video",
  "duration_minutes": 12,
  "free_preview": true,
  "is_completed": false,
  "is_locked": false,
  "drip_days": 0,
  "content": {
    "video_url": "https://youtube.com/watch?v=...",
    "video_file_url": null,
    "text_body": null,
    "h5p_embed": null,
    "scorm_url": null,
    "pdf_url": null,
    "pptx_url": null,
    "zoom_meeting_id": null,
    "zoom_schedule": null
  },
  "attachments": [
    {"name": "slides.pdf", "url": "https://...", "mime_type": "application/pdf", "size_bytes": 204800}
  ],
  "navigation": {
    "prev_lesson_id": null,
    "next_lesson_id": 46,
    "course_id": 7
  },
  "curriculum": [...]
}
```

### Error Responses
| Status | Code | When |
|--------|------|------|
| 403 | — | Not enrolled and not free preview |
| 423 | `lesson_locked` | Drip-lock active — `{"error":"lesson_locked","message":"...","unlocks_at":"ISO8601"}` |
| 404 | — | Course/lesson not found, not published, or lesson not in course |

---

## Endpoint: GET /api/v1/quiz/{quizId}

**Route:** `lms_mobile_api.quiz.detail`
**Controller:** `MobileLearningController::getQuiz`
**Authentication:** Bearer token required
**Path Parameters:** `quizId` (`\d+`)

### Response 200
```json
{
  "id": 11,
  "uuid": "...",
  "title": "JS Variables Quiz",
  "instructions": "Answer all questions within the time limit.",
  "time_limit_minutes": 30,
  "threshold_percent": 70,
  "form_style": "single_page",
  "display_right_answer": true,
  "attempts_allowed": 3,
  "attempts_used": 1,
  "can_attempt": true,
  "questions": [
    {
      "id": 101,
      "bundle": "single_choice",
      "text": "What keyword declares a variable in modern JS?",
      "details": null,
      "points": 1,
      "explanation": null,
      "choices": [
        {"id": "201", "text": "var"},
        {"id": "202", "text": "let"},
        {"id": "203", "text": "const"}
      ]
    }
  ]
}
```

**Question shapes by bundle:**

| Bundle | Extra Fields |
|--------|-------------|
| `single_choice` | `choices: [{id, text}]` |
| `multiple_choice` | `choices: [{id, text}]` |
| `true_false` | `choices: [{id: "true", text: "True"}, {id: "false", text: "False"}]` |
| `matching` | `left_items: [{id, text}]`, `right_items: [{id, text}]` |
| `ordering` | `items: [{text}]` (shuffled) |
| `fill_blank` / `short_answer` / `essay` | `placeholder: string`, `max_words: int` |

**Implicit dependency:** `MobileLearningController` uses `GradingStrategyFactory` from `lms_quiz` module via PHP `use` statement, but `lms_quiz` is NOT listed in `lms_mobile_api.info.yml` dependencies. If `lms_quiz` is disabled, this controller will fail at class loading.

---

## Endpoint: POST /api/v1/quiz/{quizId}/submit

**Route:** `lms_mobile_api.quiz.submit`
**Controller:** `MobileLearningController::submitQuiz`
**Authentication:** Bearer token required
**Path Parameters:** `quizId` (`\d+`)

### Request
```json
{
  "answers": [
    {"question_id": 101, "selected": 202},
    {"question_id": 102, "selected": [301, 303]},
    {"question_id": 103, "selected": true},
    {"question_id": 104, "order": ["item3", "item1", "item2"]},
    {"question_id": 105, "pairs": [{"left": "A", "right": "X"}, {"left": "B", "right": "Y"}]},
    {"question_id": 106, "text": "let keyword"},
    {"question_id": 107, "text": "Essay response here"}
  ],
  "time_taken_seconds": 450
}
```

**Answer format by bundle:**

| Bundle | Field | Type |
|--------|-------|------|
| `single_choice` | `selected` | int (answer entity ID) |
| `multiple_choice` | `selected` | int[] |
| `true_false` | `selected` | bool |
| `ordering` | `order` | string[] (item texts in submitted order) |
| `matching` | `pairs` | `[{left, right}]` |
| `fill_blank` / `short_answer` / `essay` | `text` | string |

### Response 200
```json
{
  "result_id": 77,
  "score_percent": 75.0,
  "passed": true,
  "status": "passed",
  "threshold_percent": 70,
  "time_taken_seconds": 450,
  "attempt_number": 2,
  "message": "Congratulations! You passed the quiz."
}
```

**Logic:**
1. Checks attempt limit (`field_take_quiz_number` vs count of `lms_quiz_result`).
2. Iterates questions; calls `GradingStrategyFactory::getStrategy(bundle)` for each → grading is strictly binary (no partial credit); essay → `pending_review`.
3. Creates `lms_question_response` entity per question.
4. Calculates score percentage; determines status from `quiz_results` taxonomy term.
5. Creates `lms_quiz_result` entity.
6. If passed: dispatches `QuizPassedEvent` (conditional on class existence) → triggers certificate issuance via `CertificateEventListener`.

**Essay status:** `status = 'pending'` when any essay/short_answer question is pending review.

---

## Endpoint: GET /api/v1/quiz/results/{resultId}

**Route:** `lms_mobile_api.quiz.result`
**Controller:** `MobileLearningController::getQuizResult`
**Authentication:** Bearer token required; ownership enforced (403 if uid mismatch)
**Path Parameters:** `resultId` (`\d+`)

### Response 200
```json
{
  "id": 77,
  "quiz_id": 11,
  "quiz_title": "JS Variables Quiz",
  "score_percent": 75.0,
  "passed": true,
  "status": "passed",
  "threshold_percent": 70,
  "attempt_number": 2,
  "time_taken_seconds": 450,
  "completed_at": "2026-06-20T10:30:00Z",
  "can_retry": true,
  "question_feedback": [
    {
      "question_id": 101,
      "question_text": "What keyword declares a variable?",
      "bundle": "single_choice",
      "your_answer": 202,
      "correct_answer": 202,
      "is_correct": true,
      "points_earned": 1,
      "points_possible": 1,
      "teacher_feedback": null
    }
  ]
}
```

**Note:** `question_feedback` is only populated when quiz `field_display_right_answer = TRUE`.

---

## Endpoint: GET /api/v1/quiz/my-results

**Route:** `lms_mobile_api.quiz.my_results`
**Controller:** `MobileLearningController::listMyQuizResults`
**Authentication:** Bearer token required

### Query Parameters
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `page` | integer | 0 | 0-based |
| `limit` | integer | 20 | Max 100 |

### Response 200
```json
{
  "total": 8,
  "page": 0,
  "limit": 20,
  "items": [
    {
      "id": 77,
      "quiz_id": 11,
      "quiz_title": "JS Variables Quiz",
      "course_id": 7,
      "course_title": "JavaScript Fundamentals",
      "score_percent": 75.0,
      "status": "passed",
      "attempt_number": 2,
      "completed_at": "2026-06-20T10:30:00Z"
    }
  ]
}
```

**Logic:** `findCourseForQuiz()` reverse-traverses quiz → lesson (via `field_quiz`) → chapter (via `field_lessons`) → course (via `field_chapters`) via entity queries.

---

## Endpoint: POST /api/v1/progress/mark-complete

**Route:** `lms_mobile_api.progress.mark_complete`
**Controller:** `MobileLearningController::markLessonComplete`
**Authentication:** Bearer token required

### Request
```json
{ "course_id": 7, "lesson_id": 45 }
```

### Response 200
```json
{
  "success": true,
  "lesson_completed": true,
  "course_completed": false,
  "progress": 37.5,
  "next_lesson_id": 46,
  "next_lesson_url": "/api/v1/courses/7/lessons/46"
}
```

**Logic:**
1. Validates course + lesson exist and published; checks enrollment; confirms lesson belongs to course.
2. Creates or updates `lms_user_lesson` record (`field_status = 'completed'`, `field_completed_at = now()`).
3. Recalculates progress: counts completed lessons / total lessons × 100.
4. Updates `lms_user_course.field_progress`.
5. If progress = 100%: sets `lms_user_course.field_status = 'completed'`, sets `field_completed_at`.
6. If progress > 0% and status was `enrolled`: sets status = `in_progress`.
7. Finds next lesson ID (first incomplete lesson in course order).

### Error Responses
| Status | Code | When |
|--------|------|------|
| 403 | — | Not enrolled |
| 404 | — | Course/lesson not found |
| 400 | — | Lesson not in course |

---

## OTP Service Reference

`lms_mobile_api.otp_service` (`OtpService`) — keyvalue-backed token management:

| Collection | TTL | Keys |
|-----------|-----|------|
| `lms_mobile_api.otp` | 900s (15 min) | OTP sessions: `{otp_token => {uid, otp_code, type: 'register'|'forgot_password'}}` |
| `lms_mobile_api.reset` | 1800s (30 min) | Reset sessions: `{reset_token => {uid}}` |
| `lms_mobile_api.tokens` | 604800s (7 days) | Access tokens: `{token => uid}` |

**OTP code:** 6-digit numeric via `random_int(100000, 999999)`
**OTP session token:** 64-char hex via `bin2hex(random_bytes(32))`
**Access token:** 32-char hex via `bin2hex(random_bytes(16))`

**Email sending:** `hook_mail` key `otp_email` — two subtypes handled: `register` subject "Your EverLMS verification code" and `forgot_password` subject "Your EverLMS password reset code". Sent via `plugin.manager.mail`.

---

## Error Response Shape

All error responses follow this shape:
```json
{
  "error": "error_code",
  "message": "Human readable message"
}
```

With optional violations array for validation errors:
```json
{
  "error": "validation_error",
  "message": "...",
  "violations": [{"field": "email", "message": "..."}]
}
```

Standard HTTP status codes used:

| Status | When |
|--------|------|
| 200 | Success |
| 400 | Bad request / invalid input |
| 401 | Invalid credentials |
| 403 | Missing/invalid/expired bearer token (AccessDeniedHttpException) |
| 404 | Resource not found |
| 422 | Validation error |
| 423 | Lesson locked (drip schedule) |

---

## API Route Summary

| Method | Path | Route Name | Auth | Status |
|--------|------|-----------|------|--------|
| POST | `/api/v1/user/register` | `lms_mobile_api.user.register` | Public | as-built |
| POST | `/api/v1/user/register/verify-otp` | `lms_mobile_api.user.register.verify_otp` | Public | as-built |
| POST | `/api/v1/user/register/resend-otp` | `lms_mobile_api.user.register.resend_otp` | Public | as-built |
| POST | `/api/v1/user/forgot-password` | `lms_mobile_api.user.forgot_password` | Public | as-built |
| POST | `/api/v1/user/forgot-password/verify-otp` | `lms_mobile_api.user.forgot_password.verify_otp` | Public | as-built |
| POST | `/api/v1/user/reset-password` | `lms_mobile_api.user.reset_password` | Public (reset_token) | as-built |
| POST | `/api/v1/oauth/token` | `lms_mobile_api.oauth.token` | Public (credentials) | as-built |
| GET | `/api/v1/user/profile` | `lms_mobile_api.user.profile` | Bearer | as-built |
| GET | `/api/v1/user/me` | `lms_mobile_api.user.me` | Bearer | as-built |
| POST | `/api/v1/user/change-password` | `lms_mobile_api.user.change_password` | Bearer | as-built |
| GET | `/api/v1/courses/search` | `lms_mobile_api.courses.search` | Public | as-built |
| GET | `/api/v1/categories` | `lms_mobile_api.categories` | Public | as-built |
| GET | `/api/v1/courses/{courseId}` | `lms_mobile_api.course.detail` | Optional | as-built |
| POST | `/api/v1/courses/{courseId}/enroll-free` | `lms_mobile_api.course.enroll_free` | Bearer | as-built |
| GET | `/api/v1/dashboard` | `lms_mobile_api.dashboard` | Bearer | as-built |
| GET | `/api/v1/my-courses` | `lms_mobile_api.my_courses` | Bearer | as-built |
| GET | `/api/v1/courses/{courseId}/lessons/{lessonId}` | `lms_mobile_api.lesson.detail` | Optional | as-built |
| GET | `/api/v1/quiz/{quizId}` | `lms_mobile_api.quiz.detail` | Bearer | as-built |
| POST | `/api/v1/quiz/{quizId}/submit` | `lms_mobile_api.quiz.submit` | Bearer | as-built |
| GET | `/api/v1/quiz/results/{resultId}` | `lms_mobile_api.quiz.result` | Bearer | as-built |
| GET | `/api/v1/quiz/my-results` | `lms_mobile_api.quiz.my_results` | Bearer | as-built |
| POST | `/api/v1/progress/mark-complete` | `lms_mobile_api.progress.mark_complete` | Bearer | as-built |

**Total: 22 routes**

---

## Known Issues

| Ref | Severity | Description |
|-----|----------|-------------|
| `getAuthenticatedUser()` triplicated | Medium | Identical private method in all 3 controllers — code duplication, refactor candidate. |
| `lms_quiz` undeclared dependency | High | `MobileLearningController` imports `GradingStrategyFactory` and `QuizPassedEvent` from `lms_quiz` via PHP `use` — not in `lms_mobile_api.info.yml`. Module install order issue. |
| `lms_wishlist` undeclared dependency | Medium | `getCourseDetail()` queries `lms_wishlist` entity storage — not in `lms_mobile_api.info.yml`. |
| No refresh token | Medium | 7-day bearer token expires with no refresh path — user must re-authenticate via `/api/v1/oauth/token`. |
| Non-standard auth header | Medium | Uses `X-Authorization` header (checked first) as workaround for server Basic Auth gates — non-standard and must be documented for Flutter Dio configuration. |
| No CORS configuration | Medium | Module does not configure CORS headers — Flutter app on different domain may fail without `sites/default/services.yml` CORS config. |
| Unused config keys | Low | `oauth_client_id` / `oauth_client_secret` in `lms_mobile_api.settings.yml` are never read — leftover from earlier OAuth2 design. |
| No config schema | Low | No `config/schema/lms_mobile_api.schema.yml` — config is not validated. |
| Standard envelope absent | Low | Responses do not use a consistent `{data, error, meta}` envelope — raw flat JSON objects. Clients cannot assume a standard shape. |
