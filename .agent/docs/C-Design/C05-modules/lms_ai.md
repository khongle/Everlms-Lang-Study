# lms_ai — AI Platform Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_ai` |
| Name | LMS AI Platform |
| Layer | **Integration** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base`, `key:key` |

## Mô tả

Module AI core của EverLMS. Định nghĩa `AiProvider` plugin system để swap AI providers (OpenAI, Gemini, Pollinations). Cung cấp services để build course content từ AI, generate SCORM packages, tích hợp Google Slides. Có QueueWorker để xử lý course generation async.

---

## Provided Entities

Không có entity.

---

## Provided Routes

| Route ID | Path | Form/Controller | Access |
|----------|------|----------------|--------|
| `lms_ai.settings` | `/admin/config/lms/ai` | `AiSettingsForm` | `administer site configuration` |
| `lms_ai.course_builder_form` | `/manage/courses/ai-builder` | `CourseBuilderForm` | `administer lms_course` |
| `lms_ai.engine_users_form` | `/admin/everlms-ai-engine` | `AiEngineUserForm` | `administer site configuration` |
| `lms_ai.preview_users_json` | `/admin/everlms-ai-engine/preview-users-json` | `AiPreviewController::previewUsersJson` | `administer site configuration` |

---

## Provided Services

| Service ID | Class | Purpose |
|-----------|-------|---------|
| `plugin.manager.lms_ai_provider` | `AiProviderManager` | Plugin manager cho AiProvider plugins; inject container.namespaces, cache.discovery, module_handler |
| `logger.channel.lms_ai` | (parent: logger.channel_base) | Dedicated log channel |
| `lms_ai.course_builder` | `CourseBuilderService` | Build course structure từ AI; inject plugin.manager, entity_type.manager, queue, logger |
| `lms_ai.scorm_builder` | `ScormBuilderService` | Generate SCORM package từ AI content; inject entity_type.manager, file_system, logger |
| `lms_ai.google_slide_builder` | `GoogleSlideBuilderService` | Generate Google Slides; inject logger.factory, config.factory, entity_type.manager |

---

## Provided Plugins

### Plugin Type: `AiProvider`

| Plugin ID | Class | Provider |
|-----------|-------|---------|
| `openai` | `OpenAiProvider` | OpenAI GPT API |
| `gemini` | `GeminiProvider` | Google Gemini API |
| `pollinations` | `PollinationsProvider` | Pollinations.ai (free tier) |

**Interface:** `AiProviderInterface`
**Annotation:** `@AiProvider` (class `Drupal\lms_ai\Annotation\AiProvider`)

### QueueWorker

| Class | Purpose |
|-------|---------|
| `CourseContentGenerator` | Process async course content generation jobs từ Drupal Queue |

---

## Forms

| Form | Purpose |
|------|---------|
| `AiSettingsForm` | Cấu hình AI provider, API keys |
| `CourseBuilderForm` | AI course wizard (dùng `AjaxQuizTrait`) |
| `AiEngineUserForm` | Test/preview AI output |

---

## Hook Implementations

Không có `.module` file.

---

## Templates & Cache Strategy

Không có template. AI responses không cache. Settings form cached qua Drupal config.

---

## Related Units

- **C05** `05-integration-theme.md` — layer overview
- `lms_zoom.md` — consume `plugin.manager.lms_ai_provider` qua ZoomAiService
- `lms_blog.md` — depends on lms_ai
- `lms_content_tools.md` — AI content generation tools

---

## Known Issues / Debt

- RISK (A10): API keys có thể được lưu trong plain config thay vì `key:key` module — dù dependencies khai báo `key:key`.
- `PollinationsProvider` là free tier không SLA — không phù hợp production.
- `CourseBuilderService` inject `@queue` nhưng queue backend cần được config đúng.
- `GoogleSlideBuilderService` cần Google OAuth credentials — chưa rõ flow authentication.
