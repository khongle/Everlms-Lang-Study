---
module: lms_mobile_api
service: Mobile API
route-groups: [API]
status: DONE
documentation-mode: as-built
---

# C05 — Module: lms_mobile_api

**Directory:** `web/modules/custom/lms_mobile_api/`
**Responsibility:** Provides a JSON REST API at `/api/v1/` for the EverLMS Flutter student app, covering authentication (OTP + custom bearer-token), course browsing, and lesson/quiz/progress flows.

> This module was not in any prior draft document. All facts are sourced from `modules-mobile.md` (scout date 2026-06-20).

---

## Route Bindings

| Route Group | Prefix | Controller | Paths |
|-------------|--------|------------|-------|
| API | `/api/v1/user/*`, `/api/v1/oauth` | `MobileAuthController` | 10 auth routes (register, OTP verify/resend, forgot-password, reset, login, profile, me, change-password) |
| API | `/api/v1/courses`, `/api/v1/categories`, `/api/v1/dashboard`, `/api/v1/my-courses` | `MobileCourseController` | 6 course routes |
| API | `/api/v1/courses/{courseId}/lessons`, `/api/v1/quiz`, `/api/v1/progress` | `MobileLearningController` | 6 learning routes |

All 22 routes declare `_access: 'TRUE'` (no Drupal permission check). All require `_format: json`. Authentication is enforced **inside controllers** by reading a bearer token from `keyvalue.expirable`.

---

## Route Table

### Auth routes — `MobileAuthController`

| Route Name | Method | Path | Controller Method | Auth Required |
|-----------|--------|------|------------------|---------------|
| `lms_mobile_api.user.register` | POST | `/api/v1/user/register` | `register()` | No |
| `lms_mobile_api.user.register.verify_otp` | POST | `/api/v1/user/register/verify-otp` | `verifyRegisterOtp()` | No |
| `lms_mobile_api.user.register.resend_otp` | POST | `/api/v1/user/register/resend-otp` | `resendRegisterOtp()` | No |
| `lms_mobile_api.user.forgot_password` | POST | `/api/v1/user/forgot-password` | `forgotPassword()` | No (email always 200 — anti-enumeration) |
| `lms_mobile_api.user.forgot_password.verify_otp` | POST | `/api/v1/user/forgot-password/verify-otp` | `verifyForgotPasswordOtp()` | No |
| `lms_mobile_api.user.reset_password` | POST | `/api/v1/user/reset-password` | `resetPassword()` | No (reset_token in body) |
| `lms_mobile_api.oauth.token` | POST | `/api/v1/oauth/token` | `oauthToken()` | No (credentials in body) |
| `lms_mobile_api.user.profile` | GET | `/api/v1/user/profile` | `getProfile()` | Yes — Bearer |
| `lms_mobile_api.user.me` | GET | `/api/v1/user/me` | `getUserMe()` | Yes — Bearer |
| `lms_mobile_api.user.change_password` | POST | `/api/v1/user/change-password` | `changePassword()` | Yes — Bearer |

### Course routes — `MobileCourseController`

| Route Name | Method | Path | Controller Method | Auth Required |
|-----------|--------|------|------------------|---------------|
| `lms_mobile_api.courses.search` | GET | `/api/v1/courses/search` | `searchCourses()` | No |
| `lms_mobile_api.categories` | GET | `/api/v1/categories` | `listCategories()` | No |
| `lms_mobile_api.course.detail` | GET | `/api/v1/courses/{courseId}` | `getCourseDetail()` | Optional (richer data when authed) |
| `lms_mobile_api.course.enroll_free` | POST | `/api/v1/courses/{courseId}/enroll-free` | `enrollFree()` | Yes — Bearer |
| `lms_mobile_api.dashboard` | GET | `/api/v1/dashboard` | `getDashboard()` | Yes — Bearer |
| `lms_mobile_api.my_courses` | GET | `/api/v1/my-courses` | `getMyCourses()` | Yes — Bearer |

Path constraint on `courseId`: `\d+`

### Learning routes — `MobileLearningController`

| Route Name | Method | Path | Controller Method | Auth Required |
|-----------|--------|------|------------------|---------------|
| `lms_mobile_api.lesson.detail` | GET | `/api/v1/courses/{courseId}/lessons/{lessonId}` | `getLessonDetail()` | Optional (required unless `free_preview=TRUE`) |
| `lms_mobile_api.quiz.detail` | GET | `/api/v1/quiz/{quizId}` | `getQuiz()` | Yes — Bearer |
| `lms_mobile_api.quiz.submit` | POST | `/api/v1/quiz/{quizId}/submit` | `submitQuiz()` | Yes — Bearer |
| `lms_mobile_api.quiz.result` | GET | `/api/v1/quiz/results/{resultId}` | `getQuizResult()` | Yes — Bearer (ownership enforced) |
| `lms_mobile_api.quiz.my_results` | GET | `/api/v1/quiz/my-results` | `listMyQuizResults()` | Yes — Bearer |
| `lms_mobile_api.progress.mark_complete` | POST | `/api/v1/progress/mark-complete` | `markLessonComplete()` | Yes — Bearer |

Path constraints: `courseId`, `lessonId`, `quizId`, `resultId` all `\d+`

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `OtpService` | Service (`lms_mobile_api.otp_service`) | Generates and stores OTP codes + session tokens; sends OTP email via `plugin.manager.mail` |

---

## Auth Mechanism — Custom keyvalue Bearer Token

**Status: as-built — NOT Simple OAuth / NOT JWT despite leftover config keys named `oauth_client_id/secret`.**

| Concern | Implementation |
|---------|----------------|
| Token store | `keyvalue.expirable` collection `lms_mobile_api.tokens` |
| Token format | 32-char hex — `bin2hex(random_bytes(16))` |
| Default TTL | 604800 s (7 days); configurable via `lms_mobile_api.settings:token_expiry_seconds` |
| Token issuance | `MobileAuthController::issueAccessToken(int $uid)` — called after OTP verify, reset-password, and `oauthToken` success |
| Token resolution | Read `X-Authorization` header first; fall back to `Authorization`; match `Bearer <token>`; look up UID in keyvalue; load and validate user active status |
| Drupal sessions | Mobile clients do NOT use Drupal session cookies |
| Refresh token | **NOT implemented** — tokens expire and cannot be renewed without re-login |
| Password check | Drupal core `password` service — `\Drupal::service('password')->check(...)` |

**OTP sessions:**

| Store | Collection | TTL | Token length |
|-------|-----------|-----|-------------|
| OTP | `lms_mobile_api.otp` | 900 s (15 min) | 64-char hex |
| Password reset | `lms_mobile_api.reset` | 1800 s (30 min) | 64-char hex |

**X-Authorization header workaround:** The server environment strips the `Authorization` header (HTTP Basic Auth gate). The module reads `X-Authorization` first and falls back to `Authorization`. Flutter clients **must** send `X-Authorization: Bearer <token>`, not the standard `Authorization` header.

---

## Internal Structure

```
web/modules/custom/lms_mobile_api/
+-- lms_mobile_api.info.yml          # Package: LMS; dep: drupal:user only
+-- lms_mobile_api.routing.yml       # 22 /api/v1/* routes; all _access: TRUE
+-- lms_mobile_api.services.yml      # Registers lms_mobile_api.otp_service
+-- lms_mobile_api.module            # hook_mail: otp_email key (register + forgot_password)
+-- config/
|   +-- install/
|       +-- lms_mobile_api.settings.yml  # otp_expiry_seconds=900, reset_token_expiry_seconds=1800
|                                         # oauth_client_id/secret = '' (unused — artifact)
+-- src/
    +-- Service/
    |   +-- OtpService.php           # OTP generation, keyvalue session management, email dispatch
    +-- Controller/
        +-- MobileAuthController.php  # 10 auth endpoints + issueAccessToken() + getAuthenticatedUser()
        +-- MobileCourseController.php # 6 course/dashboard endpoints
        +-- MobileLearningController.php # 6 lesson/quiz/progress endpoints
```

No `permissions.yml`, no `links.*.yml`, no `config/schema/`, no `templates/`, no install file, no custom entity classes.

---

## Key Endpoint Details

### MobileAuthController

| Endpoint | Request | Response (success) |
|----------|---------|-------------------|
| POST /api/v1/user/register | `{name?, email, password, password_confirm}` | `{status:"otp_sent", message, otp_token}` |
| POST /api/v1/user/register/verify-otp | `{otp_token, otp_code}` | `{status:"verified", uid, access_token, token_type:"Bearer", expires_in}` |
| POST /api/v1/user/register/resend-otp | `{otp_token}` | `{status:"otp_resent", otp_token}` (new token) |
| POST /api/v1/user/forgot-password | `{email}` | Always 200 — `{status:"otp_sent", otp_token}` (empty token on no-op) |
| POST /api/v1/user/forgot-password/verify-otp | `{otp_token, otp_code}` | `{reset_token}` |
| POST /api/v1/user/reset-password | `{reset_token, password, password_confirm}` | `{status:"success", uid, access_token, token_type:"Bearer", expires_in}` |
| POST /api/v1/oauth/token | `{username (email), password}` | `{uid, email, name, access_token, token_type:"Bearer", expires_in}` |
| GET /api/v1/user/profile | Bearer | `{uid, email, name}` |
| GET /api/v1/user/me | Bearer | `{id, uuid, name, email, first_name, last_name, avatar_url, phone, gender, date_of_birth, roles}` |
| POST /api/v1/user/change-password | `{current_password, new_password, new_password_confirm}` | `{status:"success", message}` |

### MobileCourseController

| Endpoint | Key query params | Response shape |
|----------|-----------------|----------------|
| GET /api/v1/courses/search | `search`, `category[]`, `level[]`, `price[]`, `sort`, `page`, `limit`, `featured` | `{total, page, limit, items: CourseListItem[]}` |
| GET /api/v1/categories | — | `{items: [{id, uuid, name, description, thumbnail_url, course_count}]}` |
| GET /api/v1/courses/{courseId} | — | Rich object: basics + curriculum + enrollment + is_wishlisted + certificate_template_id |
| POST /api/v1/courses/{courseId}/enroll-free | Bearer | `{status:"enrolled", course_id, enrollment_id, first_lesson_id}` |
| GET /api/v1/dashboard | Bearer | `{in_progress_courses, completed_courses_count, certificates_count, enrolled_courses_count}` |
| GET /api/v1/my-courses | Bearer; `status=all|completed|active`, `page`, `limit` | `{total, page, limit, items: MyCourseSummary[]}` |

### MobileLearningController

| Endpoint | Notes |
|----------|-------|
| GET /api/v1/courses/{courseId}/lessons/{lessonId} | Returns `ContentObject` (video_url, text_body, h5p_embed, scorm_url, pdf_url, pptx_url, zoom data) + attachments + navigation + curriculum; HTTP 423 on drip-lock |
| GET /api/v1/quiz/{quizId} | Returns quiz meta + `questions[]` with per-bundle shapes (choices, pairs, items) |
| POST /api/v1/quiz/{quizId}/submit | `{answers[], time_taken_seconds?}`; grades via `GradingStrategyFactory`; creates `lms_quiz_result`; dispatches `QuizPassedEvent` if passed |
| GET /api/v1/quiz/results/{resultId} | Per-question feedback if `field_display_right_answer=TRUE` |
| GET /api/v1/quiz/my-results | `{total, page, limit, items[]}` — reverse-traverses quiz→lesson→chapter→course |
| POST /api/v1/progress/mark-complete | `{course_id, lesson_id}`; upserts `lms_user_lesson`; recalculates `lms_user_course.field_progress`; `{success, lesson_completed, course_completed, progress, next_lesson_id}` |

---

## OtpService

| Method | Description |
|--------|-------------|
| `generateOtpCode(): string` | 6-digit numeric (`random_int(100000, 999999)`) |
| `generateToken(): string` | 64-char hex (`bin2hex(random_bytes(32))`) |
| `storeOtpSession(token, data, ttl): void` | Keyvalue `lms_mobile_api.otp` |
| `getOtpSession(token): ?array` | Returns session data or NULL |
| `deleteOtpSession(token): void` | Removes OTP session |
| `storeResetSession(token, data, ttl): void` | Keyvalue `lms_mobile_api.reset` |
| `getResetSession(token): ?array` | Returns reset data or NULL |
| `deleteResetSession(token): void` | Removes reset session |
| `sendOtpEmail(to, otp_code, type='register'): bool` | Mail via `plugin.manager.mail`; keys: `otp_email` / `register` or `forgot_password` |

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `drupal:user` | Drupal core | User entity loading and password service |
| `keyvalue.expirable` | Drupal core service | Bearer token and OTP session storage |
| `plugin.manager.mail` | Drupal core service | OTP email dispatch |
| `language_manager` | Drupal core service | Default language for mail |
| `config.factory` | Drupal core service | Read `lms_mobile_api.settings` |
| `entity_type.manager` | Drupal core service | Entity queries (lms_course, lms_user_course, lms_quiz, etc.) |
| `file_url_generator` | Drupal core service | Absolute file URLs for images, attachments |
| `logger.factory` | Drupal core service | Error/debug logging |
| `lms_quiz` | Internal — **UNDECLARED** | `MobileLearningController` imports `GradingStrategyFactory` and `QuizPassedEvent` via PHP `use` statements; **NOT listed in `lms_mobile_api.info.yml` dependencies** |
| `lms_wishlist` | Internal — **UNDECLARED** | `getCourseDetail()` queries `lms_wishlist` entity storage; **NOT listed in dependencies** |

**RULE violation:** `lms_quiz` and `lms_wishlist` are undeclared module dependencies. If either module is disabled before `lms_mobile_api`, PHP fatal errors will occur at runtime.

---

## Implementation Status

| Component | Status | Notes |
|-----------|--------|-------|
| `OtpService` | DONE | Fully implemented, no TODOs/stubs |
| `MobileAuthController` | DONE | All 10 endpoint methods + private helpers real |
| `MobileCourseController` | DONE | All 6 endpoint methods + 12 helpers real |
| `MobileLearningController` | DONE | All 6 endpoint methods + 14 helpers real |
| `hook_mail` | DONE | OTP email for `register` and `forgot_password` |
| Token auth mechanism | DONE (custom/bespoke) | No OAuth2 library; keyvalue-only |
| Refresh token | MISSING | Tokens expire with no renewal path |
| No config schema | BUG/GAP | `config/schema/` directory absent — config not schema-validated |
| `getAuthenticatedUser()` | CODE SMELL | Identical private method triplicated across all 3 controllers — refactor candidate |
| Undeclared `lms_quiz` dep | RISK | PHP fatal if `lms_quiz` disabled first |
| Undeclared `lms_wishlist` dep | RISK | PHP fatal if `lms_wishlist` disabled first |
| `oauth_client_id/secret` in settings | DEAD CONFIG | Keys exist but are never read in PHP code — artifact of earlier OAuth2 design |
| No CORS config | GAP | Module does not set CORS headers; must be configured in `sites/default/services.yml` |

---

## Known Issues

See [E02-issues/](../../E-Quality/E02-issues/):

1. **Undeclared module dependencies:** `lms_quiz` and `lms_wishlist` used but not declared in `lms_mobile_api.info.yml`.
2. **Dead config keys:** `oauth_client_id`/`oauth_client_secret` in `config/install/lms_mobile_api.settings.yml` are never read.
3. **No token refresh:** Bearer tokens expire after 7 days with no renewal endpoint; clients must fully re-authenticate.
4. **No config schema:** `lms_mobile_api.settings` has no corresponding `config/schema/` file.
5. **X-Authorization header workaround:** Non-standard header required because the HTTP server strips `Authorization`; this must be documented in the Flutter app client.
6. **CORS not configured:** No CORS headers set by this module.

---

## Cross-Links

- Flutter app: `app/` directory (v1.0.10+33, 33 screens, Riverpod/GoRouter/Dio) — see `themes-app.md` facts
- Grading strategies consumed: [lms_quiz](lms_quiz.md) — `GradingStrategyFactory` (static PHP factory, not a Drupal service)
- Entities queried: [lms_course](../C04-entities/01-content-entities.md), `lms_user_course`, `lms_user_lesson`, `lms_quiz_result`, `lms_question_response`, `lms_user_certificate`, `lms_wishlist`
- Module map: [C03 — Entity-Module Map](../C03-entity-module-map.md)
