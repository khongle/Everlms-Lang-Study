---
workflow: WF-008
title: Forum Topic & Reply Flow — Use-Case Catalog
status: as-built (with known bugs)
---

# C13 — WF-008: Forum Topic & Reply Flow — Use-Case Catalog

Use cases covering the `lms_forum` module: topic creation, reply posting, best-answer marking, and the access-control boundary for anonymous users. Two known live bugs are documented: a debug `addMessage()` call in `LmsForumController::viewTopic()` (visible to all users) and a dead `ForumController::viewTopic()` that throws an uncaught exception if ever reached.

## Files in this folder

| File | ID | Type | Summary |
|------|----|------|---------|
| [UC-WF008-001-create-topic-reply.md](UC-WF008-001-create-topic-reply.md) | UC-WF008-001 | happy | Authenticated user creates a topic and receives a reply with notification |
| [UC-WF008-002-mark-best-answer.md](UC-WF008-002-mark-best-answer.md) | UC-WF008-002 | happy | Topic owner marks a reply as Best Answer; topic set to resolved |
| [UC-WF008-003-anonymous-access-denied.md](UC-WF008-003-anonymous-access-denied.md) | UC-WF008-003 | permission | Anonymous user attempts to create a topic — access denied |

## Cross-links

- Workflow: [../../../B-Requirements/B04-business-workflows/WF-008-forum-flow.md](../../../B-Requirements/B04-business-workflows/WF-008-forum-flow.md)
- Acceptance criteria: [../../../B-Requirements/B05-acceptance-criteria/AC-08-communication.md](../../../B-Requirements/B05-acceptance-criteria/AC-08-communication.md)
- Primary modules: `lms_forum`, `lms_notification`
- Entities mutated: `lms_forum_topic`, `lms_forum_reply`, `lms_notification`

## Known bugs (document; do not work around)

| Bug | Severity | Detail |
|-----|----------|--------|
| Debug message in `LmsForumController::viewTopic()` | LOW | `\Drupal::messenger()->addMessage("FRESH CONTROLLER LOADED! ...")` shown to all users on every topic page view. Source: `web/modules/custom/lms_forum/src/Controller/LmsForumController.php` |
| Dead `ForumController::viewTopic()` throws exception | HIGH | `web/modules/custom/lms_forum/src/Controller/ForumController.php` starts with `throw new \Exception("HARD DEBUG!")` — not currently routed to (overridden by `RouteSubscriber`), but any routing misconfiguration causes HTTP 500 |
| No moderation queue | MEDIUM | No flag, report, or moderator queue entities or routes exist; draft described a moderator queue that is not implemented |
| Email and push notifications are stubs | LOW | `NotificationManager::dispatch()` for `forum_reply` creates only in-app notification; email channel is a logged-only TODO |
