# ISSUE-008 — lms_mobile_api Has Undeclared Module Dependencies and Triplicated Auth Code

| Field | Value |
|---|---|
| ID | `ISSUE-008` |
| Title | `lms_mobile_api` uses `lms_quiz` and `lms_wishlist` but neither is declared in `info.yml`; `getAuthenticatedUser()` is copy-pasted across 3 controllers; unused `oauth_*` config keys |
| Source | G4-Architecture / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `MEDIUM` |
| Affected page / route | `GET /api/v1/quiz/{quizId}`, `POST /api/v1/quiz/{quizId}/submit`, `GET /api/v1/courses/{courseId}` |
| Owning Epic | E-10 (G-HARDENING) |
| Owning Story | [`hardening-security`](../../../D-Development/D03-stories/hardening-security.md) |
| Owning module(s) | `lms_mobile_api` |

## Summary

`lms_mobile_api` has three distinct structural issues:

### 1. Undeclared dependencies on `lms_quiz` and `lms_wishlist`

`lms_mobile_api.info.yml` declares only `drupal:user` as a dependency. However:

- **`lms_quiz` dependency** — `MobileLearningController` imports `Drupal\lms_quiz\Grading\GradingStrategyFactory` via a PHP `use` statement and calls `GradingStrategyFactory::getStrategy($question->bundle())` in `submitQuiz()`. It also checks for `QuizPassedEvent` class existence before dispatching. If `lms_quiz` is disabled or uninstalled, `lms_mobile_api` routes involving quiz submission will throw a fatal `Class not found` error.
- **`lms_wishlist` dependency** — `MobileCourseController::getCourseDetail()` queries the `lms_wishlist` entity storage to compute `is_wishlisted` for authenticated users. If `lms_wishlist` is disabled, this will throw `InvalidArgumentException: "The entity type lms_wishlist does not exist"`.

Neither `lms_quiz` nor `lms_wishlist` is listed under `dependencies:` in `lms_mobile_api.info.yml`. Drupal's module system does not enforce load order or co-installation for undeclared dependencies, so:

- An admin could disable `lms_quiz` or `lms_wishlist` without being warned that `lms_mobile_api` will break.
- DDEV setup or CI environments that install modules individually may encounter fatal errors on mobile API routes.

### 2. `getAuthenticatedUser()` copy-pasted in three controllers

The private method `getAuthenticatedUser(Request $request): UserInterface` — which reads the `X-Authorization` or `Authorization: Bearer` header, resolves the UID from `keyvalue.expirable`, loads and validates the user — is **identically duplicated** in all three controllers:

- `MobileAuthController::getAuthenticatedUser()`
- `MobileCourseController::getAuthenticatedUser()`
- `MobileLearningController::getAuthenticatedUser()`

Any bug fix, security improvement, or token format change must be applied in three places. One controller receiving a fix while others do not creates inconsistent security behaviour on the mobile API surface.

### 3. Unused `oauth_client_id` / `oauth_client_secret` config keys

`config/install/lms_mobile_api.settings.yml` defines `oauth_client_id: ''` and `oauth_client_secret: ''`. These keys are **never read in any PHP code** — they are artifacts of an earlier design that was replaced by the custom keyvalue bearer token system. They create confusion for administrators viewing the module config and waste config namespace.

## Reproduction

**Undeclared deps:**
1. Disable `lms_quiz` via `drush pm:uninstall lms_quiz`.
2. Make `GET /api/v1/quiz/1` request → PHP fatal: `Class 'Drupal\lms_quiz\Grading\GradingStrategyFactory' not found`.
3. Re-enable `lms_quiz`; disable `lms_wishlist`; make `GET /api/v1/courses/1` as an authenticated user → exception on `lms_wishlist` storage access.

**Triplicated code:**
1. Search codebase: `grep -rn "getAuthenticatedUser" web/modules/custom/lms_mobile_api/`
2. Observe identical method bodies in `MobileAuthController.php`, `MobileCourseController.php`, `MobileLearningController.php`.

**Unused config:**
1. Open `web/modules/custom/lms_mobile_api/config/install/lms_mobile_api.settings.yml` — `oauth_client_id` and `oauth_client_secret` fields present.
2. Search `web/modules/custom/lms_mobile_api/src/` for `oauth_client_id` reads: `grep -rn "oauth_client" web/modules/custom/lms_mobile_api/src/` → no results.

## Root Cause

1. **Undeclared deps** — `lms_mobile_api` was developed after `lms_quiz` and `lms_wishlist` were already installed in the development environment, so the dependency was never noticed. The module was not tested in a clean install without those modules present.

2. **Triplicated code** — `getAuthenticatedUser()` was originally written in `MobileAuthController` and copy-pasted to the other two controllers for convenience. No shared `TokenAuthTrait` or token-resolver service was extracted.

3. **Unused config** — the `oauth_*` keys are a leftover from an earlier OAuth2-based design that was replaced by the custom keyvalue token approach. The config schema was never cleaned up.

## Proposed Fix

1. **Add missing dependencies to `lms_mobile_api.info.yml`**:
   ```yaml
   dependencies:
     - drupal:user
     - lms_quiz:lms_quiz
     - lms_wishlist:lms_wishlist
   ```
   If wishlist is optional, use a runtime `\Drupal::moduleHandler()->moduleExists('lms_wishlist')` guard instead of a hard dependency, and make `is_wishlisted` default to `false` when the module is absent.

2. **Extract `getAuthenticatedUser()` into a shared service** — create `lms_mobile_api.token_auth` service (`TokenAuthService`) in `lms_mobile_api.services.yml` that encapsulates the bearer token → UID → user resolution logic. Inject this service into all three controllers via constructor injection.

3. **Remove unused config keys** — delete `oauth_client_id` and `oauth_client_secret` from `config/install/lms_mobile_api.settings.yml`. Add a `hook_update_N()` to remove these from existing active config on update.

4. **Add a config schema file** (`config/schema/lms_mobile_api.schema.yml`) to document the remaining config keys (`otp_expiry_seconds`, `reset_token_expiry_seconds`, `token_expiry_seconds`) — currently absent.

## Trace links

- **Story:** `D-Development/D03-stories/hardening-security.md` §Scope "DI cleanup"
- **C05 module:** `C-Design/C05-modules/lms_mobile_api.md`
- **Facts base:** `modules-mobile.md` §lms_mobile_api — info.yml, Implementation Status Summary (Notable Design Observations #6, #7, #5)
- **Related issues:** [ISSUE-007](ISSUE-007-progress-cascade-race-condition.md) (`markLessonComplete` in this module also needs the ProgressLockService fix)
