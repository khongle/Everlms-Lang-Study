# ISSUE-006 — lms_progress hook_form_alter Grants All Text Formats to Authenticated Role at Form-Alter Time

| Field | Value |
|---|---|
| ID | `ISSUE-006` |
| Title | `lms_progress_form_comment_lesson_comment_form_alter` mutates and saves the `authenticated` Role entity — granting all text formats unconditionally |
| Source | G4-Security / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `MEDIUM` |
| Affected page / route | `GET/POST /learn/{lms_course}/{lms_lesson}` (lesson view with comment form) |
| Owning Epic | E-10 (G-HARDENING) |
| Owning Story | [`hardening-security`](../../../D-Development/D03-stories/hardening-security.md) |
| Owning module(s) | `lms_progress` |

## Summary

`lms_progress.module` implements `hook_form_BASE_FORM_ID_alter()` for the `comment.lesson_comment` form. The implementation contains a workaround block (marked `FIXME` in the source) that:

1. Loads the `authenticated` Role entity.
2. Grants the `use text format basic_html` permission to it.
3. Grants the `use text format full_html` permission to it.
4. **Saves the Role entity to the database** — modifying a global config entity as a side-effect of rendering a form.

This has two serious problems:

**Security problem:** `full_html` text format allows unfiltered HTML including `<script>` tags and other XSS vectors. Granting `full_html` to **all authenticated users** means any logged-in user who submits a lesson comment can inject arbitrary HTML/JavaScript — a stored XSS vulnerability for every lesson page.

**Architecture problem:** A `hook_form_alter()` is called on every form render. Modifying and saving a config entity (the `authenticated` role) inside `hook_form_alter` is an extreme side-effect: it triggers a config save on every lesson page load, pollutes the config management system, and can cause unexpected permission drift during deployment if `drush cim` overwrites the change.

The `config/sync/user.role.authenticated.yml` already grants `use text format basic_html` and `use text format full_html` via the normal role config, which means the hook_form_alter workaround is doubly redundant in the current config export. However, the code path still executes (and calls `$role->save()`) on every lesson comment form render.

## Reproduction

1. Open `web/modules/custom/lms_progress/lms_progress.module`.
2. Locate `lms_progress_form_comment_lesson_comment_form_alter()`.
3. Find the block commented `// FIXME: Workaround to grant text format...`:
   ```php
   $role = \Drupal\user\Entity\Role::load('authenticated');
   $role->grantPermission('use text format basic_html');
   $role->grantPermission('use text format full_html');
   $role->save();
   ```
4. Load any lesson page (e.g., `/learn/1/1`) as an authenticated user with the comment form present.
5. Check the Drupal database log (`/admin/reports/dblog`) — a config entity save event for `user.role.authenticated` is recorded on each page load.
6. Confirm that authenticated users who submit lesson comments can include `<script>alert(1)</script>` in the comment body and have it rendered unescaped (because `full_html` is granted).

## Root Cause

The hook was written as a workaround for a text-format access error on the comment form — the comment's `comment_body` field uses a text format that the user (at the time of writing) did not have permission to use. Rather than fixing the underlying permission config or restricting the comment field to a safe format, the developer added a runtime permission grant inside the form alter.

The correct fix is to either:
- Configure the `lesson_comment` comment type to use `restricted_html` or `basic_html` (not `full_html`) and ensure the `authenticated` role has that format permission via config.
- Or grant `basic_html` (but never `full_html`) in config, and set the comment field's `#allowed_formats` to `['basic_html', 'restricted_html']`.

The workaround was never removed after the config permissions were correctly set.

## Proposed Fix

1. **Remove the `$role->save()` block** from `lms_progress_form_comment_lesson_comment_form_alter()` immediately — this is the most dangerous part.
2. **Audit `lesson_comment` text format** — set the comment's `comment_body` field default format to `basic_html` or `restricted_html`. Never `full_html` for user-submitted content.
3. **Restrict allowed formats on the form** — in `hook_form_alter`, set:
   ```php
   $form['comment_body']['widget'][0]['#allowed_formats'] = ['basic_html', 'restricted_html'];
   ```
   instead of granting elevated permissions.
4. **Verify authenticated role permissions in config** — ensure `config/sync/user.role.authenticated.yml` does NOT grant `use text format full_html` to the authenticated role (currently it does — this should be restricted to `manager`, `instructor`, and `administrator` roles only).
5. **Run `drush cim`** after config changes to ensure the role entity is clean.

## Trace links

- **Story:** `D-Development/D03-stories/hardening-security.md` §Scope "Access control audit", "DI cleanup"
- **C05 module:** `C-Design/C05-modules/lms_progress.md`
- **Facts base:** `modules-progress-comms.md` §lms_progress.module — hook implementations; `config-inventory.md` §3.2 Roles x Permissions Matrix (authenticated role)
- **Related issues:** [ISSUE-007](ISSUE-007-progress-cascade-race-condition.md) (lms_progress co-issues)
- **K04:** Reusable rule — never save config entities inside `hook_form_alter()`; permission grants must go through config management, not runtime code.
