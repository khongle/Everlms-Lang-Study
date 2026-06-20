---
id: F-008
name: Communication & Community
priority: Must
status: APPROVED
parent-requirements: [BR-008]
owning_modules: [lms_notification, lms_forum, lms_rating, lms_note, lms_faq]
primary_source: C05
story_anchors: []
---

# F-008 — Communication & Community

## User story

As a **learner**, I want to receive in-app notifications when I pass a quiz or receive a certificate, discuss course topics in a forum, leave notes on lessons I'm watching, and rate courses I've completed, so that I stay informed and engaged throughout my learning journey.

## Description

EverLMS has five communication subsystems at different maturity levels:

**Notifications (`lms_notification`)** — in-app channel is fully implemented. `NotificationManager::dispatch()` creates `lms_notification` entities, routes them by the `field_channel` field (in_app / email / push). The `NotificationBellBlock` plugin shows unread count + latest 5 in the header. `NotificationController::listNotifications` marks notifications read on page load. `BroadcastForm` sends announcements to all active users in batches of 50. `NotificationPreferencesForm` lets users manage per-event opt-in across 9 event types. **Email and push channels are stubs** — the email branch only writes an info log with a TODO comment ("Implement Drupal queue creation for mail"); the push branch logs only (FCM foundation comment). SMTP module is installed but disabled (`smtp.settings.yml`, `smtp_enabled: false`).

**Forum (`lms_forum`)** — active but has two quality issues. The live controller is `LmsForumController` (confirmed via `RouteSubscriber` override of the canonical route). Features: topic listing, threaded replies, best-answer marking, pinning/locking (admin), view count, resolved flag, category taxonomy (`forum_category` vocabulary), AJAX modal reply editing. However, `LmsForumController::viewTopic` has a debug `messenger()->addMessage("FRESH CONTROLLER LOADED!")` statement committed in production. A dead class `ForumController` remains in source with `throw new \Exception("HARD DEBUG!")` at the top of `viewTopic()` — if ever accidentally routed to, this would cause a 500 error. `ForumReplyForm::save()` dispatches a `forum_reply` notification via `lms_notification.manager` when the module is installed.

**Lesson comments (`lms_lesson.field_comments`)** — Drupal core comment system (type: `lesson_comment`) attached to each lesson. `UserPortalController::lessonView` loads the comment field with the `ajax_comments` library. The `lms_progress.module hook_form_alter` for comment forms has a known side-effect: it grants all text formats to the `authenticated` role by saving the Role entity at form-alter time — a security/performance concern.

**Ratings & Reviews (`lms_rating`)** — `RatingForm` (FormBase) allows enrolled learners to submit 1-5 star + text review. New ratings default to `field_approved=FALSE` (pending moderation). `RatingManagerController` provides an admin list at `/workspace/ratings` with approve/feature/delete actions. **Three routes are missing** (`lms_rating.collection`, `lms_rating.toggle_approve`, `lms_rating.toggle_featured`) — `RatingController` and `RatingListBuilder` reference these and will throw `RouteNotFoundException` if triggered. The `lms_admin_ratings` theme hook used by `RatingManagerController::list()` is not registered in `lms_rating` (no `.module` file); it must be defined in `lms_course.module hook_theme()`.

**Notes (`lms_note`)** — per-learner, per-lesson notes with optional video timestamp. REST-style JSON API at `/api/lms/note/save|delete|get`. `/my-notes` renders notes grouped by course. Clean implementation with cache tag invalidation per `lms_note:{lesson_id}:{uid}`.

**FAQ (`lms_faq`)** — 3-scope FAQ entity (global / course / lesson via `field_scope`). No status/publish field — all FAQs are always visible. Admin CRUD via standard Drupal entity routes + `WorkspaceContentController` in workspace. Displayed on homepage (global scope) and course detail pages (course scope filtered by `field_course`).

## In scope

### Notifications (as-built — in-app channel complete; email/push stubs)

- `lms_notification` entity with fields: `field_type` (10 allowed values), `field_channel` (in_app/email/push, UNLIMITED), `field_title`, `field_message`, `field_link`, `field_read`, `field_reference_type`, `field_reference_id`.
- In-app notification creation and delivery — fully works.
- `NotificationBellBlock` plugin in header (unread count, top 5 latest); placed in lms_theme and lms_classic header region.
- `/notifications` list page with type filter; marks unread on visit; pager(15).
- `/api/lms/notifications/unread-count` JSON endpoint (public access, returns 0 for anonymous).
- Admin broadcast at `/admin/lms/notifications/broadcast` — batch sends `announcement` type to all active users.
- User preferences at `/manage/settings/notifications` — per-event opt-in/out for in-app and email (preferences stored; email not sent despite checkbox).
- Drush command `lms_notification:remind-inactive` — sends reminder to users inactive 7–14 days (up to 50 per run).
- `hook_cron` purges read notifications older than 90 days (100 per run).
- Event subscriber: dispatches `quiz_result` notification on `QuizPassedEvent`.
- Notification types supported: enrollment, quiz_result, certificate, payment, forum_reply, membership_expiry, new_lesson, announcement, reminder, system.
- **Email notifications: stub** — `NotificationManager::dispatch()` writes an info log only; no queue item, no Drupal mail call.
- **Push notifications: stub** — info log only; no FCM token storage, no HTTP call to Firebase.

### Forum (as-built — functional with known bugs)

- `lms_forum_topic` entity: name, body, `field_course` (optional course scope), `field_replies_count` (denormalized), `field_pinned`, `field_locked`, `field_category` (forum_category taxonomy), `field_last_reply_date`, `field_views`, `field_resolved`.
- `lms_forum_reply` entity: body, `field_topic`, `field_parent` (threading), `field_best_answer`, `field_upvotes`.
- Routes: `/forum` (index), `/forum/new` (add topic), `/forum/{topic}` (view + replies), `/forum/{topic}/reply`, best-answer marking, reply edit (AJAX modal), reply delete (AJAX modal).
- Access: authenticated users can create; owners can edit/delete their own; admin (`administer lms_course`) has full access; anonymous gets teaser mode only.
- ForumReplyForm dispatches `forum_reply` notification via lms_notification on save.
- `RouteSubscriber` overrides `entity.lms_forum_topic.canonical` to `LmsForumController::viewTopic`.
- **Known bug (dead code):** `ForumController::viewTopic()` starts with `throw new \Exception("HARD DEBUG!")` — dead code, would 500 if routed to.
- **Known bug (debug artifact):** `LmsForumController::viewTopic()` has `messenger()->addMessage("FRESH CONTROLLER LOADED!")` in production.

### Lesson comments (as-built)

- Drupal core comment field (`field_comments`, type `lesson_comment`) on every `lms_lesson`, default status OPEN.
- `ajax_comments` library attached in `lessonView()` for AJAX comment posting without page reload.
- Threaded replies via Drupal core comment parent/child.
- **Known bug:** `lms_progress.module hook_form_alter` saves the `authenticated` Role entity to grant text formats at form-alter time — grants permissions unconditionally to all authenticated users, bypassing role configuration.

### Ratings & Reviews (as-built — partially broken routes)

- 1-5 star integer rating + text review (`lms_rating` entity).
- `RatingForm` prevents duplicate ratings (existing-rating check).
- New ratings default to `field_approved=FALSE`; admin approves via `/workspace/ratings/{id}/approve`.
- `field_is_featured` boolean for pinning reviews; auto-approved when featured.
- Admin actions at `/workspace/ratings/{id}/{approve|feature|delete}`.
- `RatingBlock` plugin renders `RatingForm` as a block.
- **Known bug:** 3 missing routes — `lms_rating.collection`, `lms_rating.toggle_approve`, `lms_rating.toggle_featured` — cause `RouteNotFoundException` if `RatingListBuilder` operations are rendered.
- `lms_admin_ratings` theme not registered in lms_rating module (no .module file); depends on external registration.

### Notes (as-built — fully implemented)

- `lms_note` entity: uid, `field_course`, `field_lesson`, `field_body`, `field_video_timestamp` (seconds).
- JSON API: POST `/api/lms/note/save` (upsert), POST `/api/lms/note/delete`, GET `/api/lms/note/get?lesson_id=`.
- `/my-notes` — notes grouped by course; sorted by `changed` DESC.
- Cache invalidation via `lms_note:{lesson_id}:{uid}` tag.

### FAQ (as-built — entity-only, no UI status field)

- 3 scopes: global (homepage), course (course detail page, filtered by `field_course`), lesson (available but no dedicated rendering confirmed in facts base).
- `field_category` (faq_category taxonomy) for grouping.
- `weight` field for sort order.
- No `status` field — no draft/unpublished state.
- Admin CRUD via `WorkspaceContentController::faqCreateForm/faqEditForm/faqView` in workspace.

## Out of scope

- Email notifications: functional email delivery — only in-app channel is live.
- Push notifications: mobile push via FCM — not implemented; stub only.
- Forum search — no search index or full-text search on forum topics.
- Forum moderation tools (flag/report, admin approve queue) — not implemented.
- Lesson comment moderation queue — Drupal core `skip comment approval` permission granted to all authenticated users; all comments appear immediately.
- Comment like/upvote on lesson comments — not implemented.
- Review response by instructor — no reply-to-review feature.
- Sort/filter reviews on course detail — `RatingManagerController` list is admin-only; learner-facing review listing is display-only.
- FAQ search — no search on FAQ entries.
- Lesson-scope FAQ rendering — `field_scope=lesson` is stored but not confirmed rendered anywhere in the learner UI.

## Acceptance summary

A learner who passes a quiz sees an in-app notification in the bell icon. They can browse forum topics at `/forum`, post a reply (logged in), and mark a reply as best answer if they own the topic. They can leave a timestamped note on a lesson at `/learn/{course}/{lesson}` and retrieve it from `/my-notes`. They can rate a completed course and see the rating pending moderation. An admin can send a broadcast announcement at `/admin/lms/notifications/broadcast` and moderate ratings at `/workspace/ratings`. Email and push delivery are not functional in the current build. Full Given/When/Then in `B05-acceptance-criteria/F-008.md`.

## Content ownership / editability

| Visible content family | Business owner | Editor role | Expected edit surface | Notes |
|---|---|---|---|---|
| Notification text (title, message) | Platform / Admin | administrator | Created programmatically by event/manager; broadcast via BroadcastForm | No UI for per-event template editing |
| Notification preferences | Learner (self) | authenticated | `/manage/settings/notifications` | Per-event in-app/email checkboxes |
| Forum topics & replies | Learner (self) | authenticated (own), administrator | `/forum/new`, `/forum/{topic}/reply` | Admin can pin/lock; owner can edit/delete |
| Course ratings & reviews | Learner (self) | authenticated (own), administrator | Course detail page via RatingBlock | Pending approval by default |
| Lesson notes | Learner (self) | authenticated (own) | In-lesson note widget at `/learn/{course}/{lesson}` | JSON API save/delete/get |
| FAQ entries | Content team | manager, administrator | `/workspace/content/faqs/*` | 3 scopes; no publish flag |

## Dependencies (requirement-level only)

| Type | Reference | Reason |
|---|---|---|
| Feature | F-002 | `QuizPassedEvent` triggers quiz_result notification |
| Feature | F-006 | Certificate issuance can trigger certificate notification |
| Feature | F-003 | Lesson completion triggers new_lesson / reminder notifications |
| Feature | F-001 | Course enrollment triggers enrollment notification |
| Workflow | WF-001 | Enrollment workflow dispatches enrollment notification |

## Open questions

- [ ] Email notifications: what Drupal mail plugin (SMTP, SendGrid, Mailgun) should be used in production? SMTP module is installed but disabled.
- [ ] Push notifications: should FCM integration be scoped for the mobile app MVP, or deferred? Token storage on user entity not yet designed.
- [ ] `ForumController` dead code with `throw new \Exception`: safe to delete? Confirm no route references it before removing.
- [ ] `LmsForumController::viewTopic` debug `addMessage` — should be removed before production; is there a test covering this path?
- [ ] Missing lms_rating routes (`lms_rating.collection`, `lms_rating.toggle_approve`, `lms_rating.toggle_featured`): add the 3 routes or remove the dead RatingController/RatingListBuilder code?
- [ ] Lesson comment form hook_form_alter grants all text formats unconditionally to authenticated role — should this be a targeted permission check instead?
