# Feature Guide: Notifications và Notes

**Feature ID:** FEAT-08 (communication)
**Available since:** v0.1.0
**User roles:** Authenticated
**Prerequisites:** —

## What It Does

**Notifications:** Hệ thống gửi thông báo (in-app + optional email) cho 10 loại sự kiện. Bell icon header hiển thị count unread.

**Notes:** Trong mỗi lesson, user có thể viết notes riêng tư, timestamp-linked với vị trí video (nếu video lesson). Notes được lưu vào `Note` entity và exportable.

## 10 Notification Types

| Type | Trigger | Default channel |
|------|---------|-----------------|
| `enrollment` | Enroll course thành công | In-app |
| `lesson_reminder` | Idle trong khóa 7 ngày | Email + in-app |
| `quiz_graded` | Essay được instructor chấm | In-app + email |
| `certificate_issued` | Hoàn thành khóa + earned cert | In-app + email |
| `forum_reply` | Reply vào topic của user | In-app |
| `forum_mention` | `@user` trong forum | In-app + email |
| `order_completed` | Payment thành công | Email |
| `order_failed` | Payment thất bại | Email |
| `membership_expiring` | Membership hết hạn trong 7 ngày | Email |
| `badge_awarded` | Earn new badge | In-app |

## How to Use — Notifications

### Xem

- **Bell icon** header → dropdown list 10 notification gần nhất.
- **View all** → `/notifications` (paginated).
- **Mark as read:** click notification hoặc **Mark all as read** button.
- Notification **không expire** (chỉ ẩn khi user dismiss).

### Cấu hình

- `/user/{id}/edit/notifications`:
  - Per-type toggle (in-app on/off, email on/off).
  - Email digest: Immediate / Daily / Weekly / Off.

### Endpoint cho SPA/mobile (sau này)

- `GET /api/lms/notifications` — paginated list.
- `GET /api/lms/notifications/unread-count` — badge count.
- `POST /api/lms/notifications/{id}/read` — mark as read.

> ⚠️ **FIND-013:** Endpoint `/api/lms/notifications/unread-count` hiện đang allow anonymous; sẽ fix trong TASK-024 hardening. Không production-ready.

## How to Use — Notes

### Tạo note

1. Trong lesson page → sidebar **Take Notes**.
2. Write note → auto-save mỗi 5s.
3. Video lesson: click **⏱ Attach timestamp** → note link đến timestamp hiện tại.

### Xem lại

- `/my-learning/{course}/notes` → tất cả notes trong khóa, nhóm theo lesson.
- Click timestamp link → jump đến vị trí video.

### Export

- **Export all notes** → Markdown / PDF.

## Configuration Options

| Option | Default | Description |
|--------|---------|-------------|
| Notification sound | On | Bell rung khi có noti mới |
| Desktop notification | Off | Browser push (phải grant permission) |
| Note autosave interval | 5s | — |

## Tips & Best Practices

- Note timestamp để revise phần khó sau này — **Jump to timestamp** rất hữu ích cho video dài.
- Dùng format **Markdown** trong note (heading, list, code) — sẽ render khi export PDF.
- Setup email digest Weekly để tránh spam mailbox nhưng vẫn theo dõi được.

## Known Limitations

- Notes chưa share được giữa users (private only trong MVP).
- Push desktop notification chưa hỗ trợ iOS Safari (web push limitation).
- Email delivery phụ thuộc SMTP config — xem U08 §SMTP.

## Related

- Feature spec: `B-Requirements/B03-features/08-communication.md`
- Entity: `C-Design/C04-entities/07-communication-entities.md` (Notification, Note)
- Wireframe: `C-Design/C08-wireframes/07-user-dashboard.md`, `10-community.md`
- Risk: FIND-013 (anonymous access on `/api/lms/notifications/unread-count`)
