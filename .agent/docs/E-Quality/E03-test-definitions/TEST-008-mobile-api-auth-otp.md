---
id: TEST-008
type: Functional
priority: P0 — Must
ac: AC-112, AC-113, AC-115
feature: F-011 (Mobile App)
module: lms_mobile_api
service: lms_mobile_api.otp_service (OtpService)
status: NOT_YET_AUTOMATED
---

# TEST-008 — Mobile API — Auth + OTP

> Verifies the mobile authentication flows: OTP-gated registration, OTP verification that activates user and issues a bearer token, credential login via `/api/v1/oauth/token`, and bearer token enforcement on protected endpoints. Auth mechanism: custom 32-char hex token via `keyvalue.expirable` (NOT OAuth2/JWT).

**Story:** [D03 lms_mobile_api](../../D-Development/D03-stories/lms_mobile_api.md)
**AC:** [AC-112 — Mobile registration with OTP (B05 AC-11)](../../B-Requirements/B05-acceptance-criteria/AC-11-mobile-app.md) · [AC-113 — OTP verification issues bearer token (B05 AC-11)](../../B-Requirements/B05-acceptance-criteria/AC-11-mobile-app.md) · [AC-115 — Password login credential exchange (B05 AC-11)](../../B-Requirements/B05-acceptance-criteria/AC-11-mobile-app.md)
**C12 Workflow:** [WF-007 User Registration](../../C-Design/C12-workflows/WF-007-user-registration.md)
**C05 Module:** [lms_mobile_api](../../C-Design/C05-modules/lms_mobile_api.md)

---

## TC-008-A: Registration — new user creates blocked account and receives OTP

**Layer:** Functional
**Given:** No Drupal user exists with the email `testmobile@example.com`

**When:** POST `/api/v1/user/register` with body `{"email":"testmobile@example.com","password":"password123","password_confirm":"password123"}`

**Then:**
- HTTP 200 JSON response: `{"status":"otp_sent","message":"...","otp_token":"<64-char-hex>"}`
- A Drupal `user` entity is created with `status=0` (blocked), mail=`testmobile@example.com`, roles=`[student]`
- An OTP session is stored in `keyvalue.expirable` collection `lms_mobile_api.otp` with TTL 900 s
- `hook_mail` sends an email with key `otp_email`, type `register`, subject "Your EverLMS verification code"

### Test Data

| Input | Expected Output |
|-------|----------------|
| Valid email + matching passwords | HTTP 200; `otp_token` in response; user created (status=0) |
| Email already belongs to active user | HTTP 422 `{"error":"validation_error","violations":[{"field":"email",...}]}` |
| Mismatched passwords | HTTP 422 validation error |
| Password < 8 chars | HTTP 422 validation error |
| Invalid JSON body | HTTP 400 |

---

## TC-008-B: OTP verification — activates user and issues 32-char bearer token

**Layer:** Functional
**Given:**
- A blocked user was created in TC-008-A
- A valid OTP session exists in `keyvalue.expirable` with the `otp_token` from TC-008-A
- The 6-digit OTP code is known (either intercepted from test mail or mocked via `OtpService`)

**When:** POST `/api/v1/user/register/verify-otp` with body `{"otp_token":"<token>","otp_code":"<6-digit-code>"}`

**Then:**
- HTTP 200 JSON: `{"status":"verified","uid":<int>,"access_token":"<32-char-hex>","token_type":"Bearer","expires_in":604800}`
- User `status` in `users` table is set to `1` (active)
- `lms_mobile_api.otp` session is deleted
- A 32-char hex entry `<access_token> → <uid>` is stored in `keyvalue.expirable` collection `lms_mobile_api.tokens` with 7-day TTL

**And when** the same OTP code is submitted twice:

**Then:** Second request returns HTTP 400 `{"error":"invalid_otp","message":"..."}`

### Test Data

| Input | Expected Output |
|-------|----------------|
| Correct `otp_code` | HTTP 200; `access_token` in response; user activated |
| Wrong `otp_code` | HTTP 400 `invalid_otp` |
| Expired OTP (TTL elapsed) | HTTP 400 `invalid_otp` (session not found in keyvalue) |
| Reused `otp_token` after verification | HTTP 400 `invalid_otp` (session deleted) |

---

## TC-008-C: Bearer token credential login via `/api/v1/oauth/token`

**Layer:** Functional
**Given:** An active Drupal user with email `testmobile@example.com`, known password `password123`

**When:** POST `/api/v1/oauth/token` with body `{"username":"testmobile@example.com","password":"password123"}`

**Then:**
- HTTP 200 JSON: `{"uid":<int>,"email":"testmobile@example.com","name":"...","access_token":"<32-char-hex>","token_type":"Bearer","expires_in":604800}`
- A new 32-char hex token is stored in `keyvalue.expirable` `lms_mobile_api.tokens`

**And when** wrong password is submitted:

**Then:** HTTP 401 `{"error":"invalid_grant","message":"..."}`

---

## TC-008-D: Bearer token enforced on protected endpoints

**Layer:** Functional
**Given:** A valid bearer token was issued in TC-008-C

**When:** GET `/api/v1/user/me` with header `Authorization: Bearer <access_token>`

**Then:**
- HTTP 200 JSON: `{"id":<uid>,"uuid":"...","name":"...","email":"...","first_name":"...","last_name":"...","avatar_url":null,"phone":null,"gender":null,"date_of_birth":null,"roles":["student"]}`
- `authenticated` role is stripped from the roles array

**And when** the request is made without any Authorization header:

**Then:** HTTP 403 (`AccessDeniedHttpException`)

**And when** the request uses an expired or non-existent token:

**Then:** HTTP 403 (`AccessDeniedHttpException`)

---

## TC-008-E: `X-Authorization` header fallback (non-standard workaround)

**Layer:** Functional
**Given:** A valid bearer token; an HTTP environment where `Authorization` header is stripped by a server gate

**When:** GET `/api/v1/user/me` with header `X-Authorization: Bearer <access_token>`

**Then:**
- HTTP 200 (same as TC-008-D) — `getAuthenticatedUser()` checks `X-Authorization` first

---

## TC-008-F: OTP resend invalidates old token

**Layer:** Functional
**Given:** A valid OTP session exists from TC-008-A registration

**When:** POST `/api/v1/user/register/resend-otp` with body `{"otp_token":"<old_token>"}`

**Then:**
- HTTP 200 JSON: `{"status":"otp_resent","otp_token":"<new_64-char-hex>"}`
- Old `otp_token` session is deleted
- New OTP email sent via `hook_mail`
- Using the old `otp_token` now returns HTTP 400

---

## Setup / Teardown

- Setup: Full Drupal (`BrowserTestBase`); install `lms_base`, `lms_mobile_api`; configure `lms_mobile_api.settings` with `otp_expiry_seconds=900`, `token_expiry_seconds=604800`; enable `keyvalue.expirable` (built into Drupal core); optionally mock mail collector
- Teardown: Delete test user; clear `keyvalue.expirable` collections `lms_mobile_api.otp`, `lms_mobile_api.tokens`, `lms_mobile_api.reset`

## Notes

- `MobileAuthController` source: `web/modules/custom/lms_mobile_api/src/Controller/MobileAuthController.php`
- `OtpService` source: `web/modules/custom/lms_mobile_api/src/Service/OtpService.php`
- Auth mechanism is custom `keyvalue.expirable` bearer token — NOT OAuth2/JWT, despite unused `oauth_client_id`/`oauth_client_secret` config keys in `lms_mobile_api.settings.yml`
- All routes use `_access: TRUE` (Drupal gate passes); auth is entirely in-controller via `getAuthenticatedUser()` (code duplicated across 3 controllers — TC-008-D also implicitly tests this shared logic)
- No CORS headers are configured in this module; Flutter app must target the same origin or a CORS-enabled server config. Do not test CORS in PHPUnit
- No refresh token mechanism exists; when a token expires the client must re-authenticate via `/api/v1/oauth/token`
- The `forgot_password` OTP flow (AC-116) is omitted from this test spec for brevity; add TC-008-G if needed, following the same pattern
- Expected file: `web/modules/custom/lms_mobile_api/tests/src/Functional/MobileAuthOtpTest.php`
