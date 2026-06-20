---
usecase_id: UC-WF008-003
workflow: WF-008
type: permission
title: Anonymous User Attempts to Create Forum Topic — Access Denied
status: as-built
---

# UC-WF008-003 — Anonymous User Attempts Forum Topic Creation

**Purpose:** An anonymous (unauthenticated) visitor attempts to create a forum topic. The entity-create access handler denies access; the visitor can browse the topic list in teaser mode only.

---

## Actors

| Actor | Type | Notes |
|-------|------|-------|
| Anonymous Visitor | Primary | Not authenticated; attempts forum write actions |
| `ForumTopicAccessControlHandler` | Secondary | Enforces create access — requires authenticated session |
| `ForumReplyAccessControlHandler` | Secondary | Same pattern for reply access |
| System (`lms_forum`) | Secondary | Renders teaser mode for anonymous topic views |

---

## Preconditions

| # | Condition |
|---|-----------|
| P1 | Visitor has no active Drupal session (anonymous) |
| P2 | `lms_forum` module is enabled |
| P3 | Route `lms_forum.topic.add` is configured with `_entity_create_access: lms_forum_topic` |

---

## Trigger

Anonymous visitor navigates directly to `/forum/new` or `/forum/{lms_forum_topic}/reply`.

---

## Main Flow

### Part A — Browse forum (allowed for anonymous)

| Step | Given | When | Then |
|------|-------|------|------|
| 1 | Visitor is anonymous at `/forum` | `LmsForumController::index()` executes | All `lms_forum_topic` entities loaded; items array built; `lms_forum_index` template rendered; **no inline new-topic form is rendered for anonymous** — `addTopic()` is only called for the authenticated branch |
| 2 | Visitor clicks a topic title | `LmsForumController::viewTopic($topic)` executes | `field_views` incremented; `is_teaser_mode=TRUE` passed to template; **replies are NOT loaded for anonymous** (`viewTopic()` checks `current_user()->isAuthenticated()` before loading reply entities) |
| 3 | Template renders teaser | Anonymous visitor sees topic title and body | Reply list is hidden; reply form is not rendered; a "Login to reply" prompt is expected from the template (template-controlled) |

### Part B — Attempt topic creation (denied for anonymous)

| Step | Given | When | Then |
|------|-------|------|------|
| 4 | Anonymous visitor navigates to `/forum/new` | Drupal routing evaluates route requirement `_entity_create_access: lms_forum_topic` | `ForumTopicAccessControlHandler::checkCreateAccess()` is called; checks `$account->isAuthenticated()`; returns `AccessResult::forbidden()` for anonymous |
| 5 | Access result is forbidden | Drupal access layer fires | HTTP 403 response; Drupal renders the "Access Denied" page; the `lms_theme` frontend should redirect to `/user/login` (frontend theme responsibility — not enforced by `lms_forum` module) |

### Part C — Attempt reply creation (denied for anonymous)

| Step | Given | When | Then |
|------|-------|------|------|
| 6 | Anonymous visitor navigates to `/forum/{lms_forum_topic}/reply` | Drupal routing evaluates `_entity_create_access: lms_forum_reply` | `ForumReplyAccessControlHandler::checkCreateAccess()` returns `AccessResult::forbidden()` for anonymous |
| 7 | Access denied | HTTP 403 | Same as step 5 |

---

## Entities Mutated

None — no entity is created or modified in this use case. `field_views` on `lms_forum_topic` is incremented on anonymous topic view (step 2), but this is a read-adjacent side effect, not a write triggered by the anonymous user's intent.

---

## Observability Hooks

| Hook / Event | Module | What to check |
|--------------|--------|----------------|
| `ForumTopicAccessControlHandler::checkCreateAccess()` | `lms_forum` | Returns `AccessResult::forbidden()` for `AccountInterface::isAnonymous() === true` |
| `ForumReplyAccessControlHandler::checkCreateAccess()` | `lms_forum` | Same as above |
| `LmsForumController::index()` | `lms_forum` | `new_topic_form` variable in template is NULL/absent for anonymous |
| `LmsForumController::viewTopic()` | `lms_forum` | `is_teaser_mode=TRUE` in template variables; replies array empty |
| HTTP response | Drupal routing | 403 on `/forum/new` and `/forum/{topic}/reply` for anonymous |

---

## Acceptance Link

| AC ID | Summary | Planned Test |
|-------|---------|--------------|
| [AC-080](../../../B-Requirements/B05-acceptance-criteria/AC-08-communication.md#ac-080-forum-topic-creation-and-listing) | Forum topic creation requires authentication | T-080 (planned, E03 — not yet materialized) |

---

## Notes

- There is no `lms_forum.permissions.yml` file — the module relies entirely on entity-level access control handlers (`ForumTopicAccessControlHandler`, `ForumReplyAccessControlHandler`) plus the `access content` permission for the forum index route.
- The teaser-mode behaviour (replies not shown to anonymous) is implemented in `LmsForumController::viewTopic()` via `$this->currentUser->isAuthenticated()` check, not via a permission gate on the route.
- No course-enrollment check is made for forum access. Any authenticated user (regardless of whether they are enrolled in a course) can create topics and replies, even for course-scoped topics (`field_course` set).
- If the frontend theme redirects 403 to `/user/login?destination=/forum/new`, Drupal's standard redirect-after-login mechanism will return the visitor to `/forum/new` after authentication, where they can then create the topic.
