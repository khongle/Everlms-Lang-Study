# Service Contract - app-drupal → ext-gemini

| Aspect | Contract |
| ------ | -------- |
| Protocol | HTTPS (Google Gemini API) |
| Direction | outbound content-generation request/response only (no inbound callback) |
| Auth | Gemini API key (⚠️ in config, R-001 → Key/env) |
| Data | course/lesson prompt → generated draft content (`lms_ai` draft entity); no learner PII sent |
| Idempotency | not required (each generation request is independent) |
| Failure | API error/timeout → provider routing falls back to [ext-pollinations](app-drupal-to-pollinations.md); if all providers fail, instructor sees error |
| Observability | log provider/latency/error per request; no prompt/response content logged beyond generation metadata |

> Alternate/secondary of 3 AI providers behind `lms_ai` provider routing; primary is [app-drupal-to-openai](app-drupal-to-openai.md).
