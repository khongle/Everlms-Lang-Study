---
feature: F-010
status: DRAFT
---

# B05 — Acceptance Criteria for F-010 — Integrations

> Per-feature Given/When/Then criteria grounded in code. Modules covered: `lms_zoom`, `lms_ai`, `lms_payment`, `lms_stripe`, `lms_paypal` (dead code).
>
> Critical known issues are called out inline; do not mark stubs or dead-code items as passing.

---

## AC-100: AI provider abstraction — switch between OpenAI and Gemini without code change

**Priority:** Must
**Given** `lms_ai.settings` has `default_provider = openai` (configurable in `AiSettingsForm` at `/admin/config/lms/ai`)
**When** an admin changes `default_provider` to `gemini` and saves
**Then** subsequent calls to `CourseBuilderService::generateCourseStructure()` and `ZoomAiService::generateAgenda()` invoke `plugin.manager.lms_ai_provider->createInstance('gemini')` without any business-logic code change
**And** `GeminiProvider::generateJson()` strips markdown code-block delimiters (`` ```json `` prefix/suffix) before JSON parsing — a Gemini-specific normalisation not needed for OpenAI

---

## AC-101: AI course generation — creates draft course with queued lesson content

**Priority:** Must
**Given** an admin navigates to `/manage/courses/ai-builder` and submits topic, audience, and tone
**When** `CourseBuilderService::generateCourseStructure()` runs
**Then** it calls the configured AI provider's `generateJson()` with the course schema prompt; on success it creates a draft `lms_course` (status=0) and one `lms_lesson` (status=0, placeholder content) per section; each lesson is queued in `lms_ai_content_generator` for async processing
**And** the admin is redirected to the course edit URL; `CourseContentGenerator` queue worker publishes each lesson (status=1) when cron processes the queue (cron time: 60 seconds)

---

## AC-102: AI generation — no rollback on partial failure (as-built — risk)

**Priority:** Must (document current state)
**Given** `CourseBuilderService::generateCourseStructure()` has created a `lms_course` and 3 of 10 `lms_lesson` entities when an exception occurs
**When** the exception propagates
**Then** the exception is caught at the top level, an error is logged, and NULL is returned — but the already-created `lms_course` and 3 `lms_lesson` entities remain in the database as orphans
**And** there is no saga, compensation transaction, or rollback mechanism; orphaned entities must be cleaned up manually

> **Status:** No rollback / compensation pattern is implemented. This is a known data-consistency risk for the AI generation flows.

---

## AC-103: AI engine wizard — step-by-step manual pipeline via AJAX

**Priority:** Must
**Given** an admin uses the AI Engine at `/admin/everlms-ai-engine`
**When** they click through AJAX steps (Generate Users → Approve Users → Curriculum → Courses → Chapters → Lessons → SCORM → Google Slides → Quiz → Approve Quiz)
**Then** each button triggers exactly one AJAX handler in `AiEngineUserForm`; results are persisted to `JSON/ai_users.json` or `JSON/ai_curriculum.json` and to DB entities incrementally
**And** each step can fail independently; failed entities are logged and counted without rolling back successful ones; there is no automatic chain between steps

---

## AC-104: Pollinations AI provider — image-only, no text/JSON

**Priority:** Should
**Given** `PollinationsProvider` is registered as a plugin (id=`pollinations`, no API key required)
**When** code calls `generateJson()` or `generateText()` on the Pollinations provider
**Then** the method logs a warning and returns NULL; only `generateImage()` is supported (GET `https://image.pollinations.ai/prompt/{encoded_prompt}?model=flux`)
**And** Pollinations should not be set as `default_provider` for any flow that requires JSON or text generation

---

## AC-105: Zoom lesson — meeting auto-created on lesson save

**Priority:** Must
**Given** a lesson has `field_type = 'zoom'` and Zoom API credentials are configured in `lms_zoom.settings`
**When** the lesson entity is saved (presave hook fires in `lms_zoom.module`)
**Then** `ZoomMeetingManager::syncMeeting(lesson)` calls `ZoomService::createMeeting()` via Server-to-Server OAuth (token cached in Drupal State); the returned meeting ID is stored in `field_zoom_meeting_id`
**And** if the lesson already has a `field_zoom_meeting_id`, the existing meeting is updated rather than duplicated; on lesson delete, `ZoomMeetingManager::deleteMeeting(lesson)` removes the Zoom meeting (errors caught and logged)

---

## AC-106: Zoom Web SDK — HS256 JWT signature generation

**Priority:** Must
**Given** an authenticated student navigates to `/zoom/join/{meeting_id}` and the frontend JS calls POST `/api/lms/websdk/signature`
**When** `ZoomController::generateSignature()` processes the request
**Then** it resolves the current user's role (host if they match `field_instructor`, attendee otherwise); generates an HS256 JWT using `firebase/php-jwt` with `sdk_key` and `sdk_secret` from `lms_zoom.settings`; returns the signature in a JSON response
**And** if the user is the host, a ZAK token is also fetched via `ZoomService::getZakToken()` and returned alongside the signature

---

## AC-107: Zoom AI summary — VTT transcript download is a stub (as-built — stub)

**Priority:** Must (document current state)
**Given** cron runs `ZoomAiService::processSummaries()` for completed Zoom lessons (batch of 5)
**When** the service fetches recordings via `ZoomService::getRecordings()` and looks for a TRANSCRIPT/TIMELINE file
**Then** instead of making a real HTTP request for the VTT file, it substitutes a hardcoded simulated transcript string ("Simulated Transcript: ...")
**And** the AI summary generated from this fake content is appended to `field_content` with a `<!-- AI_SUMMARY_DONE -->` marker; summaries are meaningless until real VTT download is implemented

> **Status:** VTT download in `ZoomAiService::processSummaries()` is a **stub**.

---

## AC-108: Bank transfer payment gateway — real as-built

**Priority:** Must
**Given** a student checks out an order and selects "Bank Transfer" payment method
**When** the order is submitted
**Then** `BankTransferGateway` (a real `PaymentGatewayInterface` implementation with `@PaymentGateway` annotation) processes the order; the student sees bank account details from `lms_payment.settings` (BIN: BIDV, account: `1170181109`, name: `LE HUU PHAT`)
**And** the order status is set to `pending` until an admin manually confirms the transfer

---

## AC-109: Stripe PaymentIntent — real but created on form BUILD (as-built — bug)

**Priority:** Must
**Given** a student navigates to the checkout page with Stripe payment selected
**When** the Stripe payment form is rendered (page load, not on submit)
**Then** `lms_stripe` calls `Stripe\PaymentIntent::create()` on form BUILD; the `client_secret` is embedded in the form for Stripe.js; on successful payment the order status is updated to `completed`

> **Status note — bug:** PaymentIntent is created on form BUILD, not on form submit. Each page load creates a new orphaned PaymentIntent in Stripe. No webhook endpoints exist anywhere in the codebase; webhook signature verification is NOT implemented (TODO comment in source).

---

## AC-110: PayPal integration is dead code (as-built — dead code)

**Priority:** Must (document current state)
**Given** `lms_paypal` module exists in the codebase
**When** `PaymentGatewayManager` (from `lms_payment`) discovers plugins via the `@PaymentGateway` annotation
**Then** `lms_paypal`'s gateway class is **not discovered** — it implements the wrong interface and has no `@PaymentGateway` annotation; it is effectively dead code and never registered as an available payment gateway
**And** ZaloPay credentials in `lms_payment.settings.yml` are empty (`zalopay_app_id`, `zalopay_key1`, `zalopay_key2` all blank) — ZaloPay is non-functional

---

## AC-111: Payment credentials stored in plaintext committed config (as-built — critical risk)

**Priority:** Must (document current state — risk, not a passing criterion)
**Given** `config/sync/lms_payment.settings.yml` is committed to version control
**When** any developer clones the repository
**Then** Stripe test keys (`pk_test_*`, `sk_test_*`) and PayPal credentials are readable in plaintext YAML
**And** no Key module abstraction is used (`key.key.*` config files absent from `config/sync/`); secrets must be moved to environment variables or the Key module before production

> Also affected: OpenAI API key, Gemini API key, Zoom credentials (`account_id`, `client_id`, `client_secret`, `sdk_key`, `sdk_secret`), and a Google service-account RSA private key — all stored in committed config YAML. See risk register.

---

## Local traceability (F-010 only)

| AC ID  | Test Case | Implementing Story / Module                    | Status  |
|--------|-----------|------------------------------------------------|---------|
| AC-100 | T-100     | — / lms_ai (AiProviderManager)                 | PENDING |
| AC-101 | T-101     | — / lms_ai (CourseBuilderService)              | PENDING |
| AC-102 | T-102     | — / lms_ai (gap: no rollback)                  | PENDING |
| AC-103 | T-103     | — / lms_ai (AiEngineUserForm)                  | PENDING |
| AC-104 | T-104     | — / lms_ai (PollinationsProvider)              | PENDING |
| AC-105 | T-105     | — / lms_zoom (ZoomMeetingManager)              | PENDING |
| AC-106 | T-106     | — / lms_zoom (ZoomController)                  | PENDING |
| AC-107 | T-107     | — / lms_zoom (stub: VTT download)              | PENDING |
| AC-108 | T-108     | — / lms_payment (BankTransferGateway)          | PENDING |
| AC-109 | T-109     | — / lms_stripe (bug: build-time PaymentIntent) | PENDING |
| AC-110 | T-110     | — / lms_paypal (dead code)                     | PENDING |
| AC-111 | T-111     | — / lms_payment (risk: plaintext secrets)      | PENDING |

> Cross-feature traceability lives in `E05-traceability.md`.
