---
id: F-003
name: Learning Progress Tracking
priority: Must
status: APPROVED
parent-requirements: [BR-004]
owning_modules: [lms_progress, lms_note, lms_forum, lms_rating, lms_notification]
primary_source: C05
story_anchors: []
---

# F-003 — Learning Progress Tracking

## User story

As a **learner**, I want the system to track which lessons I have completed and show me my overall course progress, so that I can resume learning from where I left off and know when I have finished a course.

As an **instructor or administrator**, I want to see which courses learners are enrolled in and their completion status, so that I can monitor engagement and intervene when needed.

## Description

Learning Progress covers the full lifecycle of a learner's journey through a course: enrollment, per-lesson completion, aggregate course-progress calculation, and the learner-facing portal (dashboard, my-courses, lesson view, my-certificates). It also includes supplementary engagement features: in-lesson notes, a discussion forum, course ratings, and in-app notifications.

The core progress model uses two custom entities: `lms_user_course` (enrollment + course-level progress percentage + status) and `lms_user_lesson` (per-lesson completion status + video position). These are managed by `lms_progress` through `UserPortalController` and `ProgressApiController`. Enrollment can happen via `CourseEnrollmentController` (free courses), via order completion (paid/membership), or automatically on first lesson visit (`lessonView()` auto-enrolls if no record found).

Supplementary engagement is provided by: `lms_note` (per-lesson notes with video timestamp, CRUD API), `lms_forum` (threaded forum topics with reply editing, best-answer flagging, AJAX modal editing), `lms_rating` (star rating + review with admin moderation), and `lms_notification` (in-app notification channel — fully implemented; email and push channels are stubs).

## In scope

**Progress tracking:**
- `lms_user_course` entity: enrollment record per user-course pair; fields `field_progress` (decimal 0–100), `field_enrolled_at`, `field_completed_at`, `field_status` (enrolled / in_progress / completed).
- `lms_user_lesson` entity: lesson completion record; fields `field_status` (not_started / in_progress / completed), `field_video_position` (last playback position in seconds), `field_completed_at`.
- `ProgressApiController::markComplete()` POST endpoint (`/api/lms/progress/mark-complete`): upserts `lms_user_lesson` as completed; recalculates `lms_user_course.field_progress` as `(completed_lesson_count / total_lesson_count) * 100`; sets `lms_user_course.field_status = completed` when 100%; returns JSON `{success, lesson_completed, course_completed, progress, next_lesson_url}`.
- `UserPortalController::lessonView()` (`/learn/{lms_course}/{lms_lesson}`): auto-enrollment on first visit; loads curriculum with completion flags per lesson; resolves video embed URL (YouTube/Vimeo/direct); handles all lesson types (pdf, pptx, video, h5p, zoom, scorm); loads existing note; loads lesson comments (Drupal comment field with `ajax_comments` library); renders `lms_lesson_view` template.
- Learner dashboard (`/dashboard`): enrolled course stats (enrolled/in_progress/completed/certificates count); "continue URL" to next incomplete lesson; recent courses.
- My Courses page (`/my-courses`): per-course detail array with progress and status.
- My Certificates page (`/my-certificates`): loads all `lms_user_certificate` for current user.
- `hook_form_alter` for `lesson_comment`: hides subject/author/format fields, wraps in AJAX div, grants text formats (workaround — see known issues).
- Comment redirect custom submit handler (`lms_progress_comment_form_submit_redirect()`).

**Student notes (`lms_note`):**
- `lms_note` entity: body, course reference, lesson reference, video timestamp (seconds), owner.
- REST-style JSON API at `/api/lms/note/save` (POST, upsert), `/api/lms/note/delete` (POST), `/api/lms/note/get` (GET with `?lesson_id=`).
- My Notes page (`/my-notes`): notes grouped by course, sorted by changed DESC.
- Cache invalidation via `lms_note:{lesson_id}:{uid}` cache tag.

**Discussion forum (`lms_forum`):**
- `lms_forum_topic` entity: name, body, course reference (optional scoping), category (forum_category vocab), pinned/locked/resolved flags, reply count (denormalised), last reply date, view count.
- `lms_forum_reply` entity: body (required), topic reference, parent reply reference (threading), best-answer flag, upvotes count.
- Forum index (`/forum`), topic view (`/forum/{lms_forum_topic}`), topic creation (`/forum/new`), reply (`/forum/{lms_forum_topic}/reply`), best-answer marking, reply edit/delete (AJAX modal).
- `RouteSubscriber` overrides canonical topic route to `LmsForumController::viewTopic`.
- `ForumReplyForm::save()` dispatches `forum_reply` notification via `lms_notification.manager` when that module is installed.

**Course ratings (`lms_rating`):**
- `lms_rating` entity: course reference, score (1–5), review text, approved flag (default TRUE), featured flag.
- `RatingForm`: prevents duplicate ratings per user-course pair; submission sets `field_approved = FALSE` (pending moderation).
- Admin ratings list (`/workspace/ratings`) with approve/feature/delete actions via `RatingManagerController`.
- `RatingBlock` Drupal block plugin renders the rating form.

**In-app notifications (`lms_notification`):**
- `lms_notification` entity: type (10 allowed values), channel (in_app/email/push), title, message, link, read flag, polymorphic reference (reference_type + reference_id), recipient.
- `NotificationManager::dispatch()`: in-app channel — creates entity (as-built); email — logs only (stub); push — logs only (stub).
- `NotificationEventSubscriber`: subscribes to `QuizPassedEvent`; dispatches `quiz_result` notification to learner.
- Notification list page (`/notifications`) with type filter; marks read on page visit.
- Notification bell block (`lms_notification_bell_block`) showing unread count and top 5 latest.
- Preferences form (`/manage/settings/notifications`): 9 event types, in-app + email checkboxes per type, stored via `user.data`.
- Broadcast form (`/admin/lms/notifications/broadcast`): batch-sends announcement to all active users.
- Drush command `lms_notification:remind-inactive`: sends reminder to users inactive 7–14 days.
- Cron: deletes read notifications older than 90 days (100 per cron run).

## Out of scope

- Chapter-level progress entity (`lms_user_chapter`) — does not exist; chapter completion is inferred from lesson completion but has no separate stored record.
- Time-spent tracking per lesson — no server-side timer; `field_video_position` tracks seconds into video only.
- SCORM xAPI/cmi.completion_status tracking in the LMS database — SCORM is extracted and launched via `index.html`; completion is not reported back to the LMS.
- H5P interaction result storage in LMS entities — H5P embed only; no H5P xAPI result capture.
- Instructor student-roster and per-student progress view (no instructor-facing progress page exists).
- Email and push notification channels (both are logged-only stubs; no mail queue or FCM integration).
- Membership expiry auto-renewal via webhook (logged, deferred — see `lms_membership.module hook_cron` comment).
- `lms_forum` `ForumController` class (dead code with `throw new \Exception("HARD DEBUG!")` — not routed, not used).
- `RatingController` approve/feature actions and `RatingListBuilder` operations are broken due to missing routes (`lms_rating.collection`, `lms_rating.toggle_approve`, `lms_rating.toggle_featured`) — these will throw `RouteNotFoundException`.

## Acceptance summary

After enrolling in a course, a learner can open a lesson, mark it complete (via the `/api/lms/progress/mark-complete` POST), and see their dashboard/my-courses progress percentage update. Notes can be saved and retrieved per lesson. The notification bell shows in-app notifications. Full Given/When/Then lives in `B05-acceptance-criteria/F-003.md`.

## Content ownership / editability

| Visible content family | Business owner | Editor role | Expected edit surface | Notes |
|---|---|---|---|---|
| Forum topics and replies | Learners, Instructors | authenticated users (own content), administrators | `/forum`, `/forum/new`, `/forum/{topic}/reply` | Owner can edit/delete own posts; admin has full access |
| Course reviews (ratings) | Learners | authenticated users | Course detail page (RatingForm) | `field_approved` defaults FALSE on submission; admin approves |
| Notification content (broadcast) | Platform admin | administrator | `/admin/lms/notifications/broadcast` | Batch-sent to all active users |
| Notification preferences | Learner | authenticated (own) | `/manage/settings/notifications` | Per-user via user.data service |

## Dependencies (requirement-level only)

| Type | Reference | Reason |
|---|---|---|
| Feature | F-001 | Enrollment records reference `lms_course`; lesson view requires `lms_lesson` and curriculum structure |
| Feature | F-002 | `QuizPassedEvent` from F-002 triggers quiz_result notification; lesson completion may gate on quiz pass |
| Feature | F-004 | Paid course enrollment is triggered by order completion (lms_order hook_entity_update) |
| Workflow | WF-001 | Enrollment and learning workflow; lesson view and mark-complete are core steps |
| Workflow | WF-003 | Certificate issuance runs after quiz pass event; `myCertificates()` displays results |

## Backing modules and entities

| Module | Role | Maturity |
|---|---|---|
| `lms_progress` | UserCourse + UserLesson entities, UserPortalController, ProgressApiController, lesson-comment form alter | as-built (minor bugs — see below) |
| `lms_note` | Note entity, NoteController (JSON API), My Notes page | as-built |
| `lms_forum` | ForumTopic + ForumReply entities, LmsForumController, AJAX reply editing, best-answer | as-built (debug statement left in viewTopic; ForumController dead code) |
| `lms_rating` | Rating entity, RatingForm, RatingManagerController | partially as-built (approve/feature routes missing — see bugs) |
| `lms_notification` | Notification entity, NotificationManager (in_app only), NotificationBellBlock, BroadcastForm, Drush command | as-built for in_app; email/push are stubs |

| Entity | Key fields | Maturity |
|---|---|---|
| `lms_user_course` | field_course, field_progress (0–100), field_enrolled_at, field_completed_at, field_status (enrolled/in_progress/completed) | as-built |
| `lms_user_lesson` | field_lesson, field_status (not_started/in_progress/completed), field_video_position, field_completed_at | as-built |
| `lms_note` | field_course, field_lesson, field_body, field_video_timestamp | as-built |
| `lms_forum_topic` | name, field_body, field_course, field_category, field_pinned, field_locked, field_resolved, field_replies_count, field_views | as-built |
| `lms_forum_reply` | field_topic, field_body, field_parent (threading), field_best_answer, field_upvotes | as-built |
| `lms_rating` | field_course, field_score (1–5), field_review, field_approved, field_is_featured | as-built (broken routes on RatingController/RatingListBuilder) |
| `lms_notification` | field_type (10 values), field_channel, field_title, field_message, field_link, field_read, field_reference_type, field_reference_id | as-built |

## Priority matrix

| Capability | Priority | Maturity |
|---|---|---|
| Course enrollment entity (`lms_user_course`) | Must | as-built |
| Lesson progress entity (`lms_user_lesson`) + mark-complete API | Must | as-built |
| Course progress % recalculation on lesson complete | Must | as-built |
| Lesson view page (`/learn/{course}/{lesson}`) | Must | as-built |
| Dashboard + My Courses + My Certificates pages | Must | as-built |
| Student notes (save/get/delete + My Notes) | Should | as-built |
| In-app notifications (entity + bell block + list + preferences) | Should | as-built |
| Discussion forum (topics, threaded replies, AJAX editing, best-answer) | Should | as-built |
| Course ratings (RatingForm + admin moderation) | Should | partially as-built (3 missing routes break legacy list builder) |
| Email notification channel | Should | stub (TODO — logs only) |
| Chapter-level progress entity | Could | planned — not implemented |
| SCORM/H5P completion tracking in LMS DB | Could | planned — not implemented |
| Instructor student-roster / progress view | Could | planned — not implemented |
| Push notification channel | Could | stub (foundation only — logs only) |

## Known bugs

| Bug | Severity | Location |
|---|---|---|
| `lms_rating`: 3 missing routes (`lms_rating.collection`, `lms_rating.toggle_approve`, `lms_rating.toggle_featured`) — `RatingController` redirects and `RatingListBuilder` operations throw `RouteNotFoundException` | High | `lms_rating` module |
| `lms_forum ForumController::viewTopic()` starts with `throw new \Exception("HARD DEBUG!")` — dead code but dangerous if ever routed | Medium | `web/modules/custom/lms_forum/src/Controller/ForumController.php` |
| `LmsForumController::viewTopic()` has left-in debug `messenger()->addMessage("FRESH CONTROLLER LOADED!")` displayed to all users | Medium | `web/modules/custom/lms_forum/src/Controller/LmsForumController.php` |
| `lms_progress_comment_form_submit_redirect()` returns undefined variable `$response` on non-AJAX path when no course/lesson found | Low | `web/modules/custom/lms_progress/lms_progress.module` |
| `hook_form_alter` for lesson comments mutates and saves the `authenticated` Role entity at form-alter time, granting all text formats unconditionally | Medium | `web/modules/custom/lms_progress/lms_progress.module` |
| `lms_rating`: no `.module` file means `lms_admin_ratings` theme hook used by `RatingManagerController::list()` is not registered in this module | Medium | `lms_rating` module |

## Open questions

- [ ] Email notification channel: is a mail queue (Drupal Queue API) or a contrib mail module (e.g. Symfony Mailer) the preferred implementation path?
- [ ] Are the 3 missing `lms_rating` routes intentionally removed or accidentally omitted? Admin rating management currently works only via `RatingManagerController` (`/workspace/ratings`).
- [ ] Should `lms_user_chapter` be added as a third progress entity to enable chapter-level progress UI and sequential chapter unlock?
- [ ] SCORM/H5P: is xAPI/cmi result reporting back to the LMS required, or is launch-and-complete sufficient?
