# D03 — Unit Spec: ForumTopic + ForumReply Entities

**Unit ID:** U-040
**Group:** G-COMMUNICATION
**Title:** ForumTopic + ForumReply entities
**Status:** DONE
**Priority:** P1

---

## Scope

Implements the `lms_forum_topic` and `lms_forum_reply` custom content entities for course-scoped discussion forums. Includes access control handlers, forum controllers, forms, route subscriber, and templates for the forum index, topic view, new topic, and reply editing.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_forum/lms_forum.info.yml` | Module declaration |
| `web/modules/custom/lms_forum/lms_forum.module` | Hook implementations |
| `web/modules/custom/lms_forum/lms_forum.routing.yml` | Forum routes |
| `web/modules/custom/lms_forum/lms_forum.services.yml` | Service registration |
| `web/modules/custom/lms_forum/src/Entity/ForumTopic.php` | ForumTopic entity class |
| `web/modules/custom/lms_forum/src/Entity/ForumReply.php` | ForumReply entity class |
| `web/modules/custom/lms_forum/src/ForumTopicAccessControlHandler.php` | Topic access handler |
| `web/modules/custom/lms_forum/src/ForumReplyAccessControlHandler.php` | Reply access handler |
| `web/modules/custom/lms_forum/src/Controller/ForumController.php` | Forum controller |
| `web/modules/custom/lms_forum/src/Controller/LmsForumController.php` | LMS-integrated forum controller |
| `web/modules/custom/lms_forum/src/Form/ForumTopicForm.php` | Topic create/edit form |
| `web/modules/custom/lms_forum/src/Form/ForumReplyForm.php` | Reply create/edit form |
| `web/modules/custom/lms_forum/src/Form/ForumReplyDeleteForm.php` | Reply delete confirm form |
| `web/modules/custom/lms_forum/src/Routing/RouteSubscriber.php` | Route subscriber |
| `web/modules/custom/lms_forum/templates/lms-forum-index.html.twig` | Forum index template |
| `web/modules/custom/lms_forum/templates/lms-forum-topic.html.twig` | Topic thread template |
| `web/modules/custom/lms_forum/templates/lms-forum-new-topic-page.html.twig` | New topic page |
| `web/modules/custom/lms_forum/templates/lms-forum-topic-form.html.twig` | Topic form template |
| `web/modules/custom/lms_forum/templates/lms-forum-edit-reply-page.html.twig` | Edit reply page |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_forum_topic` (`ForumTopic extends ContentEntityBase`)
- Key fields: `title`, `field_body`, `uid`, `field_course` (ref: `lms_course`), `field_is_pinned`, `field_is_locked`, `field_reply_count`, `status`

**Entity:** `lms_forum_reply` (`ForumReply extends ContentEntityBase`)
- Key fields: `field_topic` (ref: `lms_forum_topic`), `uid`, `field_body`, `field_parent_reply` (self-ref for threading)

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Entity schemas install; forum routes work |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Topic and reply CRUD functional |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_forum_topic` entity type registered
- [x] `lms_forum_reply` entity type registered
- [x] Topics scoped to a course via `field_course`
- [x] Replies threaded via `field_parent_reply`
- [x] Access handlers enforce topic/reply owner permissions
- [x] Forum index lists topics per course

---

## Known Debt / Issues

- Full forum UI polish (pagination, search, moderation) tracked in U-043 (SPEC).

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-005 | Course entity scopes topics |
| U-039 | `forum_reply` notification type triggered here |
| U-043 | Forum thread UI polish (SPEC) |

---

*End of U-040-forum-entities.md*
