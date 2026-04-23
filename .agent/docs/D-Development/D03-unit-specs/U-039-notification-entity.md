# D03 — Unit Spec: Notification Entity (10 types) + Controller

**Unit ID:** U-039
**Group:** G-COMMUNICATION
**Title:** Notification entity (10 types) + controller
**Status:** DONE
**Priority:** P1

---

## Scope

Implements the `lms_notification` custom content entity with a `field_type` discriminator supporting 10 notification types (D01 states 6; actual implementation has 10). Includes `NotificationManager` service, `NotificationController`, `NotificationBellBlock`, event subscriber, broadcast form, and preferences form.

Note: D01 states "6 types" but actual implementation has 10: `enrollment`, `quiz_result`, `certificate`, `payment`, `forum_reply`, `membership_expiry`, `new_lesson`, `announcement`, `reminder`, `system`.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_notification/lms_notification.info.yml` | Module declaration |
| `web/modules/custom/lms_notification/lms_notification.install` | Schema install |
| `web/modules/custom/lms_notification/lms_notification.module` | Hook implementations |
| `web/modules/custom/lms_notification/lms_notification.routing.yml` | Notification routes |
| `web/modules/custom/lms_notification/lms_notification.services.yml` | Service registration |
| `web/modules/custom/lms_notification/lms_notification.libraries.yml` | CSS/JS assets |
| `web/modules/custom/lms_notification/drush.services.yml` | Drush command registration |
| `web/modules/custom/lms_notification/src/Entity/Notification.php` | Notification entity class |
| `web/modules/custom/lms_notification/src/NotificationManager.php` | Notification dispatch service |
| `web/modules/custom/lms_notification/src/Controller/NotificationController.php` | Notification list + API |
| `web/modules/custom/lms_notification/src/EventSubscriber/NotificationEventSubscriber.php` | Event subscriber |
| `web/modules/custom/lms_notification/src/Plugin/Block/NotificationBellBlock.php` | Header bell block |
| `web/modules/custom/lms_notification/src/Form/BroadcastForm.php` | Admin broadcast form |
| `web/modules/custom/lms_notification/src/Form/NotificationPreferencesForm.php` | User preferences form |
| `web/modules/custom/lms_notification/src/Commands/LmsNotificationCommands.php` | Drush commands |
| `web/modules/custom/lms_notification/templates/lms-notifications.html.twig` | Notifications list template |
| `web/modules/custom/lms_notification/templates/lms-notification-bell.html.twig` | Bell block template |
| `web/modules/custom/lms_notification/css/lms-notification.css` | Notification styles |
| `web/modules/custom/lms_notification/css/lms-notification-bell.css` | Bell block styles |
| `web/modules/custom/lms_notification/css/lms-notification-prefs.css` | Preferences styles |
| `web/modules/custom/lms_notification/js/lms-notification-bell.js` | Bell block JS |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_notification` (`Notification extends ContentEntityBase implements EntityOwnerInterface`)
- `field_type` (10 values): `enrollment`, `quiz_result`, `certificate`, `payment`, `forum_reply`, `membership_expiry`, `new_lesson`, `announcement`, `reminder`, `system`
- `field_channel` (multi-value): `in_app`, `email`, `push`
- Key fields: `uid`, `field_type`, `field_channel`, `field_title`, `field_message`, `field_is_read`, `field_reference_type`, `field_reference_id`

**Service `NotificationManager`:** `dispatch(...)`, `createInAppNotification(...)`

**NotificationController:** `listNotifications(): array`, `apiUnreadCount(): JsonResponse`

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Entity schema installs; bell block renders |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Notification create/list/mark-read functional |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_notification` entity type registered
- [x] All 10 notification types selectable
- [x] 3 channel types: in_app, email, push
- [x] Notification bell block shows unread count
- [x] `/notifications` lists user notifications
- [x] `apiUnreadCount()` returns JSON count for AJAX polling
- [x] User preferences form allows channel opt-out

---

## Known Debt / Issues

- D01 states "6 types" but 10 are implemented.
- Email and push channels (U-044) are SPEC — only in-app channel is fully implemented.

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-042 | Notification dispatch service (SPEC) |
| U-044 | Email notification integration (SPEC) |

---

*End of U-039-notification-entity.md*
