---
page: _common
mode: as-built
owner_module: lms_theme
route_group: all
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Global Chrome: Header / Primary Nav / Footer

> Shared by every page across all route groups (public, app, manage, admin). Defined once here — per-page specs do NOT repeat these components.

**Theme:** `lms_theme` (default) / `lms_classic` (alternate)
**Theme template (page wrapper):** `web/themes/custom/lms_theme/templates/page/page.html.twig`
**HTML wrapper:** `web/themes/custom/lms_theme/templates/layout/html.html.twig`
**Access:** Chrome rendered on all pages; role-conditional slots within components.
**Cache:** Per-user context (notification bell count, user avatar, impersonation banner); static slots are anonymous-cacheable.
**Design system:** Bootstrap 5.3.3 CDN + Inter font (Google Fonts) + Font Awesome 6.5.1 CDN; dark glassmorphism defaults (`#0d1117` bg, `#667eea`/`#764ba2` accent gradient, `rgba(13,17,23,0.95)` navbar). See [`../C09-skins.md`](../C09-skins.md).

---

## Components (visual order)

### 1. Global Navbar — *Thanh điều hướng chính*

- **Scope:** Global chrome
- **Implementation mechanism:** `lms_theme_preprocess_page()` injects variables; rendered in `page.html.twig`; `lms_theme/global` library attached on every page.
- **Plugin / Module:** `lms_theme` (preprocess hook `lms_theme_preprocess_page`), `open_switcher` (impersonation banner), `lms_notification` (bell block)
- **Source content type / Entity:** Site config (`system.site`), `lms_base` settings (logo, social links, app store URLs), `user` (authenticated name, picture), `lms_notification` (unread count via `NotificationBellBlock`)
- **Backed by fields / config keys:**
  - `system.site` → `name`, `slogan`
  - `lms_base` settings → logo, site name, footer contact, social links, app store URLs
  - `open_switcher` → session impersonation state
  - `lms_notification_bell_block` plugin → `lms_notification` entities, field_read=FALSE count
- **Editable source:** `system.site` config; `lms_base` module settings; user profile entity
- **Editor role / permission:** `administer site configuration`
- **Editor management UI:** `/admin/config/system/site-information`; theme settings at `/admin/appearance/settings/lms_theme`
- **Revision / moderation:** N/A (config)
- **Translation source:** Interface translation (EN primary)
- **Edit smoke:** Change site name → save → reload any page → observe navbar title
- **View mode / Projection:** Rendered inline in page template
- **Region / Placement:** `header` region (lms_theme region)
- **Notes:**
  - Navbar height: 64px, `rgba(13,17,23,0.95)` bg; z-index 1030.
  - `is_manager` flag (roles: administrator/manager/instructor) controls whether workspace nav link is shown.
  - `current_route` variable injected for active-state highlighting.
  - Impersonation banner rendered when `open_switcher` session is active (role: `switch users`).
- **How to implement (EN):** Variables are injected in `lms_theme_preprocess_page()` at `web/themes/custom/lms_theme/lms_theme.theme`. Edit `page.html.twig` to adjust nav layout.
- **How to implement (VI):** Các biến được inject trong hàm `lms_theme_preprocess_page()`. Chỉnh sửa file `page.html.twig` để thay đổi bố cục navbar.
- **Solution Architect Request:** —

---

### 2. Primary Navigation Menu — *Menu điều hướng chính*

- **Scope:** Global chrome
- **Implementation mechanism:** Twig include in `page.html.twig`; `primary_menu` region; Bootstrap 5 navbar collapse
- **Plugin / Module:** `lms_theme`, Drupal core menu system
- **Source content type / Entity:** Drupal core `main` menu (menu links)
- **Backed by fields / config keys:** `system.menu.main` config + menu link config entities
- **Editable source:** `/admin/structure/menu/manage/main` — menu link management
- **Editor role / permission:** `administer menu`
- **Editor management UI:** `/admin/structure/menu/manage/main`
- **Revision / moderation:** N/A
- **Translation source:** Menu link title config translation
- **Edit smoke:** Add menu link → save → reload public page → observe new nav item
- **View mode / Projection:** `primary_menu` rendered block
- **Region / Placement:** `primary_menu` region
- **Notes:** Mega-menu component in `scss/components/mega-menu.scss`. Active class injected via `current_route` variable from preprocess.
- **How to implement (EN):** Add/edit links at `/admin/structure/menu/manage/main`. Theme controls active-class styling.
- **How to implement (VI):** Thêm/sửa liên kết tại `/admin/structure/menu/manage/main`.
- **Solution Architect Request:** —

---

### 3. Notification Bell — *Chuông thông báo*

- **Scope:** Global chrome (authenticated users only)
- **Implementation mechanism:** `NotificationBellBlock` plugin (id: `lms_notification_bell_block`) rendered via block placement in `header` region; template `lms-notification-bell.html.twig`
- **Plugin / Module:** `lms_notification` → `Plugin/Block/NotificationBellBlock`
- **Source content type / Entity:** `lms_notification` entities (field_read=FALSE, field_channel includes `in_app`)
- **Backed by fields / config keys:** `lms_notification`: `field_read`, `field_channel`, `field_title`, `field_message`; AJAX endpoint `/api/lms/notifications/unread-count`
- **Editable source:** `lms_notification` entity (programmatically created by `NotificationManager::createInAppNotification`)
- **Editor role / permission:** N/A (system-generated); admin can review at `/notifications`
- **Editor management UI:** `computed/read-only` — notifications generated by system events
- **Revision / moderation:** N/A
- **Translation source:** N/A (system strings)
- **Edit smoke:** Trigger enrollment event → observe bell count increment
- **View mode / Projection:** Template `web/modules/custom/lms_notification/templates/lms-notification-bell.html.twig`; variables: `unread_count`, `notifications_url`, `latest_notifications` (top 5)
- **Region / Placement:** `header` region; authenticated only (anonymous sees count=0 from API)
- **Notes:** JS file `lms_notification/bell` library polls `/api/lms/notifications/unread-count`. Cache: per-user, tagged `lms_notification_list`. Block cache: per-user context.
- **How to implement (EN):** Block plugin automatically placed in header by theme. `NotificationManager::dispatch()` creates entities; block reads them.
- **How to implement (VI):** Block plugin được đặt tự động trong header. `NotificationManager::dispatch()` tạo thông báo; block đọc từ entity.
- **Solution Architect Request:** —

---

### 4. User Avatar / Account Menu — *Menu tài khoản người dùng*

- **Scope:** Global chrome (authenticated users)
- **Implementation mechanism:** `lms_theme_preprocess_page()` injects `current_user_name`, `current_user_picture`; rendered in `page.html.twig` as dropdown
- **Plugin / Module:** `lms_theme`
- **Source content type / Entity:** `user` entity (`user_picture` field, `field_first_name`, `field_last_name`)
- **Backed by fields / config keys:** `user.field_first_name`, `user.field_last_name`, `user.user_picture`
- **Editable source:** User entity at `/user/{uid}/edit` or learner `/profile` page
- **Editor role / permission:** Own account (`edit own account`)
- **Editor management UI:** `/profile` (learner portal) or `/user/{uid}/edit` (admin)
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Update avatar → save → reload → observe updated avatar in navbar
- **View mode / Projection:** Inline Twig in `page.html.twig`
- **Region / Placement:** `header` region, right-side
- **Notes:** Dropdown links: `/profile`, `/my-courses`, `/my-certificates`, `/notifications`, `/logout`.
- **How to implement (EN):** Injected by preprocess hook; no configuration needed.
- **How to implement (VI):** Được inject qua preprocess hook; không cần cấu hình thêm.
- **Solution Architect Request:** —

---

### 5. Impersonation Banner — *Banner giả mạo người dùng*

- **Scope:** Global chrome (conditional — visible only when impersonation is active)
- **Implementation mechanism:** `open_switcher` session state checked in `lms_theme_preprocess_page()`; banner Twig block in `page.html.twig`
- **Plugin / Module:** `open_switcher` module
- **Source content type / Entity:** Drupal session (original UID stored server-side)
- **Backed by fields / config keys:** Session key set by `SwitchController::switchTo()`
- **Editable source:** computed/read-only (system session state)
- **Editor role / permission:** `switch users` permission
- **Editor management UI:** `/admin/switch-user` or `/app/switch-user`; restore via `/admin/switch-back`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Impersonate user → observe banner → switch back → observe banner removed
- **View mode / Projection:** Inline conditional in `page.html.twig`
- **Region / Placement:** Top of page, above navbar
- **Notes:** `open_switcher` is a company utility module separate from EverLMS business logic.
- **How to implement (EN):** Driven by `open_switcher` session state; no additional config.
- **How to implement (VI):** Được điều khiển bởi session của module `open_switcher`.
- **Solution Architect Request:** —

---

### 6. Footer — *Footer trang*

- **Scope:** Global chrome
- **Implementation mechanism:** `lms_theme_preprocess_page()` injects footer variables from theme settings; rendered in `page.html.twig`
- **Plugin / Module:** `lms_theme`
- **Source content type / Entity:** Theme settings (`lms_theme.settings` config object)
- **Backed by fields / config keys:** `footer_address`, `footer_phone`, `footer_email`, `footer_working_hours`, `footer_copyright`, `footer_map_embed`, `app_store_url`, `google_play_url`, social links (Facebook, YouTube, LinkedIn, TikTok, Zalo)
- **Editable source:** `/admin/appearance/settings/lms_theme` (theme settings form → Footer section)
- **Editor role / permission:** `administer site configuration`
- **Editor management UI:** `/admin/appearance/settings/lms_theme`
- **Revision / moderation:** N/A (config)
- **Translation source:** Config translation
- **Edit smoke:** Change footer phone → save → reload public page → observe updated phone number
- **View mode / Projection:** Rendered in `page.html.twig` footer section; Bootstrap grid with 4 regions: `footer_first` through `footer_fourth`, plus `footer_bottom`
- **Region / Placement:** Regions: `footer_first`, `footer_second`, `footer_third`, `footer_fourth`, `footer_bottom`
- **Notes:** Footer hidden or simplified on workspace (`/workspace/*`) pages per `is-workspace` body class CSS rules.
- **How to implement (EN):** Configure at `/admin/appearance/settings/lms_theme`. All footer text/links editable there.
- **How to implement (VI):** Cấu hình tại `/admin/appearance/settings/lms_theme`. Toàn bộ nội dung footer chỉnh sửa tại đó.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Global Navbar | Site name/logo, nav links, workspace link (manager only), impersonation banner | `system.site`, `lms_base` settings, session | `name`, logo, `is_manager` | `/admin/config/system/site-information`, `/admin/appearance/settings/lms_theme` | `administer site configuration` | N/A | Interface (EN) | `user.roles`, route context | Edit site name → save → reload |
| Primary Navigation Menu | Navigation links (Home, Courses, etc.) | `system.menu.main` config | Menu link labels + URLs | `/admin/structure/menu/manage/main` | `administer menu` | N/A | Config translation | `config:system.menu.main` | Add menu link → reload |
| Notification Bell | Unread count badge, latest 5 notifications dropdown | `lms_notification` entities | `field_read`, `field_title`, `field_message` | computed/read-only | N/A (system) | N/A | N/A | `user`, `lms_notification_list` | Trigger enrollment → observe bell |
| User Avatar / Account Menu | Avatar image, display name, account links | `user` entity | `user_picture`, `field_first_name`, `field_last_name` | `/profile` or `/user/{uid}/edit` | `edit own account` | N/A | N/A | `user` context | Update avatar → reload |
| Impersonation Banner | Target username, switch-back link | Drupal session | Session UID store | `/admin/switch-user` | `switch users` | N/A | N/A | Session | Impersonate → observe banner |
| Footer | Address, phone, email, hours, copyright, social links, app store links | `lms_theme.settings` config | `footer_*`, social link keys | `/admin/appearance/settings/lms_theme` | `administer site configuration` | N/A | Config translation | `config:lms_theme.settings` | Edit phone → save → reload |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| "Bootstrap 5.3.3" CDN library URLs | Technical dependency locked to version; changed via `lms_theme.libraries.yml` | D00 conventions (tech dependency) | On Bootstrap upgrade |
| Workspace nav structure | Drupal block/region layout; admin-configured | Theme settings | On nav restructure |
