# D03 — Story Spec: lms_forum

**Story ID:** lms_forum
**Story type:** module
**Title:** Course Discussion Forums — threaded topics, AJAX reply modal, best-answer marking
**Primary source:** `C-Design/C05-modules/lms_forum.md`
**Owning module(s):** lms_forum
**Service(s):** forum
**Epic:** E-07 (Communication)
**Contributes to Feature(s):** Communication, Course Management
**Design source(s):** `../C-Design/C05-modules/lms_forum.md`
**Round:** N/A
**Driver:** SA directive
**Status:** IN_PROGRESS
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** (pre-2026-04-15; implemented before D03 layer was materialized)
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_forum`

---

## Session Strategy

**Estimated session count:** 3
**Splitting strategy:**
- S1: Entity definitions (`lms_forum_topic`, `lms_forum_reply`); access handlers; routing
- S2: `LmsForumController` (active controller: index, viewTopic, addTopic, reply, markBestAnswer, editReply); `RouteSubscriber`
- S3: Forms (`ForumTopicForm`, `ForumReplyForm`, `ForumReplyDeleteForm`); templates; `lms_forum.module`

All sessions reached a buildable state. Status is IN_PROGRESS due to open bugs that block clean DONE state.

---

## Scope

Provides threaded course discussion forums backed by two custom entities:

- `lms_forum_topic`: topics with optional course scoping, category, pinned/locked/resolved flags, denormalized reply count and last-reply date, view counter.
- `lms_forum_reply`: threaded replies with parent reference, upvotes, and best-answer flag.
- `LmsForumController`: the **active** controller (6 methods); canonical topic route is overridden from the default entity view builder to `LmsForumController::viewTopic` via `RouteSubscriber`.
- `ForumReplyForm::save()` increments topic reply count and dispatches `forum_reply` notification via `lms_notification.manager` (soft dependency check).
- AJAX modal edit/delete for replies.
- Four Twig templates.

**Open issues blocking DONE:** see Known Bugs below.

Full design: [`../C-Design/C05-modules/lms_forum.md`](../../C-Design/C05-modules/lms_forum.md)

---

## Source Documents

| Source | Why loaded |
|---|---|
| `../C-Design/C05-modules/lms_forum.md` | Owning module design — authoritative reference |
| `../C-Design/C05-modules/lms_notification.md` | ForumReplyForm calls lms_notification.manager::dispatch('forum_reply') |
| `../C-Design/C05-modules/lms_course.md` | field_course on ForumTopic for course-scoped discussions |

---

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Authenticated users can create forum topics and replies | B03-features/08-communication.md | Acceptance |
| Topic owners and admins can pin, lock, and mark topics as resolved | B03-features/08-communication.md | Acceptance |
| Best-answer marking: topic owner or admin marks a reply as the best answer | B03-features/08-communication.md | Acceptance |
| Replies support threaded nesting via parent reference | B03-features/08-communication.md | Acceptance |
| Forum reply triggers notification to topic stakeholders | B03-features/08-communication.md | Integration |
| Topics can be scoped to a specific course | B03-features/01-course-management.md | Acceptance |

---

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_forum/lms_forum.info.yml` | Create | Module declaration; deps: lms_base, lms_course | — | S1 |
| `web/modules/custom/lms_forum/lms_forum.routing.yml` | Create | 6 explicit routes; canonical override handled by RouteSubscriber | — | S1 |
| `web/modules/custom/lms_forum/lms_forum.services.yml` | Create | lms_forum.route_subscriber (event_subscriber tag) | — | S1 |
| `web/modules/custom/lms_forum/lms_forum.module` | Create | hook_theme() for 4 theme hooks | — | S1 |
| `web/modules/custom/lms_forum/src/Entity/ForumTopic.php` | Create | lms_forum_topic entity; 13 base fields; EntityOwnerTrait; AdminHtmlRouteProvider | — | S1 |
| `web/modules/custom/lms_forum/src/Entity/ForumReply.php` | Create | lms_forum_reply entity; 8 base fields; EntityOwnerTrait | — | S1 |
| `web/modules/custom/lms_forum/src/Access/ForumTopicAccessControlHandler.php` | Create | view=access content; create=authenticated; update/delete=owner or admin | — | S1 |
| `web/modules/custom/lms_forum/src/Access/ForumReplyAccessControlHandler.php` | Create | Identical pattern to ForumTopicAccessControlHandler | — | S1 |
| `web/modules/custom/lms_forum/src/Controller/LmsForumController.php` | Create | ACTIVE: index, viewTopic, addTopic, reply, markBestAnswer, editReply, getUserInfo | — | S2 |
| `web/modules/custom/lms_forum/src/Controller/ForumController.php` | Create | DEAD CODE: viewTopic() throws Exception("HARD DEBUG!"); class not routed | — | S2 |
| `web/modules/custom/lms_forum/src/Routing/RouteSubscriber.php` | Create | Alters entity.lms_forum_topic.canonical → LmsForumController::viewTopic | — | S2 |
| `web/modules/custom/lms_forum/src/Form/ForumTopicForm.php` | Create | Hides internal fields; hides pinned/locked for non-admins; hides format selector | — | S3 |
| `web/modules/custom/lms_forum/src/Form/ForumReplyForm.php` | Create | AJAX save callback; updates topic reply_count + last_reply_date; triggers notification | — | S3 |
| `web/modules/custom/lms_forum/src/Form/ForumReplyDeleteForm.php` | Create | AJAX delete callback: closes modal, removes DOM element | — | S3 |
| `web/modules/custom/lms_forum/templates/lms-forum-index.html.twig` | Create | vars: topics, new_topic_form | — | S3 |
| `web/modules/custom/lms_forum/templates/lms-forum-topic.html.twig` | Create | vars: topic, replies, reply_form, is_teaser_mode, total_replies | — | S3 |
| `web/modules/custom/lms_forum/templates/lms-forum-new-topic-page.html.twig` | Create | vars: form | — | S3 |
| `web/modules/custom/lms_forum/templates/lms-forum-edit-reply-page.html.twig` | Create | vars: form | — | S3 |
| `web/modules/custom/lms_forum/templates/lms-forum-topic-form.html.twig` | Create | Present in templates/ but NOT registered in hook_theme() — orphaned file | — | S3 |

---

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| lms_base | Internal module | Shared entity traits |
| lms_course | Internal module | field_course scoping on topics |
| lms_notification (optional runtime) | Internal module | ForumReplyForm::save() dispatches forum_reply notification if module installed |
| forum_category taxonomy | Config | field_category on ForumTopic; vocabulary must pre-exist |

---

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| RouteSubscriber pattern | Canonical topic route override | RouteSubscriber alters entity.lms_forum_topic.canonical; service registered in services.yml |
| Soft dependency pattern | lms_notification dispatch | ForumReplyForm checks module_exists / class_exists before calling manager |
| AJAX modal | ForumReplyForm, ForumReplyDeleteForm | ajaxSaveCallback (HtmlCommand); ajaxDeleteCallback (modal close + DOM remove) |

---

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission |
|---|---|---|---|---|
| Forum index | topic list, reply counts, author, avatar, category | lms_forum_topic entity | `/forum` | access content |
| Topic view | topic body, replies, best-answer mark, edit/delete ops | lms_forum_topic, lms_forum_reply | `/forum/{topic}` | authenticated (owner/admin for write ops) |
| New topic form | name, body, category, course scope | lms_forum_topic entity | `/forum/new` | authenticated |
| Reply form | body | lms_forum_reply entity | inline / modal | authenticated |

**Hardcoded content exceptions:** None.

---

## Feature Flags Introduced or Read

None. No feature flags in this module.

---

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-FORUM-001 | Integration | Authenticated user creates topic; topic appears in index | lms_forum_topic created; index shows it |
| T-FORUM-002 | Integration | Authenticated user posts reply; topic reply_count incremented | field_replies_count +1 |
| T-FORUM-003 | Integration | Topic owner marks reply as best answer; field_resolved=TRUE on topic | markBestAnswer flow correct |
| T-FORUM-004 | Integration | Non-owner cannot mark best answer on another user's topic | Access control enforced (BUG-FORUM-03 — current gap) |
| T-FORUM-005 | Unit | ForumController::viewTopic() is not reachable via any route | RouteSubscriber correctly overrides canonical |
| T-FORUM-006 | Integration | AJAX reply save updates DOM without page reload | HtmlCommand executes |

---

## Gate Results

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Entities install; routes resolve; forum index renders | — | pre-2026-04-15 |
| G2 Lint | PASS | No coding standard violations | — | pre-2026-04-15 |
| G3 Tests | PASS | Topic CRUD, reply CRUD, best-answer marking functional | — | pre-2026-04-15 |
| G4 Adversarial | FAIL | BUG-FORUM-01 (dead code throw), BUG-FORUM-02 (debug message), BUG-FORUM-03 (access gap) open | — | pre-2026-04-15 |

---

## Notes

### Known Bugs — blocks DONE status

| ID | Severity | Description |
|----|----------|-------------|
| BUG-FORUM-01 | Critical | `ForumController::viewTopic()` begins with `throw new \Exception("HARD DEBUG!")`. Class is not currently routed but is a live 500-risk if routing is misconfigured. File must be deleted. |
| BUG-FORUM-02 | High | `LmsForumController::viewTopic()` (the active controller) calls `\Drupal::messenger()->addMessage("FRESH CONTROLLER LOADED!")` — debug message shown to all users on every topic view. Must be removed. |
| BUG-FORUM-03 | Medium | `lms_forum.reply.best_answer` route requires only `_permission: access content` — any authenticated user can mark any reply as best answer on any topic (not just the topic owner or instructor). Access should be tightened. |

### Technical Debt

| ID | Severity | Description |
|----|----------|-------------|
| DEBT-FORUM-01 | Low | `lms-forum-topic-form.html.twig` is present in templates/ but not registered in `hook_theme()`. Either register it or delete it. |

### Resolution Path to DONE

1. Delete `ForumController.php` (BUG-FORUM-01)
2. Remove debug `addMessage` in `LmsForumController::viewTopic` (BUG-FORUM-02)
3. Tighten `markBestAnswer` route or add owner/admin check in controller method (BUG-FORUM-03)
4. Resolve DEBT-FORUM-01 (register or delete `lms-forum-topic-form.html.twig`)

These bugs feed [E02 Issues](../../E-Quality/E02-issues/).
