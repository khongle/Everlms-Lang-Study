# INT-005 — Google Gemini

**ID:** INT-005
**Integration:** Google Gemini (Generative AI)
**Owner Module:** `lms_ai` — plugin `GeminiProvider`
**Direction:** Outbound
**Protocol:** REST API
**Status:** Plugin implemented (`src/Plugin/AiProvider/GeminiProvider.php`)

---

## Scope

AI content generation — alternative provider cho OpenAI. Cùng interface: outline, lesson content, quiz questions. Admin switch provider qua config.

## Connection

| Property | Value |
|----------|-------|
| API Base URL | `https://generativelanguage.googleapis.com/v1beta` |
| Auth | API Key: `GOOGLE_GEMINI_API_KEY` |
| Endpoint | `/models/{model}:generateContent` |
| Models | `gemini-1.5-pro`, `gemini-1.5-flash`, `gemini-pro` |

## Data Exchanged

| Direction | Data |
|-----------|------|
| Outbound | `contents[].parts[].text` (prompt) |
| Inbound | `candidates[].content.parts[].text` (generated text) |
| Outbound | `generationConfig`: maxOutputTokens, temperature |

## Authentication

- API key stored trong Drupal Key module hoặc environment variable.
- Không hardcode (same constraint as OpenAI — AC-H01).

## Rate Limits & Quotas

| Limit | Value |
|-------|-------|
| Rate limit | Free tier: 15 RPM; paid tier higher |
| Token limit | Per model (Gemini 1.5 Pro: 1M context window) |
| Cost control | Token usage logged |

## Failure Modes

| Scenario | Behavior |
|----------|----------|
| API timeout | Retry 3x exponential backoff |
| 429 quota exceeded | Pause + retry; suggest switch to OpenAI |
| Safety filter block | Log; placeholder content; instructor notified |
| Invalid API key | Immediate fail; admin alert |

## Plugin Interface

GeminiProvider implements same `AiProviderInterface` as OpenAiProvider:
- `generateCourseOutline(topic, level, language, options): array`
- `generateLessonContent(lesson_title, context): string`
- `generateQuizQuestions(lesson_content, count, types): array`

## Related Docs

- [INT-004 — OpenAI](./INT-004-openai.md) — primary AI provider
- [INT-006 — Pollinations](./INT-006-pollinations.md) — image provider
- [WF-006 — Course Creation](../B04-business-workflows/WF-006-course-creation.md)
- [AC-H01](../B05-acceptance-criteria/AC-14-ai-features.md) — provider abstraction AC
