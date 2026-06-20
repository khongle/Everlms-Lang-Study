# D03 — Story Spec: lms_notification

**Story ID:** lms_notification
**Story type:** module
**Title:** In-App Notification System — NotificationManager service, bell block, event subscriber; email/push stubs
**Primary source:** `C-Design/C05-modules/lms_notification.md`
**Owning module(s):** lms_notification
**Service(s):** notification
**Epic:** E-07 (Communication)
**Contributes to Feature(s):** Communication, Learning Progress
**Design source(s):** `../C-Design/C05-modules/lms_notification.md`
**Round:** N/A
**Driver:** SA directive
**Status:** IN_PROGRESS
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** (pre-2026-04-15; implemented before D03 layer was materialized)
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_notification`

---

## Session Strategy

**Estimated session count:** 3
**Splitting strategy:**
- S1: `lms_notification` entity; `NotificationManager` service; `lms_notification.services.yml`; routing; module declaration and install hook
- S2: `NotificationController` (list + unread count API); `NotificationBellBlock`; `NotificationEventSubscriber`; `hook_cron()` + `hook_theme()`
- S3: `BroadcastForm` (admin batch); `NotificationPreferencesForm` (per-user preferences); `LmsNotificationCommands` (Drush); CSS/JS libraries; templates

Status is IN_PROGRESS because email and push channels are non-functional stubs. In-app channel is fully working.

---

## Scope

Delivers in-app notifications to EverLMS learners:

**Working (as-built):**
- `lms_notification` entity with 10 notification types and 3 channel values (in_app, email, push).
- `NotificationManager` service: `dispatch()` routes in_app notifications to `createInAppNotification()` (fully functional); email and push branches are stubs (log + TODO only).
- `NotificationController`: list page with type filter + mark-all-read side effect; unread-count JSON API.
- `NotificationBellBlock`: counts unread, loads top-5 latest, renders bell template with cache-per-user.
- `NotificationEventSubscriber`: subscribes to `QuizPassedEvent` (soft dependency on lms_quiz) and dispatches `quiz_result` notification.
- `BroadcastForm`: admin batch broadcast to all active users.
- `NotificationPreferencesForm`: per-user preferences for 9 event types (in-app + email checkboxes).
- `LmsNotificationCommands`: Drush `lms_notification:remind-inactive` command.
- `hook_cron()`: auto-deletes read notifications older than 90 days (100 per cron run).

**Stubs (not working):**
- Email channel: `dispatch()` for channel=`email` writes info log + TODO comment only; no email sent.
- Push channel: `dispatch()` for channel=`push` writes info log only; FCM mentioned in comment but not implemented.

Full design: [`../C-Design/C05-modules/lms_notification.md`](../../C-Design/C05-modules/lms_notification.md)

---

## Source Documents

| Source | Why loaded |
|---|---|
| `../C-Design/C05-modules/lms_notification.md` | Owning module design — authoritative reference |
| `../C-Design/C05-modules/lms_quiz.md` | QuizPassedEvent consumed by NotificationEventSubscriber |
| `../C-Design/C05-modules/lms_forum.md` | ForumReplyForm calls lms_notification.manager::dispatch('forum_reply') |

---

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| In-app notifications delivered for: quiz_result, forum_reply, announcement, reminder | B03-features/08-communication.md | Acceptance |
| Bell block shows unread count in site header | B03-features/08-communication.md | Acceptance |
| Notification list page with type filter | B03-features/08-communication.md | Acceptance |
| Per-user preferences to opt out of notification types | B03-features/08-communication.md | Acceptance |
| Admin can broadcast announcements to all users | B03-features/08-communication.md | Acceptance |
| Drush command to remind inactive users (7–14 days) | B03-features/08-communication.md | Acceptance |
| Read notifications auto-deleted after 90 days via cron | B02-technical-requirements.md | Technical |
| Email notifications delivered (planned; currently stub) | B03-features/08-communication.md | Acceptance (target-state) |
| Push notifications via FCM (planned; currently stub) | B03-features/11-mobile-app.md | Acceptance (target-state) |

---

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_notification/lms_notification.info.yml` | Create | Module declaration; deps: lms_base | — | S1 |
| `web/modules/custom/lms_notification/lms_notification.routing.yml` | Create | 4 routes: /notifications, /manage/settings/notifications, /admin/.../broadcast, /api/lms/notifications/unread-count | — | S1 |
| `web/modules/custom/lms_notification/lms_notification.services.yml` | Create | lms_notification.manager; lms_notification.event_subscriber | — | S1 |
| `web/modules/custom/lms_notification/lms_notification.module` | Create | hook_theme() (lms_notifications, lms_notification_bell); hook_cron() | — | S1 |
| `web/modules/custom/lms_notification/lms_notification.install` | Create | lms_notification_update_10001(): adds field_channel, field_reference_type, field_reference_id | — | S1 |
| `web/modules/custom/lms_notification/lms_notification.libraries.yml` | Create | 3 libraries: list, preferences, bell (CSS + JS) | — | S1 |
| `web/modules/custom/lms_notification/drush.services.yml` | Create | lms_notification.commands → LmsNotificationCommands | — | S1 |
| `web/modules/custom/lms_notification/src/Entity/Notification.php` | Create | lms_notification entity; 11 base fields; EntityOwnerTrait | — | S1 |
| `web/modules/custom/lms_notification/src/NotificationManager.php` | Create | dispatch() (in_app working; email/push stubs); createInAppNotification() | — | S1 |
| `web/modules/custom/lms_notification/src/Controller/NotificationController.php` | Create | listNotifications() (mark-read side effect); apiUnreadCount() (public route) | — | S2 |
| `web/modules/custom/lms_notification/src/EventSubscriber/NotificationEventSubscriber.php` | Create | Subscribes to QuizPassedEvent (soft dep); dispatches quiz_result notification | — | S2 |
| `web/modules/custom/lms_notification/src/Plugin/Block/NotificationBellBlock.php` | Create | lms_notification_bell_block; unread count + top-5 latest; cache per user | — | S2 |
| `web/modules/custom/lms_notification/src/Form/BroadcastForm.php` | Create | Admin broadcast; batch dispatch to all active users in batches of 50 | — | S3 |
| `web/modules/custom/lms_notification/src/Form/NotificationPreferencesForm.php` | Create | Per-user preferences for 9 event types; reads/writes via user.data | — | S3 |
| `web/modules/custom/lms_notification/src/Commands/LmsNotificationCommands.php` | Create | Drush lms_notification:remind-inactive; finds users 7–14 days inactive (up to 50) | — | S3 |
| `web/modules/custom/lms_notification/css/lms-notification.css` | Create | List page styles | — | S3 |
| `web/modules/custom/lms_notification/css/lms-notification-bell.css` | Create | Bell block styles | — | S3 |
| `web/modules/custom/lms_notification/css/lms-notification-prefs.css` | Create | Preferences form styles | — | S3 |
| `web/modules/custom/lms_notification/js/lms-notification-bell.js` | Create | Bell block JS behavior | — | S3 |
| `web/modules/custom/lms_notification/templates/lms-notifications.html.twig` | Create | vars: notifications, current_type, pager | — | S3 |
| `web/modules/custom/lms_notification/templates/lms-notification-bell.html.twig` | Create | vars: unread_count, notifications_url, latest_notifications | — | S3 |

---

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| lms_base | Internal module | Shared entity traits |
| lms_quiz (soft, optional) | Internal module | NotificationEventSubscriber checks for QuizPassedEvent class |
| lms_forum (runtime caller) | Internal module | ForumReplyForm calls lms_notification.manager::dispatch('forum_reply') |
| user.data | Drupal core service | Per-user preference storage |
| logger.factory | Drupal core service | Logging in manager and in email/push stubs |
| Drupal mail system (target-state) | Core / contrib | Email delivery (not yet implemented; stub only) |
| FCM (target-state) | External | Push delivery (not yet implemented; stub only) |

---

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| Service registration pattern | NotificationManager, NotificationEventSubscriber | lms_notification.services.yml with correct arguments and tags |
| Soft dependency / class_exists guard | NotificationEventSubscriber | Conditionally registers QuizPassedEvent subscription only if class exists |
| Drush service pattern | LmsNotificationCommands | drush.services.yml; drush.command tag |
| Cache per user | NotificationBellBlock | user cache context + lms_notification_list tag |

---

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission |
|---|---|---|---|---|
| Bell block | unread count, latest 5 notifications | lms_notification entity | Site header (block placement) | authenticated |
| Notification list | title, message, type filter, pager | lms_notification entity | `/notifications` | authenticated (own notifications) |
| Preferences form | per-type in-app/email toggles | user.data storage | `/manage/settings/notifications` | authenticated |
| Broadcast form | title, message, link | lms_notification entity (created per user) | `/admin/lms/notifications/broadcast` | administer lms_course |

**Hardcoded content exceptions:** None.

---

## Feature Flags Introduced or Read

None. No feature flags in this module.

---

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-NOTIF-001 | Unit | dispatch() for in_app creates lms_notification entity | Entity created; field_read=FALSE |
| T-NOTIF-002 | Unit | dispatch() for email writes log only; no email sent (stub) | Log present; no mail queued |
| T-NOTIF-003 | Integration | QuizPassedEvent triggers quiz_result notification | Notification entity for quiz taker |
| T-NOTIF-004 | Integration | listNotifications() marks unread as read on page load | field_read=TRUE after GET |
| T-NOTIF-005 | Integration | apiUnreadCount() returns {count: N} for authenticated; {count: 0} for anonymous | Correct count returned |
| T-NOTIF-006 | Integration | BroadcastForm dispatches to all active users in batches | N notifications created |
| T-NOTIF-007 | Integration | hook_cron() deletes read notifications older than 90 days (up to 100) | Old read notifications purged |
| T-NOTIF-008 | Integration | NotificationPreferencesForm saves per-user prefs to user.data | Preferences persist |

---

## Gate Results

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Entity installs; service registered; bell block renders; routes resolve | — | pre-2026-04-15 |
| G2 Lint | PASS | No coding standard violations | — | pre-2026-04-15 |
| G3 Tests | PASS | In-app notification delivery fully functional; all working paths tested | — | pre-2026-04-15 |
| G4 Adversarial | FAIL | STUB-NOTIF-01 (email), STUB-NOTIF-02 (push), BUG-NOTIF-01 (public route) open | — | pre-2026-04-15 |

---

## Notes

### Channel Implementation Status

| Channel | Status | Description |
|---------|--------|-------------|
| `in_app` | DONE (as-built) | Creates lms_notification entity; bell block and list page fully functional |
| `email` | stub | `dispatch()` writes `$this->logger->info("TODO: Implement Drupal queue creation for mail")`. No email is ever sent. |
| `push` | stub | `dispatch()` writes `$this->logger->info("TODO: FCM push...")`. No push is ever sent. FCM noted in comment. |

### Known Bugs — blocks DONE status

| ID | Severity | Description |
|----|----------|-------------|
| STUB-NOTIF-01 | High | Email channel is non-functional; dispatch() for channel=email only logs. No mail system integration. |
| STUB-NOTIF-02 | High | Push channel is non-functional; dispatch() for channel=push only logs. FCM not implemented. |
| BUG-NOTIF-01 | Medium | `lms_notification.api_unread_count` has `_access: TRUE` — anonymous users can call this endpoint. Should be `_user_is_logged_in: TRUE`. |
| BUG-NOTIF-02 | Medium | `listNotifications()` marks ALL unread notifications as read on every GET request. Visiting the list on any device marks as read globally. |

### Technical Debt

| ID | Severity | Description |
|----|----------|-------------|
| DEBT-NOTIF-01 | Low | Several notification types (enrollment, certificate, payment, membership_expiry, new_lesson) are declared as field_type values and in the preferences form but have no event subscriber calling dispatch(). Must be wired by owning modules. |

### Integration Points (Inbound)

| Trigger | Module | How |
|---------|--------|-----|
| Quiz passed | lms_quiz | NotificationEventSubscriber subscribes to QuizPassedEvent |
| Forum reply saved | lms_forum | ForumReplyForm::save() calls lms_notification.manager::dispatch('forum_reply') directly |
| Admin broadcast | admin UI | BroadcastForm batch-dispatches announcement notifications |
| Inactive users | Drush cron | lms_notification:remind-inactive Drush command |

### Resolution Path to DONE

1. Implement email channel: integrate Drupal mail system (queue-based recommended) in `NotificationManager::dispatch()` for channel=email (STUB-NOTIF-01)
2. Implement push channel: FCM integration via HTTP API (STUB-NOTIF-02)
3. Fix `lms_notification.api_unread_count` route to require `_user_is_logged_in: TRUE` (BUG-NOTIF-01)
4. Consider per-notification read tracking instead of bulk mark-read on list load (BUG-NOTIF-02)

These stubs and bugs feed [E02 Issues](../../E-Quality/E02-issues/).
