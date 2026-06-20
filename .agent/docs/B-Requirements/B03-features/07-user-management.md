---
id: F-007
name: User Management
priority: Must
status: APPROVED
parent-requirements: [BR-001]
owning_modules: [lms_base, lms_course, open_switcher]
primary_source: C05
story_anchors: []
---

# F-007 — User Management

## User story

As an **administrator or manager**, I want to create, list, filter, bulk-activate, and export user accounts with assigned roles, so that I can onboard learners at scale and maintain safe access control across the platform.

## Description

User management in EverLMS is built on Drupal 11 core user entities, extended by custom modules. `lms_base` auto-assigns the `student` role to every new registration (`hook_user_insert`) and converts the login form to accept email addresses instead of usernames (`lms_base_user_login_validate`). The platform defines six non-anonymous roles (student, instructor, manager, content_editor, administrator, authenticated) with permissions configured in `config/sync/`.

The admin workspace at `/workspace/users` (served by `lms_course.AdminController::userList`) provides a paginated, filterable user list with role/status/search filters. Security enforced: managers cannot see or edit administrator-role accounts (`hook_entity_access`). Bulk activate/deactivate is at `/workspace/users/bulk`; XLSX export is available when `shuchkin/simplexlsxgen` is installed. Individual user edit uses `/workspace/users/{user}/admin-edit` with role-sensitive form (admin_edit vs manager_edit mode).

User creation at `/workspace/users/create` (`AdminController::userCreateForm`) allows admin/manager to provision accounts programmatically. User profile is at `/profile` (requires login; renders via `lms_progress.UserPortalController::profile`). The instructor public profile page lives at `/instructor/{user}` and shows bio, social links, courses, student count, and rating statistics.

Impersonation (user switching) is provided by the `open_switcher` module (package: "Open Platform", not EverLMS-specific). Admins and managers with the `switch users` permission can impersonate any non-admin account at `/app/switch-user` and switch back via session.

Social login is installed (`social_auth` module, views configured) but **no OAuth providers are configured** in `social_auth.settings.yml` — the feature is present in infrastructure but not operationally active.

Password policy: `password_policy` module is installed with user fields (`field_last_password_reset`, `field_password_expiration`, `field_pending_expire_sent`) but no active policies are defined beyond the cron threshold.

AI-assisted user provisioning is available via `lms_ai.AiEngineUserForm` — generates user JSON via OpenAI, saves to `JSON/ai_users.json`, and creates/updates Drupal user entities via AJAX handlers. Avatars are AI-generated and saved as managed files.

## In scope

### Authentication (as-built)

- Email-based login: `lms_base` converts email input to Drupal username before core validation.
- Standard Drupal password reset flow (forgot password via `/user/password`).
- Auto-assignment of `student` role on new registration (`lms_base.hook_user_insert`).
- Login form UX: label changed to "Email address", placeholder text added for register/login/reset forms.
- Post-login success message cleanup (`hook_user_login` removes lingering error messages).

### Roles (as-built — 7 roles, config/sync)

| Role | Key permissions |
|---|---|
| `anonymous` | access content, search, restricted_html |
| `authenticated` | access content, post comments, basic_html + full_html |
| `student` | access/edit own comments, post comments (inherits authenticated) |
| `instructor` | administer comments, edit own comments (inherits authenticated) |
| `manager` | administer lms_course, create/edit/delete/view lms_course, switch users, administer lms_course (workspace shell) |
| `content_editor` | access admin pages, create/edit/delete own article + page content, URL aliases, file management |
| `administrator` | all permissions (Drupal bypass) |

### User administration workspace (as-built)

- Paginated user list at `/workspace/users` with role, status, search filters; AJAX-capable (`ajax=1` returns table partial).
- Security: managers cannot list or view administrator-role users (enforced in `CourseAccessControlHandler` and `hook_entity_access`).
- Bulk activate/deactivate at `/workspace/users/bulk` (POST); XLSX export if `shuchkin/simplexlsxgen` is installed.
- User create at `/workspace/users/create` — creates Drupal user entity and embeds registration form.
- User edit at `/workspace/users/{user}/admin-edit` — role-aware form (admin_edit or manager_edit) via `hook_entity_type_alter`.

### User profile (as-built)

- `/profile` — authenticated users; renders via `UserPortalController::profile` using Drupal 'full' view mode.
- `/instructor/{user}` — public instructor profile with bio, social links, courses taught, student/rating stats.
- Dashboard at `/dashboard` — enrolled courses, progress stats, "continue learning" URL.
- My Courses at `/my-courses` — full course list with per-course progress.

### Impersonation / user switching (as-built)

- `open_switcher` module: admins/managers switch to non-admin accounts at `/app/switch-user`.
- Switch back at `/admin/switch-back` (session-based, canSwitchBack access check).
- JS dialog confirms switch action; impersonation banner injected into account menu.

### AI-assisted user provisioning (as-built)

- `lms_ai.AiEngineUserForm` at `/admin/everlms-ai-engine` — generates user JSON, creates/updates Drupal users, generates and attaches AI-generated avatars.

## Out of scope

- Social login (Google, Facebook, LinkedIn) — infrastructure installed (`social_auth`) but no providers configured. Target-state.
- Two-factor authentication (2FA/TOTP/SMS) — not installed or configured.
- Login history / audit trail per user — no per-login tracking table.
- Account lockout after failed attempts — not implemented; no brute-force protection beyond Drupal core flood table.
- "Remember me" persistent session — standard Drupal session behaviour only.
- User import from CSV — the `everlms:import` Drush command handles JSON-format user import from `.agent/generated/users/`; no direct CSV upload UI.
- Student-facing privacy controls (public/private profile toggle) — not implemented.
- Role-based content visibility for `instructor` via standard permissions — instructor permissions are thin; access is enforced via entity ownership checks in module code.

## Acceptance summary

An administrator or manager can navigate to `/workspace/users`, filter by role or status, bulk-activate accounts, and export a spreadsheet. They can create a new user at `/workspace/users/create` and edit an existing one at `/workspace/users/{user}/admin-edit` without seeing administrator-role accounts if they are a manager. A new self-registration automatically receives the `student` role. An impersonator with `switch users` permission can test the learner experience via `/app/switch-user`. Full Given/When/Then in `B05-acceptance-criteria/F-007.md`.

## Content ownership / editability

| Visible content family | Business owner | Editor role | Expected edit surface | Notes |
|---|---|---|---|---|
| User accounts (name, email, roles, status) | Administrator / Manager | administrator, manager | `/workspace/users/create`, `/workspace/users/{user}/admin-edit` | Managers cannot edit administrators |
| User profile fields (avatar, bio, phone) | User (self) | authenticated | `/user/{uid}/edit` Drupal core form | Profile fields added to user entity |
| Instructor public profile | Instructor | administrator, manager | `/workspace/users/{user}/admin-edit` | Displayed at `/instructor/{user}` |
| Role assignments | Administrator | administrator | Via user edit form | `manager` role grants `switch users` |

## Dependencies (requirement-level only)

| Type | Reference | Reason |
|---|---|---|
| Feature | F-001 | Course enrollment requires authenticated + student/instructor role |
| Feature | F-003 | Learning progress tracked per user entity |
| Feature | F-004 | Orders and memberships are owned by user entities |

## Open questions

- [ ] Social login: which OAuth providers (Google, Facebook, LinkedIn) should be activated first, and in which environment?
- [ ] `instructor` role has very thin permissions — is course ownership enforcement purely via code (entity access hooks) or should explicit `create/edit own lms_course` permissions be added to `instructor` config?
- [ ] Should 2FA be a requirement before production launch, or deferred?
- [ ] Is CSV user import required in addition to the JSON-based `everlms:import` Drush command?
