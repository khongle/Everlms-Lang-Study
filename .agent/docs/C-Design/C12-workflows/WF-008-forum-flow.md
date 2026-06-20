---
workflow: WF-008
slug: forum-flow
mode: as-built
b04_source: B04/WF-008-forum-flow.md
adequacy: NOT_RUN
adequacy_run: null
---

# C12 — Workflow: WF-008 Forum Topic and Reply Flow

**Trigger:** Authenticated user navigates to `/forum`, creates a topic at `/forum/new`, views a topic at `/forum/{lms_forum_topic}`, or posts a reply at `/forum/{lms_forum_topic}/reply`.
**Outcome:** `lms_forum_topic` and/or `lms_forum_reply` entities saved; topic `field_replies_count` and `field_last_reply_date` updated; in-app notification dispatched to topic author on new reply.
**B04 source:** [`../../B-Requirements/B04-business-workflows/WF-008-forum-flow.md`](../../B-Requirements/B04-business-workflows/WF-008-forum-flow.md)
**Mode:** as-built

---

## Actors

| Actor (C02 role) | Step participation | Permissions needed |
|---|---|---|
| Any authenticated user | Browse, create topics, post replies | `access content` (view); authenticated for create |
| Topic author | Mark best answer; resolve own topic | Owner of `lms_forum_topic` |
| Admin / Manager | Pin/lock topics; full CRUD; mark best answer on any topic | `administer lms_course` |
| Anonymous | Browse topic list (teaser mode only; no replies shown) | `access content` |
| System | Increment view count; dispatch in-app notification | — |

## Entry points

| URL (C07) | Page (C11) | Method | Form | Feature flag |
|---|---|---|---|---|
| `/forum` | forum-index | GET | — | — |
| `/forum/new` | forum-new-topic | GET/POST | `ForumTopicForm` | Authenticated only |
| `/forum/{lms_forum_topic}` | forum-topic-view | GET | — | — |
| `/forum/{lms_forum_topic}/reply` | forum-reply | GET/POST | `ForumReplyForm` | Authenticated only |
| `/forum/reply/{lms_forum_reply}/edit` | forum-reply-edit | GET/POST | `ForumReplyForm` | Owner or `administer lms_course` |
| `/forum/reply/{lms_forum_reply}/delete` | forum-reply-delete | GET/POST | `ForumReplyDeleteForm` | Owner or `administer lms_course` |
| `/forum/{lms_forum_topic}/reply/{lms_forum_reply}/best` | — | GET | — | Owner or `administer lms_course` |

## Entities touched

| Entity (C04) | Operation | Fields written | State transition |
|---|---|---|---|
| [`lms_forum_topic`](../C04-entities/) | CREATE / UPDATE | `name`, `field_body`, `field_course`, `field_category`, `field_replies_count` (incremented on reply save), `field_last_reply_date`, `field_views` (incremented on viewTopic), `field_resolved` (set TRUE on best-answer mark) | No state machine; `field_resolved` boolean |
| [`lms_forum_reply`](../C04-entities/) | CREATE / UPDATE / DELETE | `field_topic`, `field_body`, `field_parent` (threading), `field_best_answer` (set TRUE on mark) | No state machine; `field_best_answer` boolean |
| `lms_notification` | CREATE | `field_type=forum_reply`, `field_title`, `field_message`, `uid` (topic author) | — |

## State transitions

```
Topic lifecycle:
  [Authenticated user submits ForumTopicForm]
    → lms_forum_topic created (field_resolved=FALSE, field_pinned=FALSE, field_locked=FALSE)

  [Admin sets field_pinned=TRUE] → pinned display (template-side sort — not enforced in index() query sort)
  [Admin sets field_locked=TRUE] → UI disables reply button (template only; no server-side enforcement)

  [Topic owner or Admin marks best answer]
    → lms_forum_reply.field_best_answer=TRUE
    → lms_forum_topic.field_resolved=TRUE

Reply lifecycle:
  [Authenticated user submits ForumReplyForm]
    → lms_forum_reply created
    → lms_forum_topic.field_replies_count += 1
    → lms_forum_topic.field_last_reply_date = now()
    → in-app notification dispatched to topic author (if lms_notification installed)
```

State guards (who can trigger which transition):

| Transition | Trigger | Guard | Side effects |
|---|---|---|---|
| Create `lms_forum_topic` | Authenticated user submits form | `ForumTopicAccessControlHandler`: authenticated users only (`create` op) | None |
| Create `lms_forum_reply` | Authenticated user at `/forum/{topic}/reply` | `ForumReplyAccessControlHandler`: authenticated users only (`create` op) | `field_replies_count` +1 on topic; `field_last_reply_date` updated; in-app notification dispatched |
| View topic (`field_views` increment) | Any user visits `/forum/{lms_forum_topic}` | `access content` permission | `lms_forum_topic.field_views` saved; reply list loaded only for authenticated |
| Mark best answer | Click `/forum/{topic}/reply/{reply}/best` | Owner of topic OR `administer lms_course` | `field_best_answer=TRUE` on reply; `field_resolved=TRUE` on topic |
| Delete reply | `/forum/reply/{reply}/delete` | `ForumReplyAccessControlHandler`: owner OR `administer lms_course` (`delete` op) | AJAX modal: DOM node `#forum-reply-{id}` removed |
| Edit reply | `/forum/reply/{reply}/edit` | Owner OR `administer lms_course` (`update` op) | AJAX modal: reply body updated in DOM |

## Modules involved (C05)

| Module | Responsibility in this workflow |
|---|---|
| [`lms_forum`](../C05-modules/) | All forum entities (`lms_forum_topic`, `lms_forum_reply`); `LmsForumController` (active controller); `ForumTopicForm`, `ForumReplyForm`, `ForumReplyDeleteForm`; `RouteSubscriber` (overrides canonical route); `ForumTopicAccessControlHandler`, `ForumReplyAccessControlHandler` |
| [`lms_notification`](../C05-modules/) | `NotificationManager::dispatch('forum_reply')` — called from `ForumReplyForm::save()` when `lms_notification` module is installed; creates in-app `lms_notification` entity for topic author |

## Permissions

| Step | Anonymous | Authenticated (student) | Admin / Manager |
|---|---|---|---|
| View `/forum` topic list | ✓ (teaser mode) | ✓ (full list + inline new-topic form) | ✓ |
| View topic at `/forum/{id}` | ✓ (teaser; no replies) | ✓ (full replies) | ✓ |
| Create topic (`/forum/new`) | — | ✓ | ✓ |
| Post reply (`/forum/{id}/reply`) | — | ✓ | ✓ |
| Edit own reply | — | ✓ | ✓ |
| Delete own reply | — | ✓ | ✓ |
| Pin / lock topic | — | — | ✓ |
| Mark best answer | — | ✓ (own topics only) | ✓ |
| Full CRUD (any topic/reply) | — | — | ✓ |

## Notifications

| Trigger | Recipient | Channel | Template |
|---|---|---|---|
| New reply posted | Topic author (uid of `lms_forum_topic`) | In-app only | `forum_reply` notification type; message set in `ForumReplyForm::save()` |

**Stub warning:** `lms_notification.manager::dispatch()` for `forum_reply` creates an in-app `lms_notification` entity. Email and push channels are stubs — only an info log is written (TODO in `NotificationManager::dispatch()`). No email is sent to the topic author.

## Cache invalidation

| Step | Cache tag(s) | Search index | List view(s) |
|---|---|---|---|
| Topic created | `lms_forum_topic:{id}`, entity default tags | — | `/forum` index |
| Reply posted | `lms_forum_reply:{id}`; topic entity resaved → `lms_forum_topic:{id}` | — | `/forum/{topic}` |
| Best answer marked | `lms_forum_reply:{id}`, `lms_forum_topic:{id}` | — | `/forum/{topic}` |
| View count increment | `lms_forum_topic:{id}` (entity save on every view) | — | — |

Note: View count incremented and saved on every `viewTopic()` call — this causes an entity write on every topic page load. Potential performance concern for high-traffic topics.

## Operations queues

No background queue processing in this workflow. All operations are synchronous.

| Page (C11) | Filters | Owner role |
|---|---|---|
| `/workspace/content/forums/…` | — | Admin, Manager (via `WorkspaceContentController` if applicable — «unverified: no confirmed forum manage route in workspace routing») |

## Editorial / management surfaces

| Entity / content source | Create/Edit UI | Role / permission | Revision/moderation state | Translation | Visible C11 surfaces |
|---|---|---|---|---|---|
| `lms_forum_topic` | `/forum/new` (public form); entity admin routes via `AdminHtmlRouteProvider` | Authenticated (create); owner or `administer lms_course` (edit/delete) | No moderation; `field_pinned`, `field_locked` booleans | Not translatable (no `translatable=TRUE`) | `/forum`, `/forum/{id}` |
| `lms_forum_reply` | `/forum/{topic}/reply`; AJAX edit modal | Authenticated (create); owner or `administer lms_course` (edit/delete) | No moderation | Not translatable | `/forum/{id}` |

## Failure modes

| Failure | Detection | Handling |
|---|---|---|
| Anonymous attempts to create topic/reply | `ForumTopicAccessControlHandler` / `ForumReplyAccessControlHandler` returns denied | 403 (frontend theme should redirect to login) |
| `ForumController::viewTopic()` routed to (misconfiguration) | `throw new \Exception("HARD DEBUG!")` at method start causes HTTP 500 | **BUG — dead code danger.** `RouteSubscriber` overrides canonical to `LmsForumController`; this class should be removed. See Known Issues below. |
| Debug message shown on every topic view | `LmsForumController::viewTopic()` calls `Drupal::messenger()->addMessage("FRESH CONTROLLER LOADED!")` | All users see this message on every topic page. Must be removed. |
| `field_body` missing on reply submit | `field_body` is `required=TRUE` on `ForumReply`; Drupal form validation rejects | User sees validation error and re-fills body |
| Locked topic — no server-side enforcement | `field_locked=TRUE` disables reply button in template only | No server-side block: POST to `/forum/{topic}/reply` still creates reply. Server-side lock enforcement is not implemented. |
| Notification dispatch fails | `lms_notification` not installed, or `NotificationManager::dispatch()` throws | `ForumReplyForm::save()` checks `\Drupal::hasService('lms_notification.manager')` before calling; fails silently |

## Known Issues (as-built)

| Issue | Severity | Source |
|---|---|---|
| `ForumController::viewTopic()` starts with `throw new \Exception("HARD DEBUG!")` — dead code but HTTP 500 risk if routing misconfigured | HIGH | `web/modules/custom/lms_forum/src/Controller/ForumController.php` |
| `LmsForumController::viewTopic()` calls `\Drupal::messenger()->addMessage("FRESH CONTROLLER LOADED! …")` visible to all users | LOW | `web/modules/custom/lms_forum/src/Controller/LmsForumController.php` |
| Pinned topic sort not enforced in `index()` query — `DESC` by `created` only, not by `field_pinned` | LOW | `LmsForumController::index()` sort order |
| Locked topic enforcement is template/UI only — no server-side access check on reply route | MEDIUM | `LmsForumController::reply()` + `ForumReplyAccessControlHandler` |
| View count write on every page load | MEDIUM (performance) | `LmsForumController::viewTopic()` saves entity on every GET |
| No moderation/report queue | MEDIUM (feature gap) | B04 draft described moderator flag/report queue — not implemented |

## Acceptance (B05)

- B05 acceptance criteria not yet materialized.

## Related

- Modules: [`lms_forum`](../C05-modules/), [`lms_notification`](../C05-modules/)
- Entities: [`C04-entities/`](../C04-entities/)
- Roles: [`C02-user-roles.md`](../C02-user-roles.md)
- B04 source: [`../../B-Requirements/B04-business-workflows/WF-008-forum-flow.md`](../../B-Requirements/B04-business-workflows/WF-008-forum-flow.md)
- Issues: E-Quality/E02-issues/ (dead ForumController, debug messages — see E)
