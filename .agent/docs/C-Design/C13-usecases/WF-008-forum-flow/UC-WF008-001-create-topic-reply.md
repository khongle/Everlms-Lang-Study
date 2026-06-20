---
usecase_id: UC-WF008-001
workflow: WF-008
type: happy
title: Authenticated User Creates Topic and Receives Reply with Notification
status: as-built
---

# UC-WF008-001 — Create Forum Topic and Receive Reply with Notification

**Purpose:** An authenticated student creates a forum topic; another authenticated user posts a reply; `lms_notification` dispatches an in-app notification to the topic author.

---

## Actors

| Actor | Type | Notes |
|-------|------|-------|
| Student A (Topic Author) | Primary | Creates the topic |
| Student B (Replier) | Primary | Posts a reply to the topic |
| System (`lms_forum`) | Secondary | Handles entity creation, counter updates, routing |
| System (`lms_notification`) | Secondary | Dispatches in-app notification on reply save |

---

## Preconditions

| # | Condition |
|---|-----------|
| P1 | Both Student A and Student B are authenticated (have a valid Drupal session) |
| P2 | `lms_forum` module is enabled |
| P3 | `lms_notification` module is enabled (required for notification dispatch) |
| P4 | `ForumTopicAccessControlHandler` grants create access to any authenticated user |

---

## Trigger

Student A navigates to `/forum` and clicks "New Topic" or directly navigates to `/forum/new`.

---

## Main Flow

| Step | Given | When | Then |
|------|-------|------|------|
| 1 | Student A is authenticated at `/forum` | `LmsForumController::index()` executes | All `lms_forum_topic` entities loaded DESC by `created`; items array built with `field_replies_count` (or DB fallback), author info (first_name/last_name + avatar with `getUserInfo()` fallback), `field_category`, `field_last_reply_date`; `lms_forum_index` template rendered including inline new-topic form |
| 2 | Student A is at `/forum/new` | `LmsForumController::addTopic()` executes | Access check `_entity_create_access: lms_forum_topic` → `ForumTopicAccessControlHandler::checkCreateAccess()` returns allowed (authenticated); blank `ForumTopic` entity created; entity default form rendered via `lms_forum_new_topic_page` theme |
| 3 | Topic form rendered | Student A fills `name` (required, max_length 255), `field_body` (optional), optionally selects `field_category` (taxonomy_term:forum_category) and `field_course` (optional course scope) | `ForumTopicForm` hides system fields (`field_replies_count`, `field_last_reply_date`, `field_views`, `field_resolved`) and hides `field_pinned`, `field_locked` for non-admin users |
| 4 | Form submitted | `ForumTopicForm::save()` executes | Sets topic `uid` to current user if new and unset; saves `lms_forum_topic` entity; redirects to `entity.lms_forum_topic.canonical` (overridden by `RouteSubscriber` to `LmsForumController::viewTopic`) |
| 5 | Student A is on the topic view page | `LmsForumController::viewTopic($topic)` executes | `field_views` incremented and saved on `lms_forum_topic`; replies loaded for authenticated user; `lms_forum_topic` template rendered with reply form; **note: debug `addMessage("FRESH CONTROLLER LOADED!")` is called here — visible as a status message to all users** |
| 6 | Student B views the topic | Student B clicks "Reply" → `/forum/{lms_forum_topic}/reply` | `LmsForumController::reply($topic)` executes; blank `ForumReply` entity created with `field_topic` set; entity form returned |
| 7 | Reply form rendered | Student B fills `field_body` (required); optionally sets threaded `field_parent` | `ForumReplyForm` hides `field_upvotes`, `field_best_answer`, `field_topic`, `field_parent` from UI; text format selector hidden via `hideTextFormat` after_build callback |
| 8 | Reply form submitted | `ForumReplyForm::save()` executes | `lms_forum_reply` entity saved with `field_topic` reference; topic `field_replies_count` incremented (+1); topic `field_last_reply_date` set to current timestamp |
| 9 | Reply saved | `ForumReplyForm::save()` calls notification dispatch | Checks `\Drupal::hasService('lms_notification.manager')`; calls `lms_notification.manager::dispatch('forum_reply', <topic_author>, ...)` |
| 10 | Notification dispatched | `NotificationManager::dispatch()` executes | In-app `lms_notification` entity created for topic author with `field_type='forum_reply'`, `field_channel=['in_app']`, `field_read=FALSE`; email channel: logs a TODO, no email sent; push channel: logs only |
| 11 | Notification entity saved | Student B redirected | Redirect to `entity.lms_forum_topic.canonical` (topic view); Student A will see unread count incremented on next page load via `NotificationBellBlock` |

---

## Entities Mutated

| Entity | Field | Change |
|--------|-------|--------|
| `lms_forum_topic` | `name`, `field_body`, `field_category`, `field_course`, `uid`, `created` | Created in step 4 |
| `lms_forum_topic` | `field_views` | Incremented in step 5 |
| `lms_forum_topic` | `field_replies_count` | Incremented by 1 in step 8 |
| `lms_forum_topic` | `field_last_reply_date` | Set to `REQUEST_TIME` in step 8 |
| `lms_forum_reply` | `field_topic`, `field_body`, `uid`, `created` | Created in step 8 |
| `lms_notification` | `uid`, `field_type`, `field_channel`, `field_title`, `field_message`, `field_read` | Created in step 10 for topic author |

---

## Observability Hooks

| Hook / Event | Module | What to check |
|--------------|--------|----------------|
| `ForumTopicForm::save()` | `lms_forum` | Entity saved; `field_topic` / `uid` correct; canonical redirect fires |
| `ForumReplyForm::save()` | `lms_forum` | `lms_forum_reply` entity created; topic counters updated (`field_replies_count`, `field_last_reply_date`) |
| `NotificationManager::dispatch('forum_reply')` | `lms_notification` | `lms_notification` entity exists in DB for topic author; `field_read=0`; in_app channel only |
| `LmsForumController::viewTopic()` debug message | `lms_forum` | **BUG:** Drupal status message "FRESH CONTROLLER LOADED!" visible on topic page — see E02 issue tracker |
| `NotificationBellBlock::build()` | `lms_notification` | Unread count for topic author reflects new notification; cache tag `lms_notification_list` invalidated |

---

## Acceptance Link

| AC ID | Summary | Planned Test |
|-------|---------|--------------|
| [AC-080](../../../B-Requirements/B05-acceptance-criteria/AC-08-communication.md#ac-080-forum-topic-creation-and-listing) | Forum topic creation and listing | T-080 (planned, E03 — not yet materialized) |
| [AC-082](../../../B-Requirements/B05-acceptance-criteria/AC-08-communication.md#ac-082-forum-reply-creation-and-topic-counter-update) | Forum reply creation and topic counter update | T-082 (planned, E03 — not yet materialized) |
| [AC-081](../../../B-Requirements/B05-acceptance-criteria/AC-08-communication.md#ac-081-forum-topic-view-debug-message-present-as-built-bug) | Debug message present on topic view | T-081 (planned, E03 — not yet materialized) |

---

## Notes

- `field_course` on `lms_forum_topic` is optional; no enrollment check is enforced. Course scoping is display only — the controller's `index()` query loads all topics without filtering by `field_course`.
- Topic sort in `index()` is DESC by `created`, not by `field_pinned`. Pinned topics do not float to the top in the current controller query.
- Threaded replies are supported via `field_parent` on `lms_forum_reply`, but thread rendering depth is controlled entirely by the `lms-forum-topic.html.twig` template.
- Email and push notification channels are stubs (`NotificationManager` logs TODO, no dispatch). Only in-app notifications are functional.
- The `ForumController` dead class (`throw new \Exception("HARD DEBUG!")`) is not reachable via any current route; `RouteSubscriber` overrides the canonical route to `LmsForumController`. However, the class file remains and is a 500 risk on routing misconfiguration.
