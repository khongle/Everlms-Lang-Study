# D03 — Unit Spec: Forum Thread UI + Permissions

**Unit ID:** U-043
**Group:** G-COMMUNICATION
**Title:** Forum thread UI + permissions
**Status:** SPEC
**Priority:** P1
**Assigned:** unassigned

---

## Scope

Implement the forum UI: topic list, thread view (topic + replies), create/reply forms, edit/delete own posts, and moderator controls. Role-based permissions: students can post/edit own; instructors can moderate; admins have full control.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Controller/ForumController.php` | Create | lms_forum |
| `src/Form/ForumTopicForm.php` | Create | lms_forum |
| `src/Form/ForumReplyForm.php` | Create | lms_forum |
| `templates/forum-list.html.twig` | Create | lms_forum |
| `templates/forum-thread.html.twig` | Create | lms_forum |
| `lms_forum.routing.yml` | Modify | lms_forum |
| `lms_forum.permissions.yml` | Create | lms_forum |

---

## Interfaces / Services

- Routes: `GET /course/{id}/forum`, `GET /forum/topic/{id}`, `POST /forum/topic/new`, `POST /forum/topic/{id}/reply`
- Access: must be enrolled in course to access course forum
- `ForumTopic` + `ForumReply` entities (U-040)
- Pagination: 20 replies per page

---

## Acceptance Criteria

- [ ] Course forum page lists topics (most recent first, pinned at top)
- [ ] Thread view shows topic + nested replies
- [ ] Enrolled student can create topic and reply
- [ ] User can edit/delete own topic or reply (within 24h window or unlimited for moderator)
- [ ] Instructor/admin can delete any post and pin/unpin topics
- [ ] Non-enrolled user sees forum but cannot post (prompt to enroll)
- [ ] Forum accessible only to enrolled users or public depending on course setting

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Permission matrix test |
| G4 Adversarial | PENDING | IDOR on edit/delete routes |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-040 | ForumTopic + ForumReply entities |
| U-021 | UserCourse — enrollment check |
| U-042 | Notification dispatch on new reply (soft dep) |

---

**Feature Flag:** None

*End of U-043-forum-thread-ui.md*
