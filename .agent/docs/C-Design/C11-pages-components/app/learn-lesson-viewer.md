---
page: Lesson Viewer (all lesson types)
mode: as-built
owner_module: lms_progress
route_group: Learn/App
adequacy: NOT_RUN
---

# Lesson Viewer — `/learn/{lms_course}/{lms_lesson}`

The in-lesson learning view for authenticated learners. A single route and template renders all lesson type variants (video, text, quiz, h5p, scorm, pdf, pptx, zoom, mixed) by switching the content area based on `lms_lesson.field_type`. The curriculum sidebar remains constant across all variants.

---

## Header Block

| Property | Value |
|---|---|
| **Canonical URL** | `/learn/{lms_course}/{lms_lesson}` |
| **Route name** | `lms_progress.lesson_view` |
| **Solution** | Drupal 11 custom page |
| **Owner module** | `lms_progress` |
| **Primary source** | `web/modules/custom/lms_progress/src/Controller/UserPortalController.php` → `lessonView(Course, Lesson)` |
| **Editable source** | `web/modules/custom/lms_progress/templates/lms-lesson-view.html.twig` |
| **Editor role** | Admin (template/theme); lesson content editable via `/workspace/lessons/{id}/admin-edit` |
| **Editor management UI** | `/workspace/lessons/{lms_lesson}/admin-edit` (route: `lms_course.admin.lesson.edit`) |
| **Theme template** | `lms_lesson_view` hook → `lms-lesson-view.html.twig` (lms_progress module) |
| **Access** | `_user_is_logged_in: TRUE`; route params upcasted as `entity:lms_course`, `entity:lms_lesson` |
| **Cache** | Per-user + per-lesson (not-cacheable by default due to dynamic completion state and AJAX comment form) |
| **Related entities** | `lms_lesson`, `lms_course`, `lms_chapter`, `lms_user_course`, `lms_user_lesson`, `lms_note`, `lms_quiz`, comment (`lesson_comment` type), `lms_zoom` (Zoom lessons) |

---

## Components (Visual Order)

| # | Component | Editable source | Notes |
|---|---|---|---|
| 1 | **Lesson header strip** | `lms-lesson-view.html.twig` | Fixed top bar: "← Back to Course" link; course title; overall course progress bar + %. Not the site navbar. |
| 2 | **Authenticated navbar** | `lms_theme/templates/` | Full site nav with notification bell. Shown above the lesson header strip. |
| 3 | **Lesson content area** | `lms-lesson-view.html.twig` | Left/main column (~8 cols). Contains lesson type badge + chapter/lesson label, lesson title, and the type-specific content block (see variant table below). Also contains Resources collapsible, Note widget, Comments section, and Lesson Navigation bar. |
| 4 | **Curriculum sidebar** | `lms-lesson-view.html.twig` | Right column (~4 cols). Course title + progress bar; expandable chapter list; lesson list per chapter with completion icons (done / current / not-started / locked via `field_drip_days`). Passed as `curriculum` variable. |
| 5 | **Note widget** | `lms-lesson-view.html.twig` + `lms_note` AJAX API | Textarea for per-lesson notes. AJAX save/delete/get via `/api/lms/note/save`, `/api/lms/note/delete`, `/api/lms/note/get`. Pre-populated via `existing_note` variable (loaded from `lms_note` entity). Optionally stores `field_video_timestamp`. |
| 6 | **Comments section** | `lms-lesson-view.html.twig` + core comment module | Uses Drupal core comment field (`lms_lesson.field_comments`, type `lesson_comment`). AJAX form (`ajax_comments` library). Form altered by `lms_progress_form_comment_lesson_comment_form_alter` to hide subject/author/format selector. WARNING: hook_form_alter mutates and saves authenticated Role entity at form-alter time — security/performance concern. |
| 7 | **Resources collapsible** | `lms-lesson-view.html.twig` | `<details>` element listing `lms_lesson.field_attachments` files as download chips. |
| 8 | **Lesson navigation bar** | `lms-lesson-view.html.twig` | "← Previous Lesson" button, "Mark Complete" button (calls `ProgressApiController::markComplete` via POST to `/api/lms/progress/mark-complete`), "Next Lesson →" button. |
| 9 | **Footer** | `lms_theme/templates/` | Shared site footer (outside lesson layout) |

---

## Lesson Type Variants (Content Area Block Only)

| `field_type` | Content block rendered | Key template variable | Controller logic |
|---|---|---|---|
| `video` | HTML5 `<video>` or YouTube/Vimeo embed iframe. Controls: play/pause, progress scrubber, time display, volume, CC, quality, fullscreen. | `video_embed_url`, `video_position` | `lessonView()` resolves embed URL from `field_video_url` (YouTube/Vimeo detect) or `field_video_file` (direct MP4). Last position in seconds from `lms_user_lesson.field_video_position`. |
| `text` | Rich HTML body rendered from `lms_lesson.field_content`. Reading meta (estimated time, difficulty). Callout blocks, code snippets. | `lesson` (entity) | Standard field render via `rendered_attachments` or direct field output. |
| `quiz` | Link/button to `/quiz/{quiz_id}/take`. Quiz ID from `lms_lesson.field_quiz`. | `quiz_id` | Controller passes `quiz_id`; template renders button or iframe. |
| `h5p` | Embedded H5P iframe from `lms_lesson.field_h5p_embed` (raw embed code). | `lesson.field_h5p_embed` | Passed through `rendered_attachments`. |
| `scorm` | Iframe pointing to `public://scorm_extracted/{lesson_id}/index.html` (auto-extracted from ZIP by `lms_lesson` module on save). | `lesson` entity | Path built in template from lesson ID. SCORM launch file detected from `imsmanifest.xml`. |
| `pdf` | Download link or inline embed for `field_pptx_attachment` / `field_attachments` PDF files. | `rendered_attachments` | |
| `pptx` | Download link for `lms_lesson.field_pptx_attachment`. | `rendered_attachments` | |
| `zoom` | Zoom join block (`lms_zoom_join_block` Block plugin rendered in sidebar or content). Meeting ID from `lms_lesson.field_zoom_meeting_id`; schedule from `field_zoom_schedule`. Link to `/zoom/join/{meeting_id}`. | `lesson` entity | WARNING: `ZoomController::generateSignature()` references wrong service id — WebSDK JWT generation is broken. See [E02-issues](../../../../E-Quality/E02-issues/). |
| `mixed` | Combination of video + text + quiz/h5p/scorm — all relevant blocks rendered based on populated fields. | Multiple | Template checks each field for content. |

---

## Visible Content Inventory

| Item | Source entity / field | Controller variable | Notes |
|---|---|---|---|
| Course title | `lms_course.name` | `course` | |
| Course progress % | `lms_user_course.field_progress` | from `course` load | |
| Lesson title | `lms_lesson.name` | `lesson` | |
| Lesson type badge | `lms_lesson.field_type` | `lesson` | |
| Chapter + lesson label (e.g. "Chapter 3 · Lesson 3") | traversed from `curriculum` | `curriculum` | |
| Video embed URL | resolved from `lms_lesson.field_video_url` or `field_video_file` | `video_embed_url` | YouTube/Vimeo/direct |
| Video resume position (seconds) | `lms_user_lesson.field_video_position` | `video_position` | |
| H5P embed code | `lms_lesson.field_h5p_embed` | via `rendered_attachments` | |
| SCORM launch path | `public://scorm_extracted/{id}/index.html` | derived | Auto-built from lesson ID |
| Lesson body (text type) | `lms_lesson.field_content` | via entity render | |
| Attachments / resources | `lms_lesson.field_attachments` | `rendered_attachments` | |
| Existing note text | `lms_note.field_body` (current user + lesson) | `existing_note` | |
| Note video timestamp | `lms_note.field_video_timestamp` | `existing_note` | |
| Comments | `comment.lesson_comment` on `lms_lesson.field_comments` | `comments` | Drupal core comment field |
| Curriculum chapters | `lms_course.field_chapters` → `lms_chapter.field_lessons` | `curriculum` | Array with completion flags |
| Lesson completion flags | `lms_user_lesson.field_status` | per lesson in `curriculum` | not_started / in_progress / completed |
| Drip-lock status | `lms_lesson.field_drip_days` vs enrollment date | per lesson in `curriculum` | locked icon if drip not elapsed |
| Quiz ID (for quiz lessons) | `lms_lesson.field_quiz` | `quiz_id` | |
| Zoom meeting ID | `lms_lesson.field_zoom_meeting_id` | via `lesson` entity | |

**Side-effect (bug-class):** `lessonView()` mutates `lms_lesson.field_comments.status` to OPEN and calls `$lesson->save()` on every page load. This is an in-place entity mutation that bypasses business logic hooks.

---

## Implementation Status

| Item | Status |
|---|---|
| Route + controller (`lessonView`) | as-built |
| Template `lms-lesson-view.html.twig` | as-built |
| Auto-enroll on first visit (creates `lms_user_course`) | as-built |
| Video lesson (YouTube/Vimeo/MP4 embed, video position resume) | as-built |
| Text lesson (rich HTML body) | as-built |
| Quiz link (passes `quiz_id` to template) | as-built |
| H5P embed (`field_h5p_embed` passthrough) | as-built |
| SCORM iframe (ZIP extraction + launch file auto-detect) | as-built |
| PDF / PPTX download links | as-built |
| Zoom lesson (meeting ID + schedule display, join link) | as-built (join page exists); Zoom WebSDK signature generation broken (wrong service ID) — target-state |
| Mixed lesson type (multi-content) | as-built |
| Curriculum sidebar (chapters + completion flags + drip locks) | as-built |
| Note widget (AJAX save/delete/get) | as-built |
| Comments section (core comment, AJAX) | as-built; hook_form_alter has security concern (mutates Role entity) |
| Mark Complete (POST to `/api/lms/progress/mark-complete`) | as-built |
| Prev / Next lesson navigation | as-built |
| Lesson body mutation on load (`field_comments.status` save) | bug — [E02-issues](../../../../E-Quality/E02-issues/) |
| Undefined `$response` in comment submit redirect (non-AJAX path) | bug — [E02-issues](../../../../E-Quality/E02-issues/) |

---

## Related Pages

- [my-courses.md](./my-courses.md) — navigation source
- [continue-learning.md](./continue-learning.md) — redirect shortcut to this page
- [my-certificates.md](./my-certificates.md) — issued on course completion
