# lms_notification — In-App Notification Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_notification` |
| Name | LMS Notification |
| Layer | **Communication** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base` |

## Mô tả

Module thông báo in-app: gửi notification khi có sự kiện (enroll, quiz pass, order complete v.v.), hiển thị notification bell, broadcast từ admin. Sử dụng EventSubscriber để lắng nghe events từ các module khác và gọi `NotificationManager`.

---

## Provided Entities

### `lms_notification` (Content Entity)
| Fields | Description |
|--------|-------------|
| uid | Người nhận |
| type | 6 loại (từ taxonomy `lms_notification_type`) |
| message | Nội dung thông báo |
| is_read | Boolean |
| link | URL liên quan |
| created | Timestamp |

---

## Provided Routes

| Route ID | Path | Controller/Form | Access |
|----------|------|----------------|--------|
| `lms_notification.list` | `/notifications` | `NotificationController::listNotifications` | logged in |
| `lms_notification.preferences` | `/manage/settings/notifications` | `NotificationPreferencesForm` | logged in |
| `lms_notification.broadcast` | `/admin/lms/notifications/broadcast` | `BroadcastForm` | `administer lms_course` |
| `lms_notification.api_unread_count` | `/api/lms/notifications/unread-count` | `NotificationController::apiUnreadCount` | public |

---

## Provided Services

| Service ID | Class | Purpose |
|-----------|-------|---------|
| `lms_notification.manager` | `NotificationManager` | Tạo, đọc, đánh dấu read notifications; inject entity_type.manager, user.data, logger |
| `lms_notification.event_subscriber` | `NotificationEventSubscriber` | Lắng nghe LMS events → dispatch notification qua manager |

**drush.services.yml:** khai báo `LmsNotificationCommands` cho Drush commands.

---

## Provided Plugins

| Plugin Type | Implementation | Purpose |
|-------------|---------------|---------|
| Block | `NotificationBellBlock` | Block hiển thị bell icon + unread count |

---

## Hook Implementations

Không có `.module` file — không implement hook.

---

## Templates & Cache Strategy

Block `NotificationBellBlock` cache per-user với cache max-age thấp (gần realtime). Route `/api/lms/notifications/unread-count` là JSON endpoint, không cache.

---

## Related Units

- **C03** § Communication Domain — entity Notification
- **C05** `04-certificate-communication.md` — layer overview
- `lms_quiz.md` — QuizPassedEvent
- `lms_order.md` — order complete event
- `lms_progress.md` — enrollment event

---

## Known Issues / Debt

- `/api/lms/notifications/unread-count` có `_access: 'TRUE'` — anonymous user có thể query, nên yêu cầu `_user_is_logged_in`.
- Không có pagination cho `/notifications` — nếu user có nhiều thông báo sẽ load tất cả.
- Drush command không được document rõ ràng.
