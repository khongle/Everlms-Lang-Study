---
id: WF-006
name: Course Creation (Manual and AI-Assisted)
status: APPROVED
features: [F-001, F-014]
---

# WF-006 — Course Creation (Manual and AI-Assisted)

Two entry points exist in the as-built system: a fully manual creation flow via the workspace admin UI, and an AI-assisted builder that generates course structure and content via `lms_ai`. Both paths converge at the same curriculum hierarchy (Course → Chapter → Lesson) and both produce draft content that must be reviewed before publishing.

## Trigger

- **Manual**: Admin or Manager navigates to `/workspace/courses/create` or clicks "Add Course" from `/workspace/courses`.
- **AI-assisted**: Admin or Manager clicks the "Build with AI" action link (defined in `lms_ai.links.action.yml`, appears on the course entity collection) → navigates to `/manage/courses/ai-builder`.

## Actors

| Actor | Role |
|-------|------|
| Admin | Full control; can create, publish, assign any instructor |
| Manager | Same as Admin for course creation; permission `administer lms_course` required |
| System | Creates entities, queues AI jobs, dispatches events |
| AI Provider | OpenAI (`gpt-4o`) or Gemini (`gemini-2.5-flash`) — configured in `lms_ai.settings` |

## Preconditions

- Actor has the `administer lms_course` permission (required for `/workspace/courses/create` and `/manage/courses/ai-builder`).
- At least one `course_category` taxonomy term exists (seeded by `lms_base.install`).
- **AI path only**: `lms_ai` module is enabled, and at least one AI provider (OpenAI or Gemini) is configured with a valid API key in `lms_ai.settings` (`«secret in config — see security risk»`).

## Happy Path — Manual Creation

| Step | Actor | Action | System Response | Feature |
|------|-------|--------|----------------|---------|
| 1 | Admin/Manager | Navigate to `/workspace/courses/create` | `AdminController::courseCreateWizard()` renders `lms_admin_course_create` template | F-001 |
| 2 | Admin/Manager | Click "Create Course" from wizard | Navigate to `/workspace/courses/{id}/edit` — `AdminController::courseEditForm()` renders the `admin_edit` form mode of `lms_course` entity | F-001 |
| 3 | Admin/Manager | Fill in name, description, short description, category, level, language, price type, price, thumbnail, banner | Form saves `lms_course` entity; `status=TRUE` (published) by default; owner set to current user | F-001 |
| 4 | Admin/Manager | Navigate to `/manage/chapters/add` | `ChapterForm` renders; saves `lms_chapter` entity with `name`, `weight`, `field_description` | F-001 |
| 5 | Admin/Manager | Link chapter to course | Set `field_chapters` on the `lms_course` entity to include the new chapter | F-001 |
| 6 | Admin/Manager | Navigate to `/manage/lessons/add` | `LessonForm` renders; `field_type` select (video, text, quiz, h5p, scorm, pdf, pptx, zoom, mixed) controls which conditional fields appear via Drupal Form API `#states` | F-001 |
| 7 | Admin/Manager | Configure lesson per type | For video: `field_video_url` or `field_video_file`; for quiz: `field_quiz` reference; for zoom: `field_zoom_meeting_id` + `field_zoom_schedule`; for scorm: `field_scorm_package` ZIP upload | F-001 |
| 8 | System | On lesson save with `field_type=scorm` | `hook_entity_insert`/`hook_entity_update` in `lms_lesson.module` triggers `_lms_lesson_extract_scorm()`: extracts ZIP to `public://scorm_extracted/{lesson_id}/`, reads `imsmanifest.xml`, creates `index.html` redirect | F-001 |
| 9 | System | On lesson save with `field_type=zoom` | `hook_entity_presave` in `lms_zoom.module` triggers `ZoomMeetingManager::syncMeeting()`: creates or updates Zoom meeting via Zoom Server-to-Server OAuth | F-001 |
| 10 | Admin/Manager | Link lesson to chapter | Set `field_lessons` on the `lms_chapter` entity to include the lesson | F-001 |
| 11 | Admin/Manager | (Optional) Attach a quiz to a lesson | Set `field_quiz` on the lesson; quiz entity managed via `/workspace/quizzes/create` | F-001 |
| 12 | Admin/Manager | (Optional) Set certificate template | Set `field_certificate_template` on the `lms_course` entity | F-001 |
| 13 | Admin/Manager | Verify and publish course | Set `status=TRUE` (or leave at default); course appears in `/courses` catalog | F-001 |

## Happy Path — AI-Assisted Creation (CourseBuilderForm)

| Step | Actor | Action | System Response | Feature |
|------|-------|--------|----------------|---------|
| 1 | Admin/Manager | Navigate to `/manage/courses/ai-builder` | `CourseBuilderForm` renders with fields: topic (textfield), audience (textfield), tone (select: professional/casual/storytelling) | F-014 |
| 2 | Admin/Manager | Fill in topic, target audience, tone; submit | AJAX `generateAjax()` calls `CourseBuilderService::generateCourseStructure()` | F-014 |
| 3 | System | `CourseBuilderService` calls OpenAI `generateJson()` | Prompt includes schema: `{title, description, sections[{title, lessons[{title, type, prompt_for_content}]}]}` | F-014 |
| 4 | System | Creates `lms_course` entity with `status=0` (draft) | Also creates one `lms_lesson` entity per lesson entry with `status=0`, name prefixed with section title, placeholder content | F-014 |
| 5 | System | Queues content generation | One `lms_ai_content_generator` queue item per lesson containing `lesson_id` + `prompt_for_content` + tone | F-014 |
| 6 | System (cron) | `CourseContentGenerator` queue worker processes items | For each item: loads lesson, calls OpenAI `generateText()` with HTML-lesson instruction, sets `field_content` (full_html format), publishes lesson (`status=1`) | F-014 |
| 7 | Admin/Manager | `CourseBuilderForm` redirects to course edit URL on success | Review draft course; all chapters and lessons exist but content may still be generating via queue | F-014 |
| 8 | Admin/Manager | Review each lesson, edit AI-generated content | Manual quality control; lesson type defaults; quizzes not auto-generated by this path | F-001, F-014 |
| 9 | Admin/Manager | Publish course when satisfied | Set `status=TRUE`; course visible in catalog | F-001 |

## Happy Path — AI Engine Wizard (AiEngineUserForm, advanced)

The `AiEngineUserForm` at `/admin/everlms-ai-engine` provides a multi-step pipeline for bulk AI content generation. It is an advanced admin tool for initial data seeding, not the standard course creation path. Steps include curriculum suggestion, course/chapter/lesson generation, slide and SCORM generation, and quiz generation — each triggered individually via AJAX buttons. See `modules-cert-integration.md` (lms_ai section) for full details. This path is **as-built** but produces entities in potentially inconsistent state (no saga/rollback).

## Error Paths

| Step | Condition | System Response | Recovery |
|------|-----------|----------------|----------|
| 3 (AI) | OpenAI/Gemini API timeout or error | `CourseBuilderService::generateCourseStructure()` catches exception, logs error, returns NULL; `generateAjax()` shows Drupal error message | Re-submit the form with same or revised inputs |
| 4–5 (AI) | Partial entity creation before failure | Already-created `lms_course` and `lms_lesson` entities are NOT rolled back (no compensation pattern); orphaned draft entities remain in DB | Admin manually deletes orphaned entities via `/workspace/courses` |
| 6 (AI) | Queue worker fails on a lesson | Item re-queued by Drupal; lesson `status` stays 0 (unpublished); error logged | Monitor queue; check `drush queue:run lms_ai_content_generator` |
| 7 (Zoom) | Zoom API error during lesson save | `ZoomMeetingManager::syncMeeting()` logs error; falls back to 'me' user if instructor lookup fails | Verify `lms_zoom.settings` credentials; retry lesson save |
| 9 (SCORM) | ZIP extract fails | PHP `ZipArchive` error logged; lesson saved but SCORM content inaccessible | Re-upload valid SCORM ZIP |

## Success Criteria

- `lms_course` entity exists with at least one `lms_chapter` (via `field_chapters`) and at least one `lms_lesson` (via chapter's `field_lessons`).
- Course `status=TRUE` (published) and visible at `/courses/{id}`.
- Lesson types requiring external sync (zoom, scorm) have completed their sync (`field_zoom_meeting_id` populated; SCORM extracted to `public://scorm_extracted/{lesson_id}/`).
- AI-generated lessons have `field_content` populated and `status=1`.

## Business Rules

- **Curriculum hierarchy**: Course → Chapter (`field_chapters`) → Lesson (`field_lessons`). Both relationships are forward references stored on the parent entity.
- **Draft status for AI content**: `CourseBuilderService` creates `lms_course` and `lms_lesson` entities with `status=0` (draft). Queue workers publish lessons individually after content generation.
- **No rollback on AI failure**: Partial entity creation is not reversed. Orphaned drafts must be cleaned manually or via `everlms:clean` Drush command.
- **Lesson type drives UI and sync**: `field_type` on `lms_lesson` (video, text, quiz, h5p, scorm, pdf, pptx, zoom, mixed) determines which sub-fields are relevant; `LessonForm` uses `#states` for conditional visibility.
- **Zoom sync is automatic**: Any save of a lesson with `field_type=zoom` triggers `ZoomMeetingManager::syncMeeting()` in `hook_entity_presave`.
- **Publish permission**: `administer lms_course` is required. Instructors without this permission cannot create or edit courses through the workspace routes.
- **Course `field_duration`**: An integer field (unit: minutes) populated via course-level aggregation logic (calculated, not auto-summed in the as-built code — «unverified whether auto-calculation runs on save»).
- **AI provider keys stored in config**: `lms_ai.settings` contains API keys in plaintext YAML — `«secret in config — see security risk»`.

## Related Docs

- Features: [F-001 Course Management](../B03-features/01-course-management.md), [F-014 AI Features](../B03-features/14-ai-features.md)
- Workflow: [WF-002 Quiz Grading](./WF-002-quiz-grading.md) (quiz attached to lesson)
- Workflow: [WF-003 Certificate Issuance](./WF-003-certificate-issuance.md) (certificate template on course)
- Source: `web/modules/custom/lms_course/src/Controller/AdminController.php`, `web/modules/custom/lms_ai/src/Service/CourseBuilderService.php`, `web/modules/custom/lms_ai/src/Form/CourseBuilderForm.php`, `web/modules/custom/lms_lesson/lms_lesson.module`
