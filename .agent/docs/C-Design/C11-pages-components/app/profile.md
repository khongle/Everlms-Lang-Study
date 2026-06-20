---
page: profile
mode: as-built
owner_module: lms_progress
route_group: app
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: My Profile

**Canonical URL:** `/profile` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.profile`
**Solution:** Custom controller (`UserPortalController::profile` in `lms_progress`) loads the current `user` entity and renders it in `full` view mode via the entity view builder; returns the `lms_profile` theme render array.
**Owner module:** [`lms_progress`](../../C05-modules/lms_progress.md) (controller); profile template is in `lms_course`'s template set
**Primary source:** Drupal `user` entity (current user)
**Editable source:** Drupal `user` entity fields — standard Drupal user edit form at `/user/{uid}/edit`; custom profile fields added by `lms_base` (`field_first_name`, `field_last_name`, `field_avatar`, `field_bio`, etc.)
**Editor role:** Authenticated (self); Administrator (`administer users`)
**Editor management UI:** `/user/{uid}/edit` (Drupal core user edit form)
**Revision / moderation:** Not revisionable (core user entity)
**Forms:** — (read-only view on this page; edit at `/user/{uid}/edit`)
**Theme template:** `web/modules/custom/lms_course/templates/lms-profile.html.twig` *(see [`../../C09-skins.md`](../../C09-skins.md))*
**Access:** Authenticated (`_user_is_logged_in: TRUE`)
**Cache:** Per-user — core user entity cache tags
**Translation:** Monolingual EN
**Related entities:** Drupal `user`, `lms_user_course` (enrolled courses count), `lms_user_certificate` (certificates count)
**Prototype reference:** [`../../C10-prototypes/html/account/profile.html`](../../C10-prototypes/html/account/profile.html)
**Notes:** `UserPortalController::profile()` uses `$this->entityTypeManager->getViewBuilder('user')->view($user, 'full')` — it delegates entirely to the Drupal entity view builder in `full` view mode. The `lms_profile` theme is registered separately (likely in `lms_course.module hook_theme`) and wraps this output. Because the controller renders the entity in `full` view mode, the displayed fields are controlled by the `user.view_display.default.full` display config in `config/sync`. This is a read-only display page — editing is done at the standard Drupal user edit URL.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined once in [`../_common.md`](../_common.md) and not repeated here.

### 1. User Avatar and Identity Block

- **Scope:** Page-specific
- **Implementation mechanism:** Rendered via Drupal entity view builder (`user` entity, `full` view mode); template `lms-profile.html.twig` wraps and styles the rendered output
- **Plugin / Module:** `lms_progress` — `UserPortalController::profile` + `lms_course/templates/lms-profile.html.twig`
- **Source content type / Entity:** Drupal `user`
- **Backed by fields / config keys:** `field_avatar` (user picture / image), `field_first_name`, `field_last_name`, `name` (username), `mail`, `field_bio`, role labels
- **Editable source:** Drupal `user` entity fields
- **Editor role / permission:** Authenticated (self via `/user/{uid}/edit`); `administer users`
- **Editor management UI:** `/user/{uid}/edit`
- **Revision / moderation:** N/A (user entity is not revisionable)
- **Translation source:** N/A
- **Edit smoke:** Edit `field_first_name` → save at `/user/{uid}/edit` → reload `/profile` → confirm name updated
- **View mode / Projection:** `full` view mode of `user` entity as configured in `user.view_display.default.full` config
- **Region / Placement:** Top-left / hero area
- **Notes:** `lms_base` module adds custom profile fields to the user entity. The actual fields visible depend on the `full` view display config. Avatar falls back to Drupal default user picture or initials-based placeholder.
- **How to implement (EN):** In controller: `$rendered = $this->entityTypeManager->getViewBuilder('user')->view($user, 'full')`. Return `lms_profile` theme with `user_rendered` variable. Configure fields to show via `/admin/config/people/accounts/fields` and display at `/admin/config/people/accounts/display`.
- **How to implement (VI):** Trong controller: `$rendered = $this->entityTypeManager->getViewBuilder('user')->view($user, 'full')`. Trả về theme `lms_profile` với biến `user_rendered`. Cấu hình trường hiển thị qua `/admin/config/people/accounts/fields`.
- **Solution Architect Request:** —

### 2. Learning Statistics Summary

- **Scope:** Page-specific
- **Implementation mechanism:** Computed stats passed as template variables by controller (enrolled count, completed courses, certificate count)
- **Plugin / Module:** `lms_progress` — `UserPortalController::profile`
- **Source content type / Entity:** `lms_user_course`, `lms_user_certificate`
- **Backed by fields / config keys:** `lms_user_course.field_status` (enrolled/in_progress/completed count); `lms_user_certificate` count
- **Editable source:** System-managed (auto-updated on enrollment and course completion); computed/read-only on profile
- **Editor role / permission:** N/A (system data)
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Enroll in a course → reload `/profile` → confirm enrolled count increments
- **View mode / Projection:** Stat counters: "Courses Enrolled", "Courses Completed", "Certificates Earned"
- **Region / Placement:** Below avatar block or sidebar stats panel
- **Notes:** These stats are not directly visible in the current template if the controller uses only entity view builder output. Actual stat computation depends on whether `UserPortalController::profile()` passes additional variables beyond the entity render. The controller as documented passes only the rendered user entity — additional stats may need to be added.
- **How to implement (EN):** Extend `profile()` to query `lms_user_course` for enrolled/completed counts and `lms_user_certificate` for certificate count. Pass as `stats` array to `lms_profile` theme.
- **How to implement (VI):** Mở rộng `profile()` để truy vấn `lms_user_course` lấy số khóa đã đăng ký/hoàn thành và `lms_user_certificate` đếm chứng chỉ. Truyền vào theme `lms_profile` dưới dạng mảng `stats`.
- **Solution Architect Request:** —

### 3. Edit Profile Link

- **Scope:** Page-specific
- **Implementation mechanism:** Static link to `/user/{current_user.id}/edit`
- **Plugin / Module:** `lms_course/templates/lms-profile.html.twig`
- **Source content type / Entity:** Static chrome / computed
- **Backed by fields / config keys:** Current user UID
- **Editable source:** computed/read-only
- **Editor role / permission:** Authenticated (self)
- **Editor management UI:** `/user/{uid}/edit`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** "Edit Profile" button/link
- **Region / Placement:** Top-right of profile header or below avatar
- **Notes:** Links to Drupal core user edit form. The form's appearance depends on `lms_theme` overrides (`templates/user/user-register-form.html.twig` and similar).
- **How to implement (EN):** `<a href="{{ path('entity.user.edit_form', {user: user.id}) }}">Edit Profile</a>` in the template.
- **How to implement (VI):** `<a href="{{ path('entity.user.edit_form', {user: user.id}) }}">Chỉnh sửa hồ sơ</a>` trong template.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| User Avatar and Identity Block | Avatar image, display name (first + last), username, email, bio, joined date | Drupal `user` entity (full view mode) | `field_avatar`, `field_first_name`, `field_last_name`, `name`, `mail`, `field_bio`, `created` | `/user/{uid}/edit` | Authenticated (self); `administer users` | N/A | Monolingual EN | `user:{uid}`, per-user | Edit name → reload profile → confirm |
| Learning Statistics Summary | Enrolled count, completed count, certificate count | `lms_user_course`, `lms_user_certificate` | `field_status`, entity count | computed/read-only | N/A | N/A | N/A | per-user | Enroll → reload → confirm count |
| Edit Profile Link | "Edit Profile" button | Static chrome / computed UID | Current user ID | Inline link | Authenticated (self) | N/A | N/A | N/A | N/A |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| "My Profile" page title | Static UI chrome | Developer convention | — |
| "Edit Profile" link label | Static developer string | Developer convention | — |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| User Avatar and Identity Block | DONE | — | As-built via entity view builder `full` mode |
| Learning Statistics Summary | PARTIAL | — | Controller delegates to entity view builder only; stat computation not confirmed in current `profile()` implementation — may need extension |
| Edit Profile Link | DONE | — | As-built (static Twig link) |
| Custom profile fields (`lms_base`) | DONE | — | As-built; fields visible per `user.view_display.default.full` config |
