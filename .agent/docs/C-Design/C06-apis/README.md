# C06 — API Catalog

> Index of all HTTP API contracts for EverLMS. Covers every route that returns JSON (REST/AJAX) and the mobile REST layer. HTML page routes are excluded; see [C07-sitemap](../C07-sitemap.md) for those.
>
> **Documentation mode:** as-built (grounded in `lms_*` module routing.yml files, controllers, and facts base; 2026-06-20).

---

## Architecture Overview

EverLMS does not use Drupal's REST module, JSON:API, or GraphQL. All API endpoints are plain Drupal routes that return `JsonResponse` from custom controllers. There are two distinct API surface areas:

| Layer | Prefix | Auth | Consumer |
|-------|--------|------|----------|
| Web / Internal API | `/api/lms/*`, `/api/courses/*` | Drupal session cookie + CSRF token (POST) | Browser AJAX (lesson view, note widget, notification bell) |
| Mobile REST API | `/api/v1/*` | Custom bearer token (keyvalue.expirable + OTP — **not** OAuth2/JWT) | Flutter app (`app/` v1.0.10+33) |

**Authentication note (mobile):** All `/api/v1/*` routes declare `_access: 'TRUE'` in routing.yml, but the `MobileAuthController` and peer controllers enforce bearer token validation at the controller level. The `/api/v1/oauth/token` route issues the token after OTP verification; subsequent requests send `Authorization: Bearer {token}`.

**No webhook endpoints exist** anywhere in the codebase. Stripe/PayPal webhook signature verification is a documented TODO — see security risk in G02-risk-register (planned, layer not yet materialized) (planned, layer G — not yet materialized).

---

## Route Count Summary

| Group | Count | Prefix | Module(s) |
|-------|-------|--------|-----------|
| Web Internal | 9 | `/api/lms/*`, `/api/courses/*` | lms_course, lms_progress, lms_note, lms_notification, lms_wishlist, lms_zoom |
| Mobile REST | 22 | `/api/v1/*` | lms_mobile_api |
| **Total** | **31** | | |

---

## Web / Internal API Routes

All return JSON. Session-authenticated (Drupal session cookie). POST requests require Drupal CSRF token (`X-CSRF-Token` header from `/session/token`).

| Method | Path | Route Name | Controller | Auth | Module | Detail |
|--------|------|------------|------------|------|--------|--------|
| GET | `/api/courses/search` | `lms_course.api_search` | `CoursePublicController::searchApi` | Public (`access content`) | lms_course | [01-content-api](01-content-api.md) |
| POST | `/api/lms/progress/mark-complete` | `lms_progress.api_mark_complete` | `ProgressApiController::markComplete` | Session (logged_in) | lms_progress | [03-progress-api](03-progress-api.md) |
| POST | `/api/lms/note/save` | `lms_note.save` | `NoteController::save` | Session (logged_in) | lms_note | [03-progress-api](03-progress-api.md) |
| POST | `/api/lms/note/delete` | `lms_note.delete` | `NoteController::delete` | Session (logged_in) | lms_note | [03-progress-api](03-progress-api.md) |
| GET | `/api/lms/note/get` | `lms_note.get` | `NoteController::getNote` | Session (logged_in) | lms_note | [03-progress-api](03-progress-api.md) |
| GET | `/api/lms/notifications/unread-count` | `lms_notification.api_unread_count` | `NotificationController::apiUnreadCount` | Public (`TRUE`) | lms_notification | — |
| POST | `/api/lms/wishlist/toggle` | `lms_wishlist.toggle` | `WishlistController::toggle` | Session (logged_in) | lms_wishlist | — |
| GET | `/api/lms/wishlist/status` | `lms_wishlist.status` | `WishlistController::getStatus` | Session (logged_in) | lms_wishlist | — |
| POST | `/api/lms/websdk/signature` | `lms_zoom.generate_signature` | `ZoomController::generateSignature` | Session (logged_in) | lms_zoom | — |

---

## Mobile REST API Routes (`/api/v1/*`)

All served by `lms_mobile_api` module. Base controller classes: `MobileAuthController`, `MobileCourseController`, `MobileLearningController`.

### Auth Endpoints

| Method | Path | Route Name | Controller | Notes |
|--------|------|------------|------------|-------|
| POST | `/api/v1/user/register` | `lms_mobile_api.user.register` | `MobileAuthController::register` | Creates user + sends OTP |
| POST | `/api/v1/user/register/verify-otp` | `lms_mobile_api.user.register.verify_otp` | `MobileAuthController::verifyRegisterOtp` | Activates account |
| POST | `/api/v1/user/register/resend-otp` | `lms_mobile_api.user.register.resend_otp` | `MobileAuthController::resendRegisterOtp` | Resends OTP code |
| POST | `/api/v1/user/forgot-password` | `lms_mobile_api.user.forgot_password` | `MobileAuthController::forgotPassword` | Sends reset OTP |
| POST | `/api/v1/user/forgot-password/verify-otp` | `lms_mobile_api.user.forgot_password.verify_otp` | `MobileAuthController::verifyForgotPasswordOtp` | Verifies reset OTP |
| POST | `/api/v1/user/reset-password` | `lms_mobile_api.user.reset_password` | `MobileAuthController::resetPassword` | Sets new password |
| POST | `/api/v1/oauth/token` | `lms_mobile_api.oauth.token` | `MobileAuthController::oauthToken` | Issues bearer token |
| GET | `/api/v1/user/profile` | `lms_mobile_api.user.profile` | `MobileAuthController::getProfile` | Bearer required |
| GET | `/api/v1/user/me` | `lms_mobile_api.user.me` | `MobileAuthController::getUserMe` | Bearer required |
| POST | `/api/v1/user/change-password` | `lms_mobile_api.user.change_password` | `MobileAuthController::changePassword` | Bearer required |

### Content Endpoints

| Method | Path | Route Name | Controller | Detail |
|--------|------|------------|------------|--------|
| GET | `/api/v1/courses/search` | `lms_mobile_api.courses.search` | `MobileCourseController::searchCourses` | [01-content-api](01-content-api.md) |
| GET | `/api/v1/categories` | `lms_mobile_api.categories` | `MobileCourseController::listCategories` | [01-content-api](01-content-api.md) |
| GET | `/api/v1/courses/{courseId}` | `lms_mobile_api.course.detail` | `MobileCourseController::getCourseDetail` | [01-content-api](01-content-api.md) |
| POST | `/api/v1/courses/{courseId}/enroll-free` | `lms_mobile_api.course.enroll_free` | `MobileCourseController::enrollFree` | [01-content-api](01-content-api.md) |
| GET | `/api/v1/dashboard` | `lms_mobile_api.dashboard` | `MobileCourseController::getDashboard` | [03-progress-api](03-progress-api.md) |
| GET | `/api/v1/my-courses` | `lms_mobile_api.my_courses` | `MobileCourseController::getMyCourses` | [03-progress-api](03-progress-api.md) |

### Learning & Assessment Endpoints

| Method | Path | Route Name | Controller | Detail |
|--------|------|------------|------------|--------|
| GET | `/api/v1/courses/{courseId}/lessons/{lessonId}` | `lms_mobile_api.lesson.detail` | `MobileLearningController::getLessonDetail` | [03-progress-api](03-progress-api.md) |
| GET | `/api/v1/quiz/{quizId}` | `lms_mobile_api.quiz.detail` | `MobileLearningController::getQuiz` | [02-assessment-api](02-assessment-api.md) |
| POST | `/api/v1/quiz/{quizId}/submit` | `lms_mobile_api.quiz.submit` | `MobileLearningController::submitQuiz` | [02-assessment-api](02-assessment-api.md) |
| GET | `/api/v1/quiz/results/{resultId}` | `lms_mobile_api.quiz.result` | `MobileLearningController::getQuizResult` | [02-assessment-api](02-assessment-api.md) |
| GET | `/api/v1/quiz/my-results` | `lms_mobile_api.quiz.my_results` | `MobileLearningController::listMyQuizResults` | [02-assessment-api](02-assessment-api.md) |
| POST | `/api/v1/progress/mark-complete` | `lms_mobile_api.progress.mark_complete` | `MobileLearningController::markLessonComplete` | [03-progress-api](03-progress-api.md) |

---

## Known Gaps

| Module | Gap | Impact |
|--------|-----|--------|
| lms_chapter | No routing.yml — chapter CRUD via Drupal entity form only; no JSON endpoint | Cannot drive chapter management from SPA or mobile |
| lms_lesson | No routing.yml — lesson view at `/learn/{course}/{lesson}` (HTML); no lesson JSON detail endpoint except `/api/v1/courses/{courseId}/lessons/{lessonId}` (mobile only) | Web SPA has no lesson detail API |
| lms_question | No routing.yml — question CRUD via entity form; no question bank JSON API | Cannot build question bank browser without new routes |
| lms_stripe, lms_paypal | No webhook receiver endpoint in any module | Stripe/PayPal payment status cannot be confirmed server-side — **security risk** |
| lms_zoom | `generateSignature()` references wrong service ID («unverified» — see bug tracker) | WebSDK signature generation may fail at runtime |

---

## File Index

| File | Domain | Modules | Routes Covered |
|------|--------|---------|----------------|
| [01-content-api.md](01-content-api.md) | Course catalog, search, detail, enroll | lms_course, lms_mobile_api | `/api/courses/search`, `/api/v1/courses/*`, `/api/v1/categories` |
| [02-assessment-api.md](02-assessment-api.md) | Quiz take/submit/result | lms_quiz, lms_mobile_api | `/quiz/*`, `/api/v1/quiz/*` |
| [03-progress-api.md](03-progress-api.md) | Mark-complete, dashboard, notes | lms_progress, lms_note, lms_mobile_api | `/api/lms/progress/*`, `/api/lms/note/*`, `/api/v1/progress/*`, `/api/v1/dashboard`, `/api/v1/my-courses`, `/api/v1/courses/{id}/lessons/{id}` |

---

*Last updated: 2026-06-20*
