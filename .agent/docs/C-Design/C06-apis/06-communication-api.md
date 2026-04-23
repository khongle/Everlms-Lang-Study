# C06-06 — Communication API

> Modules: `lms_forum`, `lms_notification`, `lms_rating`.
> Hầu hết là page routes (HTML). Một JSON endpoint thuần: `/api/lms/notifications/unread-count`.

---

## Route Table

### lms_forum

| Route ID | Path | Method | Controller | Auth | Response |
|----------|------|--------|-----------|------|----------|
| `lms_forum.index` | `/forum` | GET | `LmsForumController::index` | anon | HTML |
| `lms_forum.topic.add` | `/forum/new` | GET/POST | `LmsForumController::addTopic` | entity_create:lms_forum_topic | HTML/Form |
| `lms_forum.reply.add` | `/forum/{lms_forum_topic}/reply` | GET/POST | `LmsForumController::reply` | entity_create:lms_forum_reply | HTML/Form |
| `lms_forum.reply.best_answer` | `/forum/{lms_forum_topic}/reply/{lms_forum_reply}/best` | GET | `LmsForumController::markBestAnswer` | anon* | HTML/redirect |
| `lms_forum.reply.edit` | `/forum/reply/{lms_forum_reply}/edit` | GET/POST | `LmsForumController::editReply` | entity:lms_forum_reply.update | HTML/Form |
| `entity.lms_forum_reply.delete_form` | `/forum/reply/{lms_forum_reply}/delete` | GET/POST | `_entity_form:lms_forum_reply.delete` | entity:lms_forum_reply.delete | HTML/Form |

> *`best_answer`: permission là `access content` (anon) — business logic trong controller phải check ownership (course instructor / topic author). Gap RISK-001.

### lms_notification

| Route ID | Path | Method | Controller / Form | Auth | Response |
|----------|------|--------|-------------------|------|----------|
| `lms_notification.list` | `/notifications` | GET | `NotificationController::listNotifications` | login | HTML |
| `lms_notification.preferences` | `/manage/settings/notifications` | GET/POST | `NotificationPreferencesForm` | login | HTML/Form |
| `lms_notification.broadcast` | `/admin/lms/notifications/broadcast` | GET/POST | `BroadcastForm` | perm:administer lms_course | HTML/Form |
| `lms_notification.api_unread_count` | `/api/lms/notifications/unread-count` | GET | `NotificationController::apiUnreadCount` | `_access:TRUE`* | JSON |

> *`unread-count`: khai báo `_access:TRUE` — anonymous cũng access được route. Controller phải tự return 0 cho user chưa login.

### lms_rating

| Route ID | Path | Method | Controller | Auth | Response |
|----------|------|--------|-----------|------|----------|
| `lms_rating.admin_list` | `/workspace/ratings` | GET | `RatingManagerController::list` | perm:administer lms_course | HTML |
| `lms_rating.admin_action` | `/workspace/ratings/{lms_rating}/{action}` | GET | `RatingManagerController::executeAction` | perm:administer lms_course | HTML/redirect |

---

## Route Chi Tiết

### GET `/api/lms/notifications/unread-count`

Đây là JSON API endpoint duy nhất trong communication domain.

**Auth:** `_access: TRUE` (route layer bypass — controller tự xử lý)
**Controller:** `NotificationController::apiUnreadCount`
**Dùng cho:** Polling từ frontend để update badge count trong header.

**Response (JSON):**
```json
{
  "count": 3
}
```

**Gap:** Không có WebSocket/SSE — frontend phải polling endpoint này. Polling interval chưa được document.

---

### GET + POST `/forum/new`

**Auth:** `entity_create:lms_forum_topic`
**Controller:** `LmsForumController::addTopic`

**Form fields (expected):**
| Field | Type | Mô tả |
|-------|------|-------|
| `title` | string | Tiêu đề topic |
| `body` | text | Nội dung |
| `course_id` | integer | Liên kết với course (optional) |
| `tags` | string[] | Tags (optional) |

---

### GET `/forum/{lms_forum_topic}/reply/{lms_forum_reply}/best`

**Auth:** `access content` (public route layer — **RISK-001**)
**Path params:** Both upcast to entities

**Business logic (controller must enforce):**
- Chỉ topic author hoặc course instructor có thể mark best answer.
- Set `is_best_answer = TRUE` trên `lms_forum_reply`.
- Chỉ 1 best answer per topic.

---

### GET `/workspace/ratings/{lms_rating}/{action}`

**Auth:** `perm:administer lms_course`
**Path params:** `action` — không có constraint trong routing (free string)

**Expected action values:** `approve`, `reject`, `feature`, `unfeature`
**Business logic:** `RatingManagerController::executeAction` dispatch theo `{action}`.

> **Gap RISK-001:** `action` param không có allowlist validation ở route layer — controller phải validate.

---

## Gap: Rating Public Submit

`lms_rating.routing.yml` chỉ có admin routes. Không có route cho student submit rating.

| Thiếu | Mô tả |
|-------|-------|
| `POST /courses/{id}/rate` | Student submit star rating + review |
| `GET /courses/{id}/ratings` | Danh sách ratings của course (public) |
| Rating edit/delete | Student sửa / xóa rating của mình |

> `RatingController.php` có method `toggleApprove` và `toggleFeatured` nhưng không có route tương ứng trong routing.yml → **dead code hoặc thiếu route**.

---

## Gap: Forum Topic View

Không có route `GET /forum/{lms_forum_topic}` để xem chi tiết một topic. Topic listing (`/forum`) và reply routes tồn tại nhưng không có topic detail page route trong `lms_forum.routing.yml`.

---

## Notification Types (6 types — no routing)

| Type | Trigger | Delivery |
|------|---------|---------|
| `course_enrolled` | EnrollmentEvent | In-app + email |
| `quiz_completed` | QuizCompletedEvent | In-app |
| `certificate_issued` | CertificateIssuedEvent | In-app + email |
| `payment_received` | PaymentReceivedEvent | In-app + email |
| `forum_reply` | ForumReplyCreatedEvent | In-app |
| `broadcast` | Admin manual | In-app + email |

---

## Known Issues

| Ref | Mô tả |
|-----|-------|
| RISK-001 | `best_answer` route dùng `access content` — thiếu instructor/author ownership check ở routing |
| RISK-001 | `admin_action` route: `{action}` param không có allowlist constraint |
| — | `RatingController` có public methods nhưng không có routes → dead code gap |
| — | Forum topic detail view route bị thiếu |
| — | Notification polling không có rate limit |

---

*Module paths: `web/modules/custom/lms_forum/`, `lms_notification/`, `lms_rating/`*
