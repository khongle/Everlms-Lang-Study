---
feature: F-007
status: DRAFT
---

# B05 — Acceptance Criteria for F-007 — User Management

> Per-feature Given/When/Then criteria grounded in code. Cross-feature traceability lives in `E05-traceability.md`.
>
> Modules: `lms_base`, `open_switcher`. Roles defined in `config/sync/user.role.*.yml`.

---

## AC-069: Email-based registration assigns student role

**Priority:** Must
**Given** an anonymous visitor submits `/user/register` with a valid, unique email, strong password, and matching password confirmation
**When** the Drupal user entity is saved (status=1, role=`authenticated`)
**Then** `lms_base` `hook_user_insert()` auto-assigns the `student` role to the new account (any user whose only role on insert is `authenticated`)
**And** the user is redirected to a confirmation page and can log in immediately

---

## AC-070: Login by email address

**Priority:** Must
**Given** a registered user with email `learner@example.com`
**When** the user enters their email (not username) and correct password on `/user/login`
**Then** `lms_base_user_login_validate()` converts the email to the Drupal username before core validation fires
**And** login succeeds; the user is redirected to `/dashboard`

---

## AC-071: Login with invalid credentials

**Priority:** Must
**Given** the login form is displayed
**When** a user submits a wrong password or unknown email
**Then** the form returns a generic "Unrecognized username or password" message (no email enumeration)
**And** `lms_base` `hook_user_login()` clears stale login-error messages so they do not persist after a subsequent successful login

---

## AC-072: Role-based access enforcement

**Priority:** Must
**Given** the seven custom roles are defined (`anonymous`, `authenticated`, `student`, `instructor`, `manager`, `content_editor`, `administrator`) with permissions as in `config/sync/user.role.*.yml`
**When** a `student`-role user requests a route guarded by `_permission: administer lms_course` (e.g. `/workspace/courses`)
**Then** Drupal returns HTTP 403 and does not leak content about the resource
**And** a `manager` with `administer lms_course` permission can access the same route

---

## AC-073: Impersonation (switch-user) by manager

**Priority:** Must
**Given** a user with the `switch users` permission (granted to `manager` in config)
**When** they navigate to `/admin/switch-user`, select a non-admin target account, and click Switch
**Then** `open_switcher` `SwitchController::switchTo()` impersonates the target; the header shows a "Switch Back" link via `open_switcher.switch_back` route
**And** impersonating an `administrator`-role account is blocked (the frontend at `/app/switch-user` also provides this flow for non-Claro contexts)

---

## AC-074: Admin user list — filter by role and status

**Priority:** Must
**Given** a user with `administer users` (administrator) is on `/admin/people/list`
**When** they apply the exposed filter "Roles" = `student` and "Status" = `Active`
**Then** the `user_admin_people` View returns only active student accounts, paginated (fields: name, status, roles, created, access, operations, mail)
**And** bulk-action controls (block, unblock, etc.) appear and operate on selected rows

---

## AC-075: User profile update — name, avatar, and notification preferences

**Priority:** Must
**Given** a logged-in student is on their profile edit page
**When** they update `field_first_name`, `field_last_name`, `user_picture`, and save
**Then** the changes are persisted to `users_field_data` and related field tables
**And** avatar is stored as a managed file; image styles (`thumbnail` 100×100, `medium` 220×220) regenerate on next render via WebP conversion
**And** notification preferences (managed by `lms_notification` `NotificationPreferencesForm` at `/manage/settings/notifications`) are stored via `user.data` service under `pref_{type}` keys for 9 event types

---

## AC-076: Deactivated account loses session access

**Priority:** Must
**Given** an administrator blocks a currently-logged-in user via `/admin/people/list`
**When** the blocked user makes their next authenticated request
**Then** Drupal's session validation detects the blocked status and treats the session as invalid; the user is redirected to the login page with an "account is blocked" message
**And** no LMS data is exposed after the block takes effect

> **Status note:** This relies on standard Drupal session invalidation, not a custom EverLMS mechanism.

---

## Local traceability (F-007 only)

| AC ID  | Test Case | Implementing Story / Module       | Status  |
|--------|-----------|-----------------------------------|---------|
| AC-069 | T-069     | — / lms_base                      | PENDING |
| AC-070 | T-070     | — / lms_base                      | PENDING |
| AC-071 | T-071     | — / lms_base                      | PENDING |
| AC-072 | T-072     | — / Drupal core roles + config    | PENDING |
| AC-073 | T-073     | — / open_switcher                 | PENDING |
| AC-074 | T-074     | — / config:view.user_admin_people | PENDING |
| AC-075 | T-075     | — / lms_base, lms_notification    | PENDING |
| AC-076 | T-076     | — / Drupal core session           | PENDING |

> Cross-feature traceability lives in `E05-traceability.md`.
