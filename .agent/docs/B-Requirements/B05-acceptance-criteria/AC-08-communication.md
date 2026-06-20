---
feature: F-008
status: DRAFT
---

# B05 — Acceptance Criteria for F-008 — Communication

> Per-feature Given/When/Then criteria grounded in code. Modules covered: `lms_forum`, `lms_rating`, `lms_notification`, `lms_note`.
>
> Known bugs that affect testability are called out inline.

---

## AC-077: In-app notification delivery on quiz pass

**Priority:** Must
**Given** a student has in-app notifications enabled (default) for `quiz_result` event type
**When** the student passes a quiz (`QuizPassedEvent` is dispatched by `lms_quiz`)
**Then** `NotificationEventSubscriber::onQuizPassed()` in `lms_notification` calls `NotificationManager::dispatch('quiz_result', ...)` which creates a `lms_notification` entity with `field_channel=['in_app']` and `field_read=FALSE`
**And** the notification bell block (`lms_notification_bell_block` in the `header` region of both `lms_theme` and `lms_classic`) increments its unread count on next render (cache tagged `lms_notification_list`)

---

## AC-078: Email and push notification channels are stubs (as-built — stub)

**Priority:** Must (document current state)
**Given** a user has `email` or `push` channel enabled in their notification preferences
**When** `NotificationManager::dispatch()` is called with those channels
**Then** no email is sent and no push message is delivered; only an info-level log entry is written (`TODO: Implement Drupal queue creation for mail` comment in source)
**And** the in-app notification is still created if `in_app` is also in the channel list

> **Status:** email and push channels are **stubs** as-built (2026-06-20). This is a known gap.

---

## AC-079: Notification preference page persists per-user settings

**Priority:** Must
**Given** a logged-in user navigates to `/manage/settings/notifications`
**When** they uncheck "Email" for the `forum_reply` event type and save
**Then** `NotificationPreferencesForm::submitForm()` writes `pref_forum_reply` to the `user.data` service for the current user
**And** subsequent `NotificationManager::dispatch('forum_reply', ...)` calls for that user skip the email channel

---

## AC-080: Forum topic creation and listing

**Priority:** Must
**Given** an authenticated user (any role) navigates to `/forum`
**When** they submit a valid new topic (name required, field_body optional) via `/forum/new`
**Then** `ForumTopicForm::save()` creates a `lms_forum_topic` entity; the user is redirected to the canonical topic route (`/forum/{lms_forum_topic}`)
**And** the topic appears in `LmsForumController::index()` listing sorted by `created` DESC with reply_count and author info (first_name/last_name with ui-avatars.com fallback)

---

## AC-081: Forum topic view — debug message present (as-built — bug)

**Priority:** Must (document current state)
**Given** any user navigates to a forum topic canonical URL (`/forum/{lms_forum_topic}`)
**When** `LmsForumController::viewTopic()` executes
**Then** the topic content and replies render correctly
**And** a debug status message ("FRESH CONTROLLER LOADED! ...") is shown via `\Drupal::messenger()->addMessage()` — this is a **live bug** that must be removed before production

> **Status:** Debug message left in production code. The dead `ForumController::viewTopic()` (which contains `throw new \Exception("HARD DEBUG!")`) is not routed but remains dangerous if ever assigned to a route.

---

## AC-082: Forum reply creation and topic counter update

**Priority:** Must
**Given** an authenticated user is viewing a forum topic
**When** they submit a reply via `ForumReplyForm` at `/forum/{lms_forum_topic}/reply`
**Then** a `lms_forum_reply` entity is created with `field_topic` set to the parent topic; the topic's `field_replies_count` and `field_last_reply_date` are updated
**And** if `lms_notification` is installed, `lms_notification.manager::dispatch('forum_reply', ...)` is called for the topic owner

---

## AC-083: Forum best-answer marking

**Priority:** Should
**Given** the topic owner or a user with `administer lms_course` permission views a forum topic
**When** they click "Mark Best Answer" (`/forum/{lms_forum_topic}/reply/{lms_forum_reply}/best`)
**Then** `LmsForumController::markBestAnswer()` sets `field_best_answer=TRUE` on the reply and `field_resolved=TRUE` on the topic (if requester is owner or admin); the user is redirected to the topic canonical URL
**And** replies can be edited via AJAX modal (`/forum/reply/{lms_forum_reply}/edit`); the `ForumReplyForm::ajaxSaveCallback` updates the reply body in DOM via `HtmlCommand`

---

## AC-084: Course rating submission and duplicate prevention

**Priority:** Must
**Given** an authenticated user has not previously rated a specific course
**When** they submit a star rating (1–5) and optional review text via `RatingForm`
**Then** a new `lms_rating` entity is created with `field_approved=FALSE` (pending moderation) and `field_is_featured=FALSE`
**And** a "pending moderation" message is displayed; `field_score` is stored as integer 1–5

**Given** the same user has already submitted a rating for the same course
**When** they attempt to submit another rating
**Then** `RatingForm::buildForm()` detects the existing rating and prevents duplicate submission (no new entity created)

---

## AC-085: Rating admin approve / feature / delete via executeAction

**Priority:** Must
**Given** a user with `administer lms_course` is at `/workspace/ratings`
**When** they use the action path `/workspace/ratings/{lms_rating}/{action}` with action = `approve`, `feature`, or `delete`
**Then** `RatingManagerController::executeAction()` toggles `field_approved` (approve), toggles `field_is_featured` and auto-approves when featuring, or deletes the entity; redirects to `lms_rating.admin_list`

---

## AC-086: Rating list operations — missing routes bug (as-built — bug)

**Priority:** Must (document current state)
**Given** `RatingListBuilder::getOperations()` defines operations for `toggle_approve` and `toggle_featured`
**When** the admin entity list page renders these operation links
**Then** clicking either link throws `RouteNotFoundException` because routes `lms_rating.toggle_approve` and `lms_rating.toggle_featured` do not exist in `lms_rating.routing.yml`
**And** the menu link `lms_rating.collection` also references a non-existent route

> **Status:** 3 missing routes — active bug. `RatingManagerController` path works; `RatingListBuilder` operations and `RatingController` redirects do not.

---

## AC-087: Student note — save, update, and delete per lesson

**Priority:** Must
**Given** a logged-in student is on a lesson page with a note widget
**When** they POST to `/api/lms/note/save` with `{lesson_id, course_id, note_text, video_timestamp}`
**Then** `NoteController::save()` upserts a `lms_note` entity (created or updated); cache tag `lms_note:{lesson_id}:{uid}` is invalidated; response returns `{status: created|updated, id}`

**When** the student POSTs to `/api/lms/note/delete` with `{lesson_id}`
**Then** all `lms_note` records for that user+lesson are deleted; cache tag is invalidated; response returns `{status: deleted|not_found}`

---

## AC-088: Broadcast notification to all active users

**Priority:** Must
**Given** an admin with `administer lms_course` navigates to `/admin/lms/notifications/broadcast`
**When** they submit title + message via `BroadcastForm`
**Then** a batch operation iterates all active users in batches of 50; `NotificationManager::dispatch('announcement', ...)` is called for each user; in-app notifications are created
**And** email channel is logged only (stub — not sent); old read notifications are auto-cleaned by `hook_cron()` (read AND older than 90 days, 100 per run)

---

## Local traceability (F-008 only)

| AC ID  | Test Case | Implementing Story / Module            | Status  |
|--------|-----------|----------------------------------------|---------|
| AC-077 | T-077     | — / lms_notification, lms_quiz         | PENDING |
| AC-078 | T-078     | — / lms_notification (stub)            | PENDING |
| AC-079 | T-079     | — / lms_notification                   | PENDING |
| AC-080 | T-080     | — / lms_forum                          | PENDING |
| AC-081 | T-081     | — / lms_forum (bug: debug message)     | PENDING |
| AC-082 | T-082     | — / lms_forum                          | PENDING |
| AC-083 | T-083     | — / lms_forum                          | PENDING |
| AC-084 | T-084     | — / lms_rating                         | PENDING |
| AC-085 | T-085     | — / lms_rating (RatingManagerController) | PENDING |
| AC-086 | T-086     | — / lms_rating (bug: missing routes)   | PENDING |
| AC-087 | T-087     | — / lms_note                           | PENDING |
| AC-088 | T-088     | — / lms_notification                   | PENDING |

> Cross-feature traceability lives in `E05-traceability.md`.
