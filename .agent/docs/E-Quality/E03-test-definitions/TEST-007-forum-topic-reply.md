---
id: TEST-007
type: Functional
priority: P1 — Should
ac: AC-080, AC-082
feature: F-008 (Communication)
module: lms_forum
service: lms_forum.route_subscriber (RouteSubscriber)
status: NOT_YET_AUTOMATED
---

# TEST-007 — Forum Topic and Reply Flow

> Verifies that a student can create a forum topic, view it (via the live `LmsForumController`, not the dead `ForumController`), submit a reply, and mark a best answer. Also documents the known debug `addMessage` bug that appears in `viewTopic()`.

**Story:** [D03 lms_forum](../../D-Development/D03-stories/lms_forum.md)
**AC:** [AC-080 — Forum topic creation and listing (B05 AC-08)](../../B-Requirements/B05-acceptance-criteria/AC-08-communication.md) · [AC-082 — Reply creates notification (B05 AC-08)](../../B-Requirements/B05-acceptance-criteria/AC-08-communication.md)
**C12 Workflow:** [WF-008 Forum Flow](../../C-Design/C12-workflows/WF-008-forum-flow.md)
**C05 Module:** [lms_forum](../../C-Design/C05-modules/lms_forum.md)

---

## TC-007-A: Student creates a forum topic

**Layer:** Functional
**Given:** An authenticated user (role `student`) is on `/forum/new`

**When:** The user submits `ForumTopicForm` with `name = "My Question"` and optional `field_body`

**Then:**
- `ForumTopicForm::save()` creates a `lms_forum_topic` entity in the database
- Response is a redirect to the canonical topic URL (`/forum/{lms_forum_topic}`)
- The topic appears in `LmsForumController::index()` listing at `/forum` sorted by `created DESC`

### Test Data

| Input | Expected Output |
|-------|----------------|
| Valid `name`, no body | Topic created; redirect to canonical |
| Empty `name` (required) | Form validation error; no entity created |
| Anonymous user | HTTP 403 (entity_create_access check: `_entity_create_access: lms_forum_topic`) |

---

## TC-007-B: `LmsForumController::viewTopic()` is the active route (not `ForumController`)

**Layer:** Functional
**Given:** A `lms_forum_topic` entity exists

**When:** Any user GETs `/forum/{lms_forum_topic}`

**Then:**
- `RouteSubscriber` has altered `entity.lms_forum_topic.canonical` to use `LmsForumController::viewTopic`
- HTTP 200 with the `lms_forum_topic` theme rendered
- **Known bug (as-built):** A Drupal status message `"FRESH CONTROLLER LOADED! ..."` appears on the page — this is a debug `addMessage` left in production code. The test must ASSERT this message is present (documents as-built state); a separate issue tracks its removal.
- `ForumController::viewTopic()` (which starts with `throw new \Exception("HARD DEBUG!")`) is NEVER invoked

### Test Data

| Input | Expected Output |
|-------|----------------|
| Valid topic ID | HTTP 200; `lms_forum_topic` theme rendered; debug message present |
| Non-existent topic ID | HTTP 404 |
| Anonymous user | HTTP 200 (route requires `_permission: access content`) |

---

## TC-007-C: Reply to a topic

**Layer:** Functional
**Given:**
- A `lms_forum_topic` entity exists
- An authenticated student is viewing the topic

**When:** The student submits `ForumReplyForm` at `/forum/{lms_forum_topic}/reply` with valid `field_body`

**Then:**
- A `lms_forum_reply` entity is created with `field_topic = <topic_id>`, `field_body = <content>`
- The topic's `field_replies_count` is incremented by 1
- The topic's `field_last_reply_date` is updated to the current timestamp
- If `lms_notification` module is installed: `lms_notification.manager::dispatch('forum_reply', ...)` is called (creates an in-app `lms_notification` for the topic owner)
- Response redirects to the topic canonical URL

### Test Data

| Input | Expected Output |
|-------|----------------|
| Valid `field_body`, authenticated user | Reply created; `field_replies_count++` |
| Empty `field_body` (required) | Form validation error |
| Anonymous user | HTTP 403 (entity_create_access) |

---

## TC-007-D: Mark reply as best answer

**Layer:** Functional
**Given:**
- A `lms_forum_topic` owned by user A
- A `lms_forum_reply` on that topic by user B
- User A is authenticated

**When:** User A GETs `/forum/{lms_forum_topic}/reply/{lms_forum_reply}/best`

**Then:**
- `LmsForumController::markBestAnswer()` sets `field_best_answer = TRUE` on the reply
- `field_resolved = TRUE` is set on the topic
- Response redirects to the canonical topic URL

**And when** a non-owner (user B) tries to mark best answer:

**Then:** Access is denied (method checks ownership or `administer lms_course` permission)

---

## Setup / Teardown

- Setup: Full Drupal (`BrowserTestBase`); install `lms_base`, `lms_course`, `lms_forum`; optionally install `lms_notification` (TC-007-C); create student users; install `forum_category` taxonomy vocabulary
- Teardown: Standard BrowserTestBase cleanup

## Notes

- `LmsForumController` source: `web/modules/custom/lms_forum/src/Controller/LmsForumController.php`
- `ForumController` (dead code with `throw new \Exception("HARD DEBUG!")`): `web/modules/custom/lms_forum/src/Controller/ForumController.php` — this class is never routed to; the Kernel test should assert it is NOT reachable via any route
- `RouteSubscriber` source: `web/modules/custom/lms_forum/src/Routing/RouteSubscriber.php`
- The debug `addMessage("FRESH CONTROLLER LOADED!")` in `viewTopic()` is a confirmed production bug (see E02 issues). The test must document it as current as-built state; assert it AND log it as a known issue
- The `lms_forum_topic-form.html.twig` template present in `templates/` is NOT registered in any `hook_theme()` — it is likely a leftover; tests should not rely on this template being rendered
- Expected file: `web/modules/custom/lms_forum/tests/src/Functional/ForumTopicReplyTest.php`
