---
id: INT-004
name: OpenAI (GPT-4o + DALL-E 3)
type: REST
direction: Outbound
status: LIVE
---

# INT-004 — OpenAI

**Owner:** `lms_ai` module — `OpenAiProvider` plugin (`src/Plugin/AiProvider/OpenAiProvider.php`)
**Documentation:** https://platform.openai.com/docs/api-reference

---

## Purpose

Provides AI-driven content generation for the EverLMS course creation pipeline. OpenAI is the default AI provider and is used for: structured JSON generation (course outlines, curriculum, quiz questions), free-text generation (lesson content, user profiles), and image generation (avatars via DALL-E 3). The provider is implemented as a Drupal plugin discovered by `plugin.manager.lms_ai_provider`.

**Implementation status:** as-built. All three methods (`generateJson`, `generateText`, `generateImage`) are real working code with actual HTTP calls to the OpenAI API.

---

## Connection

| Property | Value |
|----------|-------|
| Endpoint (text/JSON) | `https://api.openai.com/v1/chat/completions` |
| Endpoint (image) | `https://api.openai.com/v1/images/generations` |
| Protocol | HTTPS/REST |
| Authentication | Bearer token (API key) |
| Default model (text/JSON) | `gpt-4o` (configurable: `gpt-4o`, `gpt-4-turbo`, `gpt-3.5-turbo`) |
| Image model | `dall-e-3` (hardcoded in `generateImage()`) |
| Rate Limit | Tier-based (TPM/RPM per model — varies by OpenAI account) |

### Credentials (config keys)

Stored in `lms_ai.settings` config YAML (`config/sync/lms_ai.settings.yml`):

| Config key | Description | Status |
|------------|-------------|--------|
| `openai.api_key` | OpenAI API key | «secret in config — see security risk» |
| `openai.model` | Default model ID | `gpt-4o` (current value) |

**SECURITY RISK:** The OpenAI API key is stored in plaintext in the committed config YAML. The Key module is listed as a dependency in `lms_ai.info.yml` but no `key.key.*` config files exist — the key is stored directly in `lms_ai.settings` instead of the Key module. This is a critical risk. See `config-inventory.md` R1.

---

## SLA

| Metric | Target | Measurement |
|--------|--------|------------|
| Latency (p95 text) | < 60s | APM monitoring (60s timeout configured) |
| Latency (p95 image) | < 90s | APM monitoring (90s timeout configured) |
| Availability | OpenAI SLA | Health check |
| Throughput | Tier-dependent | OpenAI rate limits |
| Error Rate | < 5% | Error logging |

---

## Request/Response Contract

### generateJson() — Structured JSON Output

Used for: course outlines, curriculum plans, quiz question generation, user data generation.

**Request (`POST https://api.openai.com/v1/chat/completions`):**

```json
{
  "model": "string — gpt-4o (from config)",
  "messages": [
    {
      "role": "system",
      "content": "string — system prompt, includes JSON schema appended by generateJson()"
    },
    {
      "role": "user",
      "content": "string — user prompt"
    }
  ],
  "response_format": { "type": "json_object" }
}
```

Headers: `Authorization: Bearer {openai.api_key}`, `Content-Type: application/json`

**Response (success):**

```json
{
  "id": "string — chatcmpl-xxxx",
  "object": "chat.completion",
  "model": "string",
  "choices": [
    {
      "message": {
        "role": "assistant",
        "content": "string — JSON-encoded content (parsed with JSON_THROW_ON_ERROR)"
      },
      "finish_reason": "stop"
    }
  ],
  "usage": {
    "prompt_tokens": "integer",
    "completion_tokens": "integer",
    "total_tokens": "integer"
  }
}
```

**Response (error):**

```json
{
  "error": { "message": "string", "type": "string", "code": "string" }
}
```

### generateText() — Free Text Output

Used for: lesson HTML content, meeting summaries, slide text.

**Request:** Same endpoint and structure as `generateJson()` but without `response_format: json_object`.

**Response:** Same shape; `choices[0].message.content` is returned as a plain string.

### generateImage() — DALL-E 3 Image Generation

Used for: user avatar image generation in `AiEngineUserForm`.

**Request (`POST https://api.openai.com/v1/images/generations`):**

```json
{
  "model": "dall-e-3",
  "prompt": "string — image description prompt",
  "n": 1,
  "size": "1024x1024",
  "response_format": "b64_json"
}
```

**Response (success):**

```json
{
  "data": [
    {
      "b64_json": "string — base64-encoded PNG image"
    }
  ]
}
```

**Response (error):**

```json
{
  "error": { "message": "string", "type": "string", "code": "string" }
}
```

---

## Plugin Interface

`OpenAiProvider` implements `AiProviderInterface` (with one gap noted):

| Method | Interface declared | Status |
|--------|-------------------|--------|
| `generateJson(string $prompt, ?string $schema, array $options): ?array` | Yes | as-built |
| `generateImage(string $prompt, array $options): ?string` | Yes | as-built — returns base64 string |
| `generateText(string $prompt, array $options): ?string` | **Not declared in interface** (implementation exists, interface omits it) | as-built — interface gap |

The `generateText()` method exists in all provider classes but is not declared in `AiProviderInterface`. Callers (e.g. `CourseContentGenerator` queue worker) call it directly on plugin instances.

---

## Usage Contexts

| Caller | Method | Purpose |
|--------|--------|---------|
| `CourseBuilderService::generateCourseStructure()` | `generateJson()` | Course outline with chapters and lesson titles |
| `CourseContentGenerator` (queue worker) | `generateText()` | Full HTML lesson content per queue item |
| `ZoomAiService::generateAgenda()` | `generateText()` (via `createInstance('openai')`) | Zoom meeting agenda HTML |
| `ZoomAiService::processSummaries()` | `generateText()` | Meeting transcript summary appended to lesson content |
| `AiEngineUserForm::ajaxRunUser()` | `generateJson()` | Generates list of demo users as JSON |
| `AiEngineUserForm::ajaxRequestImagePrompts()` | `generateJson()` | Per-user avatar image prompts |
| `AiEngineUserForm::ajaxRequestImages()` | `generateImage()` | User avatar PNG images |
| `AiEngineUserForm::ajaxRequestCurriculumSuggestion()` | `generateJson()` | Full curriculum JSON plan |
| `AiEngineUserForm::ajaxGenerateCoursesDetail()` etc. | `generateJson()` / `generateText()` | Entity enrichment (courses, chapters, lessons) |
| `AiEngineUserForm::ajaxGenerateQuiz()` | `generateJson()` | Quiz question sets per lesson |

---

## Retry Policy

| Property | Value |
|----------|-------|
| Max Retries | None (no retry logic in OpenAiProvider) |
| Backoff | N/A |
| Initial Delay | N/A |
| Max Delay | N/A |
| Timeout | 60s (text/JSON), 90s (image) — configured in Guzzle client |
| Retry On | N/A — exceptions are caught by callers; `generateJson()` returns NULL on failure |

---

## Fallback Behavior

| Scenario | Behavior | Duration |
|----------|----------|----------|
| API timeout (text) | Guzzle throws exception after 60s; caller receives NULL; queue worker re-throws for retry | Per queue retry policy |
| API timeout (image) | Guzzle throws exception after 90s; caller receives NULL | Image not generated |
| 429 rate limited | Exception propagates; no automatic backoff | Caller must retry manually |
| 500 server error | Exception propagates; NULL returned | Caller must retry |
| Invalid API key | Exception: 401 Unauthorized | NULL returned; error logged |
| JSON parse error | `JSON_THROW_ON_ERROR` throws; NULL returned | Error logged |
| No rollback on partial failure | If course builder creates entities then fails, orphan entities remain | Permanent (no saga pattern) |

---

## Circuit Breaker

Not implemented.

---

## Data Flow

- **Frequency:** Event-driven (user triggers AI generation buttons in admin forms); queue-based for lesson content (via `lms_ai_content_generator` queue, cron time 60s)
- **Volume:** Varies per generation job; large curriculum generation may produce 10–50+ API calls in sequence
- **Data Sensitivity:** Internal — prompts contain course/lesson titles, not personal student data
- **Encryption:** In-transit: TLS 1.2+

---

## Known Risks

| Risk | Detail |
|------|--------|
| API key in config YAML | `lms_ai.settings.yml` in `config/sync/` (git-tracked) contains live OpenAI key |
| No rollback/compensation | Partial failures in `AiEngineUserForm` or `CourseBuilderService` leave DB in inconsistent state |
| `generateText` not in interface | Callers rely on concrete method; not contractually enforced |
| `curriculum_suggestion` in config | ~758 KB cached AI response stored in `lms_ai.engine.settings` config — bloats config export |

---

## Feature Flag

- **Flag ID:** not required
- **Rationale:** Provider is selected via `default_provider` in `lms_ai.settings` config (`openai` | `gemini`). OpenAI is active when selected and API key is set.

---

## Related Docs

- [B03 — F-014 AI Features](../B03-features/14-ai-features.md)
- [INT-003 — Zoom](./INT-003-zoom.md) — uses OpenAI for meeting summaries and agendas
- Source: `web/modules/custom/lms_ai/src/Plugin/AiProvider/OpenAiProvider.php`
- Source: `web/modules/custom/lms_ai/src/Service/CourseBuilderService.php`
- Source: `web/modules/custom/lms_ai/src/Plugin/QueueWorker/CourseContentGenerator.php`
- Source: `web/modules/custom/lms_ai/src/Form/AiEngineUserForm.php`
