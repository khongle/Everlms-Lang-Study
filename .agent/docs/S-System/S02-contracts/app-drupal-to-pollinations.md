# Service Contract - app-drupal → ext-pollinations

| Aspect | Contract |
| ------ | -------- |
| Protocol | HTTPS (Pollinations API) |
| Direction | outbound content-generation request/response only (no inbound callback) |
| Auth | Pollinations API key/token (⚠️ in config, R-001 → Key/env) |
| Data | course/lesson prompt → generated draft content (`lms_ai` draft entity); no learner PII sent |
| Idempotency | not required (each generation request is independent) |
| Failure | API error/timeout → last-resort fallback in provider routing; if unavailable, instructor sees generation-unavailable error |
| Observability | log provider/latency/error per request; no prompt/response content logged beyond generation metadata |

> Lowest-priority of 3 AI providers behind `lms_ai` provider routing; see [app-drupal-to-openai](app-drupal-to-openai.md) (primary) and [app-drupal-to-gemini](app-drupal-to-gemini.md) (secondary).
