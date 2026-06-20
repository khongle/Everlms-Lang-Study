---
page: settings-integrations
mode: as-built
owner_module: lms_payment
route_group: manage
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Settings & Integrations

> Covers four Drupal `/admin/config/*` settings pages: Payment Gateway Settings, Zoom Configuration, AI Platform Settings, and (indirectly) the Notification Preferences form at `/manage/settings/notifications`.

**Canonical URLs:**
- `/admin/config/everlms/payment` — Payment Gateway Settings
- `/admin/config/lms/zoom` — Zoom Configuration
- `/admin/config/lms/ai` — AI Platform Settings
- `/manage/settings/notifications` — Notification Preferences (per-user, learner-accessible)

*(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*

**Route names:**
- `lms_payment.settings`
- `lms_zoom.settings`
- `lms_ai.settings`
- `lms_notification.preferences`

**Solution:** Four independent `ConfigFormBase` / `FormBase` instances; standard Drupal config object save pattern; no custom Twig templates (Drupal form rendering in admin/Claro theme for `/admin/*` routes, or lms_theme for `/manage/settings/notifications`).
**Owner modules:** `lms_payment`, `lms_zoom`, `lms_ai`, `lms_notification`
**Primary source:** Drupal config objects
**Editable source:**
- `lms_payment.settings` config
- `lms_zoom.settings` config
- `lms_ai.settings` config
- `user.data` service (per-user notification preferences — not a config object)
**Editor role:** `administer site configuration` (payment/zoom/ai); Authenticated user (notification prefs)
**Editor management UI:** These pages ARE the management UIs
**Revision / moderation:** N/A (config; exported to `/config/sync/*.yml`)
**Forms:**
- `Drupal\lms_payment\Form\PaymentSettingsForm`
- `Drupal\lms_zoom\Form\ZoomSettingsForm`
- `Drupal\lms_ai\Form\AiSettingsForm`
- `Drupal\lms_notification\Form\NotificationPreferencesForm`
**Theme template:** Drupal default (Claro admin theme for `/admin/*`; `lms_theme` for `/manage/settings/notifications`)
**Access:**
- Payment/Zoom/AI settings: `administer site configuration`
- Notification preferences: `_user_is_logged_in`
**Cache:** No-cache (config forms, per-request)
**Translation:** Monolingual EN
**Related entities:** No content entities; config objects only
**Prototype reference:** No C10 prototype.

**SECURITY NOTE:** Payment gateway API keys (Stripe, PayPal, ZaloPay), Zoom credentials (account_id, client_id, client_secret, sdk_key, sdk_secret), and AI API keys (OpenAI, Gemini) are all stored as **plaintext in committed config YAML** (`/config/sync/`). No Key module integration. Values must never be reproduced in documentation — «secret in config — see security risk».

---

## Components (visual order, top-to-bottom)

> Global chrome defined in [`../_common.md`](../_common.md).

### 1. Payment Gateway Settings Form — *Form cài đặt cổng thanh toán*

**Route:** `/admin/config/everlms/payment` (`lms_payment.settings`)

- **Scope:** Page-specific
- **Implementation mechanism:** `PaymentSettingsForm extends ConfigFormBase`; config object `lms_payment.settings`
- **Plugin / Module:** `lms_payment` → `src/Form/PaymentSettingsForm.php`
- **Source content type / Entity:** `lms_payment.settings` config object
- **Backed by fields / config keys:**
  | Config key | Field type | Purpose |
  |---|---|---|
  | `enable_stripe` | checkbox | Enable Stripe gateway |
  | `stripe_publishable_key` | textfield | Stripe publishable key («secret in config — see security risk») |
  | `stripe_secret_key` | textfield | Stripe secret key («secret in config — see security risk») |
  | `enable_paypal` | checkbox | Enable PayPal gateway (stub at runtime — no server-side verification) |
  | `paypal_client_id` | textfield | PayPal Client ID («secret in config — see security risk») |
  | `paypal_secret` | textfield | PayPal secret («secret in config — see security risk») |
  | `enable_zalopay` | checkbox | Enable ZaloPay (stub — hardcoded sandbox URL) |
  | `zalopay_app_id` | textfield | ZaloPay App ID |
  | `zalopay_key1`, `zalopay_key2` | textfield | ZaloPay keys |
  | `enable_bank_transfer` | checkbox | Enable Bank Transfer gateway (real/manual) |
  | `bank_bin` | textfield | Bank BIN code (VietQR) |
  | `bank_account_no` | textfield | Bank account number |
  | `bank_account_name` | textfield | Account holder name |
  | `bank_instructions` | textarea | Bank transfer instructions text |
- **Editable source:** `/admin/config/everlms/payment`
- **Editor role / permission:** `administer site configuration`
- **Editor management UI:** This page
- **Revision / moderation:** Config: tracked in `/config/sync/lms_payment.settings.yml` (plaintext keys committed — security risk)
- **Translation source:** N/A
- **Edit smoke:** Enable Stripe + save → test checkout → verify Stripe Elements renders
- **View mode / Projection:** Standard ConfigFormBase Drupal admin form (Claro theme)
- **Region / Placement:** Claro admin `content` region
- **Notes:**
  - **Stripe gateway status:** REAL — `StripeGateway::processPayment()` calls `\Stripe\PaymentIntent::retrieve()` via Stripe PHP SDK. Webhook handler is a TODO stub.
  - **PayPal gateway:** The discoverable plugin (`lms_payment\Plugin\PaymentGateway\PayPalGateway`) is a stub — accepts any `paypal_order_id` without server-side verification. `lms_paypal` module's `PayPalGateway` class is dead code (wrong interface, no annotation).
  - **ZaloPay gateway:** STUB — hardcoded sandbox redirect URL, no HMAC verification.
  - **Bank Transfer:** REAL for its use case (manual review flow).
  - **No webhook routes exist** for any gateway — async payment confirmation is unimplemented.
  - **Stripe PaymentIntent created at form BUILD time** (one PaymentIntent per page load of checkout) — security risk.
- **How to implement (EN):** `PaymentSettingsForm` in `web/modules/custom/lms_payment/src/Form/PaymentSettingsForm.php`. Access at `/admin/config/everlms/payment`. Menu link: `lms_payment.settings` under `system.admin_config`.
- **How to implement (VI):** Form tại `src/Form/PaymentSettingsForm.php`. Truy cập tại `/admin/config/everlms/payment`.
- **Solution Architect Request:** —

---

### 2. Zoom Configuration Form — *Form cài đặt tích hợp Zoom*

**Route:** `/admin/config/lms/zoom` (`lms_zoom.settings`)

- **Scope:** Page-specific
- **Implementation mechanism:** `ZoomSettingsForm extends ConfigFormBase`; config object `lms_zoom.settings`
- **Plugin / Module:** `lms_zoom` → `src/Form/ZoomSettingsForm.php`
- **Source content type / Entity:** `lms_zoom.settings` config object
- **Backed by fields / config keys:**
  | Config key | Field type | Purpose |
  |---|---|---|
  | `account_id` | textfield | Zoom Server-to-Server OAuth Account ID («secret in config — see security risk») |
  | `client_id` | textfield | Zoom OAuth Client ID («secret in config — see security risk») |
  | `client_secret` | password | Zoom OAuth Client Secret — only updated when non-empty on submit («secret in config — see security risk») |
  | `sdk_key` | textfield | Zoom WebSDK Key («secret in config — see security risk») |
  | `sdk_secret` | password | Zoom WebSDK Secret — only updated when non-empty («secret in config — see security risk») |
- **Editable source:** `/admin/config/lms/zoom`
- **Editor role / permission:** `administer site configuration`
- **Editor management UI:** This page
- **Revision / moderation:** Config tracked in `/config/sync/lms_zoom.settings.yml`
- **Translation source:** N/A
- **Edit smoke:** Enter valid Zoom credentials → save → create Zoom lesson → verify meeting created via Zoom API
- **View mode / Projection:** ConfigFormBase form (Claro theme)
- **Region / Placement:** Claro admin `content` region
- **Notes:**
  - Zoom uses Server-to-Server OAuth (Account Credentials grant). Token cached in Drupal State (`lms_zoom_access_token`, `lms_zoom_token_expires`).
  - `ZoomController::generateSignature()` has a known bug: references wrong service ID (`«unverified»` — see known issues); generates HS256 JWT via `firebase/php-jwt`.
  - `ZoomAiService::processSummaries()` VTT download is a stub (hardcoded simulated string).
  - Menu link: `lms_zoom.settings` under `system.admin_config` (weight: 50).
- **How to implement (EN):** Form in `web/modules/custom/lms_zoom/src/Form/ZoomSettingsForm.php`. Access at `/admin/config/lms/zoom`.
- **How to implement (VI):** Form tại `src/Form/ZoomSettingsForm.php`. Truy cập tại `/admin/config/lms/zoom`.
- **Solution Architect Request:** —

---

### 3. AI Platform Settings Form — *Form cài đặt nền tảng AI*

**Route:** `/admin/config/lms/ai` (`lms_ai.settings`)

- **Scope:** Page-specific
- **Implementation mechanism:** `AiSettingsForm extends ConfigFormBase`; config object `lms_ai.settings`
- **Plugin / Module:** `lms_ai` → `src/Form/AiSettingsForm.php`
- **Source content type / Entity:** `lms_ai.settings` config object
- **Backed by fields / config keys:**
  | Config key | Field type | Purpose |
  |---|---|---|
  | `default_provider` | select | Active provider: `openai` or `gemini` |
  | `openai.api_key` | password | OpenAI API key («secret in config — see security risk») |
  | `openai.model` | select | GPT model: gpt-4o / gpt-4-turbo / gpt-3.5-turbo |
  | `gemini.api_key` | password | Gemini API key («secret in config — see security risk») |
  | `gemini.model` | select | Gemini model: gemini-2.5-flash / gemini-2.5-pro / gemini-flash-latest / gemini-pro-latest |
- **Editable source:** `/admin/config/lms/ai`
- **Editor role / permission:** `administer site configuration`
- **Editor management UI:** This page
- **Revision / moderation:** Config tracked in `/config/sync/lms_ai.settings.yml`
- **Translation source:** N/A
- **Edit smoke:** Set provider=openai, enter API key → save → use AI Course Builder → verify course generated
- **View mode / Projection:** ConfigFormBase form (Claro theme)
- **Region / Placement:** Claro admin `content` region
- **Notes:**
  - Three providers available: OpenAI (full: text/JSON/image via DALL-E 3), Gemini (full: text/JSON/image via Imagen 4), Pollinations (image-only, no API key).
  - Provider discovery via Drupal Plugin system (`AiProviderManager`; `@AiProvider` annotation; `Plugin/AiProvider/` directory).
  - `generateText()` not declared in `AiProviderInterface` (missing from interface — only in concrete classes).
  - `google_api_client` contrib module required for Google Slides integration (`GoogleSlideBuilderService`).
  - Menu link: `lms_ai.engine_users_form` under `system.admin_config` (weight: 100) — note this links to the AI Engine admin page, not settings.
- **How to implement (EN):** Form in `web/modules/custom/lms_ai/src/Form/AiSettingsForm.php`. Access at `/admin/config/lms/ai`.
- **How to implement (VI):** Form tại `src/Form/AiSettingsForm.php`. Truy cập tại `/admin/config/lms/ai`.
- **Solution Architect Request:** —

---

### 4. Notification Preferences Form — *Form tuỳ chọn thông báo*

**Route:** `/manage/settings/notifications` (`lms_notification.preferences`)

- **Scope:** Page-specific (per-user learner preference — accessible to authenticated users, not admin-only)
- **Implementation mechanism:** `NotificationPreferencesForm extends FormBase`; reads/writes via `user.data` service (per-user key-value store, not a config object); rendered in `lms_theme` (not Claro)
- **Plugin / Module:** `lms_notification` → `src/Form/NotificationPreferencesForm.php`
- **Source content type / Entity:** `user.data` service keyed by `pref_{type}` under the current user's UID
- **Backed by fields / config keys:**
  | Event type | user.data key |
  |---|---|
  | enrollment | `pref_enrollment` |
  | quiz_result | `pref_quiz_result` |
  | certificate | `pref_certificate` |
  | payment | `pref_payment` |
  | forum_reply | `pref_forum_reply` |
  | new_lesson | `pref_new_lesson` |
  | membership_expiry | `pref_membership_expiry` |
  | announcement | `pref_announcement` |
  | reminder | `pref_reminder` |
  Each key stores array: `['in_app' => bool, 'email' => bool]`
- **Editable source:** Per-user data via `user.data` service — not exportable to config sync
- **Editor role / permission:** `_user_is_logged_in` (any authenticated user can manage own prefs)
- **Editor management UI:** This page (`/manage/settings/notifications`)
- **Revision / moderation:** N/A
- **Translation source:** Interface translation
- **Edit smoke:** Uncheck "email" for quiz_result → save → pass quiz → verify no email sent (note: email channel is a stub — only logs)
- **View mode / Projection:** HTML table with In-App + Email checkbox columns per event type; modal-aware (AJAX submit + cancel when in modal context)
- **Region / Placement:** `content` region; `lms_theme` page wrapper (not Claro)
- **Notes:**
  - Although under `/manage/settings/`, this route is `_user_is_logged_in` — accessible to all logged-in users including learners, not just admins. C07-sitemap notes this as learner-group conceptually.
  - **Email channel is a TODO stub** — `NotificationManager::dispatch()` only logs an info message for email channel; no actual email is sent.
  - **Push channel is a stub** — only logs.
  - In-app channel works fully.
  - Modal-aware: `$form_state->get('in_modal')` triggers AJAX submit/cancel buttons.
- **How to implement (EN):** Form in `web/modules/custom/lms_notification/src/Form/NotificationPreferencesForm.php`. Accessible at `/manage/settings/notifications`.
- **How to implement (VI):** Form tại `src/Form/NotificationPreferencesForm.php`. Truy cập tại `/manage/settings/notifications`.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Payment Gateway Settings Form | Gateway enable toggles, API keys (masked), bank transfer details | `lms_payment.settings` config | `enable_stripe`, `stripe_*`, `enable_paypal`, `paypal_*`, `enable_zalopay`, `zalopay_*`, `enable_bank_transfer`, `bank_*` | `/admin/config/everlms/payment` | `administer site configuration` | Config export | N/A | no-cache | Enable Stripe → checkout test |
| Zoom Configuration Form | Account ID, client credentials (masked), SDK keys | `lms_zoom.settings` config | `account_id`, `client_id`, `client_secret`, `sdk_key`, `sdk_secret` | `/admin/config/lms/zoom` | `administer site configuration` | Config export | N/A | no-cache | Enter creds → save → create Zoom lesson |
| AI Platform Settings Form | Provider select, OpenAI key+model, Gemini key+model | `lms_ai.settings` config | `default_provider`, `openai.*`, `gemini.*` | `/admin/config/lms/ai` | `administer site configuration` | Config export | N/A | no-cache | Set provider → AI course builder test |
| Notification Preferences Form | 9-row table of event types × In-App/Email checkboxes | `user.data` per-user key-value | `pref_{type}` × channel keys | `/manage/settings/notifications` | Authenticated (own prefs) | N/A | Interface EN | per-user, no-cache | Toggle pref → verify notification behaviour |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Notification event type labels (9 types) | Defined in `NotificationPreferencesForm::buildForm()` PHP | D00 code convention | On notification system expansion |
| AI model options (GPT/Gemini model names) | Defined in `AiSettingsForm::buildForm()` PHP select options | D00 code convention | On model version upgrades |
| Payment gateway sections (Stripe/PayPal/ZaloPay/Bank) | Defined in `PaymentSettingsForm::buildForm()` | D00 code convention | On gateway addition |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Payment Settings Form | DONE | | Form is real; PayPal/ZaloPay gateways are stubs at runtime |
| Zoom Settings Form | DONE | — | JWT signature generation has known bug (wrong service ID reference) |
| AI Settings Form | DONE | — | API keys stored plaintext in config — security risk |
| Notification Preferences Form | DONE (in-app only) | — | Email/push channels are TODO stubs |
| Webhook handling (all gateways) | NOT IMPLEMENTED | — | No webhook routes exist; Stripe/PayPal/ZaloPay webhook verification absent |
