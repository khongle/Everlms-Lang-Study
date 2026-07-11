# C12 Workflow - WF-007 User registration & onboarding

## Business source
[B04 WF-007](../../B-Requirements/B04-business-workflows/WF-007-user-registration-and-onboarding.md)

## Modules
user (core), [lms_mobile_api](../C05-modules/lms_mobile_api.md), [lms_notification](../C05-modules/lms_notification.md)

## Related features (B03)
[F-021](../../B-Requirements/B03-features/F-021-users-roles-and-impersonation.md), [F-019](../../B-Requirements/B03-features/F-019-mobile-app-api.md)

## State model
Account lifecycle:

| State | Meaning |
| ----- | ------- |
| submitted (registered) | Web account created, or mobile registration request accepted. |
| pending verify | Web email instruction sent, or mobile account blocked while OTP is pending. |
| active | Account is active with the `student` role. Mobile verification also issues a bearer token. |
| blocked/archived | Account blocked by admin or invalid/stale pending mobile registration. |

## Permission model
- Initiator: visitor (register).
- Admin: manager creates/blocks users; assigns roles.

## Concrete flow
### Web registration
1. Visitor opens `/user/register`.
2. Because `user.settings:verify_mail` is true, the web form collects email and
   username only. Password creation happens through the emailed instructions.
3. Visitor submits the form.
4. Drupal creates an active account and `lms_base_user_insert()` assigns the
   `student` role.
5. The homepage shows a visible success alert confirming that email instructions
   were sent.

### Mobile registration
1. Mobile client calls `POST /api/v1/user/register` with name, email, password,
   and password confirmation.
2. `lms_mobile_api` creates a blocked user with `student` role and stores a
   6-digit OTP session in the expirable key-value store.
3. Mobile client may call `POST /api/v1/user/register/resend-otp`; the old OTP
   token is invalidated and a new one is issued.
4. Mobile client calls `POST /api/v1/user/register/verify-otp`.
5. On valid OTP, the user is activated and a bearer token is returned.
6. Mobile client calls `GET /api/v1/user/me` using `X-Authorization: Bearer ...`
   and receives the authenticated profile plus roles.

## Implementation requirements
- `lms_mobile_api` must be enabled in active config and config sync; otherwise
  the documented `/api/v1/user/*` routes return 404.
- Mobile duplicate-email registration returns `422 validation_error` **when an active account already uses that email**. Re-registering a still-pending (blocked/unverified) email is allowed — the stale blocked user is deleted and a fresh OTP session is issued (retry UX). *(Round-2 verified: active-email dup → 422; pending re-register → new OTP.)*
- API screenshots should redact OTP/access tokens while proving status codes and
  response shapes.
- Web screenshots must capture before-fill, after-fill, and submitted
  confirmation states.

## Notifications
Welcome/verification (web email; mobile OTP). Mobile bearer token issued on OTP verify.

## Round-2 QA evidence
Verified healthy (no code changes needed). Web: `/user/register` → active account + `student` role (uid 106) + homepage success alert. Mobile API end-to-end: register → verify-otp (uid 109, Bearer, `expires_in` 604800) → `/api/v1/user/me` (roles `student`) → active-email duplicate `422`. Screens: `.agent/docs/E-Quality/E00-workflows/round-2/_screens/007-user-registration-and-onboarding/` (incl. `09-mobile-api-flow.txt`, OTP/tokens redacted).
