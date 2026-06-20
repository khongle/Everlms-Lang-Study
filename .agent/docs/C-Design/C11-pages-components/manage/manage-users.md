---
page: manage-users
mode: as-built
owner_module: lms_course
route_group: manage
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Manage Users

**Canonical URL:** `/workspace/users` *(per [`../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.admin_users`
**Solution:** Custom controller (`AdminController::userList`) renders `lms_admin_users` Twig template; AJAX partial `lms_admin_users_table` for table refresh; create/edit forms use `lms_admin_user_form` wrapper. Bulk operations via POST to `/workspace/users/bulk`.
**Owner module:** [`lms_course`](../../C05-modules/lms_course.md)
**Primary source:** Drupal `user` entity
**Editable source:** Drupal `user` entity — standard user fields (`name`, `mail`, `status`, `roles`, `user_picture`) + EverLMS custom profile fields (`field_first_name`, `field_last_name`, etc. stored on user entity)
**Editor role:** Administrator
**Editor management UI:** `/workspace/users` (list), `/workspace/users/create` (create), `/workspace/users/{user}/admin-edit` (edit)
**Revision / moderation:** Not revisionable; Drupal user entities are not revision-tracked.
**Forms:** `AdminController::userCreateForm` → `UserRegisterForm` or custom `UserCreateForm`; `AdminController::userEditForm` → Drupal user edit form wrapped in `lms_admin_user_form` template
**Theme template:** `lms_course/templates/lms-admin-users.html.twig` (list); `lms_course/templates/lms-admin-users-table.html.twig` (AJAX partial); `lms_course/templates/lms-admin-user-form.html.twig` (create/edit form wrapper)
**Access:** Role-gated: `administer lms_course` (list, create, bulk); `entity_access:user.update` (edit individual)
**Cache:** Per-user; no anonymous caching. Invalidated on user entity save/delete.
**Translation:** Monolingual EN
**Related entities:** Drupal `user`, [`lms_user_course`](../../C04-entities/01-content-entities.md), [`lms_user_membership`](../../C04-entities/01-content-entities.md), [`lms_user_certificate`](../../C04-entities/01-content-entities.md)
**Prototype reference:** [`../../C10-prototypes/html/manage/users.html`](../../C10-prototypes/html/manage/users.html)
**Notes:** Unlike other manage pages, the primary entity is Drupal core `user` — not a custom LMS entity. The EverLMS workspace wraps Drupal's user management with a custom-themed UI. `open_switcher` module provides user impersonation from `/admin/switch-user` (Drupal admin route) and `/app/switch-user` (custom frontend) — those are separate pages, not part of this spec. Bulk operations (`/workspace/users/bulk` POST) handle multi-user actions.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined ONCE in [`../_common.md`](../_common.md) and NOT repeated here.

### 1. Workspace Sidebar Navigation

- **Scope:** Global chrome (workspace)
- **Implementation mechanism:** Twig include — `lms-admin-sidebar.html.twig`
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Static nav links
- **Backed by fields / config keys:** Static template markup
- **Editable source:** Static — developer-owned
- **Editor role / permission:** N/A
- **Editor management UI:** `«static»`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** N/A
- **Region / Placement:** Left sidebar
- **Notes:** Shared across all `/workspace/*` pages

### 2. User Stats Header

- **Scope:** Page-specific
- **Implementation mechanism:** `lms-admin-users.html.twig` header section; `AdminController::userList` passes aggregate stats
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Drupal `user` aggregate + `lms_user_course` counts
- **Backed by fields / config keys:** `users.status`, `lms_user_course.field_status`, `users.created` (recent signups)
- **Editable source:** Read-only aggregate
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** Read-only
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Register new user → total count increments
- **View mode / Projection:** Summary stats cards
- **Region / Placement:** Top of content area
- **Notes:** Stats computed per page load

### 3. User Search + Filter Bar

- **Scope:** Page-specific
- **Implementation mechanism:** GET params (`?search=`, `?role=`, `?status=`) processed by `AdminController::userList`; table AJAX-refreshed via `lms-admin-users-table.html.twig` partial
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** N/A (filter UI)
- **Backed by fields / config keys:** `users.name`, `users.mail`, `users.status`, `users_roles.rid`
- **Editable source:** N/A
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/users`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Search by email → table filters to matching user
- **View mode / Projection:** Filter form (GET)
- **Region / Placement:** Above table
- **Notes:** AJAX partial `lms-admin-users-table.html.twig` used for table refresh (HTMX or custom JS)

### 4. User List Table

- **Scope:** Page-specific
- **Implementation mechanism:** `lms-admin-users.html.twig` / `lms-admin-users-table.html.twig` (AJAX partial); `AdminController::userList` passes user rows
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Drupal `user` + `lms_user_course` (enrollment count per user)
- **Backed by fields / config keys:** `uid`, `name`, `mail`, `user_picture`, `field_first_name`, `field_last_name`, `created`, `access`, `status`, roles; enrolled course count via `lms_user_course`
- **Editable source:** Drupal `user` entity via `/workspace/users/{user}/admin-edit`
- **Editor role / permission:** `administer lms_course` (list); `entity_access:user.update` (edit link)
- **Editor management UI:** `/workspace/users/{user}/admin-edit`
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Edit user name → name updates in table on reload
- **View mode / Projection:** Table row (avatar, name, email, roles, enrolled count, status, last login, actions)
- **Region / Placement:** Main content area
- **Notes:** Checkbox column for bulk operations. Avatar resolved from `user_picture` field.

### 5. Bulk Action Controls

- **Scope:** Page-specific
- **Implementation mechanism:** Form with checkbox selection → POST to `/workspace/users/bulk` → `AdminController::bulkUsers`
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Drupal `user` (multiple, selected by checkbox)
- **Backed by fields / config keys:** `users.status` (block/unblock), role assignment
- **Editable source:** Drupal `user` entity (batch mutation)
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/users` (checkbox + bulk action selector)
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Select 3 users → bulk block → all 3 status change to blocked
- **View mode / Projection:** Bulk action form
- **Region / Placement:** Above/below table
- **Notes:** Bulk action options (block, unblock, assign role, delete) determined by `AdminController::bulkUsers` implementation

### 6. Create User Form

- **Scope:** Page-specific
- **Implementation mechanism:** `/workspace/users/create` → `AdminController::userCreateForm` → user registration/create form wrapped in `lms-admin-user-form.html.twig`
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Drupal `user` (new)
- **Backed by fields / config keys:** `name`, `mail`, `pass`, `status`, `roles`, `user_picture`, custom profile fields
- **Editable source:** Drupal `user` entity
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/users/create`
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Create user → appears in list; user can log in
- **View mode / Projection:** Form
- **Region / Placement:** Full page with sidebar
- **Notes:** Uses `lms-admin-user-form.html.twig` wrapper template

### 7. Edit User Form

- **Scope:** Page-specific
- **Implementation mechanism:** `/workspace/users/{user}/admin-edit` → `AdminController::userEditForm` → Drupal user edit form wrapped in `lms-admin-user-form.html.twig`; `{user}` param upcasted to Drupal user entity
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Drupal `user` (existing, upcasted)
- **Backed by fields / config keys:** All Drupal user fields + EverLMS custom user fields
- **Editable source:** Drupal `user` entity
- **Editor role / permission:** `entity_access:user.update`
- **Editor management UI:** `/workspace/users/{user}/admin-edit`
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Change user email → save → new email shown in list
- **View mode / Projection:** Form
- **Region / Placement:** Full page with sidebar
- **Notes:** Uses `lms-admin-user-form.html.twig` wrapper template. `entity_access:user.update` is Drupal core access check on the user entity.

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Sidebar Nav | Workspace nav links | Static template | Static | Developer only | N/A | N/A | N/A | None | N/A |
| User Stats Header | Total users, active count, new signups (7d), enrolled count | `user` + `lms_user_course` aggregate | `status`, `created`, enrollment count | Read-only | `administer lms_course` | No | EN | Per-request | New user → total increments |
| Search/Filter Bar | Search input, role filter, status filter | N/A (filter params) | `?search=`, `?role=`, `?status=` | `/workspace/users` | `administer lms_course` | N/A | N/A | Per-request | Filter → table narrows |
| User List Table | Avatar, name, email, roles, enrolled courses, status, last login, actions | `user` + `lms_user_course` | `uid`, `name`, `mail`, `user_picture`, roles, `access`, `status`, enrolled count | `/workspace/users/{user}/admin-edit` | `administer lms_course` / `entity_access:user.update` | No | EN | Per-request | Edit name → updates in list |
| Bulk Action Controls | Checkboxes, action selector, apply button | `user` (multiple) | `status`, roles | `/workspace/users/bulk` (POST) | `administer lms_course` | No | EN | Per-request | Bulk block → statuses change |
| Create User Form | name, mail, password, roles, picture, profile fields | `user` (new) | Core user fields + custom | `/workspace/users/create` | `administer lms_course` | No | N/A | Per-request | Create → appears in list |
| Edit User Form | All user fields pre-filled | `user` (existing) | Core user fields + custom | `/workspace/users/{user}/admin-edit` | `entity_access:user.update` | No | N/A | Per-request | Edit email → persists |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Role display labels (administrator, instructor, student) | Drupal role config entities (`user_role`) — config-managed, not UI-editable by editors | Drupal core config | Revisit if role label customization needed |
| Avatar fallback placeholder | Template CSS/inline in `lms-admin-users.html.twig` | Developer-owned UI chrome | N/A |
| Bulk action option labels | PHP array in `AdminController::bulkUsers` | Code constant | Revisit if actions become configurable |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| User Stats Header | DONE | — | `AdminController::userList` renders stats |
| Search/Filter Bar | DONE | — | GET params processed; AJAX partial available |
| User List Table | DONE | — | `lms-admin-users.html.twig` + `lms-admin-users-table.html.twig` |
| Bulk Action Controls | DONE | — | `/workspace/users/bulk` POST handler in `AdminController::bulkUsers` |
| Create User Form | DONE | — | `AdminController::userCreateForm` + `lms-admin-user-form.html.twig` |
| Edit User Form | DONE | — | `AdminController::userEditForm` + `lms-admin-user-form.html.twig` |
