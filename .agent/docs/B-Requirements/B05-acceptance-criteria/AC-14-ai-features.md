---
feature: F-014
status: DRAFT
---

# B05 — Acceptance Criteria for F-014 — AI Features

> Per-feature Given/When/Then criteria for the AI capabilities described in [B03 F-014](../B03-features/14-ai-features.md). Implementation is grounded in `lms_ai` (Drupal Plugin system, OpenAI/Gemini/Pollinations providers, `CourseBuilderService`, `AiEngineUserForm`, `ScormBuilderService`, `GoogleSlideBuilderService`) and `lms_zoom` (`ZoomAiService` for agenda and post-meeting summaries). Where the draft specifies rollback/saga patterns, the facts base shows **no rollback exists** — those criteria are marked **target-state/planned**.

---

## AC-071: AI provider plugin abstraction (as-built)

**Priority:** Must
**Given** a developer or admin wants to add a new AI provider
**When** they create a class in any module's `Plugin/AiProvider/` directory annotated with `@AiProvider(id="...", label="...")`
**Then** `AiProviderManager` (extending `DefaultPluginManager`) discovers it automatically via the module handler without any hardcoded references to OpenAI or Gemini in business-logic controllers
**And** the active default provider is switchable via `lms_ai.settings` (`default_provider` key) without a code change
**And** `plugin.manager.lms_ai_provider` exposes the new provider to all consumers (CourseBuilderService, ZoomAiService, AiEngineUserForm)

## AC-072: AI provider settings form — API key storage (as-built)

**Priority:** Must
**Given** an admin with `administer site configuration` permission is at `/admin/config/lms/ai`
**When** the admin enters an OpenAI API key and/or Gemini API key and saves the form
**Then** the keys are stored in Drupal config object `lms_ai.settings` under `openai.api_key` and `gemini.api_key`
**And** the fields render as password-type inputs; on re-load the stored values are masked and are only updated when the admin submits a non-empty value
**Note:** Keys are stored in config YAML in plaintext — `«secret in config — see security risk»`. The Key module is NOT used despite it being listed as a dependency in `lms_ai.info.yml`.

## AC-073: AI course outline generation — CourseBuilderForm (as-built)

**Priority:** Must
**Given** an instructor with `administer lms_course` permission is at `/manage/courses/ai-builder`
**When** the instructor fills in `topic`, `audience`, and `tone` and submits the form
**Then** `CourseBuilderService::generateCourseStructure()` calls the configured AI provider's `generateJson()` with a schema expecting `{title, description, sections[{title, lessons[{title, type, prompt_for_content}]}]}`
**And** within 60 seconds (OpenAI timeout) a draft `lms_course` entity (status=0) is created with all lessons linked, each with a placeholder content body
**And** each lesson is queued as a `lms_ai_content_generator` queue item for background content generation
**And** the form redirects the instructor to the new course's edit URL on success

## AC-074: AI lesson content generation — queue worker (as-built)

**Priority:** Must
**Given** a `lms_ai_content_generator` queue item exists containing a `lesson_id` and `prompt_for_content`
**When** Drupal cron runs and the `CourseContentGenerator` queue worker processes the item (lease time: 60 s)
**Then** `generateText()` is called on the configured provider with the prompt instructing it to write full HTML lesson content
**And** the resulting HTML is saved to `field_content` (full_html) of the lesson entity
**And** the lesson is published (status=1)
**And** on exception the error is logged and the item is re-queued for retry by Drupal's queue system

## AC-075: AI course generation — partial failure behavior (as-built / known gap)

**Priority:** Must
**Given** `CourseBuilderService::generateCourseStructure()` has successfully created a course and some lessons but encounters an exception before all lessons are queued
**When** the exception propagates to the top-level catch block
**Then** the system logs the error and returns NULL to the caller
**And** the partially-created course and lesson entities remain in the database (no rollback is performed)
**Note:** Full rollback/compensation is **not implemented** (target-state/planned). This is a known data-consistency risk logged in `G02-risk-register.md`.

## AC-076: AI rollback on partial failure (target-state/planned)

**Priority:** Must
**Given** AI generation creates 15 out of 20 lesson entities and fails at the 16th
**When** the failure is detected after exhausting retries
**Then** all 15 partially-created entities are deleted and the course reverts to its pre-generation state (skeleton only)
**And** the instructor receives an error notification with a "Try Again" option
**Note:** This criterion is NOT met by the current code. Implementing it requires a saga/compensation pattern in `CourseBuilderService`.

## AC-077: AI Zoom agenda generation (as-built)

**Priority:** Should
**Given** an instructor is editing a Zoom-type lesson (field_type=zoom) in the lesson edit form
**When** the instructor clicks "Generate Zoom Agenda with AI" in the collapsible section injected by `hook_form_lms_lesson_form_alter()`
**Then** an AJAX request calls `ZoomAiService::generateAgenda()`, which invokes the OpenAI provider's `generateText()` with the lesson topic as input
**And** the returned HTML agenda string is pushed into the `field_content` textarea via the AJAX callback within the form

## AC-078: AI Zoom meeting summary — cron processing (as-built with stub)

**Priority:** Should
**Given** a Zoom-type lesson has a meeting end time more than 1 hour in the past and does NOT already contain the `<!-- AI_SUMMARY_DONE -->` marker in `field_content`
**When** `hook_cron()` fires and calls `ZoomAiService::processSummaries()` (batch of 5 per cron run)
**Then** the service fetches recordings from the Zoom API, locates the TRANSCRIPT or TIMELINE file, passes the transcript to OpenAI for summarization, and appends the summary HTML to `field_content` followed by `<!-- AI_SUMMARY_DONE -->`
**And** the lesson entity is saved so future cron runs skip it
**Note (stub):** The VTT download step uses a hardcoded simulated transcript string instead of a real HTTP fetch — real transcript download is not yet implemented.

## AC-079: AI Engine wizard — curriculum generation (as-built)

**Priority:** Must
**Given** an admin with `administer site configuration` is at `/admin/everlms-ai-engine`
**When** the admin clicks "Request Curriculum Suggestion" (AJAX handler `ajaxRequestCurriculumSuggestion`)
**Then** the AI provider generates a curriculum JSON and the result is saved to `JSON/ai_curriculum.json` inside the module directory
**And** the generated curriculum JSON is displayed in the `curriculum_suggestion` textarea in the form
**And** subsequent steps (Generate Curriculum, Generate Courses Detail, Generate Chapters Detail, etc.) are each triggered separately by individual buttons — the pipeline is fully manual, step-by-step with no automatic chaining

## AC-080: AI image generation — Pollinations provider (as-built, image-only)

**Priority:** Should
**Given** the active provider is set to `pollinations` (no API key required)
**When** `generateImage()` is called with a text prompt
**Then** a GET request is made to `https://image.pollinations.ai/prompt/{encoded_prompt}?width=1024&height=1024&nologo=true&model=flux`
**And** the raw binary response is returned as a base64-encoded string
**And** calling `generateJson()` or `generateText()` on the Pollinations provider logs a warning and returns NULL, making it unsuitable as the default provider for course building

---

## Local traceability (F-014)

| AC ID | Scenario | Implementing Module | Status |
|-------|----------|---------------------|--------|
| AC-071 | AI provider plugin abstraction | lms_ai | as-built |
| AC-072 | API key storage (admin form) | lms_ai | as-built |
| AC-073 | AI course outline — CourseBuilderForm | lms_ai | as-built |
| AC-074 | AI lesson content — queue worker | lms_ai | as-built |
| AC-075 | Partial failure behavior (no rollback) | lms_ai | as-built (known gap) |
| AC-076 | Rollback on partial failure | lms_ai (planned) | planned |
| AC-077 | Zoom AI agenda generation | lms_zoom / lms_ai | as-built |
| AC-078 | Zoom meeting summary via cron | lms_zoom / lms_ai | as-built (partial stub) |
| AC-079 | AI Engine wizard — curriculum | lms_ai | as-built |
| AC-080 | Pollinations image-only provider | lms_ai | as-built |

> Cross-feature traceability lives in `E05-traceability.md`.
