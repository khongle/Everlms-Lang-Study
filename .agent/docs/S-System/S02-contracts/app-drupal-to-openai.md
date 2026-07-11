# Service Contract - app-drupal → ext-openai

| Aspect | Contract |
| ------ | -------- |
| Protocol | HTTPS (OpenAI REST API) |
| Direction | outbound content-generation request/response only (no inbound callback) |
| Auth | OpenAI API key (⚠️ in config, R-001 → Key/env) |
| Data | course/lesson prompt → generated draft content (`lms_ai` draft entity); no learner PII sent |
| Idempotency | not required (each generation request is independent; user-triggered retry creates a new draft) |
| Failure | API error/timeout → generation fails, instructor sees error, provider routing falls back to [ext-gemini](app-drupal-to-gemini.md) / [ext-pollinations](app-drupal-to-pollinations.md) |
| Observability | log provider/latency/error per request; no prompt/response content logged beyond generation metadata |

> Primary of 3 AI providers behind `lms_ai` provider routing; see [app-drupal-to-gemini](app-drupal-to-gemini.md) and [app-drupal-to-pollinations](app-drupal-to-pollinations.md) for the fallback chain.
