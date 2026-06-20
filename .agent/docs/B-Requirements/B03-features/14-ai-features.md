---
id: F-014
name: AI-Powered Content Generation
priority: Must
status: APPROVED
parent-requirements: [BR-011]
owning_modules: [lms_ai, lms_zoom]
primary_source: C05
story_anchors: []
---

# F-014 — AI-Powered Content Generation

## User story

As a **training manager or instructor**, I want AI to generate course structures, lesson content, slide decks, SCORM packages, and Zoom meeting agendas, so that I can produce professional learning content in a fraction of the time it would take manually.

## Description

`lms_ai` is a fully implemented Drupal plugin-based AI platform. It provides a `@AiProvider` plugin system (discovered via `Plugin/AiProvider/`) managed by `AiProviderManager`. Three providers ship with the module: OpenAI (GPT-4o, full text/image/JSON), Gemini (gemini-2.5-flash, full text/image/JSON), and Pollinations (free, image-only). The default provider is `openai` (configured in `lms_ai.settings`).

On top of the provider layer, two content-generation surfaces are built: a **simple course builder form** (`CourseBuilderForm` / `CourseBuilderService`) and a **large admin wizard** (`AiEngineUserForm`, 2361 lines) for step-by-step bulk content production — users, curriculum, courses, chapters, lessons, slides, SCORM packages, Google Slides, and quizzes. The queue worker `CourseContentGenerator` handles asynchronous lesson content generation via Drupal's Queue API.

`lms_zoom` extends lms_ai with an AI-generated Zoom meeting agenda feature (`ZoomAiService::generateAgenda()`), injected as an AJAX collapsible section in the lesson edit form when `field_type = zoom`.

**Critical known risk:** The AI pipeline has **no rollback or compensation mechanism**. If `CourseBuilderService::generateCourseStructure()` creates a `lms_course` entity but fails before all lessons are queued, the empty course remains in the database. Similarly, `AiEngineUserForm` AJAX handlers do not roll back partially created entities on failure — they log errors and count successes/failures, saving progress to JSON files incrementally. This is a known data-consistency risk documented in the risk register.

All API keys (OpenAI, Gemini, Google service-account RSA key) are stored **in plaintext in committed config YAML** (`lms_ai.settings.yml`, `google_api_client.google_api_service_client.everlms_bot`). See security risk — «secret in config — see security risk».

## In scope

- `lms_ai` Drupal plugin manager (`AiProviderManager`, `@AiProvider` annotation, `Plugin/AiProvider/` discovery)
- OpenAI provider: `generateJson()`, `generateText()`, `generateImage()` (DALL-E 3)
- Gemini provider: `generateJson()`, `generateText()`, `generateImage()` (Imagen 4.0)
- Pollinations provider: `generateImage()` only (no text/JSON — free, no API key)
- `CourseBuilderService`: one-step course+lesson entity creation + queue dispatch
- `CourseContentGenerator` queue worker: async lesson HTML content fill
- `AiEngineUserForm` (2361-line admin wizard): multi-step bulk pipeline — users, curriculum, courses, chapters, lessons, slides, SCORM, Google Slides, quizzes
- `ScormBuilderService`: generates SCORM 1.2 packages (ZIP) from course or slide data
- `GoogleSlideBuilderService`: duplicates Google Slides templates and fills AI content via Slides API
- Simple course builder action button "✨ Build with AI" on course collection page
- AI settings form (`/admin/config/lms/ai`): provider selection, API keys, model selection
- Zoom AI agenda generation (in `lms_zoom`): `ZoomAiService::generateAgenda()` + AJAX form injection on lesson edit form

## Out of scope

- Anthropic / Claude provider — not implemented; no `Plugin/AiProvider/` class exists for Anthropic
- Auto-chained AI pipeline (each step in `AiEngineUserForm` requires a manual button click — no automatic saga)
- Rollback / compensation on partial failure — explicitly absent (see risk note above)
- AI-powered search or semantic recommendations (F-013 GF/AP items)
- AI plagiarism detection for essay answers (F-013)
- AI chatbot tutor (F-013)
- Caching of AI responses to reduce API costs — not implemented

## Acceptance summary

A user with `administer lms_course` permission can visit `/manage/courses/ai-builder`, enter a topic, audience, and tone, and receive a draft `lms_course` entity (status=0) with associated `lms_lesson` entities queued for content generation. After running Drupal cron, lessons are populated with AI-generated HTML content and published (status=1). Full Given/When/Then lives in `B05-acceptance-criteria/F-014.md`.

## Content ownership / editability

| Visible content family | Business owner | Editor role | Expected edit surface | Notes |
|---|---|---|---|---|
| AI provider settings (API keys, model) | Platform admin | administrator | `/admin/config/lms/ai` | Keys stored in config — see security risk |
| AI engine wizard settings (prompts, program) | Training manager | administrator | `/admin/everlms-ai-engine` | `lms_ai.engine.settings` — ~758KB curriculum cached in config |
| Google Slides template ID | Platform admin | administrator | `/admin/everlms-ai-engine` (Setting section) | `google_slide_template_id` in engine settings |
| Generated course/lesson/chapter content | Instructor / manager | instructor, manager | Standard course/lesson/chapter edit forms | AI-generated, then human-editable |
| Zoom lesson agenda (AI) | Instructor | instructor | Lesson edit form, "Generate Zoom Agenda with AI" section | Via `ZoomAiService::generateAgenda()` |

## Plugin and service reference

### Provider plugin interface

| Method | Signature | Notes |
|---|---|---|
| `generateJson` | `(string $prompt, ?string $schema, array $options): ?array` | Schema appended to prompt as constraint |
| `generateImage` | `(string $prompt, array $options): ?string` | Returns base64-encoded image string |
| `generateText` | `(string $prompt, array $options): ?string` | Implemented by all providers but **NOT declared in interface** — interface gap |

### Provider comparison

| Provider | id | Text | JSON | Image | API key required | Model (default) |
|---|---|---|---|---|---|---|
| OpenAI | `openai` | Yes | Yes (json_object mode) | Yes (DALL-E 3, 1024×1024) | Yes («secret in config — see security risk») | `gpt-4o` |
| Gemini | `gemini` | Yes | Yes (responseMimeType: application/json) | Yes (Imagen 4.0, 1:1) | Yes («secret in config — see security risk») | `gemini-2.5-flash` |
| Pollinations | `pollinations` | No (returns NULL) | No (returns NULL) | Yes (free, flux model) | No | — |

### Services

| Service ID | Class | Purpose |
|---|---|---|
| `plugin.manager.lms_ai_provider` | `AiProviderManager` | Plugin discovery and instantiation |
| `lms_ai.course_builder` | `CourseBuilderService` | One-click course structure + lesson queue |
| `lms_ai.scorm_builder` | `ScormBuilderService` | SCORM 1.2 ZIP generation |
| `lms_ai.google_slide_builder` | `GoogleSlideBuilderService` | Google Slides duplication and fill |
| `lms_zoom.ai_service` | `ZoomAiService` | Zoom AI agenda + meeting summary processing |

### Routes

| Path | Handler | Permission |
|---|---|---|
| `/admin/config/lms/ai` | `AiSettingsForm` | administer site configuration |
| `/manage/courses/ai-builder` | `CourseBuilderForm` | administer lms_course |
| `/admin/everlms-ai-engine` | `AiEngineUserForm` | administer site configuration |
| `/admin/everlms-ai-engine/preview-users-json` | `AiPreviewController::previewUsersJson` | administer site configuration |

### Queue worker

| Queue name | Worker class | Cron time | What it does |
|---|---|---|---|
| `lms_ai_content_generator` | `CourseContentGenerator` | 60s | Loads lesson, calls OpenAI `generateText()`, sets `field_content` (full_html), publishes lesson. On exception: logs and re-throws (item stays in queue for retry). |

### AiEngineUserForm AJAX pipeline steps

| Step button | Handler | What it creates / updates |
|---|---|---|
| Generate Users | `ajaxRunUser()` | AI JSON → `JSON/ai_users.json` |
| Preview JSON | `ajaxPreviewJson()` | Modal with file contents |
| Approve Users | `ajaxApproveUser()` | Creates/updates Drupal users from JSON |
| Generate Image Prompts | `ajaxRequestImagePrompts()` | AI → per-user avatar prompt JSON |
| Generate Images | `ajaxRequestImages()` | AI images → managed files → `user_picture` |
| Curriculum Suggestion | `ajaxRequestCurriculumSuggestion()` | AI JSON → `JSON/ai_curriculum.json` |
| Generate Curriculum | `ajaxGenerateCurriculum()` | Creates `lms_course` + `lms_chapter` + `lms_lesson` entities |
| Courses Detail | `ajaxGenerateCoursesDetail()` | AI enriches course entities |
| Chapters Detail | `ajaxGenerateChaptersDetail()` | AI enriches chapter entities |
| Lessons Detail | `ajaxGenerateLessonsDetail()` | AI enriches lesson entities |
| Slide Content | `ajaxGenerateSlideContent()` | AI → slide JSON per lesson |
| Courses SCORM | `ajaxGenerateCoursesScorm()` | `ScormBuilderService` → SCORM ZIPs |
| Google Slide Prompt | `ajaxGenerateGoogleSlidePrompt()` | AI → Slides mapping JSON |
| Google Slide | `ajaxGenerateGoogleSlide()` | `GoogleSlideBuilderService` → published Slides |
| Generate Quiz | `ajaxGenerateQuiz()` | AI → quiz JSON per lesson |
| Approve Quiz | `ajaxApproveQuiz()` | Creates `lms_quiz` + `lms_question` entities from JSON |

> Each step is **manual** — the operator clicks one button at a time. No automatic chaining. Partial failure (any step) leaves already-created DB entities in place with no rollback.

### ScormBuilderService output

| Method | Output ZIP path | Content |
|---|---|---|
| `buildScormPackage()` | `public://lesson_detail/{course_id}_scorm_slider.zip` | SCORM 1.2: index.html (slider UI), js/scorm.js (pipwerks), imsmanifest.xml |
| `buildScormFromSlides()` | `public://lessons/scorm/lesson_{id}_scorm_slider.zip` | Same structure, dark glassmorphism CSS, per-slide title+html |

### GoogleSlideBuilderService

- Requires `google/apiclient` (Composer) + `google_api_client` contrib module.
- Service account: `everlms-slides-api@everlms.iam.gserviceaccount.com` (config: `google_api_client.google_api_service_client.everlms_bot`). RSA private key stored in config — «secret in config — see security risk».
- Workflow: duplicate template in Drive → fill `{{title}}`/`{{content}}` via Slides API batchUpdate → make presentation public (anyone/reader).

### Zoom AI (lms_zoom)

- `ZoomAiService::generateAgenda(string $topic): ?string` — calls `openai` provider, returns HTML agenda.
- `ZoomAiService::processSummaries(): void` — cron hook; loads past Zoom lessons, fetches recordings, **STUB**: VTT download is a hardcoded simulation string ("Simulated Transcript: …") — not a real HTTP fetch. Summarizes via OpenAI, appends to `field_content` with `<!-- AI_SUMMARY_DONE -->` marker.
- Lesson edit form injection: `hook_form_lms_lesson_form_alter()` adds collapsible "Generate Zoom Agenda with AI" section (AJAX); visible only when `field_type = zoom`.

> **Known bug in lms_zoom:** `ZoomController::generateSignature()` references a wrong service ID — see risk register.

## Dependencies (requirement-level only)

| Type | Reference | Reason |
|---|---|---|
| Feature | F-013 | AI quiz generation (F-013 AC-06 stub) feeds quiz entities the same way as AiEngineUserForm |
| Workflow | WF-001 | Enrollment/learning workflow — AI-generated courses enter this workflow on publish |
| Integration | lms_zoom (internal) | ZoomAiService depends on `plugin.manager.lms_ai_provider` |

## Open questions

- [ ] No rollback on partial failure: should a compensating cleanup service be added before production use of `AiEngineUserForm`?
- [ ] `generateText` is not declared in `AiProviderInterface` — should the interface be updated to include it?
- [ ] Zoom VTT summarization stub: real HTTP download needed; who is responsible for implementing it?
- [ ] Google Slides template ID (`1bVYcpMICXEea477wLCie0TuXpbJjJ_Ii`) is hardcoded in engine settings — should this be configurable per training program?
- [ ] API keys in config YAML: migration to Drupal Key module required before production deployment. Track in risk register (R1, R2).
