---
id: WF-008
name: Forum Topic and Reply Flow
status: APPROVED
features: [F-008]
---

# WF-008 — Forum Topic and Reply Flow

The as-built `lms_forum` module provides a threaded discussion forum with topic creation, replies, best-answer marking, and in-app notification dispatch. One known code defect is documented below: a legacy `ForumController` class contains a `throw new \Exception("HARD DEBUG!")` at the top of its `viewTopic()` method — that class is currently not routed to (a `RouteSubscriber` overrides the canonical route to use `LmsForumController` instead) but is a live risk if routing is ever misconfigured.

## Trigger

- User navigates to `/forum` (global forum index).
- User clicks a topic title to view at `/forum/{lms_forum_topic}`.
- User clicks "New Topic" at `/forum/new`.
- User clicks "Reply" at `/forum/{lms_forum_topic}/reply`.

## Actors

| Actor | Role |
|-------|------|
| Any authenticated user | Browse topics, create topics, post replies |
| Topic author / Instructor | Mark a reply as Best Answer; resolve a topic |
| Admin / Manager (`administer lms_course`) | Pin topics, lock topics; full CRUD |
| Anonymous user | Read topic list (teaser mode only; no replies visible) |
| System | Increments view count; dispatches notifications via `lms_notification.manager` |

## Preconditions

- User is authenticated (anonymous users see topic list in teaser mode; reply/topic forms require login — enforced by entity create access handler).
- `lms_forum` module is enabled.
- `lms_notification` module enabled for notification dispatch (checked at runtime in `ForumReplyForm::save()`).

## Happy Path — Browse Forum

| Step | Actor | Action | System Response | Feature |
|------|-------|--------|----------------|---------|
| 1 | Any user | Navigate to `/forum` | `LmsForumController::index()` loads all `lms_forum_topic` entities sorted descending by created timestamp; builds items array with reply count (from `field_replies_count` or DB fallback), author info (first_name/last_name + avatar), category, last_reply_date | F-008 |
| 2 | Authenticated user | Topic list also includes "New Topic" form | `addTopic()` creates a blank `ForumTopic` entity and returns the entity form; rendered inline in `lms_forum_index` template | F-008 |
| 3 | Anonymous user | Topic list shows without inline form | `index()` includes the new-topic form only for authenticated users | F-008 |

## Happy Path — View Topic

| Step | Actor | Action | System Response | Feature |
|------|-------|--------|----------------|---------|
| 1 | User | Click topic title → `/forum/{lms_forum_topic}` | Route `entity.lms_forum_topic.canonical` is overridden by `RouteSubscriber` to call `LmsForumController::viewTopic()` | F-008 |
| 2 | System | Increment view count | `field_views` on `lms_forum_topic` incremented and saved | F-008 |
| 3 | Authenticated user | Replies shown in threaded display | `viewTopic()` loads all `lms_forum_reply` entities for the topic; builds reply array with mark-best, edit, delete action URLs per reply; renders `lms_forum_topic` template | F-008 |
| 4 | Anonymous user | Topic shown in teaser mode | Replies not loaded for anonymous; `is_teaser_mode=TRUE` passed to template | F-008 |

## Happy Path — Create Topic

| Step | Actor | Action | System Response | Feature |
|------|-------|--------|----------------|---------|
| 1 | Authenticated user | Navigate to `/forum/new` or submit inline form | `LmsForumController::addTopic()` creates blank `ForumTopic`; access check: `_entity_create_access: lms_forum_topic` (requires authenticated per `ForumTopicAccessControlHandler`) | F-008 |
| 2 | User | Fill in topic title (`name`) and body (`field_body`); optionally select category (`field_category`, taxonomy_term bundle: forum_category) and course scope (`field_course`) | `ForumTopicForm` hides system fields (`field_replies_count`, `field_last_reply_date`, `field_views`, `field_resolved`) from non-admin users; hides `field_pinned` and `field_locked` for non-admins | F-008 |
| 3 | User | Submit form | `ForumTopicForm::save()`: sets owner if new and unset; saves `lms_forum_topic` entity; redirects to `entity.lms_forum_topic.canonical` (i.e., topic view page) | F-008 |

## Happy Path — Post Reply

| Step | Actor | Action | System Response | Feature |
|------|-------|--------|----------------|---------|
| 1 | Authenticated user | Click "Reply" on topic page → `/forum/{lms_forum_topic}/reply` | `LmsForumController::reply()` creates blank `ForumReply` with `field_topic` set; access check: `_entity_create_access: lms_forum_reply` (requires authenticated) | F-008 |
| 2 | User | Fill in `field_body`; optionally set `field_parent` for threaded reply | `ForumReplyForm` hides `field_upvotes`, `field_best_answer`, `field_topic`, `field_parent` from UI | F-008 |
| 3 | User | Submit form | `ForumReplyForm::save()`: saves `lms_forum_reply`; updates topic `field_replies_count` (+1) and `field_last_reply_date` (now) | F-008 |
| 4 | System | Dispatch notification if `lms_notification` installed | `lms_notification.manager::dispatch('forum_reply')` called from `ForumReplyForm::save()`; creates in-app notification for topic author | F-008 |
| 5 | System | Redirect to topic canonical | User sees their reply in the thread | F-008 |

## Happy Path — Mark Best Answer

| Step | Actor | Action | System Response | Feature |
|------|-------|--------|----------------|---------|
| 1 | Topic author or Admin/Manager | Click "Mark as Best Answer" on a reply → `POST /forum/{lms_forum_topic}/reply/{lms_forum_reply}/best` | `LmsForumController::markBestAnswer()`: checks that caller is topic owner OR has `administer lms_course` | F-008 |
| 2 | System | Sets `field_best_answer=TRUE` on reply; sets `field_resolved=TRUE` on topic | Both entities saved; redirects to topic canonical | F-008 |

## Error Paths

| Step | Condition | System Response | Recovery |
|------|-----------|----------------|----------|
| 1 (topic) | Anonymous user attempts to access `/forum/new` | Entity create access denied (403); frontend theme should redirect to login | User logs in and returns to forum |
| 1 (view) | `ForumController::viewTopic()` ever called (routing misconfiguration) | `throw new \Exception("HARD DEBUG!")` in that dead class causes HTTP 500 | **BUG** — `ForumController` is dead code but dangerous; see Known Issues below |
| 2 (topic) | Topic locked (`field_locked=TRUE`) | Form UI should disable the reply button; no server-side enforcement at controller level (enforced by template/UI only — «unverified whether server-side block exists») | Admin unlocks topic |
| 3 (reply) | Reply form submitted without body | `field_body` is `required=TRUE` on `ForumReply`; Drupal form validation rejects | User fills in body |

## Known Issues (as-built)

| Issue | Severity | Detail |
|-------|----------|--------|
| Dead `ForumController` with `throw new \Exception("HARD DEBUG!")` | HIGH | `web/modules/custom/lms_forum/src/Controller/ForumController.php::viewTopic()` starts with `throw new \Exception("HARD DEBUG! ...")`. The class is not currently routed to (overridden by `RouteSubscriber` to use `LmsForumController`), but any routing misconfiguration would cause an HTTP 500. Remove class or convert to stub. |
| Debug message in production `LmsForumController` | LOW | `LmsForumController::viewTopic()` calls `\Drupal::messenger()->addMessage("FRESH CONTROLLER LOADED! ...")`. This debug message is visible to all users on every topic page view. |
| No moderation queue | MEDIUM | The draft described a moderator flag/report queue. No such feature exists in the as-built code. No flag, report, or moderator queue entities or routes are implemented. |
| Email notification stub | LOW | `lms_notification.manager::dispatch()` for the `forum_reply` event creates only an in-app notification. Email channel is a logged-only stub (TODO in `NotificationManager`). |

## Success Criteria

- `lms_forum_topic` entity saved with correct owner, `name`, `field_body`, optional `field_category` and `field_course`.
- `lms_forum_reply` entity saved with `field_topic` reference and correct owner.
- Topic `field_replies_count` and `field_last_reply_date` updated on each reply save.
- In-app notification dispatched to topic author on new reply (when `lms_notification` is enabled).
- Best answer marked: `lms_forum_reply.field_best_answer=TRUE`, `lms_forum_topic.field_resolved=TRUE`.

## Business Rules

- **Authentication required for write operations**: Topic creation, reply posting, and best-answer marking all require an authenticated session. `ForumTopicAccessControlHandler` and `ForumReplyAccessControlHandler` allow create for any authenticated user.
- **Best answer mark access**: Only the topic owner (`uid`) or a user with `administer lms_course` permission can call `markBestAnswer()`. No separate "Instructor" role check — anyone who owns the topic can resolve it.
- **Admin-only fields**: `field_pinned` and `field_locked` are hidden from non-admin users in `ForumTopicForm` but stored on the entity. Pinned topics are expected to appear first in the list — display ordering logic is in the template/controller query (sort order in `index()` is DESC by created, not by pinned flag; «unverified whether pinned sort is implemented»).
- **Threaded replies**: `field_parent` on `lms_forum_reply` supports threading, but thread rendering depth is controlled by the `lms-forum-topic.html.twig` template.
- **Course-scoped forum**: `field_course` on `lms_forum_topic` is optional. No enrollment check is enforced at the access-control level — the controller loads all topics without filtering by `field_course`. Course scoping is a display/filter concern, not an access control gate.
- **Notification channel**: Only in-app notifications are functional. Email and push channels are stubs.

## Related Docs

- Features: [F-008 Communication](../B03-features/08-communication.md)
- Workflow: [WF-001 Enrollment & Learning](./WF-001-enrollment-learning.md)
- Source: `web/modules/custom/lms_forum/src/Controller/LmsForumController.php`, `web/modules/custom/lms_forum/src/Form/ForumReplyForm.php`, `web/modules/custom/lms_forum/src/Routing/RouteSubscriber.php`
- Issue tracker: `E-Quality/E02-issues/` (dead ForumController, debug messages)
