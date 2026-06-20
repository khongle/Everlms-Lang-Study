# D02 — Epic Spec: Progress & Learner Portal

**Epic ID:** E-04  
**Maps to draft:** G-PROGRESS  
**Features contributed to:** F-030 (Progress Tracking), F-031 (Learner Dashboard), F-032 (Notes), F-033 (Forum), F-034 (Ratings), F-035 (Notifications)  
**Owner:** SA (primary) + Agent_Implementer (executes Stories); CP4 signed off by SA  
**Services affected:** Progress Service (lms_progress, lms_note), Communication Service (lms_forum, lms_rating, lms_notification)  
**Dependencies:** E-01 (Foundation), E-02 (Content — lms_course, lms_lesson entities), E-03 (Assessment — QuizPassedEvent)  
**Estimated Duration:** 2 weeks  
**Status:** COMPLETE (as-built; see known issues below for bugs in lms_forum, lms_rating, lms_notification)

---

## Purpose

Deliver the learner-facing portal and all real-time learning support features: progress tracking at lesson/chapter/course level (lms_user_course, lms_user_lesson) with cascade recalculation, the lesson view with video resume position and inline comments, the dashboard and "My Courses / Continue Learning" flows, per-lesson student notes with video-timestamp sync, course discussion forums with threaded replies and best-answer marking, course ratings and reviews, and an in-app notification system (with stubs for email and push channels).

---

## Stories

| Story ID | Title | Type | Primary source | Owning module(s) | Status | Dependencies | Priority |
|----------|-------|------|----------------|-----------------|--------|--------------|----------|
| S-04-01 | lms_progress — UserCourse/UserLesson entities | module | modules-progress-comms.md; [C05-modules/lms_progress.md](../../C-Design/C05-modules/lms_progress.md) | lms_progress | as-built | E-02 | Must |
| S-04-02 | Progress cascade service (ProgressApiController) | module | modules-progress-comms.md; [C05-modules/lms_progress.md](../../C-Design/C05-modules/lms_progress.md) | lms_progress | as-built | S-04-01 | Must |
| S-04-03 | UserPortalController — dashboard, my-courses, lesson view, certificates | page | modules-progress-comms.md; [C05-modules/lms_progress.md](../../C-Design/C05-modules/lms_progress.md) | lms_progress | as-built | S-04-01, S-04-02 | Must |
| S-04-04 | lms_note — student notes with video timestamp | module | modules-progress-comms.md; [C05-modules/lms_note.md](../../C-Design/C05-modules/lms_note.md) | lms_note | as-built | S-04-01 | Must |
| S-04-05 | lms_forum — threaded discussion forum + reply system | module | modules-progress-comms.md; [C05-modules/lms_forum.md](../../C-Design/C05-modules/lms_forum.md) | lms_forum | as-built (with bugs) | E-02 | Should |
| S-04-06 | lms_rating — course ratings and reviews | module | modules-progress-comms.md; [C05-modules/lms_rating.md](../../C-Design/C05-modules/lms_rating.md) | lms_rating | as-built (with bugs) | E-02 | Should |
| S-04-07 | lms_notification — in-app notification system (email/push stubs) | module | modules-progress-comms.md; [C05-modules/lms_notification.md](../../C-Design/C05-modules/lms_notification.md) | lms_notification | as-built (email/push stubs) | E-03 | Should |

---

## Build Order Within Epic

1. **S-04-01** — UserCourse/UserLesson entities (depends on E-02 Course/Lesson)
2. **S-04-02** — ProgressApiController cascade service (depends on S-04-01)
3. **S-04-03** — UserPortalController (depends on S-04-01, S-04-02)
4. **S-04-04** — lms_note (depends on S-04-01; can start parallel with S-04-02)
5. **S-04-05, S-04-06** — lms_forum, lms_rating (parallel; both depend on E-02)
6. **S-04-07** — lms_notification (depends on E-03 QuizPassedEvent; can start parallel with S-04-05/06)

---

## Story Details

### S-04-01 — lms_progress — UserCourse/UserLesson entities
**As-built.** Two entities: `lms_user_course` (fields: `field_course` → lms_course, `field_progress` decimal 0-100, `field_enrolled_at`, `field_completed_at`, `field_status`: enrolled/in_progress/completed) and `lms_user_lesson` (fields: `field_lesson` → lms_lesson, `field_status`: not_started/in_progress/completed, `field_video_position` int seconds, `field_completed_at`). Both implement EntityOwnerInterface. No auto-enrolment on entity creation — enrolment created explicitly by CourseEnrollmentController (free) or lms_order hook (paid). Source: [C05-modules/lms_progress.md](../../C-Design/C05-modules/lms_progress.md).

### S-04-02 — Progress cascade service (ProgressApiController)
**As-built.** Route: `/api/lms/progress/mark-complete` (POST, login required). `ProgressApiController::markComplete`: upserts `lms_user_lesson` as 'completed', recalculates course progress % by counting all lessons vs completed lessons, updates `lms_user_course.field_progress`, sets status='completed' and `field_completed_at` if 100%. Returns JSON: `{success, lesson_completed, course_completed, progress, next_lesson_url}`. **RISK-002 (Race condition):** Concurrent requests can read stale lesson counts before updates are committed — no locking or transaction isolation. Hardening required. Source: [C05-modules/lms_progress.md](../../C-Design/C05-modules/lms_progress.md).

### S-04-03 — UserPortalController: dashboard, lesson view, my-courses, certificates
**As-built.** Routes: `/dashboard`, `/my-courses`, `/learn/{lms_course}/{lms_lesson}`, `/my-certificates`, `/profile` (profile likely dead/unused). `lessonView`: auto-enrolls if no enrollment found; resolves video embed URL (YouTube/Vimeo/direct); handles lesson types (pdf, pptx, video, h5p, zoom, scorm); loads existing note; loads comment field with ajax_comments; renders `lms_lesson_view`. `dashboard` and `myCourses` share near-identical "find next lesson" logic (code duplication). `lessonView` mutates `lesson.field_comments.status` to OPEN and saves inline on every request (side-effectful). **Bug:** `lms_progress_comment_form_submit_redirect()` returns undefined `$response` variable on non-AJAX path if no lesson found (PHP warning). **Security concern:** `hook_form_alter` for comments mutates and saves the `authenticated` Role entity at form-alter time. Templates: lms-dashboard, lms-my-courses, lms-lesson-view, lms-my-certificates. Source: [C05-modules/lms_progress.md](../../C-Design/C05-modules/lms_progress.md).

### S-04-04 — lms_note — student notes with video timestamp
**As-built.** Entity `lms_note` (fields: `field_lesson` → lms_lesson, `field_course` → lms_course (added via update_10001), `field_body`, `field_video_timestamp` int seconds). JSON API routes: `/api/lms/note/save` (POST), `/api/lms/note/delete` (POST), `/api/lms/note/get` (GET), `/my-notes` (GET). `NoteController::save` upserts with cache tag invalidation `lms_note:{lesson_id}:{uid}`. `myNotes` groups notes by course. Source: [C05-modules/lms_note.md](../../C-Design/C05-modules/lms_note.md).

### S-04-05 — lms_forum — threaded discussion forum
**As-built with bugs.** Entities: `lms_forum_topic` (fields: name, field_body, field_course, field_category, field_replies_count, field_pinned, field_locked, field_resolved, field_last_reply_date, field_views) and `lms_forum_reply` (fields: field_topic, field_body, field_parent for threading, field_best_answer, field_upvotes). `LmsForumController` is the active controller (canonical route overridden by `RouteSubscriber`). Features: topic list, add topic, view topic (increments views, loads replies for auth), reply, edit reply (AJAX modal), mark best answer, delete reply. ForumReplyForm dispatches `forum_reply` notification via lms_notification when available. **Dead code bug:** `ForumController::viewTopic()` starts with `throw new \Exception("HARD DEBUG!")` — if ever routed, causes HTTP 500. **Debug artifact:** `LmsForumController::viewTopic()` has `\Drupal::messenger()->addMessage("FRESH CONTROLLER LOADED!")` left in production code. Source: [C05-modules/lms_forum.md](../../C-Design/C05-modules/lms_forum.md).

### S-04-06 — lms_rating — course ratings and reviews
**As-built with broken routes.** Entity `lms_rating` (fields: field_course, field_score 1-5, field_review, field_approved boolean default=TRUE, field_is_featured added via update_11001). `RatingForm` (FormBase, prevents duplicates, creates entity with field_approved=FALSE). `RatingManagerController` at `/workspace/ratings` — functional admin list. **Critical bug:** `RatingController::toggleApprove/toggleFeatured` both redirect to `lms_rating.collection` which does NOT exist in routing.yml → `RouteNotFoundException`. `RatingListBuilder::getOperations()` references `lms_rating.toggle_approve` and `lms_rating.toggle_featured` (both missing) → `RouteNotFoundException` if list builder rendered. `lms_admin_ratings` theme used by `RatingManagerController::list()` not registered in lms_rating (no .module file) — must be defined in lms_course or theme. Source: [C05-modules/lms_rating.md](../../C-Design/C05-modules/lms_rating.md).

### S-04-07 — lms_notification — in-app notification system
**As-built (in-app complete; email/push stubs).** Service `lms_notification.manager` (`NotificationManager`): `dispatch()` method routes to `createInAppNotification()` for 'in_app' channel (REAL), logs-only for 'email' channel (TODO: Drupal queue creation), logs-only for 'push' channel (FCM foundation comment only). Entity `lms_notification` (fields: field_type with 10 allowed values, field_channel list_string unlimited, field_title, field_message, field_link, field_read, field_reference_type, field_reference_id). `NotificationEventSubscriber` subscribes to `QuizPassedEvent` (conditional on class_exists). Cron cleans read notifications older than 90 days. `NotificationBellBlock` plugin shows unread count + top 5 recent. Broadcast form with batch processing to all active users. Per-user preferences form (9 event types, in_app + email checkboxes). Drush command `lms_notification:remind-inactive`. Source: [C05-modules/lms_notification.md](../../C-Design/C05-modules/lms_notification.md).

---

## Affected Modules

| Module | Role in Epic |
|--------|-------------|
| lms_progress | Progress entities, cascade API, learner portal pages |
| lms_note | Student notes with video timestamp |
| lms_forum | Course discussion forums, threaded replies |
| lms_rating | Course ratings and reviews |
| lms_notification | In-app notifications (email/push stubs) |

---

## Known Issues (log to E02-issues/)

| Issue | Severity | Story | Description |
|-------|----------|-------|-------------|
| Race condition on concurrent progress updates | High | S-04-02 | No DB locking; RISK-002; hardening required |
| ForumController dead code with throw Exception | High | S-04-05 | `ForumController::viewTopic` throws unconditionally; would 500 if routed |
| Forum debug message in production | Medium | S-04-05 | `messenger()->addMessage("FRESH CONTROLLER LOADED!")` in LmsForumController::viewTopic |
| lms_rating missing 3 routes | High | S-04-06 | `lms_rating.collection`, `lms_rating.toggle_approve`, `lms_rating.toggle_featured` not in routing.yml; RouteNotFoundException |
| lms_rating lms_admin_ratings theme unregistered | Medium | S-04-06 | No hook_theme in lms_rating; theme must be in lms_course or lms_theme |
| Progress comment form undefined $response | Low | S-04-03 | PHP warning: undefined variable `$response` on non-AJAX path |
| hook_form_alter mutates Role entity | Medium | S-04-03 | Grants all text formats to `authenticated` role at form-alter time; security/performance concern |
| Email/push notifications are stubs | Medium | S-04-07 | Email channel: only logs (TODO comment); push channel: logs only (FCM comment) |

---

## Acceptance Criteria (from B05)

| AC ID | Scenario | Feature | Contributing Story(ies) |
|-------|----------|---------|-------------------------|
| AC-030 | Lesson completion updates course progress % | F-030 | S-04-01, S-04-02 |
| AC-031 | Lesson view resumes video at last position | F-031 | S-04-03 |
| AC-032 | Student note saved/retrieved per lesson with video timestamp | F-032 | S-04-04 |
| AC-033 | Forum topic created; reply increments reply count | F-033 | S-04-05 |
| AC-034 | Rating submitted; moderator can approve/feature | F-034 | S-04-06 |
| AC-035 | In-app notification dispatched on quiz pass | F-035 | S-04-07 |
| AC-036 | Dashboard shows enrolled/in_progress/completed counts | F-031 | S-04-03 |
| AC-037 | "Continue learning" link resolves to next incomplete lesson | F-031 | S-04-03 |

---

## Integration Points (from B06)

| INT ID | Integration | Contributing Story(ies) | Notes |
|--------|------------|-------------------------|-------|
| INT-030 | lms_quiz.passed event → NotificationEventSubscriber | S-04-07 | Conditional class_exists guard |
| INT-031 | ForumReplyForm → lms_notification.manager::dispatch('forum_reply') | S-04-05 | Conditional module_exists guard |
| INT-032 | lms_order hook → lms_user_course enrolment creation | S-04-01 | On order status=completed; managed in lms_order module |
| INT-033 | ProgressApiController does NOT trigger certificate issuance | S-04-02 | Certificate chain triggered by QuizPassedEvent, not by progress=100% |
| INT-034 | lessonView → lms_note storage query | S-04-03, S-04-04 | UserPortalController::lessonView queries lms_note for current lesson |

---

## Test Requirements

- **Unit + integration tests per Story:** coverage >= 70% per Story
- **Cross-Story integration tests:** Progress cascade end-to-end (mark lesson complete → course progress updated); forum reply increments topic reply count; notification dispatched on quiz pass event
- **Cross-Story Integration Test Owner:** `S-04-02` (ProgressApiController is the core cascade service connecting all progress-related stories)
- **Acceptance tests:** AC-030 through AC-037

---

## Feature Flag Plan

| Flag ID | Story | Description | Risk Level | Rollout Strategy |
|---------|-------|-------------|-----------|-----------------|
| FF_FEAT_EMAIL_NOTIFICATION | S-04-07 | Enable real email notification delivery via Drupal queue | High | 0% until queue worker implemented; staged rollout |
| FF_FEAT_PUSH_NOTIFICATION | S-04-07 | Enable FCM push notification channel | High | 0% until FCM integration implemented |

---

## CP4 Readiness Criteria

- [x] Every Story in this Epic has status: DONE (all as-built)
- [x] G1-G4 passed for every Story (per draft G-PROGRESS exit criteria)
- [x] Cross-Story integration tests within Epic passing
- [x] D07 code-map delta complete for all files touched in this Epic
- [x] E05 traceability complete for every contributed Feature
- [ ] E07 epic retrospective written (E07-retrospectives/) — pending
- [ ] Known issues logged in E02-issues/ (race condition, dead ForumController, 3 missing rating routes, undefined $response, hook_form_alter role mutation, email/push stubs)
- [x] Every Story that touched code/config/UI lists relevant D00-conventions/ files
