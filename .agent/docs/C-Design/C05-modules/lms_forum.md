---
module: lms_forum
service: forum
route-groups: [Public, App]
status: DONE
---

# C05 — Module: LMS Forum

**Directory:** `web/modules/custom/lms_forum/`
**Responsibility:** Provides threaded course discussion forums with topics, replies, best-answer marking, and AJAX modal edit/delete — backed by two custom entities (`lms_forum_topic`, `lms_forum_reply`) and a `RouteSubscriber` that overrides the canonical topic route to a custom controller.

> **Documentation mode:** as-built — matches current code (2026-06-20).

---

## Route Bindings

| Route Group | Prefix | Controller | Paths |
|-------------|--------|------------|-------|
| Public | `/forum` | `LmsForumController` | `/forum` (index), `/forum/{lms_forum_topic}` (canonical — overridden by RouteSubscriber) |
| App | `/forum` | `LmsForumController` | `/forum/new`, `/forum/{lms_forum_topic}/reply`, `/forum/{lms_forum_topic}/reply/{lms_forum_reply}/best`, `/forum/reply/{lms_forum_reply}/edit` |
| App | `/forum` | Entity delete form | `/forum/reply/{lms_forum_reply}/delete` |

Index (`/forum`) requires `_permission: access content` (publicly visible); topic/reply write routes require entity create/update/delete access.

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `LmsForumController` | Controller | Active forum controller: index, viewTopic, addTopic, reply, markBestAnswer, editReply |
| `RouteSubscriber` | Event Subscriber (service) | Overrides `entity.lms_forum_topic.canonical` to point to `LmsForumController::viewTopic` |
| `lms_forum_topic` | Entity | Forum topic record |
| `lms_forum_reply` | Entity | Forum reply record (threaded) |
| `ForumTopicAccessControlHandler` | Access Handler | Owner-based update/delete; create = any authenticated user |
| `ForumReplyAccessControlHandler` | Access Handler | Same pattern as topic |
| `lms_notification.manager` | Cross-module call | `ForumReplyForm::save()` calls `dispatch('forum_reply')` when `lms_notification` is installed |

---

## Internal Structure

```
web/modules/custom/lms_forum/
+-- lms_forum.info.yml              # package: EverLMS; deps: lms_base, lms_course
+-- lms_forum.routing.yml           # 6 explicit routes; canonical override via RouteSubscriber
+-- lms_forum.services.yml          # lms_forum.route_subscriber (event_subscriber tag)
+-- lms_forum.module                # hook_theme()
+-- src/
|   +-- Controller/
|   |   +-- LmsForumController.php      # ACTIVE: index, viewTopic, addTopic, reply, markBestAnswer, editReply
|   |   +-- ForumController.php         # DEAD CODE — viewTopic() throws Exception; see Known Issues
|   +-- Entity/
|   |   +-- ForumTopic.php              # lms_forum_topic content entity
|   |   +-- ForumReply.php              # lms_forum_reply content entity
|   +-- Form/
|   |   +-- ForumTopicForm.php          # ContentEntityForm; hides internal fields; redirect to canonical on save
|   |   +-- ForumReplyForm.php          # ContentEntityForm; AJAX modal save; updates topic reply_count; triggers notification
|   |   +-- ForumReplyDeleteForm.php    # ContentEntityDeleteForm; AJAX modal close + DOM removal
|   +-- Access/
|   |   +-- ForumTopicAccessControlHandler.php
|   |   +-- ForumReplyAccessControlHandler.php
|   +-- Routing/
|   |   +-- RouteSubscriber.php         # Alters entity.lms_forum_topic.canonical → LmsForumController::viewTopic
+-- templates/
|   +-- lms-forum-index.html.twig           # vars: topics, new_topic_form
|   +-- lms-forum-topic.html.twig           # vars: topic, replies, reply_form, is_teaser_mode, total_replies
|   +-- lms-forum-new-topic-page.html.twig  # vars: form
|   +-- lms-forum-edit-reply-page.html.twig # vars: form
|   +-- lms-forum-topic-form.html.twig      # present in templates/; NOT registered in hook_theme — likely dead/twig override
```

---

## Entities

### `lms_forum_topic` — Forum Topic

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| id | auto | — | PK |
| uuid | auto | — | |
| uid | entity_reference (user) | — | via `EntityOwnerTrait` |
| name | string (255) | TRUE | label field |
| field_body | text_long | — | |
| field_course | entity_reference (lms_course) | — | optional course scoping |
| field_replies_count | integer | — | denormalized; default 0 |
| field_pinned | boolean | — | default FALSE |
| field_locked | boolean | — | default FALSE |
| field_category | entity_reference (taxonomy_term:forum_category) | — | |
| field_last_reply_date | timestamp | — | default 0 |
| field_views | integer | — | default 0; incremented in viewTopic() |
| field_resolved | boolean | — | default FALSE; set TRUE by markBestAnswer() |
| created | created | — | |
| changed | changed | — | |

Table: `lms_forum_topic`. Route provider: `AdminHtmlRouteProvider`. Access handler: `ForumTopicAccessControlHandler`.

### `lms_forum_reply` — Forum Reply

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| id | auto | — | PK |
| uuid | auto | — | |
| uid | entity_reference (user) | — | via `EntityOwnerTrait` |
| field_topic | entity_reference (lms_forum_topic) | TRUE | |
| field_body | text_long | TRUE | |
| field_parent | entity_reference (lms_forum_reply) | — | for threading |
| field_best_answer | boolean | — | default FALSE |
| field_upvotes | integer | — | default 0 |
| created | created | — | |

Table: `lms_forum_reply`. Access handler: `ForumReplyAccessControlHandler`.

---

## Controllers

### `LmsForumController` — ACTIVE (as-built)

Dependencies: `entity_type.manager`, `current_user`.

| Method | Route | Description |
|--------|-------|-------------|
| `index()` | `lms_forum.index` | Loads all topics DESC; builds items with reply_count, author info (first_name/last_name with fallback to displayName), avatar; also renders new topic form for authenticated users; renders `lms_forum_index`. Includes debug `addMessage("FRESH CONTROLLER LOADED!")` — see BUG-FORUM-02. |
| `viewTopic(ForumTopic)` | `entity.lms_forum_topic.canonical` (overridden) | Increments view count; loads replies for authenticated (anonymous gets teaser mode); builds replies array with mark-best, edit, delete URLs; renders `lms_forum_topic`. |
| `addTopic()` | `lms_forum.topic.add` | Creates blank `ForumTopic`; returns entity form wrapped in `lms_forum_new_topic_page` theme. |
| `reply(ForumTopic)` | `lms_forum.reply.add` | Creates blank `ForumReply` with topic set; returns entity form directly. |
| `markBestAnswer(ForumTopic, ForumReply)` | `lms_forum.reply.best_answer` | Sets `field_best_answer=TRUE` on reply; sets `field_resolved=TRUE` on topic if requester is owner or admin; redirects to topic canonical. Route requires only `access content` — any logged-in user can call this on any topic (see BUG-FORUM-03). |
| `editReply(ForumReply, Request)` | `lms_forum.reply.edit` | Returns ForumReply default entity form; returns form array directly for AJAX modal. |
| `getUserInfo($user)` (private) | — | Returns `[name, avatar]` from `field_first_name`/`field_last_name` with fallback to `getDisplayName()`. |

### `ForumController` — DEAD CODE (bug)

`src/Controller/ForumController.php` exists but is not referenced by any route. Its `viewTopic()` method begins with:
```php
throw new \Exception("HARD DEBUG! ...");
```
All code after the throw is unreachable. The class also contains debug `addMessage` calls. This file should be deleted. If a routing misconfiguration ever routes to it, it will return an HTTP 500.

---

## Forms

| Class | Description |
|-------|-------------|
| `ForumTopicForm` | Extends `ContentEntityForm`. Hides `field_replies_count`, `field_last_reply_date`, `field_views`, `field_resolved`; hides `field_pinned`/`field_locked` for non-admins; hides text format selector. On save: sets owner if new; redirects to `entity.lms_forum_topic.canonical`. |
| `ForumReplyForm` | Extends `ContentEntityForm`. Hides `field_upvotes`, `field_best_answer`, `field_topic`, `field_parent`. On save: increments topic `field_replies_count` and `field_last_reply_date`; calls `lms_notification.manager::dispatch('forum_reply')` if `lms_notification` module is installed. AJAX `ajaxSaveCallback` closes modal and updates reply body in DOM via `HtmlCommand`. |
| `ForumReplyDeleteForm` | Extends `ContentEntityDeleteForm`. AJAX `ajaxDeleteCallback`: closes modal, removes `#forum-reply-{id}` from DOM. Cancel/redirect goes back to topic. |

---

## Routing Service

### `RouteSubscriber`

Subscribed to `RoutingEvents::ALTER`. Alters route `entity.lms_forum_topic.canonical` (auto-generated by `AdminHtmlRouteProvider` from `links.canonical: /forum/{lms_forum_topic}`) to use `LmsForumController::viewTopic` as the controller instead of the entity's default view builder.

---

## Hook Implementations

| Hook | File | Description |
|------|------|-------------|
| `hook_theme()` | `lms_forum.module` | Registers `lms_forum_index`, `lms_forum_topic`, `lms_forum_new_topic_page`, `lms_forum_edit_reply_page` with respective variables and templates. |

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_base` | Internal | Shared base traits |
| `lms_course` | Internal | `field_course` scoping on topics |
| `lms_notification` | Internal (optional runtime) | `ForumReplyForm::save()` dispatches `forum_reply` notification if module installed |

---

## Known Issues / Bugs

| ID | Severity | Description |
|----|----------|-------------|
| BUG-FORUM-01 | Critical | `ForumController::viewTopic()` begins with `throw new \Exception("HARD DEBUG!")`. File is dead (not routed) but any future routing misconfiguration would cause HTTP 500. Dead file should be deleted. |
| BUG-FORUM-02 | High | `LmsForumController::viewTopic()` (active controller) calls `\Drupal::messenger()->addMessage("FRESH CONTROLLER LOADED! ...")` — debug statement left in production code; shows a message to all users on every topic view. Must be removed. |
| BUG-FORUM-03 | Medium | `lms_forum.reply.best_answer` route requires only `_permission: access content` — any authenticated user can mark any reply as best answer on any topic, not just the topic owner or instructor. |
| DEBT-FORUM-01 | Low | `lms-forum-topic-form.html.twig` is present in the templates directory but not registered in `hook_theme()`. Either register it or remove it. |

> These feed [E02 Issues](../../E-Quality/E02-issues/).

---

## Cross-Links

- Entities: [C04 Content Entities](../C04-entities/01-content-entities.md)
- Course scoping: [lms_course module](lms_course.md)
- Forum-reply notification: [lms_notification module](lms_notification.md)
