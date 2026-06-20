---
entity: lms_forum_topic + lms_forum_reply + lms_rating + lms_notification
mode: as-built
owner_module: lms_forum (topics/replies) / lms_rating (rating) / lms_notification (notification)
content_kind: editorial (forum_topic/reply, rating) / transactional (notification)
editor_owned: true (forum_topic, forum_reply, rating) / false (notification)
revisionable: false
translatable: false
moderation_workflow: null
---

# C04 — Entities: Communication (lms_forum_topic, lms_forum_reply, lms_rating, lms_notification)

**Owner modules:** [`lms_forum`](../C05-modules/lms_forum.md) · [`lms_rating`](../C05-modules/lms_rating.md) · [`lms_notification`](../C05-modules/lms_notification.md)
**Mode:** as-built
**Description:** Four entities covering learner-to-learner discussion (forum topics and threaded replies), course quality feedback (ratings and reviews), and system-generated in-app/email/push notifications.

---

## Entity 1: lms_forum_topic (Forum Topic)

**Storage:** `lms_forum_topic` (single flat table, non-translatable)
**Description:** A discussion thread created by an authenticated user; optionally scoped to a course.
**Owner module:** [`lms_forum`](../C05-modules/lms_forum.md)
**Content kind:** editorial
**Editor owned:** true
**Management UI:** `/forum` (public listing); admin via Views/entity list
**Create / Edit / Delete forms:** `/forum/new` (ForumTopicForm), entity edit-form via AdminHtmlRouteProvider, `/forum/reply/{id}/delete` (ContentEntityDeleteForm)
**Display modes:** default (EntityViewBuilder)
**Revision / moderation:** not revisionable; N/A
**Translation:** N/A (non-translatable)

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer (serial) | 1 | Yes | auto | No | — | Base field | Auto-increment PK |
| 2 | `uuid` | UUID | uuid | 1 | — | auto | No | — | Base field | |
| 3 | `uid` | Author | entity_reference → user | 1 | — | — | No | — | Base field | Topic creator (EntityOwnerTrait) |
| 4 | `name` | Title | string (255) | 1 | Yes | — | No | — | Base field | Entity label; displayed as topic heading |
| 5 | `field_body` | Body | text_long | 1 | No | — | No | — | Base field | Rich-text topic content |
| 6 | `field_course` | Course | entity_reference → lms_course | 1 | No | — | No | — | Base field | Optional scope; null = global forum |
| 7 | `field_replies_count` | Reply Count | integer | 1 | No | 0 | No | — | Base field | Denormalized counter; updated by ForumReplyForm on save |
| 8 | `field_pinned` | Pinned | boolean | 1 | No | FALSE | No | — | Base field | Admin-only; pinned topics sort first |
| 9 | `field_locked` | Locked | boolean | 1 | No | FALSE | No | — | Base field | Admin-only; disables reply form when TRUE |
| 10 | `field_category` | Category | entity_reference → taxonomy_term | 1 | No | — | No | bundle: forum_category | Base field | Forum category vocabulary |
| 11 | `field_last_reply_date` | Last Reply Date | timestamp | 1 | No | 0 | No | — | Base field | Updated by ForumReplyForm on new reply |
| 12 | `field_views` | Views | integer | 1 | No | 0 | No | — | Base field | Incremented by `LmsForumController::viewTopic()` on each page load |
| 13 | `field_resolved` | Resolved | boolean | 1 | No | FALSE | No | — | Base field | Set TRUE when a reply is marked best answer |
| 14 | `created` | Created | created (timestamp) | 1 | No | auto | No | — | Base field | Unix timestamp |
| 15 | `changed` | Changed | changed (timestamp) | 1 | No | auto | No | — | Base field | Auto-updated |

#### DB table: lms_forum_topic (as-built, AUTO_INCREMENT ≈ 39, ~38 rows)

```
id                      int NOT NULL AUTO_INCREMENT  PK
uuid                    varchar(128) UNIQUE
uid                     int unsigned   (FK → users_field_data.uid)
name                    varchar(255)
field_body__value       longtext
field_body__format      varchar(255)
field_course            int unsigned   (FK → lms_course.id, nullable)
field_replies_count     int
field_pinned            tinyint
field_locked            tinyint
created                 int
changed                 int
field_category          int unsigned   (FK → taxonomy_term_data.tid)
field_last_reply_date   int
field_views             int
field_resolved          tinyint
```

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | PK lookup |
| uuid | `uuid` | UNIQUE | UUID deduplication |
| uid | `uid` | B-Tree | Load user's topics |
| field_course | `field_course` | B-Tree | Course-scoped forum listing |
| field_category | `field_category` | B-Tree | Category filter |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `user` | M:1 | `uid` | No cascade |
| `lms_course` | M:1 | `field_course` | No cascade; nullable |
| `taxonomy_term` (forum_category) | M:1 | `field_category` | No cascade |
| `lms_forum_reply` | 1:M | `lms_forum_reply.field_topic` | No DB cascade; app deletes replies when topic deleted |

### Editorial usability

| Surface | Requirement |
|---|---|
| Management UI | `/forum` (public list + topic cards); topic view at `/forum/{id}` |
| Editor role / permission | `access content` (view); authenticated user (create/edit own); `administer lms_course` (admin all) |
| Form display | `ForumTopicForm` (ContentEntityForm); hides counter/admin fields from non-admins; hides text format selector |
| View displays / view modes | `default` (EntityViewBuilder); public view overridden by `LmsForumController::viewTopic()` via `RouteSubscriber` |
| Revision / moderation | Not revisionable |
| Translation | N/A |
| Preview | N/A |
| Cache invalidation | `entity:lms_forum_topic:{id}` cache tag |
| Smoke verification | Create topic as authenticated user → appears in `/forum` → view topic increments `field_views` |

### Known issue — debug output in production

`LmsForumController::viewTopic()` contains a live `\Drupal::messenger()->addMessage("FRESH CONTROLLER LOADED! ...")` debug statement. This produces a visible debug message on every topic page view in production. Should be removed.

The legacy `ForumController` class (`src/Controller/ForumController.php`) starts its `viewTopic()` with `throw new \Exception("HARD DEBUG!")` — it is not reachable via any route (overridden by `RouteSubscriber`) but would cause HTTP 500 if ever routed to. It is dead code and should be removed.

### Business rules

- `field_pinned = TRUE` → topics sort before non-pinned entries in `LmsForumController::index()`; only admin can set.
- `field_locked = TRUE` → reply form is not rendered in `lms-forum-topic.html.twig`.
- `field_resolved = TRUE` → automatically set when a reply is marked best answer via `LmsForumController::markBestAnswer()`.
- `field_replies_count` and `field_last_reply_date` are denormalized: updated synchronously in `ForumReplyForm::save()` — no queue, no cron reconcile.
- View count (`field_views`) is incremented synchronously on every page load of the topic; no deduplication per session.
- Access: `ForumTopicAccessControlHandler` — view requires `access content`; create/update/delete requires authenticated + owner; admin always allowed.

---

## Entity 2: lms_forum_reply (Forum Reply)

**Storage:** `lms_forum_reply` (single flat table, non-translatable)
**Description:** A reply (potentially threaded) within a forum topic; supports upvotes and best-answer marking.
**Owner module:** [`lms_forum`](../C05-modules/lms_forum.md)
**Content kind:** editorial
**Editor owned:** true
**Management UI:** Inline within topic view at `/forum/{topic_id}`
**Create / Edit / Delete forms:** `/forum/{topic}/reply` (ForumReplyForm), `/forum/reply/{id}/delete` (ForumReplyDeleteForm), `/forum/reply/{id}/edit` (ForumReplyForm AJAX)
**Display modes:** default (EntityViewBuilder)
**Revision / moderation:** not revisionable; N/A
**Translation:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer (serial) | 1 | Yes | auto | No | — | Base field | Auto-increment PK |
| 2 | `uuid` | UUID | uuid | 1 | — | auto | No | — | Base field | |
| 3 | `uid` | Author | entity_reference → user | 1 | — | — | No | — | Base field | Reply author (EntityOwnerTrait) |
| 4 | `field_topic` | Topic | entity_reference → lms_forum_topic | 1 | Yes | — | No | — | Base field | Parent topic |
| 5 | `field_body` | Body | text_long | 1 | Yes | — | No | — | Base field | Reply content (rich text) |
| 6 | `field_parent` | Parent Reply | entity_reference → lms_forum_reply | 1 | No | — | No | — | Base field | Self-reference for threaded replies; nullable (top-level reply) |
| 7 | `field_best_answer` | Best Answer | boolean | 1 | No | FALSE | No | — | Base field | Set by topic owner or admin via `markBestAnswer()` |
| 8 | `field_upvotes` | Upvotes | integer | 1 | No | 0 | No | — | Base field | Denormalized count; no separate upvote entity |
| 9 | `created` | Created | created (timestamp) | 1 | No | auto | No | — | Base field | Unix timestamp |

#### DB table: lms_forum_reply (as-built, AUTO_INCREMENT ≈ 164, ~163 rows)

```
id                      int NOT NULL AUTO_INCREMENT  PK
uuid                    varchar(128) UNIQUE
uid                     int unsigned   (FK → users_field_data.uid)
field_topic             int unsigned   (FK → lms_forum_topic.id)
field_body__value       longtext
field_body__format      varchar(255)
field_parent            int unsigned   (FK → lms_forum_reply.id, nullable — self-reference)
field_best_answer       tinyint
created                 int
field_upvotes           int
```

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | PK lookup |
| uuid | `uuid` | UNIQUE | UUID deduplication |
| uid | `uid` | B-Tree | User's replies |
| field_topic | `field_topic` | B-Tree | Load replies for a topic |
| field_parent | `field_parent` | B-Tree | Load child replies (threading) |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `user` | M:1 | `uid` | No cascade |
| `lms_forum_topic` | M:1 | `field_topic` | No DB cascade; topic deletion should delete replies (app responsibility) |
| `lms_forum_reply` | M:1 (self) | `field_parent` | No cascade; orphaned child replies may remain if parent deleted |

### Editorial usability

| Surface | Requirement |
|---|---|
| Management UI | Rendered inline in topic view; no standalone admin list |
| Editor role / permission | Authenticated (create); authenticated + owner (update/delete); `administer lms_course` (all) |
| Form display | `ForumReplyForm` (ContentEntityForm); hides upvotes, best_answer, topic, parent from user; AJAX-capable (modal edit) |
| View displays / view modes | Rendered inline in `lms-forum-topic.html.twig` by controller |
| Revision / moderation | Not revisionable |
| Translation | N/A |
| Cache invalidation | `entity:lms_forum_reply:{id}`, `entity:lms_forum_topic:{topic_id}` on save/delete |
| Smoke verification | Post reply on topic → `field_replies_count` increments on parent topic → marking best answer sets `field_resolved=TRUE` on topic |

### Business rules

- On save (`ForumReplyForm::save`): parent topic's `field_replies_count` and `field_last_reply_date` are updated synchronously.
- On save: if `lms_notification` module is installed, `lms_notification.manager::dispatch('forum_reply')` is called to notify the topic author.
- `field_best_answer = TRUE` can only be set by the topic author or an admin via `LmsForumController::markBestAnswer()`; access checked inline.
- `field_parent` enables one level of threading in the UI; no maximum nesting depth enforced in code (unlike the draft spec's "3 levels" — that is not implemented).
- `field_upvotes` has no UI action wired to increment it in the current codebase (field exists, increment endpoint not present as-built).

---

## Entity 3: lms_rating (Rating)

**Storage:** `lms_rating` (single flat table, non-translatable)
**Description:** A star rating (1–5) and optional text review submitted by an enrolled student for a course.
**Owner module:** [`lms_rating`](../C05-modules/lms_rating.md)
**Content kind:** editorial
**Editor owned:** true
**Management UI:** `/workspace/ratings` (admin list via `RatingManagerController`)
**Create / Edit / Delete forms:** `RatingForm` (FormBase, not ContentEntityForm); no standard edit-form; delete via ContentEntityDeleteForm
**Display modes:** default (EntityViewBuilder)
**Revision / moderation:** not revisionable; N/A
**Translation:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer (serial) | 1 | Yes | auto | No | — | Base field | Auto-increment PK |
| 2 | `uuid` | UUID | uuid | 1 | — | auto | No | — | Base field | |
| 3 | `uid` | Author | entity_reference → user | 1 | — | — | No | — | Base field | Reviewer (EntityOwnerTrait) |
| 4 | `field_course` | Course | entity_reference → lms_course | 1 | Yes | — | No | — | Base field | Course being rated |
| 5 | `field_score` | Score | integer | 1 | No | 5 | No | — | Base field | 1–5 star rating |
| 6 | `field_review` | Review | text_long | 1 | No | — | No | — | Base field | Optional text review |
| 7 | `field_approved` | Approved | boolean | 1 | No | TRUE | No | — | Base field | Default TRUE (auto-approved); admin can toggle; new submissions via `RatingForm` set to FALSE |
| 8 | `field_is_featured` | Featured | boolean | 1 | No | FALSE | No | — | Base field | Admin-only highlight; added by `lms_rating_update_11001` |
| 9 | `created` | Created | created (timestamp) | 1 | No | auto | No | — | Base field | Unix timestamp |

#### DB table: lms_rating (as-built, AUTO_INCREMENT ≈ 160, ~159 rows)

```
id                      int NOT NULL AUTO_INCREMENT  PK
uuid                    varchar(128) UNIQUE
uid                     int unsigned   (FK → users_field_data.uid)
field_course            int unsigned   (FK → lms_course.id)
field_score             int            1–5
field_review__value     longtext
field_review__format    varchar(255)
field_approved          tinyint
created                 int
field_is_featured       tinyint
```

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | PK lookup |
| uuid | `uuid` | UNIQUE | UUID deduplication |
| uid | `uid` | B-Tree | User's ratings |
| field_course | `field_course` | B-Tree | Course ratings list |
| field_approved | `field_approved` | B-Tree | Filter approved ratings |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `user` | M:1 | `uid` | No cascade |
| `lms_course` | M:1 | `field_course` | No cascade; `lms_course_field_data.field_rating` is a denormalized average |

### Known issues — missing routes

`lms_rating` has three missing route definitions:

| Missing route | Referenced by | Effect |
|---|---|---|
| `lms_rating.collection` | `RatingController::toggleApprove()`, `RatingController::toggleFeatured()`, `links.menu.yml` | `RouteNotFoundException` at runtime when redirects fire |
| `lms_rating.toggle_approve` | `RatingListBuilder::getOperations()` | `RouteNotFoundException` when entity list operations rendered |
| `lms_rating.toggle_featured` | `RatingListBuilder::getOperations()` | `RouteNotFoundException` when entity list operations rendered |

Additionally: `lms_rating` has no `.module` file, so no `hook_theme()`. The `lms_admin_ratings` theme used by `RatingManagerController::list()` must be defined in the active theme or another module; if missing, the admin list renders empty.

### Editorial usability

| Surface | Requirement |
|---|---|
| Management UI | `/workspace/ratings` — `RatingManagerController::list()` (pager 15); approve/feature/delete via `/workspace/ratings/{id}/{action}` |
| Editor role / permission | `administer lms_course` (admin list); authenticated user (submit via `RatingForm`) |
| Form display | `RatingForm` (FormBase): `field_score` select (1–5), `field_review` textarea; checks for existing rating (1 per user per course); submits with `field_approved=FALSE` |
| View displays / view modes | `default` (EntityViewBuilder); rendered inline via `RatingBlock` plugin |
| Revision / moderation | Not revisionable |
| Translation | N/A |
| Cache invalidation | `entity:lms_rating:{id}`, `entity:lms_course:{id}` (course average updated separately) |
| Smoke verification | Submit rating as student → `field_approved=FALSE` → admin approve → rating visible on course page |

### Business rules

- `RatingForm` prevents duplicate ratings: checks for existing `lms_rating` entity for `(uid, field_course)` pair before creating a new one.
- New submissions via `RatingForm` set `field_approved=FALSE` (pending moderation). The default field value is `TRUE` but the form override sets `FALSE`.
- `lms_course_field_data.field_rating` is a denormalized `decimal(3,1)` average updated independently — see module facts. No automatic recalculation trigger is wired in the current code.
- `RatingManagerController::executeAction()` supports: `approve` (toggle `field_approved`), `feature` (toggle `field_is_featured`, auto-approves when featuring), `delete`.

---

## Entity 4: lms_notification (Notification)

**Storage:** `lms_notification` (base table) + `lms_notification__field_channel` (multi-value junction)
**Description:** An in-app/email/push notification record for a specific user. In-app channel is fully functional; email and push are stubs (log-only).
**Owner module:** [`lms_notification`](../C05-modules/lms_notification.md)
**Content kind:** transactional
**Editor owned:** false (created programmatically by `NotificationManager`; broadcast via `BroadcastForm`)
**Management UI:** `/notifications` (user list); `/admin/lms/notifications/broadcast` (admin broadcast)
**Create / Edit / Delete forms:** N/A (no entity create/edit form); auto-deleted by cron after 90 days if read
**Display modes:** N/A (rendered via custom templates, not entity view builder)
**Revision / moderation:** not revisionable; N/A
**Translation:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer (serial) | 1 | Yes | auto | No | — | Base field | Auto-increment PK |
| 2 | `uuid` | UUID | uuid | 1 | — | auto | No | — | Base field | |
| 3 | `uid` | Recipient | entity_reference → user | 1 | — | — | No | — | Base field | Notification recipient (EntityOwnerTrait) |
| 4 | `field_type` | Type | list_string | 1 | No | — | No | enrollment, quiz_result, certificate, payment, forum_reply, membership_expiry, new_lesson, announcement, reminder, system | Base field | Notification event category |
| 5 | `field_channel` | Channel | list_string | UNLIMITED | No | `['in_app']` | No | in_app, email, push | Junction table: `lms_notification__field_channel` | Multi-value; each row = one delivery channel |
| 6 | `field_title` | Title | string (255) | 1 | Yes | — | No | — | Base field | Notification heading |
| 7 | `field_message` | Message | text_long | 1 | No | — | No | — | Base field | Detail text |
| 8 | `field_link` | Link | string (512) | 1 | No | — | No | — | Base field | Target URL for the notification |
| 9 | `field_read` | Read | boolean | 1 | No | FALSE | No | — | Base field | Marked TRUE when user visits `/notifications` (bulk mark-read on page load) |
| 10 | `field_reference_type` | Reference Type | string (64) | 1 | No | — | No | — | Base field | Polymorphic: entity type ID of the related entity (e.g. `lms_course`, `lms_quiz_result`); added by `lms_notification_update_10001` |
| 11 | `field_reference_id` | Reference ID | integer | 1 | No | — | No | — | Base field | Polymorphic: entity ID of the related entity; added by `lms_notification_update_10001` |
| 12 | `created` | Created | created (timestamp) | 1 | No | auto | No | — | Base field | Unix timestamp |

#### DB tables (as-built)

**lms_notification** (AUTO_INCREMENT ≈ 163, ~162 rows):

```
id                      int NOT NULL AUTO_INCREMENT  PK
uuid                    varchar(128) UNIQUE
uid                     int unsigned   (FK → users_field_data.uid, recipient)
field_type              varchar(255)
field_title             varchar(255)
field_message__value    longtext
field_message__format   varchar(255)
field_link              varchar(512)
field_read              tinyint        default 0
created                 int
field_reference_type    varchar(64)    entity type string (no FK constraint)
field_reference_id      int            entity id (no FK constraint)
```

**lms_notification__field_channel** (junction, data present):

```
bundle       varchar(128)
deleted      tinyint
entity_id    int unsigned   (FK → lms_notification.id)
revision_id  int unsigned
langcode     varchar(12)
delta        int unsigned
field_channel_value varchar(255)   e.g. in_app / email / push
PK: (entity_id, deleted, delta, langcode)
```

### Polymorphic reference pattern

`field_reference_type` + `field_reference_id` implement a raw polymorphic reference: any entity type can be referenced by storing its type string and integer ID. **There is no FK constraint and no entity_reference validation.** `NotificationManager::createInAppNotification()` accepts these as optional parameters. The referenced entity may be deleted without notification rows being cleaned up. Callers are responsible for passing valid type+id pairs.

### Channel implementation status

| Channel | Status | Notes |
|---|---|---|
| `in_app` | Fully implemented | `NotificationManager::createInAppNotification()` creates entity; `NotificationController::listNotifications()` loads and marks read; `NotificationBellBlock` shows unread count and top 5 |
| `email` | Stub | `NotificationManager::dispatch()` logs info only; no mail queue, no Drupal mail call; TODO comment in source |
| `push` | Stub | `NotificationManager::dispatch()` logs info only; FCM foundation mentioned in comment only; not implemented |

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | PK lookup |
| uuid | `uuid` | UNIQUE | UUID deduplication |
| uid | `uid` | B-Tree | User's notifications |
| field_read | `field_read` | B-Tree | Unread count query |
| field_type | `field_type` | B-Tree | Filter by notification type |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `user` | M:1 | `uid` | No cascade |
| any entity (polymorphic) | M:1 (soft) | `field_reference_type` + `field_reference_id` | No FK constraint; no cascade |

### Lifecycle

```
Event occurs (quiz pass, forum reply, enrollment, etc.)
        ↓
NotificationManager::dispatch(type, recipient, title, message, link, channels, reference)
  - Checks user preference (user.data: pref_{type})
  - in_app → createInAppNotification() → saves lms_notification entity
  - email  → info log only (stub)
  - push   → info log only (stub)
        ↓
NotificationBellBlock: shows unread count (field_read=FALSE, channel=in_app)
        ↓
User visits /notifications → listNotifications() marks all as field_read=TRUE
        ↓
hook_cron(): deletes entities where field_read=TRUE AND created < (now - 90d), batch 100/run
```

### Business rules

- Unread count for bell icon: `field_read=FALSE` AND `field_channel=in_app` for current user.
- Cron cleanup: removes read notifications older than 90 days; 100 entities per cron run.
- User preferences for 9 event types stored in `user.data` keyed by `pref_{type}`. Preferences read by `NotificationManager::dispatch()` before creating notifications.
- `field_type = 'system'` is in the allowed values list but there is no confirmed code path that dispatches this type as-built.
- `NotificationEventSubscriber` subscribes to `QuizPassedEvent` (from `lms_quiz`) conditionally — only if class exists.
- `BroadcastForm` creates `announcement` notifications for all active users in batches of 50 via Drupal Batch API.

### Editorial usability

| Surface | Requirement |
|---|---|
| Management UI | `/notifications` (user; paged 15); `/admin/lms/notifications/broadcast` (admin broadcast) |
| Editor role / permission | Authenticated (own notifications); `administer lms_course` (broadcast) |
| Form display | No create form; `BroadcastForm` (admin only): title, message, link; `NotificationPreferencesForm` at `/manage/settings/notifications` |
| View displays / view modes | `lms_notifications` theme (list) + `lms_notification_bell` theme (bell block) |
| Revision / moderation | Not revisionable |
| Translation | N/A |
| Cache invalidation | `lms_notification_list` cache tag; bell block cached per user |
| Smoke verification | Trigger a quiz pass → bell icon shows +1 unread → visit `/notifications` → count resets to 0 |

---

## Cross-references

- Owner modules: [`../C05-modules/lms_forum.md`](../C05-modules/lms_forum.md) · [`../C05-modules/lms_rating.md`](../C05-modules/lms_rating.md) · [`../C05-modules/lms_notification.md`](../C05-modules/lms_notification.md)
- Quiz event that triggers notification: [`../C04-entities/02-assessment-entities.md`](02-assessment-entities.md) (lms_quiz_result / QuizPassedEvent)
- Course entity referencing ratings: [`../C04-entities/01-content-entities.md`](01-content-entities.md) (lms_course.field_rating denormalized average)
- Entity-module map: [`../C03-entity-module-map.md`](../C03-entity-module-map.md)
