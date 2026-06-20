---
usecase_id: UC-WF008-002
workflow: WF-008
type: happy
title: Topic Owner Marks Reply as Best Answer
status: as-built
---

# UC-WF008-002 — Topic Owner Marks Reply as Best Answer

**Purpose:** The topic owner (or an admin) marks one reply as the Best Answer; the reply's `field_best_answer` is set to `TRUE` and the topic's `field_resolved` is set to `TRUE`, indicating the question is answered.

---

## Actors

| Actor | Type | Notes |
|-------|------|-------|
| Topic Owner | Primary | Authenticated user who created the topic; can mark best answer |
| Admin / Manager | Primary | User with `administer lms_course` permission; can also mark best answer on any topic |
| System (`lms_forum`) | Secondary | `LmsForumController::markBestAnswer()` handles the action |

---

## Preconditions

| # | Condition |
|---|-----------|
| P1 | The `lms_forum_topic` entity exists and is accessible |
| P2 | At least one `lms_forum_reply` entity exists for the topic |
| P3 | Requesting user is authenticated AND is either: the topic owner (`uid === current_user()->id()`) OR has `administer lms_course` permission |
| P4 | `lms_forum` module is enabled |

---

## Trigger

Topic owner (or Admin) clicks "Mark as Best Answer" on a reply, issuing a GET to `/forum/{lms_forum_topic}/reply/{lms_forum_reply}/best`.

---

## Main Flow

| Step | Given | When | Then |
|------|-------|------|------|
| 1 | User is viewing topic page at `/forum/{lms_forum_topic}` | `LmsForumController::viewTopic()` builds the reply list | Each reply in the array includes a `mark_best_url` set to `/forum/{topic_id}/reply/{reply_id}/best` if the user is the topic owner or an admin |
| 2 | User clicks "Mark Best Answer" link | GET request to `/forum/{lms_forum_topic}/reply/{lms_forum_reply}/best` | Route `lms_forum.reply.best_answer` resolves both `lms_forum_topic` and `lms_forum_reply` entities via parameter upcasting; `LmsForumController::markBestAnswer($topic, $reply)` executes |
| 3 | Access check | `markBestAnswer()` verifies caller | Checks: `$topic->getOwnerId() === current_user_id` OR `current_user()->hasPermission('administer lms_course')`; if neither, access is denied (403) |
| 4 | Access check passes | Controller sets reply field | `$reply->set('field_best_answer', TRUE)->save()` — `lms_forum_reply.field_best_answer` persisted to DB |
| 5 | Reply saved | Controller sets topic field | `$topic->set('field_resolved', TRUE)->save()` — `lms_forum_topic.field_resolved` persisted to DB |
| 6 | Both saves complete | Controller redirects | Redirects to `entity.lms_forum_topic.canonical` (the topic view page); user sees the reply visually highlighted as Best Answer per template logic |

---

## Entities Mutated

| Entity | Field | Change |
|--------|-------|--------|
| `lms_forum_reply` | `field_best_answer` | Set to `TRUE` (boolean) |
| `lms_forum_topic` | `field_resolved` | Set to `TRUE` (boolean) |

---

## Observability Hooks

| Hook / Event | Module | What to check |
|--------------|--------|----------------|
| `LmsForumController::markBestAnswer()` | `lms_forum` | DB row `lms_forum_reply`: `field_best_answer__value = 1`; DB row `lms_forum_topic`: `field_resolved__value = 1` |
| Access denial | `lms_forum` | Non-owner, non-admin user receives HTTP 403 |
| Redirect | `lms_forum` | After save, response is a redirect to canonical topic URL (not a form submit) |

---

## Acceptance Link

| AC ID | Summary | Planned Test |
|-------|---------|--------------|
| [AC-083](../../../B-Requirements/B05-acceptance-criteria/AC-08-communication.md#ac-083-forum-best-answer-marking) | Best-answer marking and AJAX edit | T-083 (planned, E03 — not yet materialized) |

---

## Notes

- There is no enforcement preventing multiple replies from being marked as Best Answer on the same topic. `markBestAnswer()` sets `field_best_answer=TRUE` on the target reply without first clearing any existing best-answer replies. The template is responsible for highlighting all replies where `field_best_answer=TRUE`.
- No notification is dispatched to the reply author when their reply is marked as Best Answer. This is a feature gap.
- `field_resolved=TRUE` is set on the topic on each best-answer mark; it is never automatically reset to `FALSE` if the best-answer flag is later cleared (there is no "unmark" route).
- The access check in `markBestAnswer()` uses the topic owner's `uid`, not the requester's relationship to a course or instructor role. Any student who creates a topic can resolve it.
- Reply edit via AJAX modal is supported by `ForumReplyForm::ajaxSaveCallback()` which closes the modal and updates `#forum-reply-{id}` in the DOM via `HtmlCommand`. This is part of `AC-083` but a separate sub-flow not detailed here.
