# D03 — Story Spec: lms_ai

**Story ID:** lms_ai
**Story type:** module
**Title:** AI Platform — Multi-Provider Plugin System, Course Builder, and SCORM/Google Slides Generation
**Primary source:** `C-Design/C05-modules/lms_ai.md`
**Owning module(s):** lms_ai
**Service(s):** plugin.manager.lms_ai_provider (AiProviderManager), lms_ai.course_builder (CourseBuilderService), lms_ai.scorm_builder (ScormBuilderService), lms_ai.google_slide_builder (GoogleSlideBuilderService)
**Epic:** E-08 (Integration & AI)
**Contributes to Feature(s):** F-014 (AI Features)
**Design source(s):** `../C05-modules/lms_ai.md`
**Round:** N/A
**Driver:** SA directive
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_ai`

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 4
**Splitting strategy:**
- S1: Plugin system — AiProviderManager, @AiProvider annotation, AiProviderInterface, OpenAiProvider, GeminiProvider, PollinationsProvider, services.yml
- S2: CourseBuilderService (queue-based lesson generation), CourseContentGenerator queue worker, CourseBuilderForm, AiSettingsForm
- S3: ScormBuilderService (SCORM 1.2 zip), GoogleSlideBuilderService (Drive API), AiPreviewController, routing.yml, links
- S4: AiEngineUserForm (2361-line multi-step AJAX wizard), prompt/*.txt files, JSON data files, developer scripts

## Scope (from primary source)

Core AI platform module for EverLMS. Provides a Drupal Plugin system (`@AiProvider`) that discovers AI provider implementations (OpenAI, Gemini, Pollinations). Three services orchestrate content generation: `CourseBuilderService` generates course structure + queues lesson content; `ScormBuilderService` packages lesson content as SCORM 1.2 ZIP; `GoogleSlideBuilderService` creates Google Slides presentations via Drive API. `AiEngineUserForm` is a 2361-line admin wizard with 15+ AJAX handlers covering the full curriculum pipeline: users → curriculum → courses → chapters → lessons → slides/SCORM/Google Slides → quizzes.

**Known risk (as-built):** No rollback or compensation pattern exists. Partial pipeline failures leave dangling entities in the database. If `generateCourseStructure()` creates a Course but fails before queuing lessons, the empty course remains and must be manually deleted.

## Source Documents

| Source | Why loaded |
|---|---|
| `../C05-modules/lms_ai.md` | Owning module design |
| `../../B-Requirements/B03-features/14-ai-features.md` | AI feature requirements |
| `../../B-Requirements/B06-integration-requirements/` | OpenAI/Gemini/Google API contracts |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Pluggable AI provider system (OpenAI, Gemini, Pollinations) | B03-features/14-ai-features | Acceptance |
| AI course structure generation (topic, audience, tone → Draft Course + Lesson queue items) | B03-features/14-ai-features | Acceptance |
| Lesson content generation via queue worker (asynchronous) | B03-features/14-ai-features | Acceptance |
| SCORM 1.2 package export from course chapters/lessons | B03-features/14-ai-features | Acceptance |
| Google Slides generation from AI-generated slide content | B03-features/14-ai-features | Acceptance |
| AiEngineUserForm multi-step pipeline (user seeding → curriculum → courses → slides → quizzes) | B03-features/14-ai-features | Acceptance |
| OpenAI DALL-E 3 image generation | B03-features/14-ai-features | Acceptance |
| Gemini Imagen 4.0 image generation | B03-features/14-ai-features | Acceptance |
| Rollback/compensation on partial pipeline failure | B03-features/14-ai-features | target-state / planned (not implemented) |
| generateText() declared in AiProviderInterface | B03-features/14-ai-features | target-state / planned (missing from interface) |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_ai/lms_ai.info.yml` | Create | Module declaration (depends on key:key) | — | S1 |
| `web/modules/custom/lms_ai/lms_ai.services.yml` | Create | AiProviderManager, logger, CourseBuilderService, ScormBuilderService, GoogleSlideBuilderService | — | S1 |
| `web/modules/custom/lms_ai/lms_ai.routing.yml` | Create | settings, course_builder_form, engine_users_form, preview_users_json routes | — | S3 |
| `web/modules/custom/lms_ai/lms_ai.links.menu.yml` | Create | AI Engine admin menu link | — | S3 |
| `web/modules/custom/lms_ai/lms_ai.links.action.yml` | Create | Build with AI action on course collection | — | S3 |
| `web/modules/custom/lms_ai/src/Annotation/AiProvider.php` | Create | @AiProvider annotation (id, label, description) | — | S1 |
| `web/modules/custom/lms_ai/src/Plugin/AiProviderInterface.php` | Create | generateJson(), generateImage() — NOTE: generateText() missing from interface | — | S1 |
| `web/modules/custom/lms_ai/src/Plugin/AiProviderManager.php` | Create | DefaultPluginManager for Plugin/AiProvider/ directory | — | S1 |
| `web/modules/custom/lms_ai/src/Plugin/AiProvider/OpenAiProvider.php` | Create | GPT-4o text/JSON/image (DALL-E 3), 60s/90s timeout | — | S1 |
| `web/modules/custom/lms_ai/src/Plugin/AiProvider/GeminiProvider.php` | Create | gemini-2.5-flash text/JSON/image (Imagen 4.0) | — | S1 |
| `web/modules/custom/lms_ai/src/Plugin/AiProvider/PollinationsProvider.php` | Create | Image-only (free, no API key) | — | S1 |
| `web/modules/custom/lms_ai/src/Service/CourseBuilderService.php` | Create | generateCourseStructure() — no rollback | — | S2 |
| `web/modules/custom/lms_ai/src/Plugin/QueueWorker/CourseContentGenerator.php` | Create | Queue worker for lesson content generation | — | S2 |
| `web/modules/custom/lms_ai/src/Service/ScormBuilderService.php` | Create | buildScormPackage(), buildScormFromSlides() via ZipArchive | — | S3 |
| `web/modules/custom/lms_ai/src/Service/GoogleSlideBuilderService.php` | Create | buildSlideFromTemplate() via google/apiclient | — | S3 |
| `web/modules/custom/lms_ai/src/Form/AiSettingsForm.php` | Create | ConfigFormBase for lms_ai.settings (provider keys + models) | — | S2 |
| `web/modules/custom/lms_ai/src/Form/CourseBuilderForm.php` | Create | Quick AJAX form (topic, audience, tone) | — | S2 |
| `web/modules/custom/lms_ai/src/Form/AiEngineUserForm.php` | Create | 2361-line multi-step AJAX wizard | — | S4 |
| `web/modules/custom/lms_ai/src/Controller/AiPreviewController.php` | Create | previewUsersJson() — modal JSON viewer | — | S3 |
| `web/modules/custom/lms_ai/prompt/*.txt` | Create | 8 prompt template files for pipeline steps | — | S4 |
| `web/modules/custom/lms_ai/JSON/*.json` | Create | Persisted pipeline output files | — | S4 |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| lms_base | Intra-module | info.yml |
| key:key (Drupal contrib) | External | info.yml dependency (for future key management — not yet used for secrets) |
| google/apiclient (Composer) | External | GoogleSlideBuilderService |
| google_api_client (Drupal contrib) | External | GoogleSlideBuilderService |
| lms_zoom (uses lms_ai.provider for Zoom AI summaries) | Cross-module | lms_zoom.info.yml dependency on lms_ai |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| DefaultPluginManager + annotation discovery | AiProviderManager | As-built |
| Queue worker annotation (lms_ai_content_generator) | CourseContentGenerator | As-built |
| JSON_THROW_ON_ERROR for API response parsing | OpenAiProvider::generateJson() | As-built |

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission | Revision / moderation | Translation | Cache invalidation | Smoke evidence |
|---|---|---|---|---|---|---|---|---|
| `/admin/config/lms/ai` | AI provider API keys + models | lms_ai.settings config | Same path | administer site configuration | None | None | Config cache | As-built |
| `/manage/courses/ai-builder` | Quick AI course generator | CourseBuilderService | Same path | administer lms_course | None | None | N/A (AJAX) | As-built |
| `/admin/everlms-ai-engine` | Full AI pipeline wizard | AiEngineUserForm | Same path | administer site configuration | None | None | N/A (AJAX) | As-built |

**Hardcoded content exceptions:** JSON data files (`JSON/ai_curriculum.json`, `JSON/ai_users.json`) are written to the module directory — not `public://` or a configurable path. This is a deployment concern for read-only filesystem environments.

## Feature Flags Introduced or Read

None.

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-AI-001 | Unit | AiProviderManager discovers openai, gemini, pollinations plugins | Plugin discovery AC |
| T-AI-002 | Integration | OpenAiProvider::generateJson() returns structured array for valid schema | JSON gen AC |
| T-AI-003 | Integration | GeminiProvider::generateJson() strips markdown code block prefix | JSON gen AC |
| T-AI-004 | Integration | PollinationsProvider::generateJson() returns NULL (unsupported) | Stub AC |
| T-AI-005 | Integration | CourseBuilderService::generateCourseStructure() creates lms_course + queue items | Course build AC |
| T-AI-006 | Integration | CourseContentGenerator queue worker sets field_content on lesson entity | Queue worker AC |
| T-AI-007 | Integration | ScormBuilderService::buildScormPackage() produces valid ZIP with imsmanifest.xml | SCORM AC |
| T-AI-008 | Unit | Partial failure in generateCourseStructure() leaves orphan course (regression guard) | No-rollback AC |

## Gate Results (per Story)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Module installs; plugin manager discovers providers; queue worker registered | — | — |
| G2 Lint | PASS | No blocking violations | — | — |
| G3 Tests | PASS | Provider generation, course builder, SCORM all smoke-tested | — | — |
| G4 Adversarial | PASS | Rollback gap logged; secrets risk documented; generateText() interface gap noted | — | — |

## Notes

- **No rollback / compensation (risk):** `CourseBuilderService::generateCourseStructure()` and all `AiEngineUserForm` AJAX handlers do not roll back on partial failure. Orphan entities (courses, chapters, lessons) may exist after failed pipeline runs. This is acceptable for internal/admin tooling but should be addressed before public-facing AI generation is exposed.
- **generateText() missing from interface (architecture gap):** All three providers implement `generateText()`, but the method is not declared in `AiProviderInterface`. Callers that type-hint the interface cannot call generateText() without a cast or workaround. Fix: add `generateText(string $prompt, array $options): ?string` to the interface.
- **API secrets in config:** `openai.api_key`, `gemini.api_key`, and Google service-account RSA key all stored in committed config YAML — `«secret in config — see security risk»`. The `key:key` module dependency in info.yml hints at a planned migration to proper key storage that was not completed.
- **JSON files in module directory:** `JSON/ai_curriculum.json` and `JSON/ai_users.json` are written to the module source directory (`web/modules/custom/lms_ai/JSON/`). On read-only filesystem deployments (e.g., containerized prod), these writes will fail. Recommend moving to `public://` or a temp directory.
- **Developer scripts:** `scripts/*.php` are `drush php-script` runner scripts for bulk data seeding. They are not module hooks and are not auto-executed. Safe to leave in place.
