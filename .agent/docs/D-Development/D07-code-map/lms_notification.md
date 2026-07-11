# D07 Code Map - lms_notification

`web/modules/custom/lms_notification/`

| Area | Location |
| ---- | -------- |
| Entity | `src/Entity/Notification.php` (`field_type`) |
| Routes | `lms_notification.routing.yml` (`/notifications`, `/manage/settings/notifications`, `/admin/lms/notifications/broadcast`, `/api/lms/notifications/unread-count`) |
| Controllers | `src/Controller/NotificationController.php` |
| Forms | `src/Form/NotificationPreferencesForm.php`, `src/Form/BroadcastForm.php` |
| Service | `src/NotificationManager.php` |
| Event | `src/EventSubscriber/NotificationEventSubscriber.php` |
| Block | `src/Plugin/Block/NotificationBellBlock.php` |
| Drush | `src/Commands/LmsNotificationCommands.php`, `drush.services.yml` |
| Templates | `templates/lms-notification-bell.html.twig`, `templates/lms-notifications.html.twig` |
| JS/CSS | `js/lms-notification-bell.js`, `css/lms-notification-bell.css`, `css/lms-notification-prefs.css`, `css/lms-notification.css` (`lms_notification.libraries.yml`) |

Story: [E09S01](../D03-stories/E09S01-lms_notification.md) · Entity: [C04](../../C-Design/C04-entities/lms_notification.md) · Module: [C05](../../C-Design/C05-modules/lms_notification.md)
