---
feature: F-005
status: DRAFT
---

# B05 — Acceptance Criteria for F-005 — Content & Media

> Feature source: `../B03-features/05-content-media.md`
> Implementing modules: `lms_lesson`, `lms_course`, `lms_blog`
> Implementation maturity: **as-built** for file storage, field definitions, SCORM extraction, and lesson form conditional logic. H5P tracking and video completion triggers are client-side behaviours.

---

## AC-049: Lesson form shows only relevant fields for the selected type

**Priority:** Must
**Given** an admin or manager is editing a `lms_lesson` entity at `/manage/lessons/{lms_lesson}/edit` or the workspace equivalent
**When** the user changes `field_type` to one of: `video`, `text`, `quiz`, `h5p`, `scorm`, `pdf`, `pptx`, `zoom`, `mixed`
**Then** `LessonForm` uses Drupal Form API `#states` to show only the fields relevant to the selected type (e.g. `field_video_url` and `field_video_file` for `video`; `field_scorm_package` for `scorm`; `field_zoom_meeting_id` and `field_zoom_schedule` for `zoom`)
**And** irrelevant fields are hidden (not removed from the entity — they remain on the entity type)

**Status:** as-built — `LessonForm` implements `#states` conditional visibility for all 9 field groups.

---

## AC-050: Video lesson renders YouTube or Vimeo embed or HTML5 player

**Priority:** Must
**Given** a `lms_lesson` with `field_type=video` has either a `field_video_url` (YouTube or Vimeo URL) or an uploaded `field_video_file` (mp4/webm/ogg)
**When** an enrolled student accesses the lesson via `/learn/{lms_course}/{lms_lesson}`
**Then** `UserPortalController::lessonView()` resolves the video embed URL (YouTube/Vimeo iframe or direct file URL) and passes it to the `lms_lesson_view` template as `video_embed_url`
**And** the template renders the appropriate player (iframe embed or `<video>` element)

**Status:** as-built — `lessonView()` handles YouTube/Vimeo URL detection and direct file fallback.

---

## AC-051: SCORM package is extracted on lesson save and launch file is auto-detected

**Priority:** Must
**Given** a `lms_lesson` with `field_type=scorm` has a ZIP file in `field_scorm_package`
**When** the lesson entity is inserted or updated
**Then** `lms_lesson.module`'s `_lms_lesson_extract_scorm()` helper runs via `hook_entity_insert` / `hook_entity_update`
**And** the ZIP is extracted to `public://scorm_extracted/{lesson_id}/` using PHP `ZipArchive`
**And** `imsmanifest.xml` is parsed to detect the launch file; if no launch file is found, an `index.html` redirect is created

**Status:** as-built — SCORM extraction logic is fully implemented.

---

## AC-052: H5P embed code is stored and rendered

**Priority:** Must
**Given** a `lms_lesson` with `field_type=h5p` has an H5P embed code in `field_h5p_embed`
**When** an enrolled student accesses the lesson
**Then** the lesson view template renders the embed code verbatim in the lesson content area

**Status:** as-built — `field_h5p_embed` is a `string_long` field; `LessonForm` shows it for `type=h5p`; `lessonView()` passes the lesson entity to the template. Note: xAPI completion tracking from H5P to `lms_user_lesson` is **not implemented** — there is no server-side xAPI endpoint. H5P completion does not automatically mark the lesson complete.

---

## AC-053: PPTX file is attached to a lesson and available for download

**Priority:** Must
**Given** a `lms_lesson` with `field_type=pptx` has an uploaded file in `field_pptx_attachment` (ppt/pptx)
**When** an enrolled student accesses the lesson
**Then** the lesson view template renders a link to the file stored at `public://lessons/pptx/`
**And** the student can download the file

**Status:** as-built — `field_pptx_attachment` field definition and form visibility are implemented. Rendering depends on the `lms-lesson-view.html.twig` template's handling of `rendered_attachments`.

---

## AC-054: General file attachments are available on any lesson

**Priority:** Must
**Given** a `lms_lesson` has one or more files in `field_attachments` (pdf, doc, docx, ppt, pptx, xls, xlsx, zip; stored at `public://lessons/attachments/`)
**When** a student accesses the lesson
**Then** the lesson view renders the attachment file list with download links

**Status:** as-built — `field_attachments` is defined with unlimited cardinality; `lessonView()` passes `rendered_attachments` to the template.

---

## AC-055: Lesson free preview flag allows anonymous or unenrolled users to view the lesson

**Priority:** Must
**Given** a `lms_lesson` with `field_free_preview=true` exists inside a course
**When** an unenrolled visitor views the course detail page
**Then** the lesson is rendered as accessible (not locked) in the curriculum display
**And** the course detail page at `/courses/{lms_course}` shows a "preview available" indicator for that lesson

**Status:** as-built — `field_free_preview` is defined on the lesson entity and is consumed by `CoursePublicController::detail()` when building the curriculum view-model.

---

## AC-056: Course thumbnail is stored and displayed on catalog and detail pages

**Priority:** Must
**Given** a `lms_course` has an image uploaded to `field_thumbnail` (stored at `public://courses/thumbnails/`)
**When** the catalog page (`/courses`) or course detail page (`/courses/{lms_course}`) is rendered
**Then** the thumbnail image URL is included in the course card data built by `buildCourseCards()` (private helper in `CoursePublicController`)
**And** the template renders the image with an `alt` attribute

**Status:** as-built — `field_thumbnail` is defined with `alt_field=true`; `buildCourseCards()` includes thumbnail data.

---

## AC-057: Blog frontend reads from node:blog; lms_article is admin-only

**Priority:** Must
**Given** Drupal `node:blog` entities (core node type) and `lms_article` entities both exist in the database
**When** a visitor accesses `/news`
**Then** `LmsBlogController::index()` queries `node` storage for `type=blog` entities (not `lms_article`) and renders the `lms_blog_list` template
**And** when a visitor accesses `/news/{node}`, `LmsBlogController::view()` renders a `node` entity (not an `lms_article`)

**Status:** as-built — this dual-track architecture is intentional. `lms_article` entities are only accessible via the admin CRUD routes at `/manage/blog`. The frontend blog at `/news` reads from `node:blog`. The AI blog writer in `LmsArticleForm` is available to admins creating `lms_article` entities.

---

## AC-058: AI blog content generation is available to admins but requires lms_ai configuration

**Priority:** Should
**Given** the `lms_ai` module is enabled and an OpenAI API key is configured in `lms_ai.settings` (`«secret in config — see security risk»`)
**And** the current user has `administer lms_blog` permission
**When** the user creates or edits an `lms_article` entity at `/manage/blog/add` or `/manage/blog/{lms_article}/edit`
**Then** `LmsArticleForm::buildForm()` injects an AI writer widget (topic, keywords, style fields plus a "Generate" button)
**And** clicking Generate triggers `LmsArticleForm::generateAiContent()` (AJAX callback) which calls the OpenAI provider, receives a JSON response with title, HTML content, seo_title, seo_description, and fills the form fields via jQuery AJAX commands

**Status:** as-built — `LmsArticleForm` is fully implemented. Requires `lms_ai` module and valid OpenAI key; degrades gracefully if the `plugin.manager.lms_ai_provider` service is not available.

---

## Local traceability (F-005)

| AC ID | Test Case | Implementing Story / Module | Status |
|-------|-----------|-----------------------------|--------|
| AC-049 | T-049 | lesson-form-states / lms_lesson | PENDING |
| AC-050 | T-050 | video-lesson-embed / lms_progress + lms_lesson | PENDING |
| AC-051 | T-051 | scorm-extract / lms_lesson | PENDING |
| AC-052 | T-052 | h5p-embed / lms_lesson | PENDING |
| AC-053 | T-053 | pptx-attach / lms_lesson | PENDING |
| AC-054 | T-054 | file-attachments / lms_lesson | PENDING |
| AC-055 | T-055 | free-preview / lms_lesson + lms_course | PENDING |
| AC-056 | T-056 | course-thumbnail / lms_course | PENDING |
| AC-057 | T-057 | blog-dual-track / lms_blog | PENDING |
| AC-058 | T-058 | ai-blog-writer / lms_blog + lms_ai | PENDING |

> Cross-feature traceability lives in `E05-traceability.md`.
