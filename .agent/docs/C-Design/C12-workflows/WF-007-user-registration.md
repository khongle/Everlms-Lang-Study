---
workflow: WF-007
slug: user-registration
mode: as-built
b04_source: B04/WF-007-user-registration.md
adequacy: NOT_RUN
adequacy_run: null
---

# C12 — Workflow: WF-007 User Registration and Login

**Trigger:** Visitor navigates to `/user/register` (web) or mobile app POSTs to `POST /api/v1/user/register`.
**Outcome:** A Drupal user account with `status=1` and `student` role exists; the actor can authenticate via email login (web) or a bearer token (mobile).
**B04 source:** [`../../B-Requirements/B04-business-workflows/WF-007-user-registration.md`](../../B-Requirements/B04-business-workflows/WF-007-user-registration.md)
**Mode:** as-built

---

## Actors

| Actor (C02 role) | Step participation | Permissions needed |
|---|---|---|
| Anonymous / Visitor | Initiates registration or login | None required |
| System (`lms_base`) | Alters login/register forms; auto-assigns `student` role on user insert | — |
| System (`lms_mobile_api`) | Handles OTP generation, email dispatch, bearer token issuance | — |
| Email Service | Sends OTP email or Drupal verification email | Drupal mail system configured |

## Entry points

| URL (C07) | Page (C11) | Method | Form | Feature flag |
|---|---|---|---|---|
| `/user/register` | drupal-register | GET/POST | `user_register_form` (altered by `lms_base`) | — |
| `/user/login` | drupal-login | GET/POST | `user_login_form` (altered by `lms_base`) | — |
| `POST /api/v1/user/register` | — (mobile API) | POST | JSON body | `lms_mobile_api` enabled |
| `POST /api/v1/user/register/verify-otp` | — (mobile API) | POST | JSON body | `lms_mobile_api` enabled |
| `POST /api/v1/user/register/resend-otp` | — (mobile API) | POST | JSON body | `lms_mobile_api` enabled |
| `POST /api/v1/oauth/token` | — (mobile API) | POST | JSON body | `lms_mobile_api` enabled |
| `POST /api/v1/user/forgot-password` | — (mobile API) | POST | JSON body | `lms_mobile_api` enabled |
| `POST /api/v1/user/forgot-password/verify-otp` | — (mobile API) | POST | JSON body | `lms_mobile_api` enabled |
| `POST /api/v1/user/reset-password` | — (mobile API) | POST | JSON body | `lms_mobile_api` enabled |

## Entities touched

| Entity (C04) | Operation | Fields written | State transition |
|---|---|---|---|
| Drupal `user` | CREATE | `name`, `mail`, `pass`, `roles=['student']`, `status` | `status=0` (blocked, mobile path) → `status=1` (active, after OTP verify) |
| `keyvalue.expirable` (`lms_mobile_api.otp`) | CREATE / DELETE | OTP session payload: `{uid, email, otp_code, type}` | — (TTL 900 s) |
| `keyvalue.expirable` (`lms_mobile_api.tokens`) | CREATE | `{token => uid}` | — (TTL 604 800 s, 7 days) |
| `keyvalue.expirable` (`lms_mobile_api.reset`) | CREATE / DELETE | Reset session payload: `{uid, email}` | — (TTL 1 800 s) |

## State transitions

```
Web path:
  [Visitor submits /user/register]
    → Drupal user entity created (status per site config, typically blocked until email verification)
    → hook_user_insert (lms_base) assigns student role if only authenticated role present
    → Email verification link sent (standard Drupal)
    → [Visitor clicks link] → status=1 (active)

Mobile registration path:
  [POST /api/v1/user/register]
    → Drupal user status=0 (blocked), role=student created
    → OTP session stored in keyvalue.expirable (TTL 900 s)
    → OTP email sent

  [POST /api/v1/user/register/verify-otp]
    → user status=0 → status=1 (active)
    → OTP session deleted
    → Bearer token issued (keyvalue.expirable, TTL 604 800 s)

Mobile login path:
  [POST /api/v1/oauth/token]
    → Password verified via Drupal password service
    → Bearer token issued

Mobile forgot-password path:
  [POST /api/v1/user/forgot-password]
    → OTP email sent (type=forgot_password), TTL 900 s

  [POST /api/v1/user/forgot-password/verify-otp]
    → OTP validated, deleted
    → Reset token issued (keyvalue.expirable, TTL 1 800 s)

  [POST /api/v1/user/reset-password]
    → Password updated
    → Reset session deleted
    → Bearer token issued (auto-login)
```

State guards (who can trigger which transition):

| Transition | Trigger | Guard | Side effects |
|---|---|---|---|
| Create user (web) | Visitor submits `user_register_form` | Anonymous access to `/user/register` | `hook_user_insert` assigns `student` role |
| Create user (mobile) | `MobileAuthController::register()` | None (public endpoint) | User created `status=0`; OTP email dispatched |
| Activate user (mobile) | `MobileAuthController::verifyRegisterOtp()` | Valid `otp_token` + matching `otp_code` in keyvalue store | Bearer token issued; OTP session deleted |
| Login (mobile) | `MobileAuthController::oauthToken()` | Valid email + password match via `password` service | Bearer token issued |
| Reset password (mobile) | `MobileAuthController::resetPassword()` | Valid `reset_token` in keyvalue store | Password updated; reset session deleted; bearer token issued |

## Modules involved (C05)

| Module | Responsibility in this workflow |
|---|---|
| [`lms_base`](../C05-modules/) | `hook_form_alter`: renames login label to "Email address", adds `lms_base_user_login_validate` custom validator; `hook_user_insert`: auto-assigns `student` role; `hook_user_login`: clears lingering login-error messages |
| [`lms_mobile_api`](../C05-modules/) | `MobileAuthController` (10 endpoints); `OtpService` (OTP generation, keyvalue storage, email dispatch); `hook_mail` key `otp_email` |

## Permissions

| Step | Public / Anonymous | Authenticated (student) | Admin/Manager |
|---|---|---|---|
| Access `/user/register` | ✓ | — | — |
| Access `/user/login` | ✓ | — | — |
| `POST /api/v1/user/register` | ✓ (no auth) | — | — |
| `POST /api/v1/oauth/token` | ✓ (no auth) | — | — |
| `POST /api/v1/user/forgot-password` | ✓ (no auth) | — | — |
| `GET /api/v1/user/profile` | — | ✓ (Bearer token) | ✓ |
| `POST /api/v1/user/change-password` | — | ✓ (Bearer token) | ✓ |
| Assign `instructor` or `manager` role | — | — | ✓ (Admin/Manager via `/workspace/users/{user}/admin-edit`) |

All mobile API routes have `_access: 'TRUE'` at the Drupal routing layer — auth is enforced inside controllers via bearer token resolution from `keyvalue.expirable`.

## Notifications

| Trigger | Recipient | Channel | Template |
|---|---|---|---|
| Mobile OTP — registration | Registrant | Email | `hook_mail` key `otp_email`, type=`register`; subject "Your EverLMS verification code"; OTP + 15-min expiry notice |
| Mobile OTP — forgot password | Registrant | Email | `hook_mail` key `otp_email`, type=`forgot_password`; subject "Your EverLMS password reset code" |
| Web registration | Registrant | Email | Standard Drupal email verification (not customized by EverLMS) |

**Email stub risk:** `lms_notification.manager` email/push channels are stubs. OTP email dispatch in `lms_mobile_api` uses `plugin.manager.mail` directly (not via NotificationManager) — this channel is real.

## Cache invalidation

| Step | Cache tag(s) | Search index | List view(s) |
|---|---|---|---|
| User created | `user:{uid}` | — | `/workspace/users` admin list |
| User activated | `user:{uid}` | — | — |
| Bearer token created | (keyvalue, no Drupal cache) | — | — |

## Operations queues

| Page (C11) | Filters | Owner role |
|---|---|---|
| `/workspace/users` (`AdminController::userList()`) | role, status, search | Admin, Manager |
| `/workspace/users/create` | — | Admin, Manager |
| `/workspace/users/{user}/admin-edit` | — | Admin, Manager |

No queue processing in this workflow. OTP expiry and token TTL are enforced by `keyvalue.expirable` store.

## Editorial / management surfaces

| Entity / content source | Create/Edit UI | Role / permission | Revision/moderation state | Translation | Visible C11 surfaces |
|---|---|---|---|---|---|
| Drupal `user` | `/workspace/users/create`, `/workspace/users/{user}/admin-edit` | `administer lms_course` + `_entity_access: user.update` | No Drupal moderation; `status` boolean | Not translatable | `/workspace/users`, `/profile` |

## Failure modes

| Failure | Detection | Handling |
|---|---|---|
| Email already registered (web) | Drupal core detects duplicate email on form validation | Shows "The email address … is already taken" |
| OTP email delivery fails (mobile) | `plugin.manager.mail` logs error; OTP session still stored | App calls `POST /api/v1/user/register/resend-otp` |
| OTP code wrong (mobile) | `verifyRegisterOtp()` checks code mismatch | 400 `{error:"invalid_otp"}` |
| OTP session expired >15 min (mobile) | `OtpService::getOtpSession()` returns NULL | 400 `{error:"invalid_otp"}`; user must restart registration from step 1 |
| Wrong password on login (mobile) | `password` service check fails | 401 `{error:"invalid_grant"}` |
| Bearer token expired (7-day TTL) | `keyvalue.expirable` returns NULL for token | `getAuthenticatedUser()` throws `AccessDeniedHttpException` (403); client must re-authenticate via `/api/v1/oauth/token` |
| Stale blocked registration exists (mobile re-register) | `register()` detects `status=0` account with same email | Deletes stale blocked account before creating new one |

## Known design constraints

- **No refresh token:** Mobile bearer tokens have 7-day TTL. No refresh mechanism. Clients must re-authenticate via `POST /api/v1/oauth/token` after expiry.
- **Authorization header conflict:** `getAuthenticatedUser()` checks `X-Authorization` first, then `Authorization` — non-standard workaround for servers that strip the `Authorization` header for Basic Auth.
- **`oauth_client_id`/`oauth_client_secret` in settings.yml:** Config keys present but unused in code — artifacts of an earlier OAuth2 design that was replaced by the custom keyvalue token approach.
- **Social/OAuth login:** Not implemented. No OAuth module installed.
- **Instructor role assignment:** Manual only — Admin/Manager must assign via `/workspace/users/{user}/admin-edit`. Registration grants `student` only.
- **`student` role auto-assignment scope:** `hook_user_insert` in `lms_base` fires for all user creation paths (web, Drush, API, admin-created), so any new user created via the LMS gets the `student` role unless they already have a non-`authenticated` role.

## Acceptance (B05)

- B05 acceptance criteria not yet materialized.

## Related

- Modules: [`lms_base`](../C05-modules/), [`lms_mobile_api`](../C05-modules/)
- Roles: [`C02-user-roles.md`](../C02-user-roles.md)
- API: [`C06-apis/`](../C06-apis/)
- B04 source: [`../../B-Requirements/B04-business-workflows/WF-007-user-registration.md`](../../B-Requirements/B04-business-workflows/WF-007-user-registration.md)
- Downstream workflow: [`WF-001-enrollment-learning.md`](WF-001-enrollment-learning.md) (post-registration learning path)
