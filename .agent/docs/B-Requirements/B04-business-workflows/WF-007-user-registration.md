---
id: WF-007
name: User Registration and Login
status: APPROVED
features: [F-007]
---

# WF-007 — User Registration and Login

Two registration paths exist in the as-built system: (1) web registration via the standard Drupal user form, altered by `lms_base` to enable email-based login and auto-assign the `student` role; and (2) mobile OTP registration via `lms_mobile_api`. Social/OAuth login described in the draft is **not implemented** — no OAuth module or social login integration exists in the current codebase.

## Trigger

- **Web**: Visitor navigates to `/user/register` or clicks a "Register" link in the frontend theme.
- **Mobile**: Flutter app POSTs to `POST /api/v1/user/register`.
- **Login (web)**: Visitor navigates to `/user/login`.
- **Login (mobile)**: Flutter app POSTs to `POST /api/v1/oauth/token`.

## Actors

| Actor | Role |
|-------|------|
| Visitor/Anonymous | Initiates registration or login |
| System (`lms_base`) | Alters forms, validates email login, auto-assigns role |
| System (`lms_mobile_api`) | Handles OTP generation, email dispatch, token issuance |
| Email Service | Sends OTP or Drupal verification email via `plugin.manager.mail` |

## Preconditions

- **Web registration**: Email not already active in the system.
- **Mobile registration**: Email not already active; `lms_mobile_api` module enabled.
- **Mobile OTP email**: Drupal mail system configured with a working outbound mailer.

## Happy Path — Web Registration

| Step | Actor | Action | System Response | Feature |
|------|-------|--------|----------------|---------|
| 1 | Visitor | Navigate to `/user/register` | Drupal renders `user_register_form`; `lms_base` `hook_form_alter` has altered the form: added input placeholders, hidden profile fields (field_first_name, field_last_name, etc.) for anonymous registrants | F-007 |
| 2 | Visitor | Fill in username and email; submit | Drupal core processes standard registration; default account status depends on site settings (`user_register` config) | F-007 |
| 3 | System | `hook_user_insert` fires (lms_base) | If new user has only the `authenticated` role (i.e., standard new user), automatically assigns `student` role | F-007 |
| 4 | System | Drupal sends account verification email (if configured) | Standard Drupal verification email sent via `plugin.manager.mail` | F-007 |
| 5 | Visitor | Clicks email verification link | Account activated; redirected to front or dashboard | F-007 |
| 6 | Visitor | Navigates to `/user/login` | `lms_base` `hook_form_alter` has altered `user_login_form`: label renamed to "Email address"; custom validator `lms_base_user_login_validate` added | F-007 |
| 7 | Visitor | Enters email address as username | `lms_base_user_login_validate` looks up the user account by email and rewrites the username field value before Drupal core validates | F-007 |
| 8 | System | Successful authentication | `hook_user_login` in `lms_base` clears any lingering login-error messages; user is logged in with `student` role | F-007 |

## Happy Path — Mobile Registration (OTP Flow)

| Step | Actor | Action | System Response | Feature |
|------|-------|--------|----------------|---------|
| 1 | Mobile App | `POST /api/v1/user/register` with `{email, password, password_confirm, name?}` | `MobileAuthController::register()`: validates input; rejects if active account for that email already exists; deletes any stale blocked registrations for the same email | F-007 |
| 2 | System | Creates Drupal user with `status=0` (blocked) and role `student` | Generates 6-digit OTP via `OtpService::generateOtpCode()` and a 64-char session token via `OtpService::generateToken()` | F-007 |
| 3 | System | Stores OTP session in `keyvalue.expirable` | Collection `lms_mobile_api.otp`; TTL 900 s (15 min); session payload: `{uid, email, otp_code, type:'register'}` | F-007 |
| 4 | System | Sends OTP email | `OtpService::sendOtpEmail()` dispatches via `plugin.manager.mail`; `hook_mail` key `otp_email` formats subject "Your EverLMS verification code" | F-007 |
| 5 | System | Returns `{status:"otp_sent", otp_token: string}` to client | `otp_token` is the keyvalue key (64-char hex) — client must store and submit with OTP code | F-007 |
| 6 | Mobile App | `POST /api/v1/user/register/verify-otp` with `{otp_token, otp_code}` | `MobileAuthController::verifyRegisterOtp()`: validates session type = `register`; checks code match | F-007 |
| 7 | System | Activates user account (`status=1`) | Deletes OTP session; issues 32-char bearer access token via `issueAccessToken(uid)`: stored in `keyvalue.expirable` collection `lms_mobile_api.tokens`, TTL 604800 s (7 days) | F-007 |
| 8 | System | Returns `{status:"verified", uid, access_token, token_type:"Bearer", expires_in}` | Mobile app stores token for subsequent authenticated requests | F-007 |

## Happy Path — Mobile Login

| Step | Actor | Action | System Response | Feature |
|------|-------|--------|----------------|---------|
| 1 | Mobile App | `POST /api/v1/oauth/token` with `{username (email), password}` | `MobileAuthController::oauthToken()`: looks up active user by email; verifies password via Drupal `password` service | F-007 |
| 2 | System | Password valid | Issues bearer token; returns `{uid, email, name, access_token, token_type:"Bearer", expires_in}` | F-007 |

## OTP Resend Flow

| Step | Actor | Action | System Response |
|------|-------|--------|----------------|
| 1 | Mobile App | `POST /api/v1/user/register/resend-otp` with `{otp_token}` | Retrieves existing OTP session (type=`register`); generates new OTP code + new session token; deletes old session; stores new session; resends email |
| 2 | System | Returns `{status:"otp_resent", otp_token: string}` | New `otp_token` invalidates the previous one |

## Password Reset Flow (Mobile)

| Step | Actor | Action | System Response |
|------|-------|--------|----------------|
| 1 | Mobile App | `POST /api/v1/user/forgot-password` with `{email}` | Always returns 200 to prevent email enumeration; silently no-ops for unknown email; sends OTP for valid active user (type=`forgot_password`) |
| 2 | Mobile App | `POST /api/v1/user/forgot-password/verify-otp` with `{otp_token, otp_code}` | Validates OTP; generates 30-min reset token; returns `{reset_token}` |
| 3 | Mobile App | `POST /api/v1/user/reset-password` with `{reset_token, password, password_confirm}` | Updates password; issues new access token for auto-login |

## Error Paths

| Step | Condition | System Response | Recovery |
|------|-----------|----------------|----------|
| 1 (web) | Email already registered as active user | Drupal shows "The email address ... is already taken" | Use `/user/password` to reset |
| 7 (web) | Email entered but no matching user | `lms_base_user_login_validate` lets Drupal core show its standard "Unrecognized username or password" (does not reveal whether email or password was wrong) | Retry or use password reset |
| 1 (mobile) | Active account already exists for email | 422 `{error:"validation_error", violations:[{field:"email", message:"..."}]}` | Use `/api/v1/oauth/token` to log in instead |
| 1 (mobile) | Passwords don't match or < 8 chars | 422 `{error:"validation_error", violations:[...]}` | Fix and re-submit |
| 4 (mobile) | Email delivery fails | Drupal mail system logs error; OTP session still stored | App may call `/api/v1/user/register/resend-otp` |
| 6 (mobile) | OTP code wrong | 400 `{error:"invalid_otp"}` | Re-submit correct code; or resend OTP |
| 6 (mobile) | OTP session expired (>15 min) | OTP session deleted from keyvalue; 400 `{error:"invalid_otp"}` | Re-start registration from step 1 |
| 2 (login mobile) | Wrong password | 401 `{error:"invalid_grant"}` | Retry or use forgot-password flow |

## Success Criteria

- Web: Drupal user account exists with `student` role; user can log in with email address at `/user/login`.
- Mobile: Drupal user account exists with `status=1`, role `student`; client has a valid bearer token in `keyvalue.expirable` collection `lms_mobile_api.tokens`; token can be used in `Authorization: Bearer <token>` or `X-Authorization: Bearer <token>` header for subsequent API calls.

## Business Rules

- **Email-as-username (web)**: `lms_base` replaces the "Username" label with "Email address" on the login form and rewrites the submitted value from email to Drupal username before core authentication runs. Users must use their email, not their internal Drupal username.
- **Student role auto-assignment**: `hook_user_insert` in `lms_base` assigns the `student` role to any newly created user who has only the `authenticated` role. This runs regardless of registration path (web, Drush, API, admin-created), so long as the user entity insert hook fires.
- **Mobile OTP expiry**: OTP code is valid for 900 s (15 min). The session token exchanged for the OTP is consumed on first successful verification (deleted from keyvalue store).
- **Mobile bearer token**: TTL 7 days by default (`lms_mobile_api.settings:token_expiry_seconds`). No refresh token mechanism — clients must re-authenticate after expiry.
- **Mobile auth header**: The module checks `X-Authorization` header first, then falls back to `Authorization`. This works around potential HTTP Basic Auth gates on the server that strip the standard `Authorization` header.
- **Social/OAuth login**: Not implemented in the current codebase. The `lms_mobile_api.settings.yml` has `oauth_client_id`/`oauth_client_secret` config keys but these are unused — artifacts of an earlier design.
- **Instructor role**: Must be assigned manually by Admin or Manager via `/workspace/users/{user}/admin-edit`. Registration only assigns `student`.
- **Mobile `lms_quiz` coupling**: `MobileLearningController` references `lms_quiz` classes but `lms_quiz` is not declared in `lms_mobile_api.info.yml` dependencies — undeclared implicit dependency.

## Related Docs

- Features: [F-007 User Management](../B03-features/07-user-management.md)
- Workflow: [WF-001 Enrollment & Learning](./WF-001-enrollment-learning.md) (post-registration learning path)
- Source: `web/modules/custom/lms_base/lms_base.module`, `web/modules/custom/lms_mobile_api/src/Controller/MobileAuthController.php`, `web/modules/custom/lms_mobile_api/src/Service/OtpService.php`
