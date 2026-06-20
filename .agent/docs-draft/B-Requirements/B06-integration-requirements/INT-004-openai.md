# INT-004 — OpenAI

**ID:** INT-004
**Integration:** OpenAI (GPT models)
**Owner Module:** `lms_ai` — plugin `OpenAiProvider`
**Direction:** Outbound
**Protocol:** REST API
**Status:** Plugin implemented (`src/Plugin/AiProvider/OpenAiProvider.php`)

---

## Scope

AI content generation cho course creation: outline structure, lesson text content, quiz questions, và SEO descriptions. OpenAI là một trong 3 AI providers (cùng Gemini và Pollinations).

## Connection

| Property | Value |
|----------|-------|
| API Base URL | `https://api.openai.com/v1` |
| Auth | Bearer token: `OPENAI_API_KEY` |
| Endpoint | `/chat/completions` |
| Model | Configurable: `gpt-4o`, `gpt-4-turbo`, `gpt-3.5-turbo` |

## Data Exchanged

| Direction | Data |
|-----------|------|
| Outbound | messages[], model, max_tokens, temperature |
| Inbound | choices[].message.content (generated text) |
| Outbound | Course outline prompt, lesson content prompt, quiz generation prompt |

## Authentication

- API key trong Drupal Key module hoặc environment variable.
- Không hardcode trong code (see AC-H01).

## Rate Limits & Quotas

| Limit | Value |
|-------|-------|
| Rate limit | Tier-based (TPM/RPM per model) |
| Token budget | Configurable max_tokens per generation job |
| Cost control | Token usage logged per generation; budget alerts |

## Failure Modes

| Scenario | Behavior |
|----------|----------|
| API timeout | Retry 3x exponential backoff (1s, 2s, 4s) |
| 429 rate limit | Pause generation; retry after Retry-After header |
| 500 server error | Retry; nếu vẫn fail → rollback partial content |
| API key invalid | Immediate fail; admin alert "OpenAI API key invalid" |
| Content filter blocked | Log; use placeholder; instructor notified |

## Plugin Interface

OpenAiProvider implements `AiProviderInterface`:
- `generateCourseOutline(topic, level, language, options): array`
- `generateLessonContent(lesson_title, context): string`
- `generateQuizQuestions(lesson_content, count, types): array`

## Related Docs

- [INT-005 — Google Gemini](./INT-005-google-gemini.md) — alternative provider
- [INT-006 — Pollinations](./INT-006-pollinations.md) — image generation provider
- [WF-006 — Course Creation](../B04-business-workflows/WF-006-course-creation.md)
- [AC-H01, AC-H02, AC-AI01–AC-AI03](../B05-acceptance-criteria/AC-14-ai-features.md)
