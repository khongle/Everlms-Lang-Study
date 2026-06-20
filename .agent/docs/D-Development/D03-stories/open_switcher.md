# D03 — Story Spec: Open Switcher

**Story ID:** open_switcher
**Story type:** module
**Title:** Administrator user-impersonation utility with session tracking and JS confirmation dialog
**Primary source:** `../../C-Design/C05-modules/open_switcher.md`
**Owning module(s):** open_switcher
**Service(s):** _(none — no services.yml)_
**Epic:** E-01 (Foundation & Theme)
**Contributes to Feature(s):** F-007 (User Management — support/testing tooling)
**Design source(s):** `../../C-Design/C05-modules/open_switcher.md`
**Round:** N/A
**Driver:** SA directive — support and testing utility (package: Open Platform, not EverLMS)
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** pre-2026-04-15 (pre-framework)
**Tasks:** see `../D04-tasks/` filtered by `Story ID: open_switcher`

---

## Session Strategy

**Estimated session count:** 1 (small)
**Splitting strategy:** N/A — single cohesive session: controllers + form + hooks + template.

---

## Scope (from primary source)

Standalone user-impersonation utility for administrators and support staff:

1. **SwitchController** — `switchTo()` (validates target, stores original uid/name in session, fires JS dialog, executes `user_login_finalize`), `switchBack()` (restores original session), `canSwitchBack()` (custom access check on session key).
2. **SwitchFrontendController** — `/app/switch-user` page listing non-admin, non-anonymous users with optional `?role=` filter.
3. **SwitchUserForm** — admin `/admin/switch-user` paginated form (25/page) with role filter and "Switch" links.
4. **hook_theme / hook_preprocess_menu / hook_page_attachments** — registers `open_switcher_page` theme hook; injects impersonation state into account menu template; attaches `impersonation_dialog` library with one-shot `drupalSettings` dialog config.
5. **Template** — `open-switcher-page.html.twig` frontend UI with impersonation banner and user table (EverLMS `oc-*` CSS classes).
6. **`impersonation_dialog` library** — `css/impersonation-dialog.css` + `js/impersonation-dialog.js`; displays one-shot confirmation dialog after session switch.

**Package:** `Open Platform` (not `EverLMS`). No dependency on any `lms_*` module. Supports Drupal `^10.3 || ^11`.

Routes: 3 Admin (`/admin/switch-user`, `/admin/switch-user/{user}`, `/admin/switch-back`) + 2 Public-app (`/app/switch-user`, `/app/switch-user/{user}`).

---

## Source Documents

| Source | Why loaded |
|--------|-----------|
| `../../C-Design/C05-modules/open_switcher.md` | Owning module design (as-built) |
| `../../B-Requirements/B05-acceptance-criteria/AC-07-user-management.md` | User management AC (impersonation tooling) |

---

## Requirements

| Requirement | Source | Type |
|-------------|--------|------|
| Admins can impersonate non-admin users and switch back | AC-07-user-management.md | Acceptance |
| Cannot switch to uid=1 (unless caller is uid=1) or to self | Internal design (security) | Acceptance |
| canSwitchBack() prevents unauthorized switch-back | Internal design (security) | Acceptance |
| switch users permission marked restrict access: true | Internal design (security) | Non-Functional |

---

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/open_switcher/open_switcher.info.yml` | Create | package=Open Platform; core ^10.3\|\|^11; no deps | pre-framework | pre-2026-04-15 |
| `web/modules/custom/open_switcher/open_switcher.routing.yml` | Create | 5 routes: 3 Admin + 2 Public (/app) | pre-framework | pre-2026-04-15 |
| `web/modules/custom/open_switcher/open_switcher.permissions.yml` | Create | switch users (restrict access: true) | pre-framework | pre-2026-04-15 |
| `web/modules/custom/open_switcher/open_switcher.module` | Create | hook_theme, hook_preprocess_menu, hook_page_attachments | pre-framework | pre-2026-04-15 |
| `web/modules/custom/open_switcher/open_switcher.links.menu.yml` | Create | Switch User + Switch Back in account menu | pre-framework | pre-2026-04-15 |
| `web/modules/custom/open_switcher/open_switcher.libraries.yml` | Create | impersonation_dialog library | pre-framework | pre-2026-04-15 |
| `web/modules/custom/open_switcher/css/impersonation-dialog.css` | Create | Dialog overlay styles | pre-framework | pre-2026-04-15 |
| `web/modules/custom/open_switcher/js/impersonation-dialog.js` | Create | One-shot dialog via drupalSettings | pre-framework | pre-2026-04-15 |
| `web/modules/custom/open_switcher/src/Controller/SwitchController.php` | Create | switchTo, switchBack, canSwitchBack, switchSession | pre-framework | pre-2026-04-15 |
| `web/modules/custom/open_switcher/src/Controller/SwitchFrontendController.php` | Create | page(): filtered user list for /app UI | pre-framework | pre-2026-04-15 |
| `web/modules/custom/open_switcher/src/Form/SwitchUserForm.php` | Create | Admin paginated user table with role filter | pre-framework | pre-2026-04-15 |
| `web/modules/custom/open_switcher/templates/open-switcher-page.html.twig` | Create | Frontend Switch User page (oc-* CSS classes) | pre-framework | pre-2026-04-15 |

---

## Dependencies

| Dependency | Type | Source |
|------------|------|--------|
| `session_manager` | External (Drupal core) | DI in SwitchController (session read/write) |
| `request_stack` | External (Drupal core) | DI in SwitchController + SwitchFrontendController |
| _(none — custom)_ | — | open_switcher has no dependency on any lms_* module |

---

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|------------|-----------|---------|
| Cache max-age=0 on access-sensitive routes | canSwitchBack(), SwitchFrontendController::page() | Verified in facts base |
| restrict access: true on elevated permissions | switch users permission | Confirmed in permissions.yml |

---

## Editorial Usability / Entity-First Evidence

Not applicable — open_switcher is an admin tooling module with no learner-facing content.

**Hardcoded content exceptions:** Dialog title/message/confirmText strings in `hook_page_attachments` are hardcoded PHP strings — suitable for a developer utility; no i18n requirement noted.

---

## Feature Flags Introduced or Read

None.

---

## What Was Built (from facts base)

All code is **as-built** and fully functional. Status confirmed by `modules-foundation.md`:

- `SwitchController` — all 4 methods implemented; security safeguards (cannot switch to uid=1 unless caller is uid=1; cannot switch to self; `canSwitchBack` prevents unauthorized switch-back). Uses `user_login_finalize($account)` for actual session switch.
- `SwitchFrontendController::page()` — excludes anonymous, uid=0/1, and administrator-role users; supports `?role=` filter; cache max-age=0.
- `SwitchUserForm` — impersonation banner + role filter + paged table (25/page) with JS confirm dialog.
- Hook implementations all verified in facts.
- `open-switcher-page.html.twig` — uses EverLMS `oc-*` CSS class convention.

**Known gaps (not defects — acknowledged design choices):**
- No audit log of who switched to whom and when.
- `/app/switch-user` is under the `/app` prefix (not `/admin`) — could be mistaken for a public route by firewall rules.

---

## Tasks (draft unit references)

| Task / Draft ID | Description | Status |
|----------------|-------------|--------|
| _(no dedicated U-xxx in draft)_ | open_switcher was not tracked as a separate draft unit | — |

Note: The draft unit spec tree (U-001–U-084) did not assign a dedicated unit to `open_switcher`. This Story was built outside the draft's unit tracking scope (package: Open Platform).

---

## Acceptance Link

See [`../../B-Requirements/B05-acceptance-criteria/AC-07-user-management.md`](../../B-Requirements/B05-acceptance-criteria/AC-07-user-management.md) (user management — impersonation tooling).

---

## DONE Evidence

| Check | Result |
|-------|--------|
| /admin/switch-user resolves and renders paginated user table | PASS — SwitchUserForm verified |
| /app/switch-user resolves with role filter | PASS — SwitchFrontendController::page() verified |
| switchTo() validates (no self, no uid=1 unless caller uid=1) | PASS — SwitchController::switchTo() verified |
| switchBack() restores original session | PASS — SwitchController::switchBack() verified |
| canSwitchBack() denies access if no session key | PASS — custom access check verified |
| JS confirmation dialog fires after switch | PASS — hook_page_attachments + impersonation-dialog.js verified |
| "Switch Back" visible in account menu during impersonation | PASS — hook_preprocess_menu verified |

---

## Gate Results (per Story, see A02 §3)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | All 5 routes resolve; templates render | 1/3 | pre-2026-04-15 |
| G2 Lint | PASS | No violations reported | 1/3 | pre-2026-04-15 |
| G3 Tests | PASS | Switch/switch-back cycle verified; access checks pass | 1/3 | pre-2026-04-15 |
| G4 Adversarial | PASS | Security safeguards in place; no-audit-log gap noted as debt | 1/3 | pre-2026-04-15 |

---

## Notes

- This module is package `Open Platform`, not `EverLMS` — it is a reusable utility. Future consideration: extract to a standalone Drupal.org project.
- The `/app/switch-user` prefix crossing into the non-`/admin` namespace is an existing design choice; document for firewall/WAF configuration.
- No `.install` file — no custom DB schema tables; pure session-based impersonation.
