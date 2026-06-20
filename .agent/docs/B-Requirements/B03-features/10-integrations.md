---
id: F-010
name: Platform Integrations
priority: Must
status: APPROVED
parent-requirements: [BR-011, BR-012]
owning_modules: [lms_payment, lms_stripe, lms_paypal, lms_zoom, lms_ai]
primary_source: C05
story_anchors: []
---

# F-010 — Platform Integrations

## User story

As a **platform administrator**, I want **working integrations with Stripe, Zoom, and AI content providers**, so that **learners can pay for courses, attend live sessions, and access AI-generated content without leaving the platform**.

## Description

EverLMS integrates with several third-party services. Implementation quality varies significantly across integrations — some are production-grade and others are stubs. This document records the **actual as-built state**, not aspirational targets. The payment plugin system (`lms_payment`) provides a `PaymentGatewayManager` with `@PaymentGateway` annotation discovery, allowing gateway modules to be independently enabled.

**Critical security note:** All third-party credentials (Stripe keys, PayPal credentials, Zoom account/client/SDK secrets, OpenAI API key, Gemini API key, Google service-account RSA private key) are stored as **plaintext in committed config YAML** under `config/sync/`. No Key module (`key.key.*`) is in use. See [Security risk register](../../E-Quality/E02-issues/) and [F-012 Security & Performance](./12-security-performance.md) for the open risk. Values are always redacted in documentation: «secret in config — see security risk».

### Payment Integrations

| Gateway | Plugin ID | Module | Status |
|---|---|---|---|
| Stripe | `stripe` | `lms_stripe` | **as-built** — real PaymentIntent confirm flow |
| Bank Transfer (VietQR) | `bank_transfer` | `lms_payment` | **as-built** — manual receipt upload |
| PayPal (plugin used at runtime) | `paypal` | `lms_payment` | **stub** — no server-side API verification |
| ZaloPay | `zalopay` | `lms_payment` | **stub** — hardcoded sandbox redirect URL |
| lms_paypal module `PayPalGateway` | N/A | `lms_paypal` | **dead code** — wrong interface, no annotation, never discovered |

**Stripe flow (as-built):** `CheckoutForm::buildForm()` calls `\Stripe\PaymentIntent::create()` server-side on every page load (one intent per build, not per submission — a known bug). Client-side Stripe Elements confirms the intent in the browser. On form submit, `StripeGateway::processPayment()` retrieves the intent and checks `status === 'succeeded'`. Requires `stripe/stripe-php` Composer package.

**Webhook gap:** No webhook endpoint exists for any gateway. `StripeGateway::handleWebhook()` contains a `// TODO: Implement webhook verification.` comment and returns 200 with no validation. PayPal and ZaloPay webhook handlers are equally empty. Async payment confirmation (e.g., 3DS redirects, delayed captures) cannot be processed. This is a critical production gap.

### Zoom Integration (`lms_zoom`)

**Status: as-built** with one documented stub.

- **OAuth:** Server-to-Server OAuth (Account Credentials grant); token cached in Drupal State.
- **Meeting lifecycle:** `ZoomMeetingManager::syncMeeting()` creates or updates Zoom meetings on Lesson presave (`field_type=zoom`); deletes on lesson delete.
- **Web SDK join:** `/zoom/join/{meeting_id}` renders the Zoom Web SDK player in a dedicated full-screen page template. `ZoomController::generateSignature()` issues an HS256 JWT via `firebase/php-jwt`.
- **AI agenda generation:** `ZoomAiService::generateAgenda()` calls OpenAI to generate an HTML agenda, injected via AJAX into the lesson edit form.
- **Meeting summaries:** `hook_cron()` calls `ZoomAiService::processSummaries()` — fetches recordings and generates AI summaries appended to lesson content. **Stub:** VTT transcript download uses a hardcoded simulation string instead of real HTTP fetch.
- **Bug:** `ZoomController::generateSignature()` uses `\Drupal::service('lms_zoom.zoom_service')` (line 95) but the registered service ID is `lms_zoom.service` — will throw a Symfony `ServiceNotFoundException` at runtime when the signature endpoint is called by a host user.
- **Config keys:** account_id, client_id, client_secret, sdk_key, sdk_secret — all stored plaintext in `lms_zoom.settings.yml` «secret in config — see security risk».

### AI Integration (`lms_ai`)

**Status: as-built** core plugin system; no rollback/compensation on partial failure.

- **Plugin manager:** `AiProviderManager` (`DefaultPluginManager`, annotation `@AiProvider`, alter hook `lms_ai_provider_info`, cache bin `lms_ai_provider_plugins`).
- **Providers:**
  - `openai` — full: `generateJson()`, `generateText()`, `generateImage()` (DALL-E 3). Default model: `gpt-4o`. «secret in config — see security risk».
  - `gemini` — full: `generateJson()`, `generateText()`, `generateImage()` (Imagen 4.0). Default model: `gemini-2.5-flash`. «secret in config — see security risk».
  - `pollinations` — partial: `generateImage()` only (free, no API key); `generateJson()` and `generateText()` log warnings and return NULL.
- **CourseBuilderService:** Generates course structure JSON via OpenAI, creates `lms_course` + `lms_lesson` entities, queues `lms_ai_content_generator` items for lesson content generation. No rollback: partial creation leaves orphaned entities.
- **AiEngineUserForm:** 2361-line admin wizard at `/admin/everlms-ai-engine` with step-by-step AJAX pipeline for generating users, avatars, curriculum, course/chapter/lesson detail, SCORM packages, Google Slides, and quizzes. Each step is manual (no automatic chain). Partial failures are logged but not compensated.
- **Google Slides:** `GoogleSlideBuilderService` duplicates a template presentation in Google Drive and fills dynamic content. Requires `google/apiclient` + `google_api_client` contrib. Service account RSA private key stored in config «secret in config — see security risk».
- **SCORM:** `ScormBuilderService` generates SCORM 1.2 ZIP packages from lesson/slide data using PHP `ZipArchive`.

### Communication / Email

- **Mail system:** Drupal `php_mail` / `sendmail` (default). SMTP module configured but **disabled** (`smtp.settings.yml`: `smtp_on: false`). Transactional emails (enrollment confirmation, membership expiry, OTP codes) sent via Drupal's `plugin.manager.mail`.
- **Email notification on enrollment:** triggered by `lms_order.module` `hook_entity_update` when order status transitions to `completed`.

### Interactive Content

- **H5P:** Supported as a lesson type (`field_type=h5p`) — `lms_mobile_api` returns raw `h5p_embed` HTML string; the web lesson template (`lms-lesson-view.html.twig`) renders the embed code via `{{ lesson.field_h5p_embed.value|raw }}`. No xAPI/cmi5 event listeners, no score extraction, and no automatic completion-tracking bridge exist anywhere in the codebase. Completion is only via the manual "Mark Complete" button (confirmed in lms-lesson-view.html.twig and MobileLearningController.php).
- **SCORM:** Generated by `ScormBuilderService` (AI pipeline). SCORM packages stored as ZIP at `public://lessons/scorm/`; runtime player renders the extracted package via an `<iframe src="public://scorm_extracted/{id}/index.html">` (web) or returns a `scorm_url` string pointing to the same path (mobile API). No SCORM API (LMSInitialize/LMSSetValue) runtime bridge is injected — the SCORM package runs in isolation inside the iframe with no LMS communication. Completion is only via the manual "Mark Complete" button (confirmed in lms-lesson-view.html.twig and MobileLearningController::getLessonDetail()).

### Analytics / Marketing Trackers

**No Google Analytics 4 integration exists.** `lms_analytics` module description claims GA4 but contains zero GA4 code. No tag injection, no event tracking, no GTM configuration is present anywhere in the codebase. This is a **target-state / planned** requirement, not an as-built integration.

## In scope (as-built)

- Stripe PaymentIntent payment flow with client-side Elements confirmation.
- Bank Transfer (VietQR) with receipt upload.
- PaymentGateway plugin architecture enabling new gateways via `@PaymentGateway` annotation.
- Zoom meeting create/update/delete lifecycle tied to Lesson entity saves.
- Zoom Web SDK player with HS256 JWT signature endpoint.
- AI provider plugin system (OpenAI full, Gemini full, Pollinations image-only).
- AI course builder (CourseBuilderService + CourseContentGenerator queue worker).
- AI Engine admin wizard (AiEngineUserForm) with SCORM and Google Slides generation.
- Transactional email delivery via Drupal mail system for enrollment, OTP, membership expiry.

## Out of scope

- PayPal server-side payment verification — stub only.
- ZaloPay real integration — stub only.
- Webhook signature verification for any gateway — not implemented.
- Google Analytics 4 / GA4 / GTM — not implemented (planned).
- Facebook Pixel, Microsoft Ads, or other marketing trackers — not implemented.
- AI rollback/compensation on partial pipeline failure — not implemented.
- Zoom VTT transcript download (real HTTP) — stub (simulated string).
- SMTP email in production — SMTP module is installed but disabled.
- Social auth providers (Google/Facebook login on web) — `social_auth.settings.yml` shows no active providers configured despite views existing.

## Acceptance summary

An administrator can configure Stripe keys in the Payment Gateway Settings form and process a test payment that results in an enrolled learner. A Zoom lesson can be created, automatically syncing a Zoom meeting, and a logged-in user can join via the Web SDK player. An AI-generated course can be scaffolded via the Course Builder form. Full Given/When/Then criteria live in `../B05-acceptance-criteria/F-010.md`.

## Content ownership / editability

| Visible content family | Business owner | Editor role | Expected edit surface | Notes |
|---|---|---|---|---|
| Payment gateway configuration | Platform admin | administrator | `/admin/config/everlms/payment` | Stripe/PayPal keys; never commit real keys to VCS |
| Zoom settings | Platform admin | administrator | `/admin/config/lms/zoom` | SDK + API credentials |
| AI provider settings | Platform admin | administrator | `/admin/config/lms/ai` | OpenAI/Gemini keys and default models |
| AI Engine prompts | Content team | administrator | `/admin/everlms-ai-engine` | Stored in `lms_ai.engine.settings` config |

## Dependencies (requirement-level only)

| Type | Reference | Reason |
|---|---|---|
| Feature | F-004 (Order & Membership) | Payment integrations complete the purchase flow |
| Feature | F-001 (Course Management) | AI course builder generates `lms_course` entities |
| Feature | F-005 (Content & Media) | Zoom and SCORM are lesson content types |
| Integration | INT-001 (Stripe) | Real-time card payment processing |
| Integration | INT-002 (Zoom) | Live class delivery |
| Integration | INT-003 (OpenAI / Gemini) | AI content and image generation |

## Open questions

- [ ] Stripe PaymentIntent creation on `buildForm()` (not `submitForm()`) — every page load creates a new PaymentIntent. Confirm whether this causes orphaned intents and whether a fix (lazy creation / AJAX) is planned.
- [ ] Webhook endpoints for Stripe, PayPal, ZaloPay are absent. Is async payment confirmation (3DS, subscription renewals) required before production go-live?
- [ ] `lms_paypal` module (`PayPalGateway` dead code) — should it be removed or rewritten with the correct `Plugin\PaymentGatewayInterface`?
- [ ] GA4 integration: confirm it is deferred (not dropped) and what the target delivery milestone is.
- [ ] SMTP must be configured and enabled before production — confirm SMTP provider and credentials (do NOT commit to config).
- [ ] Social auth (Google/Facebook web login): is this a planned feature or dropped?
