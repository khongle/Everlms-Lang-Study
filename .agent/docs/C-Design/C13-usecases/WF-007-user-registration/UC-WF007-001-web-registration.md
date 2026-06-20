---
usecase_id: UC-WF007-001
workflow: WF-007
type: happy
title: Web Registration — Student Role Auto-Assignment
status: as-built
---

# UC-WF007-001 — Web Registration: Student Role Auto-Assignment

**Purpose:** Visitor registers via `/user/register`; `lms_base` assigns the `student` role automatically; visitor logs in with email address.

---

## Actors

| Actor | Type | Notes |
|-------|------|-------|
| Visitor (anonymous) | Primary | Initiates registration |
| System (`lms_base`) | Secondary | Alters forms, auto-assigns role via `hook_user_insert` |
| Email Service | Secondary | Sends account verification email via `plugin.manager.mail` |

---

## Preconditions

| # | Condition |
|---|-----------|
| P1 | Email address is not already registered as an active Drupal user |
| P2 | Drupal `user_register` config allows visitor registration (or registration is open) |
| P3 | `lms_base` module is enabled |

---

## Trigger

Visitor navigates to `/user/register` or clicks a "Register" link in the `lms_theme` frontend.

---

## Main Flow

| Step | Given | When | Then |
|------|-------|------|------|
| 1 | Visitor is anonymous on `/user/register` | Page loads | `lms_base` `hook_form_alter` alters `user_register_form`: adds input placeholders; hides `field_first_name`, `field_last_name`, and other profile fields for anonymous registrants |
| 2 | Form is rendered | Visitor fills email, username, password fields and submits | Drupal core `user_register_form` validation runs; standard Drupal user entity is created with `status` per site config |
| 3 | User entity insert fires | Drupal saves the new user entity | `lms_base` `hook_user_insert()` checks: if new user has only the `authenticated` role, the `student` role is added and user entity is re-saved |
| 4 | User entity saved with `student` role | Account creation complete | Drupal sends account verification email via `plugin.manager.mail` if email verification is configured |
| 5 | Visitor receives verification email | Visitor clicks verification link | Account activated (`status=1`); visitor redirected to front or `/dashboard` |
| 6 | Visitor is now at `/user/login` | Visitor enters their email address as username | `lms_base` `hook_form_alter` has altered `user_login_form`: label renamed to "Email address"; `lms_base_user_login_validate` added as form validator |
| 7 | Login form submitted with email value | `lms_base_user_login_validate` fires | Validator looks up Drupal username by email (`user_load_by_mail`); rewrites the `name` field value before Drupal core validation runs |
| 8 | Core validation passes | Drupal authenticates the user | `lms_base` `hook_user_login` clears any lingering login-error messages; user session established with `student` role |

---

## Entities Mutated

| Entity | Field | Change |
|--------|-------|--------|
| `user` (Drupal core) | `roles` | `hook_user_insert`: adds `student` role if only `authenticated` on insert |
| `user` | `status` | Set to `1` on email link click (standard Drupal flow) |

No custom LMS entity is created at registration time. Enrollment entities (`lms_user_course`) are created separately at checkout.

---

## Observability Hooks

| Hook / Event | Module | What to check |
|--------------|--------|----------------|
| `hook_user_insert` | `lms_base` | Role `student` must be in `$account->getRoles()` after user save |
| `hook_form_alter` on `user_register_form` | `lms_base` | Profile fields hidden; placeholders present |
| `hook_form_alter` on `user_login_form` | `lms_base` | Label is "Email address"; custom validator attached |
| `hook_user_login` | `lms_base` | No stale error messages after successful login |
| Drupal core `hook_mail` | Drupal core | Verification email dispatched if `user_register` config = `visitors_admin_approval` or email verification enabled |

---

## Acceptance Link

| AC ID | Summary | Planned Test |
|-------|---------|--------------|
| [AC-069](../../../B-Requirements/B05-acceptance-criteria/AC-07-user-management.md#ac-069-email-based-registration-assigns-student-role) | Email-based registration assigns student role | T-069 (planned, E03 — not yet materialized) |
| [AC-070](../../../B-Requirements/B05-acceptance-criteria/AC-07-user-management.md#ac-070-login-by-email-address) | Login by email address | T-070 (planned, E03 — not yet materialized) |

---

## Notes

- The `hook_user_insert` role-assignment fires for **any** new user insert, regardless of path (web form, Drush, admin UI, API). Users created by `lms:generate-demo` Drush command also receive the `student` role.
- Social/OAuth registration is **not implemented**. `lms_mobile_api.settings.yml` contains `oauth_client_id` and `oauth_client_secret` keys but these are unused artifacts of an earlier design.
- Instructor role must be manually assigned by Admin or Manager via `/workspace/users/{user}/admin-edit`.
- The `hook_form_alter` for `user_register_form` hides profile fields (`field_first_name`, etc.) for anonymous registrants; these fields can be filled later on the user profile edit page.
