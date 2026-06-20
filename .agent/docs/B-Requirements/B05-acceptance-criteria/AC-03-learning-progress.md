---
feature: F-003
status: DRAFT
---

# B05 — Acceptance Criteria for F-003 — Learning Progress

> Feature source: `../B03-features/03-learning-progress.md`
> Implementing modules: `lms_progress`, `lms_note`, `lms_forum`, `lms_rating`, `lms_notification`
> Implementation maturity: mostly **as-built** with specific gaps noted per criterion.

---

## AC-026: Lesson view auto-enrolls the student if not already enrolled

**Priority:** Must
**Given** an authenticated user who has no `lms_user_course` entity for a course visits `/learn/{lms_course}/{lms_lesson}`
**When** `UserPortalController::lessonView()` runs
**Then** a `lms_user_course` entity is created for the user and course with `field_status=enrolled`
**And** the lesson content is rendered without requiring a separate enroll step

**Status:** as-built — `lessonView()` auto-enrolls before rendering.

---

## AC-027: Mark-complete API updates lesson and course progress atomically

**Priority:** Must
**Given** an authenticated student is enrolled in a course and has not yet marked a lesson as complete
**When** the student sends `POST /api/lms/progress/mark-complete` with `{course_id, lesson_id}`
**Then** `ProgressApiController::markComplete()` creates or updates a `lms_user_lesson` record with `field_status=completed`
**And** `lms_user_course.field_progress` is recalculated as `(completed_lessons / total_lessons) × 100`
**And** if `field_progress = 100`, `lms_user_course.field_status` is set to `completed`
**And** the JSON response includes `{success, lesson_completed, course_completed, progress, next_lesson_url}`

**Status:** as-built — `markComplete()` is fully implemented.

---

## AC-028: Marking an already-completed lesson is idempotent

**Priority:** Must
**Given** a `lms_user_lesson` entity already exists with `field_status=completed` for a user + lesson pair
**When** the student sends `POST /api/lms/progress/mark-complete` for the same lesson again
**Then** `markComplete()` updates the existing record (upsert) rather than creating a duplicate
**And** the course progress percentage is not double-counted

**Status:** as-built — `markComplete()` uses an upsert (queries existing `lms_user_lesson` before create or update).

---

## AC-029: Dashboard displays correct per-course progress summary

**Priority:** Must
**Given** an authenticated student has `lms_user_course` entities for one or more courses with varying `field_progress` and `field_status` values
**When** the student requests `/dashboard`
**Then** `UserPortalController::dashboard()` loads all enrollments and builds a stats object with enrolled, in_progress, completed, and certificates counts
**And** a "continue URL" pointing to the next incomplete lesson is resolved for the in-progress course
**And** the `lms_dashboard` template renders per-course progress bars with the correct percentage values

**Status:** as-built — `dashboard()` is fully implemented. Note: `dashboard()` and `myCourses()` contain near-identical logic for finding the next lesson (code duplication, not a functional defect).

---

## AC-030: My Courses page lists enrolled courses with status and progress

**Priority:** Must
**Given** an authenticated student has enrollments
**When** the student requests `/my-courses`
**Then** `UserPortalController::myCourses()` returns the `lms_my_courses` theme containing per-course entries with `field_progress`, `field_status`, and a continue URL

**Status:** as-built — `myCourses()` is fully implemented.

---

## AC-031: Student can save and retrieve notes per lesson

**Priority:** Must
**Given** an authenticated student is viewing a lesson
**When** the student submits `POST /api/lms/note/save` with `{lesson_id, course_id, note_text, video_timestamp}`
**Then** `NoteController::save()` creates or updates a `lms_note` entity for the uid + lesson pair
**And** `GET /api/lms/note/get?lesson_id=` returns `{status, note_text}` with the saved content
**And** the cache tag `lms_note:{lesson_id}:{uid}` is invalidated on save and delete

**Status:** as-built — all three `NoteController` methods are fully implemented.

---

## AC-032: My Notes page groups notes by course

**Priority:** Must
**Given** an authenticated student has `lms_note` entities across multiple courses
**When** the student requests `/my-notes`
**Then** `NoteController::myNotes()` loads all notes for the user sorted by `changed DESC` and groups them by `field_course`
**And** the `lms_my_notes` template renders the grouped structure with course headings

**Status:** as-built — `myNotes()` is fully implemented.

---

## AC-033: Forum topic creation and reply work for authenticated users

**Priority:** Must
**Given** an authenticated user requests `/forum/new`
**When** the user submits the `ForumTopicForm` with a `name` and optional `field_body` and `field_category`
**Then** a `lms_forum_topic` entity is saved and the user is redirected to the topic's canonical URL
**And** when any authenticated user submits a reply via `/forum/{lms_forum_topic}/reply`, a `lms_forum_reply` entity is saved, `field_replies_count` on the topic is incremented, and (if `lms_notification` is enabled) a `forum_reply` in-app notification is dispatched to the topic owner

**Status:** as-built — `LmsForumController` and both form classes are fully implemented. Note: `LmsForumController::viewTopic()` contains a debug `addMessage("FRESH CONTROLLER LOADED!")` call that outputs a visible debug message to end users — a known production bug. `ForumController::viewTopic()` (dead code) contains `throw new \Exception("HARD DEBUG!")` — it is not routed but is a risk if routing ever changes.

---

## AC-034: Best answer can be marked by topic owner or admin

**Priority:** Should
**Given** a `lms_forum_topic` exists with at least one `lms_forum_reply`
**When** the topic owner or a user with `administer lms_course` requests `/forum/{lms_forum_topic}/reply/{lms_forum_reply}/best`
**Then** `LmsForumController::markBestAnswer()` sets `field_best_answer=true` on the reply and `field_resolved=true` on the topic
**And** the response redirects to the topic's canonical URL

**Status:** as-built — `markBestAnswer()` is fully implemented with owner/admin guard.

---

## AC-035: Rating form creates a pending review rating

**Priority:** Must
**Given** an authenticated student who does not already have a rating for a course opens the course detail page (which includes the `RatingBlock`)
**When** the student selects a star score (1–5) and submits the `RatingForm`
**Then** a `lms_rating` entity is created with `field_approved=false` (pending moderation) and `field_is_featured=false`
**And** the student sees a "pending moderation" status message

**Status:** as-built — `RatingForm::submitForm()` is fully implemented. Note: `RatingController::toggleApprove()` and `toggleFeatured()` reference the missing route `lms_rating.collection`, which causes `RouteNotFoundException` when the `RatingListBuilder` operations are rendered. Rating management via `RatingManagerController` at `/workspace/ratings` works correctly.

---

## AC-036-note: Email and push notification channels are stubs

**Priority:** Must
**Given** the `lms_notification` module is installed and `NotificationManager::dispatch()` is called with `channels=['email']` or `channels=['push']`
**When** the dispatch method processes those channels
**Then** only an info-level log entry is written; no email is sent and no push notification is delivered

**Status:** **blocked — not implemented.** Email channel in `NotificationManager` contains only `// TODO: Implement Drupal queue creation for mail`. Push channel logs only. In-app channel (`channel=in_app`) is fully implemented and works correctly.

> Note: AC-036 number is reserved here as a cross-reference note. The formal in-app notification ACs live in this file due to the logical grouping; the index in README.md covers AC-026–AC-035 for this feature.

---

## Local traceability (F-003)

| AC ID | Test Case | Implementing Story / Module | Status |
|-------|-----------|-----------------------------|--------|
| AC-026 | T-026 | lesson-auto-enroll / lms_progress | PENDING |
| AC-027 | T-027 | mark-complete-api / lms_progress | PENDING |
| AC-028 | T-028 | mark-complete-idempotent / lms_progress | PENDING |
| AC-029 | T-029 | dashboard-progress / lms_progress | PENDING |
| AC-030 | T-030 | my-courses / lms_progress | PENDING |
| AC-031 | T-031 | note-save-get / lms_note | PENDING |
| AC-032 | T-032 | my-notes / lms_note | PENDING |
| AC-033 | T-033 | forum-topic-reply / lms_forum | PENDING |
| AC-034 | T-034 | forum-best-answer / lms_forum | PENDING |
| AC-035 | T-035 | rating-submit / lms_rating | PENDING |

> Cross-feature traceability lives in `E05-traceability.md`.
