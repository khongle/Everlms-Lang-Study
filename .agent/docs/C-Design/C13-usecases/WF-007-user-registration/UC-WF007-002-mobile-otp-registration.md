---
usecase_id: UC-WF007-002
workflow: WF-007
type: happy
title: Mobile OTP Registration — Flutter App
status: as-built
---

# UC-WF007-002 — Mobile OTP Registration: Flutter App

**Purpose:** Flutter app registers a new student via the OTP email flow; on successful OTP verification the account is activated and a 7-day bearer token is issued.

---

## Actors

| Actor | Type | Notes |
|-------|------|-------|
| Mobile App (Flutter) | Primary | Sends API requests; stores bearer token |
| `MobileAuthController` | Secondary | Handles all auth endpoints in `lms_mobile_api` |
| `OtpService` | Secondary | Generates OTP codes, session tokens; dispatches email |
| Email Service | Secondary | Sends OTP code via `plugin.manager.mail` → `hook_mail` key `otp_email` |
| `keyvalue.expirable` store | Secondary | Persists OTP sessions and bearer tokens |

---

## Preconditions

| # | Condition |
|---|-----------|
| P1 | No active (`status=1`) Drupal user account exists for the supplied email |
| P2 | `lms_mobile_api` module is enabled |
| P3 | Outbound mail is configured and operational |
| P4 | Request body is valid JSON (`Content-Type: application/json`) |

---

## Trigger

Flutter app POSTs to `POST /api/v1/user/register` with `{email, password, password_confirm, name?}`.

---

## Main Flow

| Step | Given | When | Then |
|------|-------|------|------|
| 1 | Request arrives at `POST /api/v1/user/register` | `MobileAuthController::register()` executes | Validates: `email` required, `password` min 8 chars, `password` === `password_confirm`; rejects with `422 validation_error` on any failure |
| 2 | Input valid; no active account for email | Controller checks for stale blocked registrations | Existing blocked user records with same email are deleted before creating the new record |
| 3 | Email is clear | Controller creates Drupal user entity | User saved with `status=0` (blocked), role `student`; `ensureUniqueUsername()` derives a unique username from email prefix |
| 4 | User entity saved | `OtpService::generateOtpCode()` called | Returns a 6-digit numeric string via `random_int(100000, 999999)` |
| 5 | OTP code generated | `OtpService::generateToken()` called | Returns a 64-char hex session token via `bin2hex(random_bytes(32))` |
| 6 | OTP code and session token available | `OtpService::storeOtpSession()` called | Stores `{uid, email, otp_code, type:'register'}` in `keyvalue.expirable` collection `lms_mobile_api.otp`; TTL = `lms_mobile_api.settings:otp_expiry_seconds` (default 900 s / 15 min) |
| 7 | OTP session stored | `OtpService::sendOtpEmail()` called | Dispatches via `plugin.manager.mail`; `hook_mail` key `otp_email` sets subject "Your EverLMS verification code" with OTP code in body |
| 8 | Email dispatched | Controller returns response | `200 {status:"otp_sent", message:string, otp_token:string}` — `otp_token` is the 64-char session key; client must store this for the verify step |
| 9 | Client has `otp_token`; user checks email | Flutter app POSTs to `POST /api/v1/user/register/verify-otp` with `{otp_token, otp_code}` | `verifyRegisterOtp()` calls `OtpService::getOtpSession(otp_token)`; validates session `type === 'register'` and `otp_code` matches |
| 10 | OTP code matches and session not expired | Verification passes | `$account->set('status', 1)->save()` — user account activated; OTP session deleted from keyvalue store |
| 11 | Account activated | `issueAccessToken($uid)` called | Generates 32-char hex token via `bin2hex(random_bytes(16))`; stores `token => uid` in `keyvalue.expirable` collection `lms_mobile_api.tokens`; TTL = `lms_mobile_api.settings:token_expiry_seconds` (default 604800 s / 7 days) |
| 12 | Token stored | Controller returns | `200 {status:"verified", uid:int, access_token:string, token_type:"Bearer", expires_in:int}` — app stores token for all subsequent authenticated requests |

---

## Entities Mutated

| Entity / Store | Field / Key | Change |
|----------------|-------------|--------|
| `user` (Drupal core) | `status` | Step 3: set to `0`; Step 10: set to `1` |
| `user` | `roles` | `student` role assigned on entity creation (via `lms_base` `hook_user_insert` — fires when `status=0` user is inserted) |
| `keyvalue.expirable` collection `lms_mobile_api.otp` | `{otp_token}` | Created in step 6; deleted in step 10 |
| `keyvalue.expirable` collection `lms_mobile_api.tokens` | `{access_token}` | Created in step 11; expires after TTL |

---

## Observability Hooks

| Hook / Log | Module | What to check |
|------------|--------|----------------|
| `MobileAuthController::register()` | `lms_mobile_api` | `422` response body contains `violations` array with field-level messages |
| `OtpService::sendOtpEmail()` | `lms_mobile_api` | Drupal mail log; `hook_mail` with key `otp_email` and type `register` |
| `keyvalue.expirable` | Drupal core | Collection `lms_mobile_api.otp`: entry exists post-step-6, absent post-step-10 |
| `keyvalue.expirable` | Drupal core | Collection `lms_mobile_api.tokens`: entry exists post-step-11 |
| Logger channel `lms_mobile_api` | `lms_mobile_api` | Error entries if email dispatch fails |

---

## Acceptance Link

| AC ID | Summary | Planned Test |
|-------|---------|--------------|
| [AC-069](../../../B-Requirements/B05-acceptance-criteria/AC-07-user-management.md#ac-069-email-based-registration-assigns-student-role) | Registration assigns student role | T-069 (planned, E03 — not yet materialized) |

---

## Notes

- The `Authorization` header may be stripped by some HTTP Basic Auth server gates (nginx/Apache); the module works around this by checking `X-Authorization` first on all authenticated endpoints. Mobile clients must use `X-Authorization: Bearer <token>` if the standard header is blocked.
- There is **no refresh token mechanism**. After the 7-day TTL, the client must re-authenticate via `POST /api/v1/oauth/token`.
- `lms_mobile_api.settings.yml` exposes `oauth_client_id` / `oauth_client_secret` config keys — these are NOT read anywhere in the code; they are artifacts of an earlier design.
- `getAuthenticatedUser()` bearer token resolution logic is duplicated verbatim across all three controllers (`MobileAuthController`, `MobileCourseController`, `MobileLearningController`) — refactor candidate.
- `lms_mobile_api` does not declare `lms_quiz` as a dependency in `info.yml`, yet `MobileLearningController` imports `GradingStrategyFactory` and `QuizPassedEvent` from `lms_quiz` — undeclared implicit dependency.
