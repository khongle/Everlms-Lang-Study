---
module: open_switcher
service: ~
route-groups: [Admin, Public]
status: DONE
---

# C05 — Module: Open Switcher

**Directory:** `web/modules/custom/open_switcher/`
**Responsibility:** Reusable administrator impersonation utility — allows privileged users to switch into any non-admin account and switch back, with session-based tracking, a JS confirmation dialog, and both `/admin` and `/app` UIs.

> documentation-mode: **as-built** — all described code matches the current implementation.

> **Package note:** `open_switcher` ships with `package: Open Platform` (not `EverLMS`). It is a standalone, reusable utility that carries no dependency on any `lms_*` module and supports Drupal `^10.3 || ^11`.

## Route Bindings

| Route Group | Prefix | Controller / Form | Paths |
|-------------|--------|-------------------|-------|
| Admin | `/admin` | `SwitchUserForm` (form) | `/admin/switch-user` |
| Admin | `/admin` | `SwitchController::switchTo` | `/admin/switch-user/{user}` |
| Admin | `/admin` | `SwitchController::switchBack` | `/admin/switch-back` |
| Public (app shell) | `/app` | `SwitchFrontendController::page` | `/app/switch-user` |
| Public (app shell) | `/app` | `SwitchController::switchTo` | `/app/switch-user/{user}` |

**Access rules:**
- `/admin/switch-user*` and `/app/switch-user*` — Drupal permission `switch users` (`restrict access: true`).
- `/admin/switch-back` — custom access callback `SwitchController::canSwitchBack`; allows access only when session key `open_switcher_original_uid` is non-empty. Cache max-age=0.

**Menu links** (account menu):
- `Switch User` → `open_switcher.frontend` (weight 90, parent `user.page`)
- `⬅ Switch Back` → `open_switcher.switch_back` (weight 91, parent `user.page`)

## Public Interface

No `services.yml` — this module registers no Drupal services. The three controller/form classes are accessed only via the route layer.

| Export | Type | Description |
|--------|------|-------------|
| `switch users` | Drupal permission | Grants access to switch and switch-back routes. Must be restricted to admin/dev roles only. |

## Internal Structure

```
web/modules/custom/open_switcher/
+-- open_switcher.info.yml          # package=Open Platform; core ^10.3||^11; no deps
+-- open_switcher.routing.yml       # 5 routes: 3 Admin + 2 Public (/app)
+-- open_switcher.permissions.yml   # switch users (restrict access: true)
+-- open_switcher.module            # hook_theme, hook_preprocess_menu, hook_page_attachments
+-- open_switcher.links.menu.yml    # Switch User + Switch Back in account menu
+-- open_switcher.libraries.yml     # impersonation_dialog lib
+-- css/
|   +-- impersonation-dialog.css    # Dialog overlay styles
+-- js/
|   +-- impersonation-dialog.js     # Reads drupalSettings.openSwitcher.dialog and
|                                   #   renders confirmation dialog after session switch
+-- src/
|   +-- Controller/
|   |   +-- SwitchController.php         # switchTo, switchBack, canSwitchBack, switchSession
|   |   +-- SwitchFrontendController.php # page(): builds filtered user list for /app UI
|   +-- Form/
|       +-- SwitchUserForm.php           # Admin paginated user table with role filter
+-- templates/
    +-- open-switcher-page.html.twig     # Frontend UI: impersonation banner + user table
```

No `.install`, no config/install/, no config/schema/.

### Hook implementations

| Hook | What it does |
|------|-------------|
| `hook_theme` | Registers `open_switcher_page` theme hook; variables: `users`, `role_options`, `selected_role`, `is_impersonating`, `original_name`, `original_uid`, `switch_back_url`, `filter_url`. |
| `hook_preprocess_menu` | Account-menu only: reads `open_switcher_original_uid` from session; injects impersonation state (`is_impersonating`, `switch_back_url`, `original_user_name`, `original_user_uid`, `impersonated_roles`, `impersonated_uid`) into template variables — ensures "Switch Back" is visible even when the impersonated user lacks `switch users` permission. |
| `hook_page_attachments` | Reads one-time `open_switcher_dialog` session value; if set, attaches `open_switcher/impersonation_dialog` library and passes dialog config (title, message, details, confirmText, variant) to `drupalSettings`; clears the session value after attaching. |

### Controller details

**SwitchController** — core session management:

| Method | Description |
|--------|-------------|
| `switchTo(int $user): RedirectResponse` | Validates target (not self, not uid=1 unless caller is uid=1); stores original uid+name in session; sets `open_switcher_dialog` for the success dialog; calls `switchSession()`; redirects to front page. |
| `switchBack(): RedirectResponse` | Reads original uid from session; clears all switcher session keys; sets "switched back" dialog; calls `switchSession()` with original uid; redirects to front page. |
| `canSwitchBack(): AccessResult` | Custom access check — allows if `open_switcher_original_uid` session key is non-empty. Cache max-age=0. |
| `switchSession(int $uid): void` | Loads user entity and calls `user_login_finalize($account)` to execute the actual Drupal session switch. |

**SwitchFrontendController:**

| Method | Description |
|--------|-------------|
| `page(Request $request): array` | Builds user list: excludes anonymous, uid=0/1, and users with `administrator` role. Supports `?role=` query-param filter. Returns render array via `open_switcher_page` theme. Cache max-age=0. |

**SwitchUserForm** (admin):
- Impersonation status banner when currently impersonating.
- Role filter select (excludes anonymous/authenticated/administrator system roles).
- Paged user table (25/page): UID, username, email, roles, "Switch" link with JS confirm dialog.
- `submitForm()` redirects with `?role=` for filter persistence.

### Library: `impersonation_dialog`

| Asset | Dependencies |
|-------|-------------|
| `css/impersonation-dialog.css`, `js/impersonation-dialog.js` | `core/drupal`, `core/drupalSettings`, `core/once` |

JS reads `drupalSettings.openSwitcher.dialog` on page load and renders a one-shot confirmation dialog injected by `hook_page_attachments`.

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `session_manager` | Drupal core service | Session read/write in `SwitchController` (injected at runtime). |
| `request_stack` | Drupal core service | Reads query params and session in `SwitchController` and `SwitchFrontendController`. |
| _(none — custom)_ | Internal | open_switcher has no dependency on any `lms_*` module by design. |

**RULE:** No circular dependencies. `open_switcher` is dependency-free with respect to custom modules; it can be installed/uninstalled independently of EverLMS modules.

**Security safeguards (as-built):**
- Cannot switch to uid=1 unless the caller is uid=1.
- Cannot switch to self.
- `canSwitchBack()` access check prevents unauthorized `/admin/switch-back` calls.
- `switch users` permission is marked `restrict access: true`.

**Known gaps (not yet addressed):**
- No audit log of who switched to whom and when.
- `/app/switch-user` exposes impersonation UI under the non-`/admin` prefix — could be mistaken for a public route by firewall rules.
