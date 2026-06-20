---
id: F-005
name: Content & Media Management
priority: Must
status: APPROVED
parent-requirements: [BR-002]
owning_modules: [lms_lesson, lms_zoom, lms_ai, lms_content_tools]
primary_source: C05
story_anchors: []
---

# F-005 — Content & Media Management

## User story

As an **instructor or manager**, I want to attach diverse media types (video, SCORM, H5P, PDF, live Zoom sessions) to lessons, so that learners experience rich, varied educational content rather than plain text.

## Description

A lesson (`lms_lesson`) is the atomic unit of learning content. The `field_type` field gates which media fields are relevant: `video`, `text`, `quiz`, `h5p`, `scorm`, `pdf`, `pptx`, `zoom`, or `mixed`. `LessonForm` enforces this with Drupal Form API `#states` — only the applicable media fields are shown to the editor.

Video support spans two channels: self-hosted files (`field_video_file`, mp4/webm/ogg, `public://lessons/videos/`) and external embed URLs (`field_video_url`, YouTube/Vimeo). SCORM 1.2 packages (`.zip`) are uploaded to `field_scorm_package`, extracted server-side via PHP `ZipArchive` on entity insert/update (`_lms_lesson_extract_scorm()`), and served from `public://scorm_extracted/{lesson_id}/`. H5P is supported via raw embed code in `field_h5p_embed`. Zoom lessons carry `field_zoom_meeting_id` + `field_zoom_schedule`; meeting creation and sync is handled by `lms_zoom` on entity presave.

Video position is persisted per-learner in `lms_user_lesson.field_video_position` (seconds). Progress is updated by the `ProgressApiController::markComplete` POST endpoint. File attachments (`field_attachments`, up to unlimited, pdf/doc/docx/ppt/pptx/xls/xlsx/zip) are downloadable by enrolled users only, enforced by `lms_course.module hook_entity_access`.

Content authoring uses CKEditor 5 (`full_html` format) for `field_content` (main body) and Drupal's managed file API for all uploads. The `lms_content_tools` module provides `SeedInputForm` for capturing AI generation parameters and the `everlms:import` Drush command for bulk content import from `.agent/generated/` manifest.

## In scope

- `lms_lesson.field_type` with 9 allowed values: text, video, quiz, h5p, scorm, pdf, pptx, zoom, mixed.
- Self-hosted video upload (`field_video_file`) — extensions: mp4, webm, ogg; stored at `public://lessons/videos/`.
- External video URL (`field_video_url`, max 512 chars) — YouTube and Vimeo embed resolution handled in `UserPortalController::lessonView`.
- Video resume: `lms_user_lesson.field_video_position` (seconds) persisted per learner.
- SCORM 1.2 package upload, server-side extraction via `ZipArchive`, `imsmanifest.xml` parsing for launch file auto-detection.
- H5P embed code field (`field_h5p_embed`) — content rendered in-page via iframe/embed; score tracking not implemented (H5P xAPI bridge not present).
- PDF attachment field (`field_pptx_attachment`) for PPTX lessons.
- Unlimited file attachments (`field_attachments`) for downloadable resources.
- Drip content: `field_drip_days` (days after enrollment before lesson unlocks).
- Free preview flag: `field_free_preview` — bypasses enrollment check for anonymous visitors.
- Zoom lesson type: meeting create/update on save via `lms_zoom.ZoomMeetingManager`; countdown block; Web SDK join page.
- CKEditor 5 (`full_html`) for rich text body field.
- `lms_content_tools` admin UI at `/workspace/content-tools` for seed input and content clean operations.
- AI-assisted SCORM package generation via `lms_ai.ScormBuilderService` (produces SCORM 1.2 ZIP with slider UI).
- Blog/news content: dual-track — `node:blog` for public frontend (`/news`), `lms_article` entity for admin CMS (`/manage/blog`). AI Blog Writer widget in `LmsArticleForm` when lms_ai is enabled.
- FAQ system (3 scopes — global, course, lesson) via `lms_faq` entity.

## Out of scope

- H5P score/xAPI bridge to `lms_user_lesson` — H5P completion is not tracked; `field_h5p_embed` is display-only.
- DRM or video encryption — no license server, no encrypted HLS.
- Playback speed controls, subtitles/caption upload — not implemented at the Drupal layer (depends on client player JS).
- Content revision/versioning — Drupal entity revisions not enabled for `lms_lesson`.
- Content scheduling (drip by date) — `field_drip_days` counts from enrollment date, not absolute calendar date.
- Webform integration — no Drupal Webform module linkage.
- Google Slides integration — `lms_ai.GoogleSlideBuilderService` exists but requires `google_api_client` contrib module and Google service-account credentials (see security risk in G02-risk-register.md (planned, layer not yet materialized) — planned, layer G — not yet materialized).
- SCORM 2004 — only SCORM 1.2 manifest parsing is implemented.
- VTT transcript download in `lms_zoom.ZoomAiService::processSummaries()` — stub: returns a hardcoded simulation string instead of fetching real recording.

## Acceptance summary

A manager or instructor can create a lesson, select a `field_type`, complete the appropriate conditional fields (visible via `#states`), and save. A learner enrolled in the parent course can view the lesson at `/learn/{course}/{lesson}`, play a video from where they left off, download attachments, and have their completion status updated via POST to `/api/lms/progress/mark-complete`. SCORM packages are playable in-browser after extraction. Zoom lesson types show a countdown block and a join button that opens the Zoom Web SDK player. Full Given/When/Then in `B05-acceptance-criteria/F-005.md`.

## Content ownership / editability

| Visible content family | Business owner | Editor role | Expected edit surface | Notes |
|---|---|---|---|---|
| Lesson body (field_content) | Instructor / Manager | manager, administrator | `/workspace/lessons/{id}/admin-edit` entity form | CKEditor 5, full_html |
| Lesson type + media fields | Instructor / Manager | manager, administrator | Same entity form with #states visibility | Conditional: only applicable fields shown |
| Blog articles (lms_article) | Content team | manager, content_editor | `/manage/blog/add`, `/manage/blog/{id}/edit` | AI Blog Writer available to admins |
| FAQ entries (lms_faq) | Content team | manager, administrator | `/workspace/content/faqs/*` via WorkspaceContentController | 3 scopes: global, course, lesson |
| SCORM packages | Instructional designer | manager, administrator | lesson edit form, field_scorm_package | Extraction automatic on save |

## Dependencies (requirement-level only)

| Type | Reference | Reason |
|---|---|---|
| Feature | F-001 | Content lives inside Course → Chapter → Lesson hierarchy |
| Feature | F-003 | Lesson progress marking triggers learning progress updates |
| Workflow | WF-001 | Enrollment + lesson access control |
| Integration | INT-zoom | Zoom lesson type requires active Zoom API credentials |

## Open questions

- [ ] Should H5P completion (xAPI/cmi.score) be piped into `lms_user_lesson.field_status`? Currently there is no bridge.
- [ ] What is the self-hosted video maximum file size limit? PHP `upload_max_filesize` / Drupal file size limit not confirmed in facts base.
- [ ] Is `field_drip_days` enforced in `hook_entity_access` or only in the lesson view UI? Enforcement path needs audit.
