---
id: INT-005
name: Google Gemini
type: REST
direction: Outbound
status: LIVE
---

# INT-005 — Google Gemini

**Owner:** `lms_ai` module — plugin `GeminiProvider` (`web/modules/custom/lms_ai/src/Plugin/AiProvider/GeminiProvider.php`)
**Documentation:** <https://ai.google.dev/api/generate-content>

Provides AI text generation (JSON structured output and free text) and image generation (Imagen 4.0) as a secondary provider alongside INT-004 OpenAI. Configured model as-built: `gemini-2.5-flash`. Provider is selectable via admin config at `/admin/config/lms/ai`.

## Connection

| Property | Value |
|----------|-------|
| Endpoint | `https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent` |
| Image endpoint | `https://generativelanguage.googleapis.com/v1beta/models/imagen-4.0-generate-001:predict` |
| Protocol | HTTPS |
| Authentication | API Key — `lms_ai.settings` → `gemini.api_key` («secret in config — see security risk») |
| Active model | `gemini-2.5-flash` (configured; also supports `gemini-2.5-pro`, `gemini-flash-latest`, `gemini-pro-latest`) |

**Security risk (as-built):** The Gemini API key is stored in plaintext in `config/sync/lms_ai.settings.yml`, which is committed to VCS. No Key module abstraction is used. See `G02-risk-register.md` R1.

## SLA

| Metric | Target | Measurement |
|--------|--------|-------------|
| Latency (p95) | not measured — local dev only, no production telemetry; Google publishes no p95 SLA for Gemini API | APM / Drupal watchdog |
| Availability | 99.9% (Google-published) | Health check |
| Throughput | Free tier: 15 RPM; paid tier higher | Per-account quota |
| Error Rate | < 5% | Drupal logger |

## Request/Response Contract

**generateJson — Request (POST `:generateContent`):**

```json
{
  "contents": [
    { "parts": [{ "text": "string — full prompt with appended JSON schema" }], "role": "user" }
  ],
  "generationConfig": {
    "responseMimeType": "application/json"
  }
}
```

**generateJson — Response (success):**

```json
{
  "candidates": [
    {
      "content": {
        "parts": [{ "text": "string — JSON body (may have ```json``` wrapper stripped by provider)" }],
        "role": "model"
      }
    }
  ]
}
```

**generateImage — Request (POST `imagen-4.0-generate-001:predict`):**

```json
{
  "instances": [{ "prompt": "string — image description" }],
  "parameters": { "aspectRatio": "1:1" }
}
```

**generateImage — Response (success):**

```json
{
  "predictions": [
    { "bytesBase64Encoded": "string — base64 PNG/JPEG data" }
  ]
}
```

**Response (error):**

```json
{
  "error": { "code": "integer", "message": "string", "status": "string" }
}
```

## Retry Policy

| Property | Value |
|----------|-------|
| Max Retries | 0 — confirmed single HTTP call, no retry loop in `GeminiProvider::request()` (confirmed in GeminiProvider.php) |
| Backoff | None (as-built) |
| Initial Delay | N/A |
| Max Delay | N/A |
| Timeout | Not set explicitly in GuzzleHttp call (Drupal default ~30s) |
| Retry On | No automatic retry implemented |

## Fallback Behavior

| Scenario | Behavior | Duration |
|----------|----------|----------|
| Service unavailable / timeout | Exception propagates to caller; `AiEngineUserForm` logs error and counts failure; no retry | Per request |
| `generateJson` content blocked by safety filter | Response may include empty candidate or SAFETY finish_reason; provider returns NULL after JSON parse failure | Per request |
| Partial pipeline failure (AI Engine) | Already-created entities are NOT rolled back; see rollback risk note below | N/A |
| Provider returns malformed JSON | `generateJson()` strips markdown fences then calls `json_decode()` with `JSON_THROW_ON_ERROR`; exception propagates on failure | Per request |

**Rollback risk (as-built):** `CourseBuilderService` and `AiEngineUserForm` have no saga/compensation pattern. If Gemini fails mid-pipeline, partially created `lms_course`, `lms_chapter`, `lms_lesson` entities remain in the database. This is a known issue — see `G02-risk-register.md`.

## Circuit Breaker

| Property | Value |
|----------|-------|
| Failure Threshold | None — no circuit breaker implemented (as-built) |
| Open Duration | N/A |
| Half-Open Probes | N/A |
| Recovery Criteria | N/A |

## Data Flow

- **Frequency:** Event-driven — triggered by admin actions in `AiEngineUserForm` AJAX handlers or `CourseBuilderForm` submit
- **Volume:** Low — single requests per user action; no batch/queue usage for Gemini specifically
- **Data Sensitivity:** Internal (course content prompts, curriculum data)
- **Encryption:** In-transit: TLS 1.2+; at-rest: not applicable (transient prompts/responses not stored)

## Plugin Implementation (as-built)

`GeminiProvider` implements `AiProviderInterface`:

| Method | Status | Notes |
|--------|--------|-------|
| `generateJson(string $prompt, ?string $schema, array $options): ?array` | as-built | Sets `responseMimeType: application/json`; strips ```` ```json ```` fences; parses with `JSON_THROW_ON_ERROR` |
| `generateText(string $prompt, array $options): ?string` | as-built | Standard `generateContent` call; returns first candidate text |
| `generateImage(string $prompt, array $options): ?string` | as-built | Calls Imagen 4.0 (`imagen-4.0-generate-001:predict`); returns `base64_encode($bytesBase64Encoded)` |

Note: `generateText()` is implemented but is **not declared in `AiProviderInterface`** — it exists only by convention. See interface gap in `lms_ai` module facts.

## Admin Configuration

| Config key (`lms_ai.settings`) | Value (as-built) |
|-------------------------------|-----------------|
| `gemini.api_key` | «secret in config — see security risk» |
| `gemini.model` | `gemini-2.5-flash` |

Admin UI: `/admin/config/lms/ai` (`AiSettingsForm`) — model selector offers `gemini-2.5-flash`, `gemini-2.5-pro`, `gemini-flash-latest`, `gemini-pro-latest`.

## Feature Flag

- **Flag ID:** Not required
- **Rationale:** Provider selection is controlled by `lms_ai.settings → default_provider` config key; no separate feature flag needed. Gemini can be switched to at runtime via admin UI without a code deploy.

## Cross-links

- [INT-004 OpenAI](./INT-004-openai.md) — primary AI provider, same interface
- [INT-006 Pollinations](./INT-006-pollinations.md) — image-only provider (free tier alternative)
