---
id: INT-006
name: Pollinations.ai (Image Generation)
type: REST
direction: Outbound
status: LIVE
---

# INT-006 — Pollinations.ai

**Owner:** `lms_ai` module — plugin `PollinationsProvider` (`web/modules/custom/lms_ai/src/Plugin/AiProvider/PollinationsProvider.php`)
**Documentation:** <https://pollinations.ai> / <https://image.pollinations.ai>

Free, no-auth image generation service. Implemented as an `AiProviderInterface` plugin but supports **image generation only** — text and JSON generation methods return `NULL` with a logged warning. Used in `AiEngineUserForm` to generate user avatar images during AI-driven demo content seeding.

## Scope (as-built)

| Capability | Status |
|------------|--------|
| `generateImage()` | as-built — HTTP GET returns raw image bytes; base64-encoded for caller |
| `generateJson()` | not supported — logs warning, returns NULL |
| `generateText()` | not supported — logs warning, returns NULL |

## Connection

| Property | Value |
|----------|-------|
| Endpoint | `https://image.pollinations.ai/prompt/{url_encoded_prompt}` |
| Protocol | HTTPS GET |
| Authentication | None — public free-tier endpoint |
| Parameters (query string) | `width=1024`, `height=1024`, `nologo=true`, `model=flux` |
| Rate Limit | Free tier — no published SLA; best-effort |

## SLA

| Metric | Target | Measurement |
|--------|--------|-------------|
| Latency (p95) | not measured — local dev only, no production telemetry; Pollinations publishes no SLA (free public service) | Drupal watchdog |
| Availability | No SLA (free public service) | N/A |
| Throughput | No stated limit; rate throttling possible | N/A |
| Error Rate | < 10% acceptable (no business-critical path) | Drupal logger |

## Request/Response Contract

**Request (GET):**

```
GET https://image.pollinations.ai/prompt/{url_encoded_prompt}?width=1024&height=1024&nologo=true&model=flux
```

No request body. No authentication header.

**Response (success):**

```
HTTP 200
Content-Type: image/jpeg  (or image/png)
Body: raw image binary
```

The provider reads the raw binary response body and returns `base64_encode($raw_binary)` to the caller.

**Response (error):**

```
HTTP 4xx / 5xx
Body: plain text error or empty
```

No structured JSON error envelope. Failures are detected by non-200 HTTP status or GuzzleHttp exception.

## Retry Policy

| Property | Value |
|----------|-------|
| Max Retries | 0 — confirmed single HTTP GET, no retry loop in `PollinationsProvider::generateImage()` (confirmed in PollinationsProvider.php) |
| Backoff | None (as-built) |
| Initial Delay | N/A |
| Max Delay | N/A |
| Timeout | Not set explicitly; Drupal/GuzzleHttp default (~30s) |
| Retry On | No automatic retry |

## Fallback Behavior

| Scenario | Behavior | Duration |
|----------|----------|----------|
| Service unavailable / timeout | Exception propagates; `AiEngineUserForm::ajaxRequestImages()` logs error, counts failure, continues to next item | Per image |
| Non-200 HTTP response | GuzzleHttp exception caught upstream; image item skipped | Per image |
| Rate limited (HTTP 429) | No backoff logic; caller skips image | Per image |

## Circuit Breaker

| Property | Value |
|----------|-------|
| Failure Threshold | None — no circuit breaker implemented |
| Open Duration | N/A |
| Half-Open Probes | N/A |
| Recovery Criteria | N/A |

## Data Flow

- **Frequency:** Event-driven — triggered manually by admin via `AiEngineUserForm` "Generate Images" AJAX step
- **Volume:** Low — one HTTP GET per user avatar during demo seeding (typically 10 users per batch per `ai_users.json`)
- **Data Sensitivity:** Public (text prompts only; no PII in prompt)
- **Encryption:** In-transit: TLS 1.2+; images not stored server-side by Pollinations

## Usage in Pipeline (as-built)

`AiEngineUserForm::ajaxRequestImages()`:
1. Reads `ai_users.json` to get per-user `image_prompt` fields
2. Calls `PollinationsProvider::generateImage($prompt)` for each user
3. Decodes returned base64 string, saves as Drupal managed file (`public://ai-generated/user-{uid}.jpg`)
4. Attaches file to `user_picture` field of the Drupal user entity

No other pipeline step uses Pollinations. Course thumbnails and lesson illustrations use OpenAI DALL-E 3 (INT-004) or Gemini Imagen (INT-005).

## Admin Configuration

No configuration required. No API key, no settings form. Provider is always available as a plugin option in `AiProviderManager`.

## Feature Flag

- **Flag ID:** Not required
- **Rationale:** Pollinations is a plugin discovered by `AiProviderManager`; it is always available but only invoked for image generation. No feature flag needed — the pipeline step that calls it (`ajaxRequestImages`) is only reachable through the AI Engine admin form by an administrator.

## Known Limitations

- Text/JSON generation is intentionally unsupported — this plugin is image-only.
- No SLA or rate-limit guarantees from the free-tier public endpoint.
- Model is hardcoded to `flux`; not configurable via admin UI.
- Image dimensions are hardcoded to `1024x1024`; not configurable via admin UI.

## Cross-links

- [INT-004 OpenAI](./INT-004-openai.md) — primary provider (text + image via DALL-E 3)
- [INT-005 Google Gemini](./INT-005-google-gemini.md) — secondary provider (text + image via Imagen 4.0)
