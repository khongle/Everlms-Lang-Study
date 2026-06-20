---
module: lms_progress
service: progress
route-groups: [App, API]
status: DONE
---

# C05 — Module: LMS Progress

**Directory:** `web/modules/custom/lms_progress/`
**Responsibility:** Tracks enrollment and lesson/course completion progress for each learner, and serves the user-facing portal pages (dashboard, lesson view, certificate list).

> **Documentation mode:** as-built — matches current code (2026-06-20).

---

## Route Bindings

| Route Group | Prefix | Controller | Paths |
|-------------|--------|------------|-------|
| App | `/` | `UserPortalController` | `/dashboard`, `/my-courses`, `/learn/{lms_course}/{lms_lesson}`, `/my-certificates` |
| API (legacy) | `/api/lms` | `ProgressApiController` | `/api/lms/progress/mark-complete` [POST] |

All routes require `_user_is_logged_in: TRUE`.

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `UserPortalController` | Controller | Renders dashboard, my-courses, lesson view, my-certificates pages |
| `ProgressApiController` | Controller | JSON endpoint: mark lesson complete, recalculate course progress |
| `lms_user_course` | Entity | Enrollment + course-level progress record |
| `lms_user_lesson` | Entity | Per-lesson completion record (status + video position) |

No `lms_progress.services.yml` exists — controllers inject services via `\Drupal::service()` directly (anti-pattern; no exported service IDs).

---

## Internal Structure

```
web/modules/custom/lms_progress/
+-- lms_progress.info.yml           # package: EverLMS; deps: lms_base, lms_course, lms_chapter, lms_lesson
+-- lms_progress.routing.yml        # 5 routes (4 App + 1 API)
+-- lms_progress.module             # hook_theme(), hook_form_alter(), helper functions
+-- lms_progress.install            # (none)
+-- src/
|   +-- Controller/
|   |   +-- UserPortalController.php    # dashboard(), myCourses(), lessonView(), myCertificates(), profile()
|   |   +-- ProgressApiController.php   # markComplete()
|   +-- Entity/
|   |   +-- UserCourse.php              # lms_user_course content entity
|   |   +-- UserLesson.php              # lms_user_lesson content entity
+-- templates/
|   +-- lms-dashboard.html.twig         # vars: stats, course_summary, recent_courses
|   +-- lms-my-courses.html.twig        # vars: courses, summary
|   +-- lms-lesson-view.html.twig       # vars: course, lesson, curriculum, video_position, video_embed_url, quiz_id, is_admin, rendered_attachments, comments, existing_note
|   +-- lms-my-certificates.html.twig   # vars: certificates, summary
```

---

## Entities

### `lms_user_course` — User Course Progress

| Field | Type | Notes |
|-------|------|-------|
| id | auto | PK |
| uuid | auto | |
| uid | entity_reference (user) | via `EntityOwnerTrait` |
| field_course | entity_reference (lms_course) | required |
| field_progress | decimal (precision 5, scale 2) | 0–100; default 0 |
| field_enrolled_at | created | |
| field_completed_at | timestamp | |
| field_status | list_string | enrolled / in_progress / completed; default enrolled |

Table: `lms_user_course`. Admin permission: `administer lms_course`. Access handler: core default `EntityAccessControlHandler`.

### `lms_user_lesson` — User Lesson Progress

| Field | Type | Notes |
|-------|------|-------|
| id | auto | PK |
| uuid | auto | |
| uid | entity_reference (user) | |
| field_lesson | entity_reference (lms_lesson) | required |
| field_status | list_string | not_started / in_progress / completed; default not_started |
| field_video_position | integer | last video position in seconds; default 0 |
| field_completed_at | timestamp | |
| created | created | |

Table: `lms_user_lesson`. Admin permission: `administer lms_course`.

> **Note:** No `lms_user_chapter` entity exists in `src/Entity/`. Chapter-level progress is derived by querying lesson completions within a chapter.

---

## Controllers

### `UserPortalController` — as-built

Dependencies: `entity_type.manager`, `current_user`.

| Method | Route | Description |
|--------|-------|-------------|
| `dashboard()` | `lms_progress.dashboard` | Loads all `lms_user_course` for user; builds stats (enrolled/in_progress/completed/certificates); finds next incomplete lesson URL; renders `lms_dashboard`. |
| `myCourses()` | `lms_progress.my_courses` | Near-identical to `dashboard()` but renders as `lms_my_courses` with per-course detail. Code duplication — see Known Issues. |
| `lessonView(Course, Lesson)` | `lms_progress.lesson_view` | Auto-enrolls if no enrollment found; builds curriculum (chapters + lessons with completion flags); resolves video embed URL (YouTube/Vimeo/direct); handles lesson types (pdf, pptx, video, h5p, zoom, scorm); loads existing note; loads comment field with `ajax_comments` library; renders `lms_lesson_view`. Side effect: mutates and saves `lesson.field_comments.status = OPEN` inline. |
| `myCertificates()` | `lms_progress.certificates` | Loads all `lms_user_certificate` for user; renders `lms_my_certificates`. |
| `profile()` | (not routed) | Loads current user and renders in 'full' view mode. Dead / unused — no route points to it. |

### `ProgressApiController` — as-built

Dependencies: `entity_type.manager`, `current_user`.

| Method | Route | Description |
|--------|-------|-------------|
| `markComplete(Request)` | `lms_progress.api_mark_complete` [POST] | Accepts JSON `{course_id, lesson_id}`; upserts `lms_user_lesson` as completed; recalculates course progress % (completed lessons / total lessons); updates `lms_user_course.field_progress`; sets status to `completed` if 100%; returns JSON `{success, lesson_completed, course_completed, progress, next_lesson_url}`. Does NOT trigger certificate issuance — that is handled by `lms_certificate` event subscriber on `QuizPassedEvent`. |

---

## Hook Implementations

| Hook | File | Description |
|------|------|-------------|
| `hook_theme()` | `lms_progress.module` | Registers 4 templates: `lms_dashboard`, `lms_my_courses`, `lms_lesson_view`, `lms_my_certificates`. |
| `hook_form_BASE_FORM_ID_alter()` (`lms_progress_form_comment_lesson_comment_form_alter`) | `lms_progress.module` | Alters `comment.lesson_comment` form: grants all text formats to authenticated role (security side-effect), hides subject/author fields, hides format selector via `after_build`, auto-adds `comment_body` if missing, styles save button. |
| `lms_progress_comment_form_after_build()` | `lms_progress.module` | Hides format selector after build; wraps form in AJAX wrapper div. |
| `lms_progress_comment_form_submit_redirect()` | `lms_progress.module` | Custom submit handler: redirects non-AJAX submits back to lesson view; for AJAX rebuilds form clean. |
| `lms_progress_set_comment_parent()` | `lms_progress.module` | Sets `pid` on comment entity for threaded replies before save. |

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_base` | Internal | Base entity traits, shared config |
| `lms_course` | Internal | `lms_course` entity param upcasting in routes |
| `lms_chapter` | Internal | Curriculum structure in lessonView |
| `lms_lesson` | Internal | `lms_lesson` entity param upcasting; lesson type logic |
| `lms_certificate` | Internal (runtime, not declared) | `lessonView()` queries `lms_user_certificate` storage |
| `lms_note` | Internal (runtime, not declared) | `lessonView()` queries `lms_note` storage for existing note |
| `ajax_comments` (contrib) | External | AJAX-enabled comment library attached in lessonView |

---

## Known Issues / Bugs

| ID | Severity | Description |
|----|----------|-------------|
| BUG-PROG-01 | Medium | `lms_progress_comment_form_submit_redirect()` returns `$response` which is never initialized on the non-AJAX path when no course/lesson is found — PHP undefined variable warning. |
| BUG-PROG-02 | High | `hook_form_alter` for comments calls `$role->save()` to grant all text formats to the `authenticated` role at form-alter time — unconditional role mutation on every comment form render (security + performance risk). |
| DEBT-PROG-01 | Low | `dashboard()` and `myCourses()` share near-identical "find next lesson" logic — should be extracted to a shared private method or service. |
| DEBT-PROG-02 | Low | `UserPortalController::profile()` is never routed — dead code. |
| DEBT-PROG-03 | Low | Controllers use `\Drupal::service()` static calls instead of injected dependencies. |

> These feed [E02 Issues](../../E-Quality/E02-issues/).

---

## Cross-Links

- Entities: [C04 Content Entities](../C04-entities/01-content-entities.md)
- Owned entities also used by: [lms_certificate module](lms_certificate.md)
- Lesson content source: [lms_lesson module](lms_lesson.md), [lms_chapter module](lms_chapter.md), [lms_course module](lms_course.md)
- Notes displayed in lesson view: [lms_note module](lms_note.md)
- Quiz embed in lesson view: [lms_quiz module](lms_quiz.md)
