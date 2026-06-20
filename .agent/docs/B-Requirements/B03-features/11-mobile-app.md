---
id: F-011
name: Mobile App
priority: Must
status: APPROVED
parent-requirements: [BR-011]
owning_modules: [lms_mobile_api]
primary_source: mixed
story_anchors: []
---

# F-011 — Mobile App

## User story

As a **student**, I want **a native mobile app (iOS & Android) that lets me browse courses, take lessons, complete quizzes, and track my progress**, so that **I can learn on the go without needing a browser**.

## Description

The EverLMS mobile experience consists of two layers: a Flutter app (`app/`, package `com.weebpal.everlms`, version `1.0.10+33`) and a Drupal backend module (`lms_mobile_api`) that provides a purpose-built JSON API at `/api/v1/*`. Both are fully implemented and in active development.

**Flutter app technology stack:** Riverpod (state management), GoRouter (navigation), Dio (`ApiClient` wrapper, HTTP). Supports 33 screens across 28 feature directories. Localized in English and Vietnamese (`l10n/`). Design tokens are **identical** to the web (`AppColors` matches `_variables.scss` exactly: `#0D1117` background, `#667EEA`/`#764BA2` accent, Inter font).

**Backend API (`lms_mobile_api`):** Three controllers (`MobileAuthController`, `MobileCourseController`, `MobileLearningController`) expose 22 routes under `/api/v1/`. All routes declare `_access: TRUE` (Drupal-layer access bypassed); authentication is enforced inside controllers via a **custom bearer token backed by Drupal's `keyvalue.expirable` store** — not OAuth2/JWT despite `oauth_client_id`/`oauth_client_secret` fields existing in `lms_mobile_api.settings.yml` (those keys are unused artifacts of an earlier design).

### Authentication mechanism (as-built)

| Mechanism | Detail |
|---|---|
| Token format | 32-char hex (`bin2hex(random_bytes(16))`) |
| Token store | `keyvalue.expirable` collection `lms_mobile_api.tokens` |
| Default TTL | 604 800 s (7 days), configurable via `lms_mobile_api.settings:token_expiry_seconds` |
| Token issuance | `/api/v1/oauth/token` (login), `/api/v1/user/register/verify-otp` (post-registration), `/api/v1/user/reset-password` (post-reset) |
| Header | `X-Authorization: Bearer {token}` (primary); `Authorization: Bearer {token}` (fallback) |
| No refresh token | Token expires; no refresh mechanism exists |
| OTP | 6-digit numeric; session stored in `keyvalue.expirable` collection `lms_mobile_api.otp`; TTL 900 s (15 min) |
| Password reset token | 64-char hex; stored in `keyvalue.expirable` collection `lms_mobile_api.reset`; TTL 1800 s (30 min) |

The `X-Authorization` header is used (rather than standard `Authorization`) to avoid conflict with HTTP Basic Auth (`SHIELD_USERNAME`/`SHIELD_PASSWORD`) that protects the Drupal site at the nginx level. The Flutter `ApiClient` sends Basic Auth in `Authorization` and bearer token in `X-Authorization`.

### API endpoint groups

**Auth (`/api/v1/`):**
- `POST /user/register` — register with OTP email verification
- `POST /user/register/verify-otp` — activate account, receive bearer token
- `POST /user/register/resend-otp`
- `POST /user/forgot-password` — always returns 200 (enumeration prevention)
- `POST /user/forgot-password/verify-otp`
- `POST /user/reset-password` — receive bearer token for auto-login
- `POST /oauth/token` — email/password login → bearer token
- `GET /user/profile` — minimal profile (uid, email, name)
- `GET /user/me` — full profile (avatar, phone, gender, DOB, roles)
- `POST /user/change-password`

**Courses (`/api/v1/`):**
- `GET /courses/search` — paginated search with filters: category, level, price, sort, featured
- `GET /categories` — taxonomy terms (vid=`course_category`) with per-term course count
- `GET /courses/{courseId}` — rich course object: curriculum, enrollment state, drip-lock per lesson, wishlist status
- `POST /courses/{courseId}/enroll-free` — free-course enrollment, creates `lms_user_course`
- `GET /dashboard` — in-progress courses (max 5), counts
- `GET /my-courses` — paginated enrolled courses with progress

**Learning (`/api/v1/`):**
- `GET /courses/{courseId}/lessons/{lessonId}` — lesson content (video URL, text, H5P embed, SCORM URL, PDF, Zoom meeting ID), attachments, navigation (prev/next), full curriculum with completion/drip-lock state; returns HTTP 423 for drip-locked lessons
- `GET /quiz/{quizId}` — quiz with questions; all 8 bundle types supported
- `POST /quiz/{quizId}/submit` — grades answers via `GradingStrategyFactory`, creates `lms_quiz_result` + `lms_question_response` per question, dispatches `QuizPassedEvent` on pass
- `GET /quiz/results/{resultId}` — per-question feedback if `field_display_right_answer=TRUE`
- `GET /quiz/my-results` — paginated quiz history
- `POST /progress/mark-complete` — marks lesson complete (`lms_user_lesson`), recalculates `lms_user_course.field_progress`, transitions course status to `in_progress` or `completed`

### Known design issues

| Issue | Severity | Detail |
|---|---|---|
| `getAuthenticatedUser()` triplicated | Medium | Identical private method in all 3 controllers — refactor candidate |
| `lms_quiz` not in `lms_mobile_api.info.yml` dependencies | Medium | `MobileLearningController` uses `GradingStrategyFactory` and `QuizPassedEvent` via PHP `use` but not declared |
| `lms_wishlist` not in dependencies | Low | `getCourseDetail()` queries `lms_wishlist` storage but module not declared |
| No CORS headers | High | No CORS config in module or `sites/default/services.yml`; cross-origin requests from web clients will fail |
| No refresh token | High | Tokens expire after 7 days with no silent refresh; users must re-login |
| `oauth_client_id`/`oauth_client_secret` in settings | Low | Unused config keys; leftover from earlier OAuth2 design |
| No config schema | Low | `lms_mobile_api.settings.yml` has no schema file |

## In scope (as-built)

- Custom bearer token auth with OTP-based registration and password reset.
- Social auth in mobile app: Google, Facebook, Apple (iOS), Microsoft MSAL — all wired in Flutter; backend endpoint compatibility is «unverified» for all four.
- Full course catalog browsing, filtering, pagination via `/api/v1/courses/search`.
- Course detail with curriculum, drip-lock, enrollment state, wishlist.
- Free-course enrollment.
- Lesson delivery: video (HLS/DASH via `better_player_plus`, YouTube, Vimeo), text/HTML, H5P WebView, SCORM, PDF, Zoom meeting.
- Quiz taking and grading (all 8 question types) via same `GradingStrategyFactory` as web.
- Progress tracking: lesson-level completion, course-level progress percentage.
- Dashboard summary screen.
- Push notifications: Firebase Cloud Messaging (Android + iOS); APNs auth key present; `OneSignalNotificationServiceExtension` iOS target.
- In-app purchases: `in_app_purchase` (consumables); Stripe Flutter SDK; PayPal Flutter package.
- Offline mode: `MainOfflineScreen` and `CourseDetailsOfflineScreen` with `flutter_downloader`.
- Dark/light theme toggle persisted in SharedPreferences; default dark.
- English + Vietnamese localization.
- White-label / multi-tenant base URL switching via `SwitchDomainScreen`.
- 33 screens across auth, catalog, learning, profile, commerce, notifications, forum, news, settings, onboarding.

## Out of scope

- Biometric login (Face ID / Fingerprint) — not confirmed in API or Flutter code from available facts («unverified»).
- OAuth2 / JWT token standard — replaced by custom keyvalue bearer token.
- Silent token refresh — no refresh token mechanism exists.
- SCORM runtime tracking (LRS) — SCORM URL served to WebView; completion tracked via `mark-complete` endpoint, not SCORM `cmi.core.lesson_status`.
- Web frontend (covered by [F-005 Content & Media](./05-content-media.md) and themes).
- Admin-facing screens — mobile app is student-only.

## Acceptance summary

A student installs the app, registers with email OTP verification, browses courses, enrolls in a free course, watches a video lesson, marks it complete (updating progress to > 0%), and takes a quiz. Full Given/When/Then criteria live in `../B05-acceptance-criteria/F-011.md`.

## Content ownership / editability

| Visible content family | Business owner | Editor role | Expected edit surface | Notes |
|---|---|---|---|---|
| App base URL / multi-tenant config | Platform admin | administrator | `SwitchDomainScreen` in app or `.env` | Stored in SharedPreferences |
| OTP email copy | Platform admin | developer | `lms_mobile_api.module` `hook_mail` | Code change to edit subject/body |
| App store listings | Marketing | administrator | App Store Connect / Google Play Console | Outside EverLMS backend |

## Dependencies (requirement-level only)

| Type | Reference | Reason |
|---|---|---|
| Feature | F-001 (Course Management) | Course entity data served via `/api/v1/courses/*` |
| Feature | F-002 (Assessment System) | Quiz taking + grading uses `GradingStrategyFactory` from `lms_quiz` |
| Feature | F-003 (Learning Progress) | `lms_user_course`, `lms_user_lesson` read/written by learning endpoints |
| Feature | F-004 (Order & Membership) | In-app purchase and Stripe/PayPal checkout |
| Feature | F-006 (Certification) | Certificate download endpoint (`/api/v1/certificates/{id}/download`) |
| Integration | INT-004 (Firebase FCM) | Push notification delivery |

## Open questions

- [ ] CORS policy: production deployment must configure `sites/default/services.yml` CORS or nginx headers for `/api/v1/*`. Which origins are allowed?
- [ ] No refresh token: what is the UX plan when the 7-day token expires (force re-login vs. silent refresh)?
- [ ] Social auth (Google/Facebook/Apple/Microsoft) backend: does the Drupal backend have matching endpoints at `/api/v1/auth/social-register`? «unverified» from available facts.
- [ ] Release signing: `secret.properties` is a placeholder; Android release keystore (`file.keystore`) management needs a secure workflow before publishing to Google Play.
- [ ] `.env` file containing test Stripe/PayPal keys is committed to the repo — this must be gitignored before any public repository exposure (see [F-012](./12-security-performance.md)).
