# D03 — Story Spec: lms_mobile_api

**Story ID:** lms_mobile_api
**Story type:** module
**Title:** Mobile JSON API — Auth (OTP/Bearer), Courses, Learning, and Quiz for Flutter App
**Primary source:** `C-Design/C05-modules/lms_mobile_api.md`
**Owning module(s):** lms_mobile_api
**Service(s):** lms_mobile_api.otp_service (OtpService)
**Epic:** E-09 (Mobile)
**Contributes to Feature(s):** F-016 (Mobile App)
**Design source(s):** `../C05-modules/lms_mobile_api.md`
**Round:** N/A
**Driver:** SA directive
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_mobile_api`

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 3
**Splitting strategy:**
- S1: OtpService, MobileAuthController (all 10 endpoints), hook_mail, services.yml, config/install/lms_mobile_api.settings.yml
- S2: MobileCourseController (6 endpoints + 12 helpers), routing.yml (course + category + dashboard group)
- S3: MobileLearningController (6 endpoints + 14 helpers), quiz submission with GradingStrategyFactory, progress mark-complete

## Scope (from primary source)

Provides the complete REST/JSON API backend for the EverLMS Flutter mobile student app (`app/` v1.0.10+33). Base URL: `/api/v1/`. Covers three domains:

1. **Auth (MobileAuthController):** Registration with email OTP verification, forgot password, password reset, OAuth-style token login (`/api/v1/oauth/token`), profile/me endpoints, password change. Custom bearer token backed by `keyvalue.expirable` — NOT OAuth2/JWT.

2. **Courses (MobileCourseController):** Course search with multi-filter/sort/pagination, category listing, course detail (curriculum + enrollment + wishlist), free course enrollment, student dashboard, my-courses list.

3. **Learning (MobileLearningController):** Lesson detail (content types: video/text/h5p/scorm/pdf/pptx/zoom) with drip-lock, quiz take (all 8 question bundles), quiz submit (uses `GradingStrategyFactory` from `lms_quiz`), quiz result + history, lesson mark-complete with progress cascade.

All routes use `_access: TRUE` (Drupal gate); auth is enforced in-controller via Bearer token lookup in keyvalue store.

**Known design issues (as-built):**
- `getAuthenticatedUser()` is triplicated across all three controllers (refactor candidate).
- `lms_quiz` and `lms_wishlist` are used but not declared in `lms_mobile_api.info.yml` dependencies.
- No refresh token mechanism — 7-day bearer tokens expire with no renewal path.
- No CORS headers configured in this module.
- `oauth_client_id`/`oauth_client_secret` in settings.yml are unused artifacts.

## Source Documents

| Source | Why loaded |
|---|---|
| `../C05-modules/lms_mobile_api.md` | Owning module design |
| `../../B-Requirements/B03-features/11-mobile-app.md` | Mobile feature requirements |
| `../../B-Requirements/B06-integration-requirements/` | Mobile API contract |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Email OTP registration and verification | B03-features/11-mobile-app | Acceptance |
| Forgot password + OTP + reset flow | B03-features/11-mobile-app | Acceptance |
| Bearer token login via /api/v1/oauth/token | B03-features/11-mobile-app | Acceptance |
| Course search with category, level, price, sort filters | B03-features/11-mobile-app | Acceptance |
| Course detail including curriculum + enrollment + drip-lock | B03-features/11-mobile-app | Acceptance |
| Free course enrollment | B03-features/11-mobile-app | Acceptance |
| Dashboard (in-progress courses, stats) | B03-features/11-mobile-app | Acceptance |
| Lesson detail with all content types + drip-lock (HTTP 423) | B03-features/11-mobile-app | Acceptance |
| Quiz take + submit using full GradingStrategyFactory | B03-features/11-mobile-app | Acceptance |
| Lesson mark-complete with progress cascade | B03-features/11-mobile-app | Acceptance |
| Refresh token mechanism | B03-features/11-mobile-app | target-state / planned (not implemented) |
| CORS headers for mobile client | B03-features/11-mobile-app | target-state / planned (not in module) |
| Declare lms_quiz + lms_wishlist in info.yml dependencies | D00 — hygiene | target-state / planned |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_mobile_api/lms_mobile_api.info.yml` | Create | Module declaration (core_version_requirement: ^10 \|\| ^11; missing quiz/wishlist deps) | — | S1 |
| `web/modules/custom/lms_mobile_api/lms_mobile_api.module` | Create | hook_mail (otp_email for register + forgot_password) | — | S1 |
| `web/modules/custom/lms_mobile_api/lms_mobile_api.services.yml` | Create | lms_mobile_api.otp_service | — | S1 |
| `web/modules/custom/lms_mobile_api/lms_mobile_api.routing.yml` | Create | 22 routes across auth + course + learning groups | — | S1 |
| `web/modules/custom/lms_mobile_api/config/install/lms_mobile_api.settings.yml` | Create | Default config (otp_expiry_seconds=900, reset=1800; unused oauth fields) | — | S1 |
| `web/modules/custom/lms_mobile_api/src/Service/OtpService.php` | Create | OTP code/session management + email dispatch | — | S1 |
| `web/modules/custom/lms_mobile_api/src/Controller/MobileAuthController.php` | Create | 10 endpoints + issueAccessToken + getAuthenticatedUser | — | S1 |
| `web/modules/custom/lms_mobile_api/src/Controller/MobileCourseController.php` | Create | 6 endpoints + 12 helpers | — | S2 |
| `web/modules/custom/lms_mobile_api/src/Controller/MobileLearningController.php` | Create | 6 endpoints + 14 helpers; uses GradingStrategyFactory | — | S3 |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| drupal:user | Intra-Drupal | info.yml (only declared dependency) |
| lms_quiz (GradingStrategyFactory, QuizPassedEvent) | Cross-module — implicit | MobileLearningController; NOT in info.yml |
| lms_wishlist (lms_wishlist entity storage) | Cross-module — implicit | MobileCourseController::getCourseDetail(); NOT in info.yml |
| lms_progress (lms_user_course, lms_user_lesson entities) | Cross-module — implicit | All three controllers |
| lms_course, lms_chapter, lms_lesson | Cross-module — implicit | All three controllers |
| keyvalue.expirable (Drupal core service) | Core | Token + OTP + reset stores |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| Bearer token via X-Authorization then Authorization header fallback | All three controllers | As-built (non-standard; documented) |
| JSON _format routing requirement | All 22 routes | As-built |
| 6-digit numeric OTP via random_int | OtpService::generateOtpCode() | As-built |
| HTTP 423 for drip-locked lesson | MobileLearningController::getLessonDetail() | As-built |
| Email enumeration prevention | MobileAuthController::forgotPassword() | As-built (always returns 200) |

## Editorial Usability / Entity-First Evidence

Not applicable — pure JSON API module. No templates, no admin pages, no content surfaces visible in a browser.

**Hardcoded content exceptions:**
- OTP email subject/body in `hook_mail` ("Your EverLMS verification code", "expires in 15 minutes") — admin-configurable text not exposed.
- Default bearer token TTL (604800 s = 7 days) hardcoded in `getTokenExpiry()` default if config key missing.

## Feature Flags Introduced or Read

None.

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-MOB-001 | Integration | POST /api/v1/user/register creates blocked user + returns otp_token | Register AC |
| T-MOB-002 | Integration | POST /api/v1/user/register/verify-otp with correct code activates user + issues access_token | OTP verify AC |
| T-MOB-003 | Integration | POST /api/v1/oauth/token with valid credentials returns access_token | Login AC |
| T-MOB-004 | Integration | POST /api/v1/oauth/token with invalid credentials returns 401 invalid_grant | Auth fail AC |
| T-MOB-005 | Integration | GET /api/v1/user/me returns full profile including roles (without authenticated) | Profile AC |
| T-MOB-006 | Integration | GET /api/v1/courses/search with category + level filters returns paginated results | Search AC |
| T-MOB-007 | Integration | GET /api/v1/courses/{id} returns curriculum with drip-lock computed | Detail AC |
| T-MOB-008 | Integration | POST /api/v1/courses/{id}/enroll-free creates lms_user_course + returns enrollment_id | Enroll AC |
| T-MOB-009 | Integration | GET /api/v1/courses/{id}/lessons/{lessonId} returns HTTP 423 for drip-locked lesson | Drip-lock AC |
| T-MOB-010 | Integration | POST /api/v1/quiz/{id}/submit grades all 8 question types and creates lms_quiz_result | Quiz submit AC |
| T-MOB-011 | Integration | POST /api/v1/progress/mark-complete updates progress % and sets course status=completed at 100% | Progress AC |
| T-MOB-012 | Integration | POST /api/v1/progress/mark-complete at 100% dispatches QuizPassedEvent → certificate issued | Event chain AC |
| T-MOB-013 | Unit | getAuthenticatedUser() throws AccessDeniedHttpException on expired/invalid token | Auth guard AC |
| T-MOB-014 | Unit | forgotPassword() returns 200 with empty otp_token for unknown email (enumeration prevention) | Security AC |
| T-MOB-015 | Unit | resendRegisterOtp() invalidates old otp_token and issues new one | OTP resend AC |

## Gate Results (per Story)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Module installs; all 22 routes resolve | — | — |
| G2 Lint | PASS | No blocking violations | — | — |
| G3 Tests | PASS | All three controller groups smoke-tested against Flutter app | — | — |
| G4 Adversarial | PASS | Implicit deps, missing refresh token, code triplication all documented; no critical blockers | — | — |

## Notes

- **getAuthenticatedUser() triplicated (refactor candidate):** Identical private method exists in MobileAuthController, MobileCourseController, and MobileLearningController. Should be extracted to a shared trait or base controller class. Logged as D00 convention gap.
- **Missing info.yml dependencies (hygiene):** `lms_quiz` (for `GradingStrategyFactory` and `QuizPassedEvent`) and `lms_wishlist` (for wishlist entity storage) are used but not declared in info.yml. This could cause a runtime exception if those modules are disabled without triggering a dependency error. Fix: add them to `dependencies:` in info.yml.
- **No CORS headers:** Mobile clients hitting `/api/v1/` from a Flutter WebView or web build may encounter CORS issues. CORS must be configured in `sites/default/services.yml` (Drupal's built-in CORS config) or via a middleware module, not in this module.
- **No refresh token:** Bearer tokens expire after 7 days with no renewal path. Users must re-authenticate. This is a UX gap for long-session mobile use.
- **X-Authorization header workaround:** The module reads `X-Authorization` first, then falls back to `Authorization`. This works around HTTP Basic Auth server gates that consume the `Authorization` header. Flutter client must send `X-Authorization: Bearer <token>` for reliable behaviour. Document this in the API contract in `C06-apis/`.
- **oauth_client_id/secret in settings.yml are dead config:** These keys exist in `config/install/lms_mobile_api.settings.yml` but are never read in PHP code. They are artifacts of an earlier OAuth2 design. Can be removed in a cleanup pass.
- **No config schema file:** `lms_mobile_api.settings` has no corresponding `config/schema/lms_mobile_api.schema.yml`. This suppresses Drupal config validation. Add schema file to pass `drush config:validate`.
- **GradingStrategyFactory coupling:** `MobileLearningController` imports `Drupal\lms_quiz\Grading\GradingStrategyFactory` via PHP `use` statement. If lms_quiz is not installed, the module fails to load. This implicit dependency must be declared explicitly.
