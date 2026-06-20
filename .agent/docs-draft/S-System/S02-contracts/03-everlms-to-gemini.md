# Contract: EverLMS → Google Gemini

**Caller:** `everlms-web` (module: `lms_ai`)
**Callee:** Google Generative Language API (Gemini)
**Protocol:** HTTPS REST
**Auth:** API Key — passed as **URL query parameter** `?key={api_key}` (see security finding below)
**Handler class:** `\Drupal\lms_ai\Plugin\AiProvider\GeminiProvider`
**Status:** ACTIVE

---

## Endpoints Used

| Endpoint | Method | Purpose | Timeout |
|----------|--------|---------|---------|
| `https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent?key={api_key}` | POST | Text and JSON generation | 60s |
| `https://generativelanguage.googleapis.com/v1beta/models/imagen-4.0-generate-001:predict?key={api_key}` | POST | Image generation (Imagen 4) | 90s |

---

## Models Used

| Task | Model | Configurable? |
|------|-------|---------------|
| Text / JSON generation | `gemini-2.5-flash` (default) | Yes — `lms_ai.settings` → `gemini.model` |
| Image generation | `imagen-4.0-generate-001` | No (hardcoded) |

---

## Request Configuration

### generateContent (Text / JSON)

```json
{
  "systemInstruction": {
    "parts": [{ "text": "{system_prompt}" }]
  },
  "contents": [
    { "parts": [{ "text": "{prompt}" }] }
  ],
  "generationConfig": {
    "temperature": 0.7,
    "responseMimeType": "application/json"  // only for generateJson()
  }
}
```

### Imagen (Image Generation)

```json
{
  "instances": [{ "prompt": "{prompt}" }],
  "parameters": {
    "sampleCount": 1,
    "aspectRatio": "1:1"
  }
}
```

---

## Security Finding — FIND-001: API Key in URL

> **FIND-001 (SECURITY):** The Gemini API key is appended to the URL as `?key={api_key}`.
> This means the key appears in:
> - PHP access logs / Drupal watchdog (if debug request logging is on)
> - Any HTTP proxy or CDN access logs
> - Browser dev-tools network tab if called client-side (not applicable here — server-side only)
>
> **Recommended fix:** Migrate to `Authorization: Bearer {api_key}` header, or use Google ADC
> (Application Default Credentials) with a service account instead of a plain API key.
> See also `I01` and `docs-v3/issues/` for related AI security findings.

Config path: `lms_ai.settings` → `gemini.api_key`
Admin UI: `/admin/config/everlms/ai`

---

## Retry / Timeout

| Setting | Value |
|---------|-------|
| Text generation timeout | 60 seconds |
| Image generation timeout | 90 seconds |
| Retry on failure | NOT IMPLEMENTED (single attempt) |
| max_tokens / maxOutputTokens | Not set (model default) |

---

## Failure Mode

When Gemini API is unreachable or returns an error:
- `generateText()`, `generateJson()`, `generateImage()` return `null`.
- Error is logged via `lms_ai` logger channel.
- Caller (e.g., `CourseBuilderService`) must handle `null` gracefully.
- AI features degrade silently; no automatic retry or queue fallback.

**Criticality:** Tier 3 — AI features are non-critical.
Drupal falls back to non-AI content workflows.
