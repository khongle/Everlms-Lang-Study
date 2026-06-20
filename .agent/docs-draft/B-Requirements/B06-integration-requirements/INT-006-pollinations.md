# INT-006 — Pollinations AI (Image Generation)

**ID:** INT-006
**Integration:** Pollinations.ai
**Owner Module:** `lms_ai` — plugin `PollinationsProvider`
**Direction:** Outbound
**Protocol:** REST API (HTTP GET/POST)
**Status:** Plugin implemented (`src/Plugin/AiProvider/PollinationsProvider.php`)

---

## Scope

AI image generation cho course thumbnails, lesson illustrations, và banner images. Pollinations là provider cho image generation (khác với OpenAI/Gemini là text generation).

## Connection

| Property | Value |
|----------|-------|
| API Base URL | `https://image.pollinations.ai` |
| Auth | None (public API) hoặc API token nếu dùng paid tier |
| Endpoint | `/prompt/{encoded_prompt}` (GET) |
| Protocol | HTTPS GET — returns image directly |

## Data Exchanged

| Direction | Data |
|-----------|------|
| Outbound | URL-encoded prompt, width, height, model, seed (optional) |
| Inbound | Image file (JPEG/PNG) response |

## Authentication

- Public endpoint không cần auth (free tier).
- API key optional cho paid tier features; stored trong config nếu dùng.

## Rate Limits & Quotas

| Limit | Value |
|-------|-------|
| Free tier | Rate limited; no SLA |
| Image size | Configurable: 512×512 đến 1792×1024 |

## Failure Modes

| Scenario | Behavior |
|----------|----------|
| API timeout | Retry 2x; nếu fail: use placeholder image |
| Service unavailable | Use placeholder image; log warning |
| Inappropriate content generated | Requires instructor review before publish; content moderation flag |

## Use Cases

| Use Case | Prompt Template |
|----------|----------------|
| Course thumbnail | `"Educational course thumbnail for {topic}, professional, clean design"` |
| Lesson illustration | `"Illustration for {lesson_title}, educational style"` |
| Category image | `"Icon for {category_name} subject"` |

## Related Docs

- [INT-004 — OpenAI](./INT-004-openai.md) — text generation provider
- [INT-005 — Google Gemini](./INT-005-google-gemini.md) — text generation provider
- [WF-006 — Course Creation](../B04-business-workflows/WF-006-course-creation.md)
