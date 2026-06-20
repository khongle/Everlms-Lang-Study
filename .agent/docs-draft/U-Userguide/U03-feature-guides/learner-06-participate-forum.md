# Feature Guide: Tham gia Forum

**Feature ID:** FEAT-08 (communication)
**Available since:** v0.1.0
**User roles:** Authenticated; một số forum yêu cầu enrollment trong khóa liên quan.
**Prerequisites:** Tài khoản active; nếu forum gắn course thì phải enrolled.

## What It Does

Mỗi course có một forum riêng; ngoài ra có forum chung cho community. User có thể tạo topic, reply, mention (@username), upload attachment, vote up/down reply.

## How to Use

### Truy cập forum

- **Course forum:** Tab **Discussion** trong course detail page.
- **Global forum:** `/community` từ menu.
- **My posts:** User menu → **My Forum Activity**.

### Tạo topic mới

1. Click **New Topic**.
2. Chọn category (General / Q&A / Announcement — tùy forum).
3. Title + body (rich text).
4. Tag (optional, từ vocabulary Topic).
5. **Submit** → redirect về topic view.

### Reply

1. Scroll xuống cuối topic → input "Post a reply".
2. Rich text + quote (chọn text trong reply cũ → **Quote** button).
3. Submit.

### Mention & Notifications

- `@username` trong body → user được mention nhận notification.
- Subscribe to topic → notification khi có reply mới.
- User setting: `/user/{id}/edit/notifications`.

### Moderation (moderator role)

- **Pin topic**, **Lock topic**, **Delete reply** (soft delete, audit log kept).
- **Report** flow cho user — admin nhận ticket ở `/admin/lms/forum/reports`.

## Configuration Options (user)

| Option | Default | Description |
|--------|---------|-------------|
| Email digest | Daily | Tổng hợp forum activity trong 24h |
| Reply notify | On | Notify khi có reply vào topic mình viết |
| Mention notify | On | Notify khi bị mention |

## Tips & Best Practices

- Dùng **code block** (triple backtick) để format code — Twig render đẹp.
- Check **FAQ** trong course trước khi post Q&A (giảm duplicate).
- Rating helpful/not-helpful reply → giúp instructor nhìn ra câu hay.

## Known Limitations

- Full-text search trên forum chưa integrate Elasticsearch — đang dùng Drupal Search API mặc định (đôi khi match kém).
- Attachment giới hạn 5MB / file trong MVP.
- No real-time push (phải reload); WebSocket roadmap v2+.

## Related

- Feature spec: `B-Requirements/B03-features/08-communication.md`
- Entity: `C-Design/C04-entities/07-communication-entities.md` (ForumTopic, ForumReply)
- Workflow: `B-Requirements/B04-business-workflows/WF-008-forum-discussion.md`
- Wireframe: `C-Design/C08-wireframes/10-community.md`
