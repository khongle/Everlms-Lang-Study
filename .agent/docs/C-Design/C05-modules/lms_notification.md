---
module: lms_notification
service: notification
route-groups: [App, Admin, API]
status: IN_PROGRESS
---

# C05 — Module: LMS Notification

**Directory:** `web/modules/custom/lms_notification/`
**Responsibility:** Delivers in-app notifications to learners via a `NotificationManager` service, a `NotificationBellBlock`, and event-subscriber hooks — with email and push channels present as stubs only.

> **Documentation mode:** as-built, partially implemented.
> - In-app channel (`in_app`): **DONE** — fully working.
> - Email channel (`email`): **stub** — logs an info message + TODO comment; no mail is sent.
> - Push channel (`push`): **stub** — logs only; FCM noted in comment but not implemented.

---

## Route Bindings

| Route Group | Prefix | Controller / Form | Paths |
|-------------|--------|-------------------|-------|
| App | `/` | `NotificationController` | `/notifications` [GET] |
| App | `/manage` | `NotificationPreferencesForm` | `/manage/settings/notifications` [GET, POST] |
| Admin | `/admin` | `BroadcastForm` | `/admin/lms/notifications/broadcast` [GET, POST] |
| API (legacy) | `/api/lms` | `NotificationController` | `/api/lms/notifications/unread-count` [GET, public] |

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `NotificationManager` | Service (`lms_notification.manager`) | Core service: `dispatch()`, `createInAppNotification()` |
| `NotificationEventSubscriber` | Event Subscriber (`lms_notification.event_subscriber`) | Listens to `QuizPassedEvent` from `lms_quiz` |
| `LmsNotificationCommands` | Drush Command | `lms_notification:remind-inactive` |
| `NotificationController` | Controller | List notifications page + unread count API |
| `BroadcastForm` | Form | Admin batch-broadcast announcement to all users |
| `NotificationPreferencesForm` | Form | Per-user channel preferences for 9 event types |
| `NotificationBellBlock` | Block Plugin | Bell icon with unread count in site header |
| `lms_notification` | Entity | In-app notification record |

---

## Internal Structure

```
web/modules/custom/lms_notification/
+-- lms_notification.info.yml           # package: EverLMS; deps: lms_base
+-- lms_notification.routing.yml        # 4 routes
+-- lms_notification.services.yml       # lms_notification.manager, lms_notification.event_subscriber
+-- lms_notification.module             # hook_theme(), hook_cron()
+-- lms_notification.install            # lms_notification_update_10001() — adds field_channel, field_reference_type, field_reference_id
+-- lms_notification.libraries.yml      # 3 libs: list, preferences, bell
+-- drush.services.yml                  # lms_notification.commands → LmsNotificationCommands
+-- src/
|   +-- NotificationManager.php         # dispatch(), createInAppNotification()
|   +-- Controller/
|   |   +-- NotificationController.php  # listNotifications(), apiUnreadCount()
|   +-- Entity/
|   |   +-- Notification.php            # lms_notification content entity
|   +-- EventSubscriber/
|   |   +-- NotificationEventSubscriber.php  # onQuizPassed()
|   +-- Form/
|   |   +-- BroadcastForm.php           # admin broadcast with batch processing
|   |   +-- NotificationPreferencesForm.php  # per-user preferences
|   +-- Plugin/
|   |   +-- Block/
|   |   |   +-- NotificationBellBlock.php    # lms_notification_bell_block
|   +-- Commands/
|   |   +-- LmsNotificationCommands.php # Drush remind-inactive command
+-- css/
|   +-- lms-notification.css
|   +-- lms-notification-bell.css
|   +-- lms-notification-prefs.css
+-- js/
|   +-- lms-notification-bell.js
+-- templates/
|   +-- lms-notifications.html.twig         # vars: notifications, current_type, pager
|   +-- lms-notification-bell.html.twig     # vars: unread_count, notifications_url, latest_notifications
```

---

## Entity: `lms_notification` — Notification

| Field | Type | Cardinality | Required | Notes |
|-------|------|-------------|----------|-------|
| id | auto | 1 | — | PK |
| uuid | auto | 1 | — | |
| uid | entity_reference (user) | 1 | — | recipient; via `EntityOwnerTrait` |
| field_type | list_string | 1 | — | enrollment / quiz_result / certificate / payment / forum_reply / membership_expiry / new_lesson / announcement / reminder / system |
| field_channel | list_string | UNLIMITED | — | in_app / email / push; default [in_app] |
| field_title | string (255) | 1 | TRUE | |
| field_message | text_long | 1 | — | |
| field_link | string (512) | 1 | — | |
| field_read | boolean | 1 | — | default FALSE |
| field_reference_type | string (64) | 1 | — | entity type id of related entity; added by update_10001 |
| field_reference_id | integer | 1 | — | entity id of related entity; added by update_10001 |
| created | created | 1 | — | |

Table: `lms_notification`. Admin permission: `administer lms_course`. Access handler: core default `EntityAccessControlHandler`.

---

## Service: `NotificationManager`

File: `src/NotificationManager.php`

Injected services: `entity_type.manager`, `user.data`, `logger.factory`.

| Method | In-App Status | Email Status | Push Status |
|--------|--------------|--------------|-------------|
| `dispatch(type, recipient, title, message, link, channels, reference)` | DONE | STUB | STUB |
| `createInAppNotification(...)` | DONE | — | — |

**Dispatch logic:**
1. Reads user preferences via `user.data->get($uid, 'lms_notification', "pref_{$type}")`.
2. For `in_app` channel: calls `createInAppNotification()` — creates and saves `lms_notification` entity. **Working.**
3. For `email` channel: writes `$this->logger->info("TODO: send email...")` and has a TODO comment for Drupal queue mail. **No email is sent.**
4. For `push` channel: writes `$this->logger->info("TODO: FCM push...")`. **No push is sent.**

`createInAppNotification()` creates the `lms_notification` entity with all fields (uid, type, channel, title, message, link, read=FALSE, optional reference_type/reference_id) and saves it.

---

## Event Subscriber: `NotificationEventSubscriber`

Conditionally subscribes to `QuizPassedEvent::EVENT_NAME` only if `Drupal\lms_quiz\Event\QuizPassedEvent` class exists (soft dependency pattern).

`onQuizPassed(QuizPassedEvent $event)`: calls `lms_notification.manager->dispatch('quiz_result', $userId, ...)` to notify the quiz taker.

---

## Controllers

### `NotificationController` — as-built (working)

| Method | Route | Description |
|--------|-------|-------------|
| `listNotifications()` | `lms_notification.list` | Loads user's notifications with optional `?type=` filter; pager (15); marks all unread as read on page visit (side effect on GET); renders `lms_notifications` template. Cache: `max-age: 0` per user (uncached). |
| `apiUnreadCount()` | `lms_notification.api_unread_count` | Returns JSON `{count: N}` of unread in_app notifications. Returns `{count: 0}` for anonymous. Route has `_access: TRUE` — publicly accessible (see BUG-NOTIF-01). |

---

## Forms

### `BroadcastForm` — as-built (working)

- Fields: title, message, link.
- On submit: sets up batch operation to dispatch `announcement` notifications to all active users.
- `processBatch()` (static): queries users in batches of 50; calls `lms_notification.manager::dispatch('announcement', ...)` per user.

### `NotificationPreferencesForm` — as-built (working)

- Manages per-user preferences for 9 event types: enrollment, quiz_result, certificate, payment, forum_reply, new_lesson, membership_expiry, announcement, reminder.
- Reads/writes via `user.data` service (key per `pref_{type}`).
- Renders a table with In-App + Email checkbox columns per event type.
- Modal-aware: adds AJAX submit + cancel when rendered in a modal context.

---

## Plugins

### `NotificationBellBlock` — as-built (working)

| Attribute | Value |
|-----------|-------|
| Plugin ID | `lms_notification_bell_block` |
| Admin label | Notification Bell |
| Category | LMS |
| `build()` | Counts unread in_app notifications for current user; loads top 5 latest in_app notifications; renders `lms_notification_bell` template. |
| Cache | Per user (user cache context); tagged `lms_notification_list`. |

---

## Drush Commands

### `LmsNotificationCommands` (`lms_notification:remind-inactive`) — as-built (working)

| Command | Alias | Description |
|---------|-------|-------------|
| `lms_notification:remind-inactive` | `lms-remind` | Queries users who last accessed 7–14 days ago (up to 50); dispatches `reminder` type notification to each via `lms_notification.manager`. |

---

## Hook Implementations

| Hook | File | Description |
|------|------|-------------|
| `hook_theme()` | `lms_notification.module` | Registers `lms_notifications` (vars: notifications, current_type, pager) and `lms_notification_bell` (vars: unread_count, notifications_url, latest_notifications). |
| `hook_cron()` | `lms_notification.module` | Deletes `lms_notification` entities that are read AND older than 90 days; processes up to 100 per cron run. |

---

## Libraries

| Library | Assets | Use |
|---------|--------|-----|
| `lms_notification/list` | `lms-notification.css` | Notification list page (`/notifications`) |
| `lms_notification/preferences` | `lms-notification-prefs.css` | Preferences form |
| `lms_notification/bell` | `lms-notification-bell.css`, `lms-notification-bell.js` | Bell block in site header |

---

## Integration Points (Inbound)

| Trigger | Module | How |
|---------|--------|-----|
| Quiz passed | `lms_quiz` | `NotificationEventSubscriber` subscribes to `QuizPassedEvent` |
| Forum reply saved | `lms_forum` | `ForumReplyForm::save()` calls `lms_notification.manager::dispatch('forum_reply')` directly |
| Admin broadcast | (admin UI) | `BroadcastForm` batch-dispatches announcement notifications |
| Inactive users | Drush cron | `lms_notification:remind-inactive` Drush command |

Other event types (enrollment, certificate, payment, membership_expiry, new_lesson) are declared as `field_type` allowed values and `NotificationPreferencesForm` preference options but have no event subscriber wired up — they must be dispatched manually by the relevant modules via `lms_notification.manager::dispatch()`.

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_base` | Internal | Shared base traits |
| `lms_quiz` | Internal (soft, optional) | Event subscriber conditionally checks for `QuizPassedEvent` class |
| `lms_forum` | Internal (runtime caller) | `ForumReplyForm` calls the manager service |
| `user.data` | Drupal core service | User preference storage |
| `logger.factory` | Drupal core service | Logging in manager (and email/push stubs) |

---

## Known Issues / Bugs

| ID | Severity | Description |
|----|----------|-------------|
| BUG-NOTIF-01 | Medium | `lms_notification.api_unread_count` route has `_access: TRUE` — anonymous users can call this endpoint. Should be `_user_is_logged_in: TRUE`. Currently returns `{count: 0}` for anonymous, but the open access is a policy gap. |
| BUG-NOTIF-02 | Medium | `listNotifications()` marks all unread notifications as read on every GET request (side effect on read). This means visiting the list page on any device marks all as read everywhere. |
| STUB-NOTIF-01 | High | Email channel is a non-functional stub. `dispatch()` for channel=`email` only writes an info log with `TODO: Implement Drupal queue creation for mail`. No email is ever sent. |
| STUB-NOTIF-02 | High | Push channel is a non-functional stub. `dispatch()` for channel=`push` only writes an info log. FCM is mentioned in a comment but not implemented. |
| DEBT-NOTIF-01 | Low | Several notification types (enrollment, certificate, payment, membership_expiry, new_lesson) are defined in `field_type` allowed values and preferences form but have no event subscriber calling `dispatch()`. Must be wired by owning modules. |

> These feed [E02 Issues](../../E-Quality/E02-issues/).

---

## Cross-Links

- Entity detail: [C04 Content Entities](../C04-entities/01-content-entities.md)
- Forum-reply trigger: [lms_forum module](lms_forum.md)
- Quiz-passed event: [lms_quiz module](lms_quiz.md)
