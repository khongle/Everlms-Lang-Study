# C06 — API: Integration (AI Provider Plugin, Zoom WebSDK Signature, Payment Gateway Plugin)

**Documentation mode:** as-built (AI provider plugin manager real; OpenAI + Gemini full; Pollinations image-only; Zoom signature real; payment gateway plugin contract real but webhook stubs; no GA4 integration despite module description)

**Modules:** `lms_ai`, `lms_zoom`, `lms_analytics`, `lms_payment`
**Module specs:** [lms_ai](../C05-modules/lms_ai.md), [lms_zoom](../C05-modules/lms_zoom.md), [lms_analytics](../C05-modules/lms_analytics.md), [lms_payment](../C05-modules/lms_payment.md)

---

## API Overview

**Route Group:** Admin config + authenticated runtime endpoints
**Base paths:** `/api/lms/websdk/signature`, `/admin/config/lms/ai`, `/admin/everlms-ai-engine/*`, `/admin/lms/reports/*`
**Authentication:** `_user_is_logged_in` for Zoom signature; `administer site configuration` or `administer lms_course` for all admin/config routes
**Consumers:** Internal (frontend JS for Zoom WebSDK; internal event/queue system for AI; admin UI for analytics)
**Response format:** JSON for Zoom signature + AI preview; HTML for all other routes; CSV for analytics exports

**Internal extension contracts documented here (not HTTP APIs):**
1. AI Provider Plugin interface (`AiProviderInterface` + `AiProviderManager`)
2. Payment Gateway Plugin interface (`PaymentGatewayInterface` + `PaymentGatewayManager`)
3. Zoom `generateSignature()` runtime endpoint

---

## Zoom WebSDK Signature Endpoint

### POST `/api/lms/websdk/signature`

**Route:** `lms_zoom.generate_signature`
**Module:** `lms_zoom`
**Controller:** `ZoomController::generateSignature`
**Authentication:** `_user_is_logged_in: TRUE`
**Methods:** POST
**Content-Type:** `application/json`

**Feature:** Live class session — Zoom WebSDK embed

### Request

**Headers:**
| Header | Value | Required |
|--------|-------|----------|
| Cookie | Drupal session | Yes (logged-in check) |
| Content-Type | application/json | Yes |

**Body:**
```json
{
  "meeting_number": "123456789",
  "role": 0
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `meeting_number` | string | Yes | Zoom meeting number |
| `role` | integer | Yes | 0 = attendee, 1 = host |

### Response

**Success (200):**
```json
{
  "signature": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "sdk_key": "abc123XYZ",
  "meeting_number": "123456789",
  "user_name": "Nguyen Van A",
  "user_email": "student@example.com",
  "zak": "eyJhbGci..."
}
```

| Field | Type | Notes |
|-------|------|-------|
| `signature` | string | HS256 JWT; short TTL |
| `sdk_key` | string | Zoom SDK Key from `lms_zoom.settings:sdk_key`; never expose `sdk_secret` |
| `meeting_number` | string | Echo of input |
| `user_name` | string | Current user display name |
| `user_email` | string | Current user email |
| `zak` | string | ZAK token (host only) — returned only when user is the meeting host |

**Error Responses:**
| Status | When |
|--------|------|
| 403 | User not logged in |
| 500 | Zoom API unreachable or `firebase/php-jwt` not installed |

### Logic (as-built)

1. Reads `sdk_key` and `sdk_secret` from `lms_zoom.settings` config. «secrets in config — see security risk»
2. Resolves current user's role: traverses `lms_lesson` entity (via route or Zoom meeting ID lookup) → `field_type = 'zoom'` → `lms_chapter` → `lms_course` → `field_instructor`. If current user is the instructor → role=1 (host), fetches ZAK token via `ZoomService::getZakToken()`.
3. Generates HS256 JWT using `firebase/php-jwt` library. JWT payload: `sdkKey, mn (meeting_number), role, iat, exp (iat + 7200)`.
4. Returns JSON response with signature + metadata.

**Known bug (as-built):** `ZoomController::generateSignature()` references a wrong service ID when resolving the Zoom service. This is listed in DOC-BRIEF as a known issue — `generateSignature()` may fail at runtime if the service resolution fails. Severity: High.

**Dependency:** `firebase/php-jwt` Composer package required. If missing, throws exception.

---

## Zoom Page Route

### GET `/zoom/join/{meeting_id}`

**Route:** `lms_zoom.join_meeting`
**Module:** `lms_zoom`
**Controller:** `ZoomController::joinMeeting`
**Authentication:** `_user_is_logged_in: TRUE`
**Methods:** GET
**Path Parameters:**

| Param | Type | Constraint | Description |
|-------|------|-----------|-------------|
| `meeting_id` | string | none | Zoom meeting number (raw string, not entity-upcasted) |

**Response:** HTML (Twig: `lms-zoom-web-player.html.twig`) with `lms_zoom/websdk` library attached.

**Theme suggestions:** `lms_zoom.module` registers `html__lms_zoom_web_player` and `page__lms_zoom_web_player` theme suggestions for this route — produces a stripped full-screen HTML wrapper (no header/footer) for the Zoom WebSDK iframe.

**Logic:** Calls `ZoomService::getMeeting(meeting_id)` → Zoom REST API `GET /meetings/{id}`; renders player template with `meeting_id`, `passcode`, current user info. Attaches `lms_zoom/websdk` JS library.

---

## AI Provider Plugin Interface

`lms_ai` provides a Drupal plugin manager for AI provider backends. This is an internal extension contract — not an HTTP API.

**Plugin manager service:** `plugin.manager.lms_ai_provider`
**Class:** `AiProviderManager` (extends `DefaultPluginManager`)
**Plugin annotation:** `@AiProvider`
**Discovery namespace:** `Plugin/AiProvider` in any module
**Alter hook:** `lms_ai_provider_info`
**Cache bin:** `lms_ai_provider_plugins`

### AiProviderInterface

```php
interface AiProviderInterface extends PluginInspectionInterface {
    public function generateJson(string $prompt, ?string $schema, array $options = []): ?array;
    public function generateImage(string $prompt, array $options = []): ?string;
    // generateText() implemented by all providers but NOT declared in interface (design gap)
}
```

| Method | Return | Description |
|--------|--------|-------------|
| `generateJson(prompt, schema, options)` | `?array` | Generates structured JSON. `schema` is appended to system prompt. Returns NULL on failure. |
| `generateImage(prompt, options)` | `?string` | Returns base64-encoded image binary string. Returns NULL if not supported by provider. |
| `generateText(prompt, options)` | `?string` | Not in interface — implemented by all providers. Returns plain text string. |

**Known gap:** `generateText()` is implemented by all providers but is NOT declared in `AiProviderInterface`. Callers must either rely on duck typing or the interface must be updated.

### Provider Implementations (as-built)

| Plugin ID | Class | Status | Capabilities |
|-----------|-------|--------|-------------|
| `openai` | `OpenAiProvider` | REAL | `generateJson` (GPT-4o + `json_object` mode), `generateText` (chat completions), `generateImage` (DALL-E 3, 1024×1024, b64_json) |
| `gemini` | `GeminiProvider` | REAL | `generateJson` (Gemini 2.5 Flash + `application/json` MIME), `generateText` (generateContent), `generateImage` (Imagen 4.0, 1:1, bytesBase64Encoded) |
| `pollinations` | `PollinationsProvider` | PARTIAL | `generateImage` only (free, no API key; GET `https://image.pollinations.ai/prompt/...`); `generateJson` + `generateText` return NULL with warning log |

**Config keys** (all stored in `lms_ai.settings` — «secrets in config — see security risk»):

| Key | Provider | Description |
|-----|----------|-------------|
| `openai.api_key` | OpenAI | API key for OpenAI |
| `openai.model` | OpenAI | Model ID; default `gpt-4o`; options: gpt-4o, gpt-4-turbo, gpt-3.5-turbo |
| `gemini.api_key` | Gemini | API key for Google Gemini |
| `gemini.model` | Gemini | Model ID; default `gemini-2.5-flash`; options: gemini-2.5-flash, gemini-2.5-pro, gemini-flash-latest, gemini-pro-latest |
| `default_provider` | Both | Active provider; options: openai, gemini |

### AI Admin Endpoints (not REST — HTML forms + AJAX)

| Route | Path | Handler | Auth | Description |
|-------|------|---------|------|-------------|
| `lms_ai.settings` | `/admin/config/lms/ai` | `AiSettingsForm` | administer site configuration | Configure provider + API keys |
| `lms_ai.course_builder_form` | `/manage/courses/ai-builder` | `CourseBuilderForm` (AJAX) | administer lms_course | Single-step AI course outline generator |
| `lms_ai.engine_users_form` | `/admin/everlms-ai-engine` | `AiEngineUserForm` (AJAX wizard) | administer site configuration | Multi-step AI pipeline: users → curriculum → courses → chapters → lessons → slides/SCORM/Google Slides |
| `lms_ai.preview_users_json` | `/admin/everlms-ai-engine/preview-users-json` | `AiPreviewController::previewUsersJson` | administer site configuration | Returns `JSON/ai_users.json` contents in AJAX dialog |

**`/admin/everlms-ai-engine/preview-users-json` — the one JSON-returning route in AI:**

**Response:** AJAX JSON dialog with raw file contents of `JSON/ai_users.json`.
**Used by:** `AiEngineUserForm` preview button.

### CourseBuilderService Contract (internal)

```php
CourseBuilderService::generateCourseStructure(
    string $topic,
    string $targetAudience,
    string $tone
): ?Course
```

- Calls `openai.generateJson()` with course schema.
- Creates `lms_course` (status=0 draft) + `lms_lesson` entities.
- Queues `lms_ai_content_generator` queue items (queue worker: `CourseContentGenerator`, cron time 60s).
- **No rollback:** partial failures leave orphaned entities. Known risk — no saga/compensation pattern.

### AI Service classes summary

| Service | Class | Purpose |
|---------|-------|---------|
| `plugin.manager.lms_ai_provider` | `AiProviderManager` | Plugin discovery + instantiation |
| `lms_ai.course_builder` | `CourseBuilderService` | Structured course generation via AI |
| `lms_ai.scorm_builder` | `ScormBuilderService` | Generates SCORM 1.2 ZIP from course/lesson data |
| `lms_ai.google_slide_builder` | `GoogleSlideBuilderService` | Creates Google Slides presentations via Google Drive API |

---

## Payment Gateway Plugin Interface

Documented here as the extension contract that third-party payment gateway modules must implement. The HTTP checkout routes are in [04-commerce-api.md](04-commerce-api.md).

**Plugin manager service:** `plugin.manager.payment_gateway`
**Class:** `PaymentGatewayManager` (extends `DefaultPluginManager`)
**Plugin annotation:** `@PaymentGateway(id, label, description)`
**Discovery namespace:** `Plugin/PaymentGateway` in any module
**Alter hook:** `lms_payment_payment_gateway_info`
**Cache bin:** `lms_payment_payment_gateway_plugins`

### PaymentGatewayInterface (Plugin)

`Drupal\lms_payment\Plugin\PaymentGatewayInterface` — the interface plugins MUST implement:

```php
interface PaymentGatewayInterface extends PluginInspectionInterface {
    public function processPayment($order, array $payment_details = []): array;
    public function handleWebhook($request): Response;
    public function getLabel(): string;
}
```

| Method | Return | Description |
|--------|--------|-------------|
| `processPayment($order, $payment_details)` | `array` | Process a payment. Must return `['status' => 'success\|pending\|redirect\|failed', 'transaction_id' => string, 'message' => string]` |
| `handleWebhook($request)` | `Response` | Handle an inbound webhook event from the payment provider. **No routes exist to receive webhooks** — all current implementations are stubs. |
| `getLabel()` | `string` | Human-readable gateway name |

### Implementing a Gateway Plugin

Extend `PaymentGatewayBase` and annotate with `@PaymentGateway`:

```php
/**
 * @PaymentGateway(
 *   id = "my_gateway",
 *   label = @Translation("My Gateway"),
 *   description = @Translation("Payments via My Gateway.")
 * )
 */
class MyGateway extends PaymentGatewayBase {
    public function processPayment($order, array $payment_details = []): array {
        // ...
        return ['status' => 'success', 'transaction_id' => $txn_id];
    }

    public function handleWebhook($request): Response {
        // Validate signature here — NOT implemented in any existing plugin
        return new Response('OK', 200);
    }
}
```

**`PaymentGatewayBase` default implementations:**
- `processPayment()`: returns `['status' => 'failed', 'message' => 'Payment processing not implemented.']` — STUB.
- `handleWebhook()`: returns `Response('Webhook not implemented.', 500)` — STUB.

**Warning — second interface:** `Drupal\lms_payment\Service\PaymentGatewayInterface` also exists (separate from the plugin interface). It is only implemented by `lms_paypal\PayPalGateway` (dead code — see [04-commerce-api.md](04-commerce-api.md)). Do NOT implement this interface for new gateways — use the `Plugin\PaymentGatewayInterface`.

### Config (lms_payment.settings)

Admin form at `/admin/config/everlms/payment`. Config keys: `enable_stripe`, `stripe_publishable_key`, `stripe_secret_key`, `enable_paypal`, `paypal_client_id`, `paypal_secret`, `enable_zalopay`, `zalopay_app_id`, `zalopay_key1`, `zalopay_key2`, `enable_bank_transfer`, `bank_bin`, `bank_account_no`, `bank_account_name`, `bank_instructions`. All stored in YAML config — «secrets in config — see security risk».

---

## Zoom Service Contract (internal)

`ZoomService` (service id: `lms_zoom.service`) provides the Zoom REST API wrapper. OAuth: Server-to-Server Account Credentials grant. Token cached in Drupal State (`lms_zoom_access_token`, `lms_zoom_token_expires`).

**Config keys** (`lms_zoom.settings` — «secrets in config — see security risk»): `account_id`, `client_id`, `client_secret`, `sdk_key`, `sdk_secret`.

| Method | Signature | Description |
|--------|-----------|-------------|
| `createMeeting` | `(topic, startTime, durationMinutes, ?instructorEmail): ?array` | POST `/users/{userId}/meetings` (type=2 scheduled). Uses instructorEmail or 'me'. |
| `getMeeting` | `(meetingId): ?array` | GET `/meetings/{id}` |
| `deleteMeeting` | `(meetingId): bool` | DELETE `/meetings/{id}` |
| `getRecordings` | `(meetingId): ?array` | GET `/meetings/{id}/recordings` |
| `getZakToken` | `(userId): ?string` | GET `/users/{userId}/token?type=zak` — for WebSDK host start |

**Lifecycle hooks:**
- `hook_entity_presave` (Lesson, field_type=zoom): calls `ZoomMeetingManager::syncMeeting()` → creates/updates Zoom meeting.
- `hook_entity_delete` (Lesson, field_type=zoom): calls `ZoomMeetingManager::deleteMeeting()`.
- `hook_cron`: calls `ZoomAiService::processSummaries()` — loads completed zoom lessons (batch 5), fetches recordings, generates AI summaries via OpenAI. **One stub:** VTT transcript download uses hardcoded simulated string instead of real HTTP fetch.

---

## Analytics Routes (Admin — HTML + CSV)

`lms_analytics` exposes admin report routes. These are not REST APIs — they render HTML dashboard pages and CSV exports. Documented here for completeness as integration-layer reporting.

| Route | Path | Auth | Response | Description |
|-------|------|------|----------|-------------|
| `lms_analytics.course_report` | `/admin/lms/reports/courses` | administer lms_course | HTML | Course enrollment, completion, revenue stats |
| `lms_analytics.course_report_export` | `/admin/lms/reports/courses/export` | administer lms_course | CSV | Course stats CSV (UTF-8 BOM, limit 10000) |
| `lms_analytics.user_report` | `/admin/lms/reports/users` | administer users | HTML | User activity, quiz, certificate stats |
| `lms_analytics.user_report_export` | `/admin/lms/reports/users/export` | administer users | CSV | User stats CSV |
| `lms_analytics.quiz_report_list` | `/admin/lms/reports/quizzes` | administer lms_quiz | HTML | Quiz overview list with pass rates |
| `lms_analytics.quiz_report_detail` | `/admin/lms/reports/quizzes/{quiz_id}` | administer lms_quiz | HTML | Per-quiz item analysis (score distribution, hardest questions) |
| `lms_analytics.quiz_report_export` | `/admin/lms/reports/quizzes/{quiz_id}/export` | administer lms_quiz | CSV | Quiz item analysis CSV |

**Path parameters:** `quiz_id` is a raw integer (NOT entity-upcasted). The controller throws `NotFoundHttpException` if quiz not found, but there is no route-layer access check on the quiz ID — any admin with `administer lms_quiz` can access any quiz_id.

**Note on GA4:** `lms_analytics.info.yml` description mentions "Analytics integration (GA4)" but **no GA4 integration code exists** in the module. Internal SQL reporting only.

---

## Caching

| Endpoint | Strategy |
|----------|----------|
| `/api/lms/websdk/signature` | No caching — live JWT per request |
| `/admin/everlms-ai-engine/preview-users-json` | No caching — reads live JSON file |
| Analytics report routes | Drupal page cache disabled (dynamic SQL queries with user context) |
| AI provider plugin discovery | Cache bin: `lms_ai_provider_plugins` |
| Payment gateway plugin discovery | Cache bin: `lms_payment_payment_gateway_plugins` |

---

## API Route Summary

| Method | Path | Route Name | Auth | Response | Status |
|--------|------|-----------|------|----------|--------|
| POST | `/api/lms/websdk/signature` | `lms_zoom.generate_signature` | logged_in | JSON | as-built (service id bug) |
| GET | `/zoom/join/{meeting_id}` | `lms_zoom.join_meeting` | logged_in | HTML | as-built |
| GET | `/admin/everlms-ai-engine/preview-users-json` | `lms_ai.preview_users_json` | administer site configuration | JSON/dialog | as-built |
| GET+POST | `/manage/courses/ai-builder` | `lms_ai.course_builder_form` | administer lms_course | HTML/AJAX | as-built |
| GET+POST | `/admin/everlms-ai-engine` | `lms_ai.engine_users_form` | administer site configuration | HTML/AJAX | as-built |
| GET+POST | `/admin/config/lms/ai` | `lms_ai.settings` | administer site configuration | HTML | as-built |
| GET+POST | `/admin/config/lms/zoom` | `lms_zoom.settings` | administer site configuration | HTML | as-built |
| GET+POST | `/admin/config/everlms/payment` | `lms_payment.settings` | administer site configuration | HTML | as-built |
| GET | `/admin/lms/reports/courses` | `lms_analytics.course_report` | administer lms_course | HTML | as-built |
| GET | `/admin/lms/reports/courses/export` | `lms_analytics.course_report_export` | administer lms_course | CSV | as-built |
| GET | `/admin/lms/reports/users` | `lms_analytics.user_report` | administer users | HTML | as-built |
| GET | `/admin/lms/reports/users/export` | `lms_analytics.user_report_export` | administer users | CSV | as-built |
| GET | `/admin/lms/reports/quizzes` | `lms_analytics.quiz_report_list` | administer lms_quiz | HTML | as-built |
| GET | `/admin/lms/reports/quizzes/{quiz_id}` | `lms_analytics.quiz_report_detail` | administer lms_quiz | HTML | as-built |
| GET | `/admin/lms/reports/quizzes/{quiz_id}/export` | `lms_analytics.quiz_report_export` | administer lms_quiz | CSV | as-built |

---

## Known Issues

| Ref | Severity | Description |
|-----|----------|-------------|
| Zoom `generateSignature()` wrong service ID | High | `ZoomController::generateSignature()` references wrong service ID — may fail at runtime. See DOC-BRIEF known bugs. |
| AI pipeline no rollback | High | `CourseBuilderService` and `AiEngineUserForm` AJAX handlers do not roll back on partial failure — orphaned entities remain in DB. |
| `generateText()` not in AiProviderInterface | Medium | All providers implement `generateText()` but it is not declared in `AiProviderInterface`. Callers relying on interface type cannot call it safely. |
| `PollinationsProvider` — text/JSON unsupported | Medium | `generateJson()` and `generateText()` return NULL with warning log — callers must handle NULL gracefully. |
| Zoom AI summary VTT stub | Medium | `ZoomAiService::processSummaries()` downloads VTT as hardcoded simulated string instead of real HTTP fetch from Zoom recordings. |
| Secrets in config YAML | Critical | OpenAI key, Gemini key, Zoom credentials, Stripe keys all stored in plaintext committed config YAML. «secret in config — see security risk» |
| No webhook routes for payment gateways | Critical | `PaymentGatewayInterface::handleWebhook()` has no corresponding routes — Stripe/PayPal/ZaloPay cannot send async payment events. (Also documented in 04-commerce-api.md.) |
| GA4 description false | Low | `lms_analytics.info.yml` claims GA4 integration; no GA4 code exists. |
| Zoom webhook receiver absent | Medium | Zoom sends `meeting.ended`, `recording.completed` webhooks for AI summary triggers. No route receives these — `hook_cron` polling is used as workaround instead. |
