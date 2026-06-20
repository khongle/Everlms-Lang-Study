# C06 — API: Communication (Forum, Rating, Notification)

**Documentation mode:** as-built (in-app notification fully real; email/push stubs; forum active but has debug artifact; rating has 3 missing routes; wishlist JSON endpoints covered in 04-commerce-api.md)

**Modules:** `lms_forum`, `lms_rating`, `lms_notification`, `lms_note`
**Entities:** [lms_forum_topic](../C04-entities/01-content-entities.md), [lms_forum_reply](../C04-entities/01-content-entities.md), [lms_rating](../C04-entities/01-content-entities.md), [lms_notification](../C04-entities/01-content-entities.md), [lms_note](../C04-entities/01-content-entities.md)
**Module specs:** [lms_forum](../C05-modules/lms_forum.md), [lms_notification](../C05-modules/lms_notification.md)

---

## API Overview

**Route Group:** Authenticated learner (`/forum/*`, `/api/lms/*`) + Admin (`/workspace/*`)
**Authentication:** Mixed — most routes require `_user_is_logged_in`; notification unread-count is `_access: TRUE`
**Consumers:** Web frontend (Twig pages + AJAX), notification bell block
**Response format:** HTML (Twig) for most routes; JSON for `apiUnreadCount` and note endpoints

**JSON endpoints in this service:**
- `GET /api/lms/notifications/unread-count` — notification bell poll
- `POST /api/lms/note/save`, `POST /api/lms/note/delete`, `GET /api/lms/note/get` — lesson notes (covered below)

---

## Forum Endpoints

### GET `/forum`

**Route:** `lms_forum.index`
**Controller:** `LmsForumController::index`
**Authentication:** `_permission: access content` (public, including anonymous)
**Response:** HTML (Twig: `lms-forum-index.html.twig`)

**Logic:** Loads all `lms_forum_topic` entities DESC by created; builds items array with `reply_count`, author info (`field_first_name`/`field_last_name` + fallback to `getDisplayName()`), avatar; also renders new-topic form for authenticated users. Template variables: `{topics, new_topic_form}`.

---

### GET `/forum/{lms_forum_topic}`

**Route:** `entity.lms_forum_topic.canonical` (overridden by `RouteSubscriber`)
**Controller:** `LmsForumController::viewTopic` (replaces auto-generated entity canonical)
**Authentication:** `_permission: access content`
**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lms_forum_topic` | entity:lms_forum_topic | Topic entity (upcasted) |

**Response:** HTML (Twig: `lms-forum-topic.html.twig`)

**Logic:** Increments `field_views` counter; loads replies — anonymous users get `is_teaser_mode = TRUE` (truncated view); authenticated users see full replies with mark-best, edit, delete action URLs. Template variables: `{topic, replies, reply_form, is_teaser_mode, total_replies}`.

**Known bug (as-built):** `LmsForumController::viewTopic()` contains a live `\Drupal::messenger()->addMessage("FRESH CONTROLLER LOADED! ...")` debug statement that outputs a visible message on every topic view. Must be removed.

**Dead code risk:** `ForumController::viewTopic()` starts with `throw new \Exception("HARD DEBUG!")` — this class is not routed to (RouteSubscriber overrides to `LmsForumController`) but if ever accidentally routed would cause HTTP 500.

---

### GET+POST `/forum/new`

**Route:** `lms_forum.topic.add`
**Controller:** `LmsForumController::addTopic`
**Authentication:** `_entity_create_access: lms_forum_topic` (authenticated + can create)
**Response:** HTML (Twig: `lms-forum-new-topic-page.html.twig` wrapping `ForumTopicForm`)

**Form Fields (from `ForumTopicForm` entity form):**

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `name` | text | Yes | Topic title (max 255) |
| `field_body` | text_long | No | Topic body |
| `field_course` | entity_reference | No | Optional course scoping |
| `field_category` | taxonomy_term | No | `forum_category` vocabulary |
| Hidden: `field_pinned`, `field_locked` | boolean | — | Hidden for non-admins in `ForumTopicForm` |

**On save:** Sets owner if new; redirects to `entity.lms_forum_topic.canonical`.

---

### POST `/forum/{lms_forum_topic}/reply`

**Route:** `lms_forum.reply.add`
**Controller:** `LmsForumController::reply`
**Authentication:** `_entity_create_access: lms_forum_reply`
**Methods:** POST
**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lms_forum_topic` | entity:lms_forum_topic | Parent topic (upcasted) |

**Form Fields (from `ForumReplyForm` entity form):**

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `field_body` | text_long | Yes | Reply body |

Hidden: `field_upvotes`, `field_best_answer`, `field_topic`, `field_parent`.

**On save:**
- Updates `topic.field_replies_count` (increment) and `topic.field_last_reply_date`.
- If `lms_notification` is installed: calls `lms_notification.manager::dispatch('forum_reply', ...)`.
- Redirects to topic canonical.
- AJAX modal support: `ajaxSaveCallback` closes modal and updates reply in DOM.

---

### GET `/forum/{lms_forum_topic}/reply/{lms_forum_reply}/best`

**Route:** `lms_forum.reply.best_answer`
**Controller:** `LmsForumController::markBestAnswer`
**Authentication:** `_permission: access content` (route layer — public)
**Methods:** GET
**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lms_forum_topic` | entity:lms_forum_topic | Topic (upcasted) |
| `lms_forum_reply` | entity:lms_forum_reply | Reply to mark (upcasted) |

**Response:** Redirect to topic canonical.

**Logic (as-built):** Sets `field_best_answer = TRUE` on reply and `field_resolved = TRUE` on topic **only if** current user is topic owner or has `administer lms_course` permission. No error/403 if unauthorized — silently does nothing and redirects.

**Known gap:** Route access is `_permission: access content` (anyone can hit the URL). Authorization is enforced only in-controller with silent fail. Should be `_entity_access: lms_forum_topic.update` or custom access check.

---

### GET+POST `/forum/reply/{lms_forum_reply}/edit`

**Route:** `lms_forum.reply.edit`
**Controller:** `LmsForumController::editReply`
**Authentication:** `_entity_access: lms_forum_reply.update` (owner or admin)
**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lms_forum_reply` | entity:lms_forum_reply | Reply entity (upcasted) |

**Response:** HTML — `ForumReplyForm` entity form (in-page or AJAX modal).

---

### GET+POST `/forum/reply/{lms_forum_reply}/delete`

**Route:** `entity.lms_forum_reply.delete_form`
**Handler:** `_entity_form: lms_forum_reply.delete` (`ForumReplyDeleteForm`)
**Authentication:** `_entity_access: lms_forum_reply.delete`
**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lms_forum_reply` | entity:lms_forum_reply | Reply entity (upcasted) |

**Response:** HTML confirm form. AJAX `ajaxDeleteCallback` removes `#forum-reply-{id}` from DOM and closes modal.

---

## Notification Endpoints

### GET `/api/lms/notifications/unread-count`

**Route:** `lms_notification.api_unread_count`
**Controller:** `NotificationController::apiUnreadCount`
**Authentication:** `_access: TRUE` (route layer — controller returns `{count: 0}` for anonymous)
**Methods:** GET

**Response 200:**
```json
{ "count": 3 }
```

**Logic:** Counts `lms_notification` entities for current user where `field_read = FALSE` AND `field_channel` includes `in_app`. Returns `{count: 0}` for anonymous users (no session).

**Usage:** Polled by the `lms_notification_bell` block JS to update the unread badge in the header. No WebSocket/SSE — polling only. Polling interval is not defined in the module (frontend-side concern).

**Caching:** No caching (varies by user session).

---

## Note Endpoints (lms_note)

These are in-lesson AJAX JSON endpoints. Module: `lms_note`.

### POST `/api/lms/note/save`

**Route:** `lms_note.save`
**Controller:** `NoteController::save`
**Authentication:** `_user_is_logged_in: TRUE`

**Request Body (JSON):**
```json
{
  "lesson_id": 42,
  "course_id": 7,
  "note_text": "Important concept here",
  "video_timestamp": 145
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `lesson_id` | integer | Yes | `lms_lesson` entity ID |
| `course_id` | integer | Yes | `lms_course` entity ID (added by `lms_note_update_10001`) |
| `note_text` | string | Yes | Note body |
| `video_timestamp` | integer | No | Seconds into video; default 0 |

**Response 200:**
```json
{ "status": "created", "id": 15 }
```
or
```json
{ "status": "updated", "id": 15 }
```

**Logic:** Upserts `lms_note` entity for `uid + lesson_id`; invalidates cache tag `lms_note:{lesson_id}:{uid}`.

---

### POST `/api/lms/note/delete`

**Route:** `lms_note.delete`
**Controller:** `NoteController::delete`
**Authentication:** `_user_is_logged_in: TRUE`

**Request Body (JSON):**
```json
{ "lesson_id": 42 }
```

**Response 200:**
```json
{ "status": "deleted" }
```
or
```json
{ "status": "not_found" }
```

**Logic:** Deletes all `lms_note` entities for `uid + lesson_id`; invalidates cache tag.

---

### GET `/api/lms/note/get`

**Route:** `lms_note.get`
**Controller:** `NoteController::getNote`
**Authentication:** `_user_is_logged_in: TRUE`

**Query Parameters:**

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| `lesson_id` | integer | Yes | `lms_lesson` entity ID |

**Response 200:**
```json
{ "status": "found", "note_text": "Important concept here" }
```
or
```json
{ "status": "not_found", "note_text": null }
```

---

## Notification Delivery System

`NotificationManager` handles dispatch. Authentication channels:

| Channel | Status | Notes |
|---------|--------|-------|
| `in_app` | REAL — fully implemented | Creates `lms_notification` entity; `NotificationBellBlock` displays unread count |
| `email` | STUB | `dispatch()` logs info message only: `TODO: Implement Drupal queue creation for mail` |
| `push` | STUB | `dispatch()` logs info message only: FCM foundation comment only |

**Notification types (field_type allowed values):**
`enrollment`, `quiz_result`, `certificate`, `payment`, `forum_reply`, `membership_expiry`, `new_lesson`, `announcement`, `reminder`, `system`

**Per-user preferences:** `NotificationPreferencesForm` at `/manage/settings/notifications` — manages 9 event types with In-App + Email checkboxes per type. Stored via `user.data` service under key `pref_{type}`.

**Event subscribers (as-built):**
- `NotificationEventSubscriber::onQuizPassed()` → subscribes to `QuizPassedEvent` (conditional on `lms_quiz` module) → dispatches `quiz_result` notification.
- `ForumReplyForm::save()` → calls `lms_notification.manager::dispatch('forum_reply')` if module installed.
- `lms_order.module hook_entity_update` → creates `lms_notification` entity on order completion.

**Cron:** `lms_notification.module hook_cron` — deletes read notifications older than 90 days, 100 per cron run.

**Broadcast:** Admin form at `/admin/lms/notifications/broadcast` — creates announcement notifications for all active users in batches of 50 via Drupal batch API.

**Drush command:** `lms_notification:remind-inactive` (`lms-remind`) — sends `reminder` notification to users who last accessed 7–14 days ago (up to 50 users).

---

## Rating Routes (Admin Only)

`lms_rating` exposes only admin-facing workspace routes. There is no student-facing route to submit a rating via HTTP — the `RatingBlock` plugin (id: `lms_rating_block`) embeds `RatingForm` inside course pages, but the form submit uses standard Drupal Form API (not a JSON endpoint).

### GET `/workspace/ratings`

**Route:** `lms_rating.admin_list`
**Controller:** `RatingManagerController::list`
**Authentication:** `_permission: administer lms_course`
**Response:** HTML (theme: `lms_admin_ratings`)

**Note:** `#theme: lms_admin_ratings` is NOT registered in `lms_rating.module` (no `.module` file exists in `lms_rating`). The theme registration must exist in `lms_theme` or another module. If not found, the block renders empty.

**Logic:** Loads all `lms_rating` entities DESC with pager(15); builds items array with student name, avatar (with `ui-avatars.com` fallback), course name, score (1–5), review excerpt (50 chars), approved flag, featured flag.

---

### GET `/workspace/ratings/{lms_rating}/{action}`

**Route:** `lms_rating.admin_action`
**Controller:** `RatingManagerController::executeAction`
**Authentication:** `_permission: administer lms_course`
**Methods:** GET
**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lms_rating` | entity:lms_rating | Rating entity (upcasted) |
| `action` | string | One of: `approve`, `feature`, `delete` |

**Logic:**
- `approve` → toggles `field_approved` boolean.
- `feature` → toggles `field_is_featured`; auto-sets `field_approved = TRUE` when featuring.
- `delete` → deletes entity.
- Redirects to `lms_rating.admin_list` after action.

**Known gap:** No route-layer constraint on `{action}` — any string is accepted; controller must validate.

**Known bug:** `RatingController` class (`src/Controller/RatingController.php`) has `toggleApprove()` and `toggleFeatured()` methods that reference routes `lms_rating.toggle_approve`, `lms_rating.toggle_featured`, and `lms_rating.collection` — **none of which exist in `lms_rating.routing.yml`**. `RatingListBuilder::getOperations()` also references these missing routes. Rendering the entity list builder will throw `RouteNotFoundException`. This is confirmed dead/broken code.

---

## Error Responses

All JSON endpoints in this service use the following shape:

| Status | Body | When |
|--------|------|------|
| 200 | `{"status": "..."}` | Success |
| 403 | Drupal 403 page | Insufficient permission |
| 404 | Drupal 404 page | Entity not found |

JSON endpoints do not currently return a structured error envelope matching the template standard — they return plain `JsonResponse` objects.

---

## API Route Summary

| Method | Path | Route Name | Auth | Response | Status |
|--------|------|-----------|------|----------|--------|
| GET | `/forum` | `lms_forum.index` | public | HTML | as-built |
| GET | `/forum/{lms_forum_topic}` | `entity.lms_forum_topic.canonical` (overridden) | public | HTML | as-built (has debug artifact) |
| GET+POST | `/forum/new` | `lms_forum.topic.add` | entity_create | HTML | as-built |
| POST | `/forum/{topic}/reply` | `lms_forum.reply.add` | entity_create | HTML | as-built |
| GET | `/forum/{topic}/reply/{reply}/best` | `lms_forum.reply.best_answer` | public (auth in-controller) | redirect | as-built (access gap) |
| GET+POST | `/forum/reply/{reply}/edit` | `lms_forum.reply.edit` | entity_access.update | HTML | as-built |
| GET+POST | `/forum/reply/{reply}/delete` | `entity.lms_forum_reply.delete_form` | entity_access.delete | HTML | as-built |
| GET | `/notifications` | `lms_notification.list` | logged_in | HTML | as-built |
| GET+POST | `/manage/settings/notifications` | `lms_notification.preferences` | logged_in | HTML | as-built |
| GET+POST | `/admin/lms/notifications/broadcast` | `lms_notification.broadcast` | administer lms_course | HTML | as-built |
| GET | `/api/lms/notifications/unread-count` | `lms_notification.api_unread_count` | public (returns 0 for anon) | JSON | as-built |
| POST | `/api/lms/note/save` | `lms_note.save` | logged_in | JSON | as-built |
| POST | `/api/lms/note/delete` | `lms_note.delete` | logged_in | JSON | as-built |
| GET | `/api/lms/note/get` | `lms_note.get` | logged_in | JSON | as-built |
| GET | `/workspace/ratings` | `lms_rating.admin_list` | administer lms_course | HTML | as-built (theme gap) |
| GET | `/workspace/ratings/{rating}/{action}` | `lms_rating.admin_action` | administer lms_course | redirect | as-built |

---

## Known Issues

| Ref | Severity | Description |
|-----|----------|-------------|
| Forum debug message | High | `LmsForumController::viewTopic()` has live `messenger()->addMessage("FRESH CONTROLLER LOADED!")` — outputs visible message on every topic view. |
| ForumController dead code | High | `ForumController::viewTopic()` throws `new \Exception("HARD DEBUG!")` — would cause HTTP 500 if ever routed. Not currently routed but dangerous. |
| `lms_rating` — 3 missing routes | High | `lms_rating.collection`, `lms_rating.toggle_approve`, `lms_rating.toggle_featured` referenced by `RatingListBuilder` and `RatingController` but not defined in routing.yml. Will throw `RouteNotFoundException`. |
| `lms_admin_ratings` theme not registered | Medium | `RatingManagerController::list()` uses `#theme: lms_admin_ratings` but no `hook_theme()` in `lms_rating` (no `.module` file). Depends on theme or another module registering it. |
| `best_answer` route — insufficient access | Medium | `_permission: access content` lets any anonymous user hit the URL; only silently blocked in-controller. |
| Email/push notifications stub | Medium | `NotificationManager::dispatch()` logs only for `email` and `push` channels — no actual delivery. `BroadcastForm` will silently fail for email delivery. |
| No student rating submit route | Low | `RatingForm` is embedded via block inside course pages (Drupal Form API); no dedicated `POST /courses/{id}/rate` JSON endpoint exists. |
| No rate limit on notification poll | Low | `/api/lms/notifications/unread-count` has no rate limiting; high-frequency frontend polling possible. |
