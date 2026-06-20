---
page: notifications
mode: as-built
owner_module: lms_notification
route_group: app
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Notifications

**Canonical URL:** `/notifications` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_notification.list`
**Solution:** Custom controller (`NotificationController::listNotifications`) queries `lms_notification` entities for current user (optional `?type=` filter), paginates at 15, marks unread as read on page visit, returns `lms_notifications` theme render array.
**Owner module:** [`lms_notification`](../../C05-modules/lms_notification.md)
**Primary source:** `entity:lms_notification` (all items owned by current user, filtered by channel `in_app`)
**Editable source:** `lms_notification` entities — created by `NotificationManager::dispatch()` from various module events; title/message set programmatically; `field_read` toggled on page visit
**Editor role:** System (notifications are created programmatically); administrator via `/admin/lms/notifications/broadcast` for announcements
**Editor management UI:** `/admin/lms/notifications/broadcast` (`BroadcastForm`) for admin-initiated announcements
**Revision / moderation:** Not revisionable
**Forms:** — (read-only listing on page; type-filter via query param); Notification Preferences at `/manage/settings/notifications`
**Theme template:** `web/modules/custom/lms_notification/templates/lms-notifications.html.twig` *(see [`../../C09-skins.md`](../../C09-skins.md))*
**Access:** Authenticated (`_user_is_logged_in: TRUE`)
**Cache:** No-cache / per-user — `no-max-age` set in controller; cache tag `lms_notification_list`
**Translation:** Monolingual EN
**Related entities:** [`lms_notification`](../../C04-entities/README.md)
**Prototype reference:** — (no dedicated notification prototype)
**Notes:** Unread notifications are bulk-marked as read when the user visits this page (controller iterates and saves each). Email and push channels are stubs (in-app only is functional). `lms_notification.manager::dispatch()` is the sole creation path; called by `NotificationEventSubscriber` (on quiz pass), `ForumReplyForm::save`, `BroadcastForm::processBatch`, and `lms_order.module`. Cron (`lms_notification_cron`) deletes read notifications older than 90 days in batches of 100.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined once in [`../_common.md`](../_common.md) and not repeated here.

### 1. Notification Type Filter Tabs

- **Scope:** Page-specific
- **Implementation mechanism:** URL query param `?type={type_slug}` passed to controller; rendered as tab/chip strip in template using `current_type` variable
- **Plugin / Module:** `lms_notification` — `NotificationController::listNotifications` + `lms-notifications.html.twig`
- **Source content type / Entity:** `lms_notification.field_type` allowed values
- **Backed by fields / config keys:** `field_type` allowed: enrollment, quiz_result, certificate, payment, forum_reply, membership_expiry, new_lesson, announcement, reminder, system
- **Editable source:** computed/read-only (filter is a UI construct; notification types are code-defined)
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** Horizontal tab or chip strip; "All" tab + one tab per type
- **Region / Placement:** Top of content area, below page title
- **Notes:** Selecting a type tab reloads page with `?type={type}`. Controller passes `current_type` to template for active-state highlighting.
- **How to implement (EN):** Template renders tab links as `<a href="?type={type}">` for each `field_type` allowed value. Active tab determined by `{% if current_type == type %}`.
- **How to implement (VI):** Template render các tab link dạng `<a href="?type={type}">` cho từng giá trị `field_type`. Tab đang chọn xác định qua `{% if current_type == type %}`.
- **Solution Architect Request:** —

### 2. Notification List (paginated)

- **Scope:** Page-specific
- **Implementation mechanism:** Twig loop over `notifications` array; each item is a data array built from `lms_notification` entity
- **Plugin / Module:** `lms_notification` — `NotificationController::listNotifications` + `lms-notifications.html.twig`
- **Source content type / Entity:** `lms_notification`
- **Backed by fields / config keys:** `field_title`, `field_message`, `field_type`, `field_link`, `field_read`, `created`, `field_channel` (`in_app`), `field_reference_type`, `field_reference_id`
- **Editable source:** `lms_notification` entities — system-created; content from `NotificationManager::dispatch()` call arguments
- **Editor role / permission:** System (auto); `administer lms_course` for broadcast announcements
- **Editor management UI:** `/admin/lms/notifications/broadcast` for announcement creation
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Trigger a quiz pass → reload `/notifications` → confirm quiz_result notification visible
- **View mode / Projection:** List rows with: notification icon (by type), title, message excerpt, relative timestamp, unread indicator dot; `field_link` renders as clickable row
- **Region / Placement:** Main content area
- **Notes:** All unread notifications visible on this page are marked as read upon render (controller saves each `field_read=TRUE`). Pager at 15 items per page (Drupal `pager_default_initialize`). Notifications are ordered `created DESC`. `field_reference_type`/`field_reference_id` allow linking to the related entity.
- **How to implement (EN):** Controller: entity query `lms_notification` by `uid=current_user`, `field_channel=in_app`, optional `field_type` filter, pager(15), DESC. Mark unread as read. Pass `notifications` array + `current_type` + `pager` render array to template.
- **How to implement (VI):** Controller: entity query `lms_notification` theo `uid=current_user`, `field_channel=in_app`, filter `field_type` nếu có, pager(15), DESC. Đánh dấu chưa đọc thành đã đọc. Truyền `notifications`, `current_type`, `pager` vào template.
- **Solution Architect Request:** —

### 3. Pager

- **Scope:** Page-specific
- **Implementation mechanism:** Drupal core pager render array (`pager` variable in template)
- **Plugin / Module:** `lms_notification` — `lms-notifications.html.twig`
- **Source content type / Entity:** Drupal pager (system)
- **Backed by fields / config keys:** Computed from total `lms_notification` count
- **Editable source:** computed/read-only
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** Standard Drupal pager (previous/next/page numbers)
- **Region / Placement:** Bottom of notification list
- **Notes:** Uses Drupal's `pager_default_initialize(total, 15)` mechanism.
- **How to implement (EN):** `{{ pager }}` in template; pager render array built from `\Drupal::service('pager.manager')->createPager()` in controller.
- **How to implement (VI):** `{{ pager }}` trong template; mảng render pager xây dựng từ `\Drupal::service('pager.manager')->createPager()` trong controller.
- **Solution Architect Request:** —

### 4. Empty State

- **Scope:** Page-specific
- **Implementation mechanism:** Twig conditional when `notifications` is empty
- **Plugin / Module:** `lms_notification` — `lms-notifications.html.twig`
- **Source content type / Entity:** Static chrome
- **Backed by fields / config keys:** Static text
- **Editable source:** computed/read-only
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** "No notifications" empty state block
- **Region / Placement:** Main content area (replaces list when empty)
- **Notes:** —
- **How to implement (EN):** `{% if notifications is empty %}` block with friendly message.
- **How to implement (VI):** Block `{% if notifications is empty %}` với thông báo thân thiện.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Notification Type Filter Tabs | Tab labels: All, Enrollment, Quiz Result, Certificate, Payment, Forum Reply, etc. | `lms_notification.field_type` allowed values (code-defined) | `field_type` allowed values | computed/read-only | N/A | N/A | N/A | N/A | N/A |
| Notification List | Notification icon, title, message, timestamp, unread dot, clickable link | `lms_notification` | `field_title`, `field_message`, `field_type`, `field_link`, `field_read`, `created` | `/admin/lms/notifications/broadcast` (admin announcements) | System (auto-created); `administer lms_course` (broadcast) | N/A | Monolingual EN | `lms_notification_list`, no-max-age, per-user | Trigger quiz pass → confirm notification appears |
| Pager | Previous/next/page number controls | Drupal core pager | computed | computed/read-only | N/A | N/A | N/A | N/A | N/A |
| Empty State | "No notifications" message | Static chrome | N/A | computed/read-only | N/A | N/A | N/A | N/A | N/A |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| "Notifications" page title | Static UI chrome | Developer convention | — |
| "No notifications" empty state text | Static developer string | Developer convention | — |
| Notification type labels in filter tabs | Code-defined allowed values in entity field | Developer convention | — |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Notification Type Filter Tabs | DONE | — | As-built via `?type=` query param |
| Notification List (in_app) | DONE | — | As-built; marks unread as read on visit |
| Pager | DONE | — | As-built; 15 items per page |
| Empty State | DONE | — | As-built |
| Email channel dispatch | STUB | — | `NotificationManager::dispatch` logs only; no queue/mail send implemented |
| Push channel dispatch | STUB | — | `NotificationManager::dispatch` logs only; FCM foundation only |
| Notification Preferences form | DONE | — | At `/manage/settings/notifications` (`NotificationPreferencesForm`); 9 event types, per-user prefs via `user.data` |
| Broadcast (admin) | DONE | — | At `/admin/lms/notifications/broadcast`; batch processing to all active users |
| Notification Bell Block | DONE | — | `NotificationBellBlock` plugin; counts unread + top 5 latest; cache `lms_notification_list` per user |
| Cron cleanup (90-day) | DONE | — | `hook_cron` deletes read + age > 90d in batches of 100 |
