---
module: lms_ai
service: ai
route-groups: [Admin, Manage]
status: DONE
documentation-mode: as-built
---

# C05 — Module: LMS AI Platform

**Directory:** `web/modules/custom/lms_ai/`
**Responsibility:** Provides the core AI plugin system (`AiProvider`) and services for AI-driven course content generation, SCORM packaging, and Google Slides integration; consumed by `lms_zoom` and the admin AI Engine wizard.

---

## Route Bindings

| Route Group | Prefix | Controller / Form | Paths |
|---|---|---|---|
| Admin | `/admin/config/lms` | `AiSettingsForm` | `/ai` — provider API keys |
| Admin | `/admin` | `AiEngineUserForm`, `AiPreviewController` | `/everlms-ai-engine`, `/everlms-ai-engine/preview-users-json` |
| Manage | `/manage/courses` | `CourseBuilderForm` | `/ai-builder` — AJAX course generation wizard |

---

## Public Interface

| Export | Type | Description |
|---|---|---|
| `plugin.manager.lms_ai_provider` | Service (`AiProviderManager`) | Drupal Plugin Manager for `AiProvider` plugins; consumed by `lms_zoom.ai_service` and all AI-using modules |
| `lms_ai.course_builder` | Service (`CourseBuilderService`) | Generates course structure + queues lesson content jobs |
| `lms_ai.scorm_builder` | Service (`ScormBuilderService`) | Packages course/lesson content as SCORM 1.2 ZIPs |
| `lms_ai.google_slide_builder` | Service (`GoogleSlideBuilderService`) | Publishes AI-generated content to Google Slides via Drive API |
| `AiProviderInterface` | PHP Interface | Contract for all AI provider plugins: `generateJson()`, `generateImage()` |
| `@AiProvider` | Annotation | Plugin discovery annotation |

---

## Internal Structure

```
web/modules/custom/lms_ai/
+-- lms_ai.info.yml           # deps: lms_base, key:key; lifecycle: stable
+-- lms_ai.routing.yml        # 4 routes
+-- lms_ai.services.yml       # 5 services
+-- lms_ai.links.menu.yml     # "EverLMS AI Engine" under system.admin_config
+-- lms_ai.links.action.yml   # "✨ Build with AI" action on lms_course collection
+-- src/
|   +-- Annotation/
|   |   +-- AiProvider.php              # @AiProvider annotation (id, label, description)
|   +-- Plugin/
|   |   +-- AiProviderInterface.php     # generateJson(), generateImage()
|   |   +-- AiProviderManager.php       # DefaultPluginManager; dir=Plugin/AiProvider; alter=lms_ai_provider_info; cache=lms_ai_provider_plugins
|   |   +-- AiProvider/
|   |   |   +-- OpenAiProvider.php      # id=openai; GPT-4o; generateJson/Text/Image
|   |   |   +-- GeminiProvider.php      # id=gemini; gemini-2.5-flash; generateJson/Text/Image
|   |   |   +-- PollinationsProvider.php # id=pollinations; image-only (free, no API key)
|   |   +-- QueueWorker/
|   |       +-- CourseContentGenerator.php # queue: lms_ai_content_generator; cron_time: 60s
|   +-- Service/
|   |   +-- CourseBuilderService.php    # generateCourseStructure(); no rollback
|   |   +-- ScormBuilderService.php     # buildScormPackage(), buildScormFromSlides()
|   |   +-- GoogleSlideBuilderService.php # buildSlideFromTemplate(); requires google_api_client contrib
|   +-- Form/
|   |   +-- AiSettingsForm.php          # ConfigFormBase: default_provider, openai/gemini keys+models
|   |   +-- CourseBuilderForm.php       # FormBase; AJAX generateAjax()
|   |   +-- AiEngineUserForm.php        # ConfigFormBase; 2361 lines; multi-step AI pipeline wizard
|   +-- Controller/
|       +-- AiPreviewController.php     # previewUsersJson() — modal JSON preview
+-- prompt/                             # *.txt prompt templates loaded by AiEngineUserForm
|   +-- user.txt
|   +-- user_image.txt
|   +-- curriculum_suggestion_prompt.txt
|   +-- course_detail.txt
|   +-- lesson_detail.txt
|   +-- chapter_detail.txt
|   +-- slide_content.txt
+-- JSON/                               # Persisted AI output files (not version-controlled secrets)
|   +-- ai_curriculum.json
|   +-- ai_users.json
|   +-- sample_quizzes.json
|   +-- scorm_lesson_350.json
+-- scripts/                            # Dev/seed drush php-script files (not module hooks)
```

---

## Plugin System: `AiProvider`

### Interface: `AiProviderInterface`

| Method | Signature | Notes |
|---|---|---|
| `generateJson` | `(string $prompt, ?string $schema, array $options): ?array` | Schema appended to prompt; response parsed with `JSON_THROW_ON_ERROR` |
| `generateImage` | `(string $prompt, array $options): ?string` | Returns base64-encoded image binary |
| `generateText` | `(string $prompt, array $options): ?string` | **Implemented by all providers but NOT declared in the interface** — interface gap, see Known Issues |

### Registered Providers (as-built)

| Plugin ID | Class | Capabilities | Config key |
|---|---|---|---|
| `openai` | `OpenAiProvider` | generateJson, generateText, generateImage (DALL-E 3) | `lms_ai.settings` → `openai.api_key`, `openai.model` (default: `gpt-4o`) — «secret in config — see security risk» |
| `gemini` | `GeminiProvider` | generateJson, generateText, generateImage (Imagen 4.0) | `lms_ai.settings` → `gemini.api_key`, `gemini.model` (default: `gemini-2.5-flash`) — «secret in config — see security risk» |
| `pollinations` | `PollinationsProvider` | generateImage only (GET `image.pollinations.ai`) | No API key; free, no SLA |

**OpenAI timeouts:** 60s for text, 90s for image generation.
**Gemini JSON:** strips markdown code fences (` ```json `) from response before parsing.

---

## Service Details

### `CourseBuilderService::generateCourseStructure(topic, targetAudience, tone)` (as-built)

1. Calls OpenAI `generateJson()` with course schema: `{title, description, sections[{title, lessons[{title, type, prompt_for_content}]}]}`.
2. Creates `lms_course` entity (status=0 draft).
3. For each lesson in each section: creates `lms_lesson` entity (name prefixed with section title, status=0, placeholder content).
4. Queues `lms_ai_content_generator` item with `{lesson_id, prompt_for_content, tone}`.
5. Appends lesson to `course.field_lessons`; saves course.
6. Returns Course entity on success.

**NO ROLLBACK:** If creation fails partway through, already-created entities remain. Exception caught at top level logs error and returns NULL, leaving orphaned drafts in the database.

### `CourseContentGenerator` (QueueWorker, as-built)

- Queue id: `lms_ai_content_generator`; cron time: 60s.
- `processItem($data)`: Loads lesson, calls OpenAI `generateText()` with full HTML lesson instruction, sets `field_content` (full_html format), publishes lesson (status=1). On exception: logs error and re-throws — item left for Drupal queue retry.

### `ScormBuilderService` (as-built)

| Method | Output | Notes |
|---|---|---|
| `buildScormPackage(array $course_data)` | `public://lesson_detail/{course_id}_scorm_slider.zip` | SCORM 1.2; index.html (slider UI), js/scorm.js (pipwerks wrapper), imsmanifest.xml; lesson content is placeholder |
| `buildScormFromSlides(array $lesson_data)` | `public://lessons/scorm/lesson_{id}_scorm_slider.zip` | Slide array `[{title, html}]`; dark glassmorphism CSS; more polished than buildScormPackage |

Uses PHP `ZipArchive`.

### `GoogleSlideBuilderService` (as-built — requires contrib)

- `buildSlideFromTemplate(templateId, generated_json, lesson_title, google_api_client_id)`:
  1. Loads Google API Client via `google_api_client.client` Drupal service.
  2. Duplicates template in Google Drive.
  3. If `generated_json['slide_content']` present: `buildDynamicSlides()` — duplicates layout slide (index 1) N times; fills `{{title}}`/`{{content}}` via Drive batchUpdate; strips HTML to plain text.
  4. Else (legacy): `flattenJsonToReplaceRequests()` — fixed-slot template replacement.
  5. Makes presentation public (Drive permission: anyone/reader).
  6. Returns new `presentationId` or FALSE.
- Requires: `google/apiclient` (Composer), `google_api_client` Drupal contrib module, Google service-account RSA key — «secret in config — see security risk».

### `AiEngineUserForm` (as-built — 2361 lines)

Admin wizard at `/admin/everlms-ai-engine`. ConfigFormBase for `lms_ai.engine.settings`. Multi-step manual pipeline: each button triggers one AJAX step.

| Section | AJAX Handler | What it does |
|---|---|---|
| Get Started | `ajaxRunUser()` | AI generates JSON user list → saves to `JSON/ai_users.json` |
| Get Started | `ajaxPreviewJson()` | Opens modal with `JSON/ai_users.json` contents |
| Get Started | `ajaxApproveUser()` | Creates/updates Drupal users from JSON; saves UIDs back |
| Get Started | `ajaxRequestImagePrompts()` | AI generates image prompt JSON per user |
| Get Started | `ajaxRequestImages()` | AI generates avatar images; saves as managed files; attaches to `user_picture` |
| Curriculum Designer | `ajaxRequestCurriculumSuggestion()` | AI → curriculum JSON → `JSON/ai_curriculum.json` + textarea |
| Curriculum Designer | `ajaxGenerateCurriculum()` | Creates `lms_course` + `lms_chapter` + `lms_lesson` entities from JSON |
| AI Course Builder | `ajaxGenerateCoursesDetail()` | AI enriches course entities |
| AI Course Builder | `ajaxGenerateChaptersDetail()` | AI enriches chapter entities |
| AI Course Builder | `ajaxGenerateLessonsDetail()` | AI enriches lesson entities |
| AI Course Builder | `ajaxGenerateSlideContent()` | AI generates `slide_content` JSON per lesson |
| AI Course Builder | `ajaxGenerateCoursesScorm()` | Calls `ScormBuilderService` to generate SCORM ZIPs |
| AI Course Builder | `ajaxGenerateGoogleSlidePrompt()` | AI generates Google Slides mapping JSON |
| AI Course Builder | `ajaxGenerateGoogleSlide()` | Calls `GoogleSlideBuilderService` |
| AI Course Builder | `ajaxGenerateQuiz()` | AI generates quiz JSON per lesson |
| AI Course Builder | `ajaxApproveQuiz()` | Creates quiz entities from JSON |

**NO SAGA / NO ROLLBACK:** Partial failures leave entities in inconsistent state. Handlers log errors and count successes/errors but do not clean up.

---

## Dependencies

| Dependency | Type | Why |
|---|---|---|
| `lms_base` | Internal | Shared entity/service base |
| `key:key` | External (Drupal contrib) | Declared in info.yml; **API keys are NOT stored via Key module — stored in config YAML instead** — «secret in config — see security risk» |
| `google/apiclient` | External (Composer) | Google Drive + Slides API for `GoogleSlideBuilderService` |
| `google_api_client` | External (Drupal contrib) | Google API client service wrapper |

---

## Prompt Files

Loaded by `AiEngineUserForm` from `prompt/*.txt` in module directory:

| File | Used by |
|---|---|
| `user.txt` | `ajaxRunUser()` |
| `user_image.txt` | `ajaxRequestImagePrompts()` |
| `curriculum_suggestion_prompt.txt` | `ajaxRequestCurriculumSuggestion()` |
| `course_detail.txt` | `ajaxGenerateCoursesDetail()` |
| `chapter_detail.txt` | `ajaxGenerateChaptersDetail()` |
| `lesson_detail.txt` | `ajaxGenerateLessonsDetail()` |
| `slide_content.txt` | `ajaxGenerateSlideContent()` |

Note: `google_slide_format.txt` is referenced in code but may not exist on disk — «unverified».

---

## Known Issues / Debt

| Ref | Severity | Description |
|---|---|---|
| RISK | Critical | OpenAI and Gemini API keys stored in plaintext in committed config YAML — «secret in config — see security risk». `key:key` is declared as a dependency but not used for secret storage. |
| RISK | High | `CourseBuilderService::generateCourseStructure()` has no rollback/compensation. Partial failures leave orphaned draft courses and lessons. |
| RISK | High | All 15+ `AiEngineUserForm` AJAX handlers have no saga or cleanup on partial failure — database inconsistency risk on any step failure. |
| DEBT | Medium | `generateText()` is implemented by all three providers but not declared in `AiProviderInterface` — callers use it via implicit contract only. |
| DEBT | Medium | `PollinationsProvider` is a free service with no SLA; not suitable for production AI features. |
| DEBT | Low | `JSON/` data files (`ai_users.json`, `ai_curriculum.json`) are stored inside the module directory on-disk — not suitable for multi-server deployments; not version-controlled. |
| DEBT | Low | `google_slide_format.txt` prompt file may not exist on disk; code references it without guarding for file absence. |
