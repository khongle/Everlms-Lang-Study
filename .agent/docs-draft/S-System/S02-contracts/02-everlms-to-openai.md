# Contract: EverLMS → OpenAI

**Caller:** `everlms-web` (module: `lms_ai`)
**Callee:** OpenAI API
**Protocol:** HTTPS REST
**Auth:** Bearer token — OpenAI API Key (`lms_ai.settings` → `openai.api_key`)
**Handler class:** `\Drupal\lms_ai\Plugin\AiProvider\OpenAiProvider`

---

## Endpoints Used

| OpenAI Endpoint | Method | Purpose | Timeout |
|-----------------|--------|---------|---------|
| `https://api.openai.com/v1/chat/completions` | POST | Text generation, JSON generation | 60s |
| `https://api.openai.com/v1/images/generations` | POST | Image generation (DALL-E 3) | 90s |

---

## Models Used

| Task | Model | Configurable? |
|------|-------|---------------|
| Text / JSON generation | `gpt-4o` (default) | Yes — `lms_ai.settings` → `openai.model` |
| Image generation | `dall-e-3` | No (hardcoded) |

---

## Request Configuration

### Chat Completions

```json
{
  "model": "gpt-4o",
  "messages": [
    {"role": "system", "content": "You are a helpful AI assistant..."},
    {"role": "user", "content": "{prompt}"}
  ],
  "temperature": 0.7,
  "response_format": {"type": "json_object"}  // for generateJson() only
}
```

### Image Generation

```json
{
  "model": "dall-e-3",
  "prompt": "{prompt}",
  "n": 1,
  "size": "1024x1024",
  "response_format": "b64_json"
}
```

---

## Timeout & Retry

| Setting | Value |
|---------|-------|
| Chat completions timeout | 60 seconds |
| Image generation timeout | 90 seconds |
| Retry on failure | NOT IMPLEMENTED (single attempt) |
| Max tokens | Not set (model default) |

> **Gap:** No `max_tokens` cap is set. An adversarial prompt could cause runaway token usage and cost. Recommend setting `max_tokens: 4096` for JSON/text, adjusting per use case.

---

## Cost Ceiling

**Status: NOT SET** — No cost ceiling or budget alert is configured.

Recommended actions:
1. Set OpenAI usage limits in OpenAI Dashboard (hard cap per month).
2. Add Drupal config for per-request `max_tokens`.
3. Monitor token usage via OpenAI usage API or billing alerts.

---

## Failure Mode

When OpenAI API is unreachable or returns error:
- `generateText()` / `generateJson()` / `generateImage()` return `null`.
- Error is logged via `lms_ai` logger channel.
- Caller (e.g., `CourseBuilderService`) must handle `null` gracefully.
- No queue retry — failed AI generation requests are not requeued automatically.
- AI features degrade silently; user-visible content generation fails with no automatic recovery.

---

## API Key Configuration

Config path: `lms_ai.settings` → `openai.api_key`
Admin UI: `/admin/config/everlms/ai` (assumed from `AiSettingsForm`)

---

## Criticality

**Tier 3** — AI features are non-critical. System remains functional without OpenAI.
Drupal falls back to non-AI content workflows.
