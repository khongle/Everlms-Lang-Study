---
doc_id: C04-03
title: Progress Entities
layer: C-Design / C04-entities
updated: 2026-06-20
entities:
  - lms_user_course
  - lms_user_lesson
  - lms_note
---

# C04-03 — Progress & Notes Entities

Covers the three transactional entities owned by [`lms_progress`](../C05-modules/lms_progress.md) and [`lms_note`](../C05-modules/lms_note.md) that track per-user learning state and inline notes.

---

## Entity: lms_user_course (User Course Progress)

---
```
entity: lms_user_course
mode: as-built
owner_module: lms_progress
content_kind: transactional
editor_owned: false
revisionable: false
translatable: false
moderation_workflow: null
```

**Storage:** `lms_user_course` (single flat table, InnoDB)
**Description:** One row per enrolled student per course; tracks enrollment status and overall completion percentage.
**Owner module:** [`lms_progress`](../C05-modules/lms_progress.md)
**Mode:** as-built
**Content kind:** transactional
**Editor owned:** false
**Management UI:** No dedicated admin UI; queried by `UserPortalController` and `ProgressApiController`. Admin access via Views if configured.
**Create / Edit / Delete forms:** N/A (created programmatically via `UserPortalController::lessonView` auto-enroll and `lms_order_process_completed_order` hook)
**Display modes:** N/A (rendered via custom Twig templates)
**Revision / moderation:** No revisions; no workflow
**Translation:** N/A (non-translatable)
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer | 1 | Yes | auto | No | — | Base table PK | Auto-increment |
| 2 | `uuid` | UUID | uuid | 1 | — | auto | No | — | Base field | External-facing identifier |
| 3 | `uid` | Student | entity_reference | 1 | — | — | No | → `user` | Base field | Owner via `EntityOwnerTrait` |
| 4 | `field_course` | Course | entity_reference | 1 | Yes | — | No | → `lms_course` | Base field | Course being tracked |
| 5 | `field_progress` | Progress | decimal (5,2) | 1 | — | 0 | No | 0.00–100.00 | Base field | Percentage complete; recalculated by `ProgressApiController::markComplete` |
| 6 | `field_enrolled_at` | Enrolled At | created (Unix int) | 1 | — | auto | No | — | Base field | Set on first enrollment |
| 7 | `field_completed_at` | Completed At | timestamp (Unix int) | 1 | — | null | No | — | Base field | Set when progress reaches 100% |
| 8 | `field_status` | Status | list_string | 1 | — | `enrolled` | No | `enrolled`, `in_progress`, `completed` | Base field | Transitions: enrolled → in_progress → completed |

#### Physical table schema (`lms_user_course`, AUTO_INCREMENT ~175)

```
id                  int unsigned NOT NULL AUTO_INCREMENT  PK
uuid                varchar(128) UNIQUE
uid                 int unsigned   FK → users_field_data.uid
field_course        int unsigned   FK → lms_course.id
field_progress      decimal(5,2)   0.00–100.00
field_enrolled_at   int            Unix timestamp
field_completed_at  int            Unix timestamp (NULL until completed)
field_status        varchar(255)   enrolled / in_progress / completed
```

**Data presence:** ~174 rows (AUTO_INCREMENT=175). No DB-level UNIQUE constraint on `(uid, field_course)` — application must prevent duplicate enrollment.

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | PK |
| — | `uuid` | UNIQUE | Entity UUID lookup |
| — | `uid` | (implicit FK pattern) | Filter enrollments by user |
| — | `field_course` | (implicit FK pattern) | Filter enrollments by course |

No composite index on `(uid, field_course)` — de-duplication enforced at application layer only.

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `user` | M:1 | `uid` | — |
| `lms_course` | M:1 | `field_course` | — |

### State machine

```
enrolled → in_progress → completed
```

- Transition `enrolled → in_progress`: happens implicitly when `field_progress > 0` (no explicit trigger; status updated by `ProgressApiController::markComplete`).
- Transition `in_progress → completed`: set when `field_progress = 100.00` by `ProgressApiController::markComplete`.
- No reverse transitions modeled.

### Business rules

- A `lms_user_course` row is created automatically when a student visits a lesson via `UserPortalController::lessonView` (auto-enroll) or when an order completes (`_lms_order_process_completed_order`).
- `field_progress` is recalculated by counting completed `lms_user_lesson` rows against the total lesson count for the course. Formula in `ProgressApiController::markComplete`: `(completed_lessons / total_lessons) * 100`.
- When `field_progress` reaches `100.00`, `field_status` is set to `completed` and `field_completed_at` is stamped.
- Enrollment (this entity) is **decoupled from payment** (`lms_order`): a student can be enrolled manually without an order record.
- No DB UNIQUE constraint on `(uid, field_course)`; application logic in `ProgressApiController` and `UserPortalController` prevents duplicates but does not guarantee it under concurrent inserts.

### Implementation status

> Mode is `as-built`; the following notes document gaps vs the draft design.

| Feature / field | Status | Notes |
|---|---|---|
| `field_progress` auto-calculation | DONE | Implemented in `ProgressApiController::markComplete` |
| `field_status` state transitions | DONE | Set in same controller method |
| `field_enrolled_at` | DONE | Uses Drupal `created` field type (auto-stamp) |
| `field_completed_at` | DONE | Stamped on 100% |
| Draft field `field_enrollment_source` | NOT PRESENT | Not in code or DB; the draft listed `purchase/membership/manual/free` but this field was never implemented |
| Draft field `field_last_accessed` | NOT PRESENT | Not in code or DB |
| Chapter-level progress (`lms_user_chapter`) | NOT PRESENT | Draft described a 3-level hierarchy; code implements only 2 levels (course + lesson). No `lms_user_chapter` entity exists. |
| Cascade via chapter | NOT APPLICABLE | Progress rolls up directly from lessons to course; chapters are not aggregation points in the current implementation |

---

## Entity: lms_user_lesson (User Lesson Progress)

---
```
entity: lms_user_lesson
mode: as-built
owner_module: lms_progress
content_kind: transactional
editor_owned: false
revisionable: false
translatable: false
moderation_workflow: null
```

**Storage:** `lms_user_lesson` (single flat table, InnoDB)
**Description:** One row per student per lesson; tracks completion status and video resume position.
**Owner module:** [`lms_progress`](../C05-modules/lms_progress.md)
**Mode:** as-built
**Content kind:** transactional
**Editor owned:** false
**Management UI:** N/A (no admin form; managed via `ProgressApiController`)
**Create / Edit / Delete forms:** N/A (programmatic only)
**Display modes:** N/A
**Revision / moderation:** No revisions
**Translation:** N/A
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer | 1 | Yes | auto | No | — | Base table PK | Auto-increment |
| 2 | `uuid` | UUID | uuid | 1 | — | auto | No | — | Base field | |
| 3 | `uid` | Student | entity_reference | 1 | — | — | No | → `user` | Base field | Owner via `EntityOwnerTrait` |
| 4 | `field_lesson` | Lesson | entity_reference | 1 | Yes | — | No | → `lms_lesson` | Base field | |
| 5 | `field_status` | Status | list_string | 1 | — | `not_started` | No | `not_started`, `in_progress`, `completed` | Base field | |
| 6 | `field_video_position` | Video Position | integer | 1 | — | 0 | No | Seconds | Base field | Last watched second; used for video resume |
| 7 | `field_completed_at` | Completed At | timestamp (Unix int) | 1 | — | null | No | — | Base field | |
| 8 | `created` | Created | created (Unix int) | 1 | — | auto | No | — | Base field | Row creation timestamp |

#### Physical table schema (`lms_user_lesson`, AUTO_INCREMENT ~38)

```
id                  int unsigned NOT NULL AUTO_INCREMENT  PK
uuid                varchar(128) UNIQUE
uid                 int unsigned   FK → users_field_data.uid
field_lesson        int unsigned   FK → lms_lesson.id
field_status        varchar(255)   not_started / in_progress / completed
field_video_position int           last watched second (default 0)
field_completed_at  int            Unix timestamp (NULL until completed)
created             int            Unix timestamp
```

**Data presence:** ~37 rows (AUTO_INCREMENT=38). Low count relative to lesson count (~1772) indicates sparse tracking — only lessons explicitly acted upon have rows.

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | PK |
| — | `uuid` | UNIQUE | Entity UUID lookup |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `user` | M:1 | `uid` | — |
| `lms_lesson` | M:1 | `field_lesson` | — |
| `lms_user_course` | logical parent | app-level (no FK) | — |

### State machine

```
not_started → in_progress → completed
```

- All transitions driven by `ProgressApiController::markComplete` POST request.
- `not_started` is the default; the row may not exist until first interaction (upsert pattern).
- No backward transitions modeled.

### Business rules

- `ProgressApiController::markComplete` upserts this entity to `completed` status when a student explicitly marks a lesson complete.
- `field_video_position` is stored on lesson view load (`lessonView` passes `video_position` to template) but the update path for intermediate video position saves is via a separate request pattern (note: `ProgressApiController::markComplete` is the only defined API endpoint; continuous position saves are not implemented in the API layer).
- After marking complete, the controller recalculates `lms_user_course.field_progress` for the same course.
- The draft described completion triggers per lesson type (video 90%, text click, quiz pass, H5P, SCORM, Zoom). **In practice, all completion is triggered by the `mark-complete` API call** — no automatic type-based logic is implemented.

### Implementation status

| Feature / field | Status | Notes |
|---|---|---|
| `field_status` state machine | DONE | `not_started / in_progress / completed` |
| `field_video_position` storage | DONE (partial) | Stored; loaded by `lessonView`; intermediate auto-save not implemented in API |
| `field_completed_at` | DONE | Set by `markComplete` |
| Draft field `field_time_spent` | NOT PRESENT | Not in code or DB |
| Draft field `field_video_progress` (%) | NOT PRESENT | Code tracks seconds (`field_video_position`), not percentage |
| Type-based completion logic | NOT PRESENT | Single `mark-complete` endpoint; no per-type automation |

---

## Entity: lms_note (Student Note)

---
```
entity: lms_note
mode: as-built
owner_module: lms_note
content_kind: transactional
editor_owned: false
revisionable: false
translatable: false
moderation_workflow: null
```

**Storage:** `lms_note` (single flat table, InnoDB)
**Description:** A student-written note on a specific lesson, optionally anchored to a video timestamp.
**Owner module:** [`lms_note`](../C05-modules/lms_note.md)
**Mode:** as-built
**Content kind:** transactional
**Editor owned:** false
**Management UI:** N/A (no admin form; students manage via NoteController API)
**Create / Edit / Delete forms:** N/A (JSON API: `POST /api/lms/note/save`, `POST /api/lms/note/delete`)
**Display modes:** Rendered in `lms-lesson-view.html.twig` (existing note) and `lms-my-notes.html.twig` (grouped listing)
**Revision / moderation:** No revisions
**Translation:** N/A
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer | 1 | Yes | auto | No | — | Base table PK | Auto-increment |
| 2 | `uuid` | UUID | uuid | 1 | — | auto | No | — | Base field | |
| 3 | `uid` | Student | entity_reference | 1 | — | — | No | → `user` | Base field | Owner via `EntityOwnerTrait` |
| 4 | `field_course` | Course | entity_reference | 1 | Yes | — | No | → `lms_course` | Base field | Added via `lms_note_update_10001`; required |
| 5 | `field_lesson` | Lesson | entity_reference | 1 | Yes | — | No | → `lms_lesson` | Base field | Required |
| 6 | `field_body` | Note | text_long | 1 | Yes | — | No | — | Base field | Free-form student text |
| 7 | `field_video_timestamp` | Video Timestamp | integer | 1 | — | 0 | No | Seconds | Base field | Point in video where note was taken |
| 8 | `created` | Created | created (Unix int) | 1 | — | auto | No | — | Base field | |
| 9 | `changed` | Changed | changed (Unix int) | 1 | — | auto | No | — | Base field | Auto-bumped on update |

#### Physical table schema (`lms_note`, AUTO_INCREMENT ~265)

```
id                    int unsigned NOT NULL AUTO_INCREMENT  PK
uuid                  varchar(128) UNIQUE
uid                   int unsigned   FK → users_field_data.uid
field_lesson          int unsigned   FK → lms_lesson.id
field_body__value     longtext
field_body__format    varchar(255)
field_video_timestamp int            seconds into video (default 0)
created               int
changed               int
field_course          int unsigned   FK → lms_course.id
```

**Data presence:** ~264 rows (AUTO_INCREMENT=265). `field_course` column present — `lms_note_update_10001` was applied.

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | PK |
| — | `uuid` | UNIQUE | Entity UUID lookup |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `user` | M:1 | `uid` | — |
| `lms_course` | M:1 | `field_course` | — |
| `lms_lesson` | M:1 | `field_lesson` | — |

### Business rules

- One note per student per lesson (upsert pattern in `NoteController::save` — existing note is updated in place).
- Delete removes all notes for `uid + lesson_id` (not single-note delete by ID).
- Cache tag `lms_note:{lesson_id}:{uid}` is invalidated on save and delete.
- `field_course` is required (added post-initial-schema via update hook 10001); the `myNotes` view groups notes by course using this field.
- `field_video_timestamp` anchors the note to a point in a video; value is 0 for non-video lessons or when no timestamp was provided.

### Editorial usability

| Surface | Requirement |
|---|---|
| Student note save | `POST /api/lms/note/save` with JSON `{lesson_id, course_id, note_text, video_timestamp}` |
| Student note delete | `POST /api/lms/note/delete` with JSON `{lesson_id}` |
| Student note read | `GET /api/lms/note/get?lesson_id=` |
| My Notes page | `GET /my-notes` → `NoteController::myNotes` → grouped by course |
| Lesson view inline | Existing note loaded by `UserPortalController::lessonView` into `lms-lesson-view.html.twig` |
| Permission | `_user_is_logged_in: TRUE` on all routes |
| Cache invalidation | Tag `lms_note:{lesson_id}:{uid}` cleared on save/delete |

---

## Progress Calculation Summary (as-built)

The current implementation is a **2-level roll-up** (Lesson → Course), not the 3-level hierarchy described in the draft.

| Level | Entity | Trigger | What happens |
|---|---|---|---|
| Lesson | `lms_user_lesson` | `POST /api/lms/progress/mark-complete` | Row upserted to `completed`; `field_completed_at` stamped |
| Course | `lms_user_course` | Same request, after lesson update | `field_progress` = (completed lessons / total lessons) × 100; `field_status` set to `completed` if 100% |
| Chapter | (no entity) | — | No chapter-level aggregation entity exists |

```
POST /api/lms/progress/mark-complete
    → upsert lms_user_lesson (completed)
    → count completed lms_user_lesson rows for course
    → update lms_user_course.field_progress
    → if 100%: set lms_user_course.field_status = completed
    → return JSON {success, lesson_completed, course_completed, progress, next_lesson_url}
```

Certificate issuance is **not** triggered here — it is triggered separately by `QuizPassedEvent` in the `lms_certificate` module.
