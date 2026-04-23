# Contract: EverLMS → Pollinations.ai

**Caller:** `everlms-web` (module: `lms_ai`)
**Callee:** Pollinations.ai (free image generation service)
**Protocol:** HTTPS REST (GET request with prompt in URL path)
**Auth:** None — no API key required (free public tier)
**Handler class:** `\Drupal\lms_ai\Plugin\AiProvider\PollinationsProvider`
**Status:** ACTIVE

---

## Role in EverLMS

Pollinations is used as the **free image generation fallback**.
When OpenAI (DALL-E 3) or Gemini Imagen are unavailable, not configured, or when cost is a concern,
`AiProviderManager` can route image generation requests to Pollinations.

Pollinations uses the Flux model under the hood. No account or sign-up is required.

---

## Endpoint Used

| Endpoint | Method | Purpose | Timeout |
|----------|--------|---------|---------|
| `https://image.pollinations.ai/prompt/{url_encoded_prompt}?width=1024&height=1024&nologo=true&model=flux` | GET | Image generation (returns binary JPEG/PNG) | 90s |

**Parameters (query string):**

| Param | Value | Description |
|-------|-------|-------------|
| `width` | `1024` | Output image width in pixels |
| `height` | `1024` | Output image height in pixels |
| `nologo` | `true` | Suppress Pollinations watermark |
| `model` | `flux` | Inference model — Flux (default free model) |

---

## Response Format

Unlike OpenAI and Gemini (which return base64 JSON), Pollinations returns **raw binary image data**
directly in the response body (HTTP 200 + `Content-Type: image/jpeg` or `image/png`).

EverLMS converts this to base64 internally:
```php
return base64_encode((string) $response->getBody());
```

This matches the `AiProviderInterface::generateImage()` contract (base64 string returned).

---

## Capabilities

| Method | Supported? |
|--------|-----------|
| `generateImage()` | Yes |
| `generateText()` | No (logs warning, returns null) |
| `generateJson()` | No (logs warning, returns null) |

---

## Rate Limits & Fair Use

- Pollinations.ai does not publish a formal rate limit for the free tier.
- Service is provided on a best-effort basis with no SLA.
- Heavy load may result in slower response times or transient 429/503 errors.
- **Do not use for production-critical paths.** Use OpenAI / Gemini for guaranteed image generation.

---

## Failure Modes

| Scenario | Behaviour |
|----------|-----------|
| Non-200 HTTP status | Logs error, returns `null` |
| Network timeout (>90s) | Exception caught, logs error, returns `null` |
| Service downtime | Silent failure — caller gets `null`, no user-visible error |
| Prompt too long for URL | URL encoding may silently truncate; no validation in provider |

> **Gap:** Prompt length is not validated before URL encoding. Very long prompts may be silently
> truncated by proxy or server. Recommend capping prompt at 500 chars for Pollinations requests.

---

## Criticality

**Tier 3** — Free image fallback only. If Pollinations is down, image generation falls back to
other providers or is skipped silently.
