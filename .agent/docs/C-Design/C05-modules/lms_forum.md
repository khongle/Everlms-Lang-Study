# lms_forum — Discussion Forum Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_forum` |
| Name | LMS Forum |
| Layer | **Communication** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base`, `lms_course` |

## Mô tả

Module diễn đàn thảo luận trong khóa học. Cung cấp 2 entity (ForumTopic + ForumReply), threaded replies, tính năng "mark best answer". `RouteSubscriber` để alter routes động.

---

## Provided Entities

### `lms_forum_topic` (Content Entity)
| Fields | Description |
|--------|-------------|
| title | Tiêu đề chủ đề |
| body | Nội dung |
| uid | Tác giả |
| course ref | Gắn với khóa học |
| is_pinned | Pin topic |
| is_closed | Đóng topic |
| created/changed | Timestamps |

Access handler: `ForumTopicAccessControlHandler`

### `lms_forum_reply` (Content Entity)
| Fields | Description |
|--------|-------------|
| body | Nội dung reply |
| uid | Tác giả |
| topic ref | Parent topic |
| parent_reply ref | Parent reply (threaded) |
| is_best_answer | Boolean |
| created/changed | Timestamps |

Access handler: `ForumReplyAccessControlHandler`

---

## Provided Routes

| Route ID | Path | Controller/Form | Access |
|----------|------|----------------|--------|
| `lms_forum.index` | `/forum` | `LmsForumController::index` | `access content` |
| `lms_forum.topic.add` | `/forum/new` | `LmsForumController::addTopic` | entity create access |
| `lms_forum.reply.add` | `/forum/{lms_forum_topic}/reply` | `LmsForumController::reply` | entity create access |
| `lms_forum.reply.best_answer` | `/forum/{lms_forum_topic}/reply/{lms_forum_reply}/best` | `LmsForumController::markBestAnswer` | `access content` |
| `entity.lms_forum_reply.delete_form` | `/forum/reply/{lms_forum_reply}/delete` | Entity delete form | entity delete access |
| `lms_forum.reply.edit` | `/forum/reply/{lms_forum_reply}/edit` | `LmsForumController::editReply` | entity update access |

---

## Provided Services

| Service ID | Class | Purpose |
|-----------|-------|---------|
| `lms_forum.route_subscriber` | `RouteSubscriber` | Alter routes (EventSubscriber tagged) |

---

## Provided Plugins

Không có plugin.

---

## Hook Implementations

Có `.module` file — nội dung chưa được đọc chi tiết trong session này.

---

## Controllers

| Class | Purpose |
|-------|---------|
| `ForumController` | Core forum logic |
| `LmsForumController` | Frontend forum: index, addTopic, reply, markBestAnswer, editReply |

---

## Templates & Cache Strategy

Forum pages cache per-route với cache tags trên forum_topic entities. Reply list phụ thuộc topic cache tag.

---

## Related Units

- **C03** § Communication Domain — entities ForumTopic, ForumReply
- **C05** `04-certificate-communication.md` — layer overview
- `lms_course.md` — course-scoped topics

---

## Known Issues / Debt

- `lms_forum.reply.best_answer` chỉ yêu cầu `access content` — bất kỳ user nào cũng có thể mark best answer. Nên restrict chỉ topic author hoặc instructor.
- Không có moderation/spam filter.
- `RouteSubscriber` không rõ alter gì — cần audit.
