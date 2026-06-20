---
feature: F-012
status: DRAFT
---

# B05 — Acceptance Criteria for F-012 — Security & Performance

> Per-feature Given/When/Then criteria grounded in code and config. Cross-cutting concerns verified against `config/sync/` (396 YAML files), module access control handlers, Drupal form API, and known issues.
>
> Several criteria document **risks** (secrets in committed config) rather than passing tests. Do not mark these as passing without remediation.

---

## AC-126: Authenticated routes enforce login redirect and API 403

**Priority:** Must
**Given** any route annotated `_user_is_logged_in: TRUE` (e.g. `/my-courses`, `/dashboard`, `/learn/{course}/{lesson}`, all `/api/v1/*` protected endpoints)
**When** an unauthenticated client requests the route
**Then** Drupal's access layer denies the request; web routes redirect to `/user/login?destination=<original-path>`; API routes (`/api/v1/*`) return HTTP 403 via `AccessDeniedHttpException` from `getAuthenticatedUser()`
**And** after successful login the user is redirected to the originally requested URL (web flows only)

---

## AC-127: Role-based access — no privilege escalation via URL manipulation

**Priority:** Must
**Given** a `student`-role user is authenticated
**When** they request an admin-gated route (e.g. `/workspace/courses`, `/admin/lms/reports/courses`, `/admin/lms/notifications/broadcast`) directly via URL
**Then** Drupal's permission check (`_permission: administer lms_course`) returns access denied; the response is HTTP 403 with no data leakage about the resource
**And** entity-level access control handlers (`ForumTopicAccessControlHandler`, `ForumReplyAccessControlHandler`) enforce update/delete only for the entity owner or a user with `administer lms_course`

---

## AC-128: XSS prevention via Drupal text format filters

**Priority:** Must
**Given** filter formats are defined in `config/sync/filter.format.*.yml`: `basic_html` (filter_html + image handling), `restricted_html` (filter_html + filter_autop + filter_url), `full_html` (filter_htmlcorrector), `plain_text` (filter_html_escape)
**When** a user submits a forum post, lesson comment, or content field with `<script>alert('xss')</script>`
**Then** `filter_html` (applied in `basic_html` and `restricted_html`) strips or escapes the disallowed tag before storage and output; `filter_htmlcorrector` (in `full_html`) ensures well-formed HTML without arbitrary script execution
**And** the `authenticated` role is granted `basic_html` and `full_html` text format permissions; `anonymous` gets `restricted_html` only

> **Status note — bug:** `lms_progress.module` `hook_form_alter` grants all text formats to the `authenticated` role by saving the role entity at form-alter time. This unconditionally widens text format access as a side effect and must be fixed — it is a security and performance concern.

---

## AC-129: CSRF protection via Drupal Form API tokens

**Priority:** Must
**Given** any state-changing HTML form built via Drupal's `FormBuilder` (checkout forms, notification preferences, broadcast, certificate revoke, forum topic/reply forms, quiz settings)
**When** the form is rendered
**Then** Drupal's Form API automatically embeds CSRF tokens in `form_build_id` and `form_token` hidden fields; on submission these are validated server-side before any data is changed
**And** if the token is missing or stale, the form submission is rejected with "The form has become outdated"

> **Note on API routes:** Mobile API routes (`/api/v1/*`) use `_access: TRUE` with no CSRF requirement — they rely on Bearer token auth. Legacy API routes (`/api/lms/*`) require `_user_is_logged_in: TRUE` but do not enforce CSRF (standard JSON API pattern).

---

## AC-130: Password hashing via bcrypt

**Priority:** Must
**Given** a user sets or resets their password (web form or mobile `/api/v1/user/reset-password`)
**When** the password is saved via Drupal's `user` entity or `MobileAuthController::resetPassword()`
**Then** Drupal stores the password using `password_bcrypt` (Drupal 11 default, bcrypt with adaptive cost ≥10)
**And** mobile password verification uses Drupal's `password` service (`$password->check($plain, $hash)`) — not a custom implementation
**And** the `password_policy` module is installed (cron threshold 250 in config); user entity has `field_last_password_reset`, `field_password_expiration`, `field_pending_expire_sent` fields; active policy rules are not committed in current config

---

## AC-131: Secrets stored in plaintext committed config — critical risk (as-built)

**Priority:** Must (document current state — risk, not a passing criterion)
**Given** the git repository contains committed config YAML files
**When** any developer clones the repository
**Then** the following credentials are readable in plaintext:
- `lms_ai.settings.yml`: OpenAI API key, Gemini API key — «secret in config — see security risk»
- `lms_payment.settings.yml`: Stripe test keys (`pk_test_*`, `sk_test_*`), PayPal client ID and secret — «secret in config — see security risk»
- `lms_zoom.settings.yml`: `account_id`, `client_id`, `client_secret`, `sdk_key`, `sdk_secret` — «secret in config — see security risk»
- `google_api_client.google_api_service_client.everlms_bot.yml`: RSA private key (PEM) for Google service account — «secret in config — see security risk»
**And** the Key module (`key`) is listed as a dependency of `lms_ai` but NO `key.key.*.yml` files exist in `config/sync` — all secrets bypass Key module abstraction; `.env` with Stripe/PayPal test credentials is also committed

> **This criterion CANNOT pass without remediating secrets.** See risk register.

---

## AC-132: Stripe in test mode / no webhook verification (as-built — risk)

**Priority:** Must (document current state)
**Given** `lms_payment.settings.yml` contains `stripe_publishable_key: pk_test_*`
**When** the system processes a Stripe payment
**Then** payment is processed in Stripe TEST MODE only; no real money is charged
**And** no webhook endpoint exists in any module; webhook signature verification is NOT implemented; Stripe cannot reliably notify EverLMS of asynchronous payment events (refunds, disputes, confirmations)

> **Status:** Stripe test-mode only; webhook processing is a TODO. Both block production launch.

---

## AC-133: Page cache disabled in current config (as-built — dev mode)

**Priority:** Must (document current state)
**Given** `config/sync/system.performance.yml` has `cache.page.max_age: 0`
**When** the application serves pages
**Then** Drupal's full page cache is disabled; every request hits PHP and the database
**And** CSS/JS preprocessing (`css.preprocess: true`, `js.preprocess: true`) and gzip compression (`css.gzip: true`, `js.gzip: true`) are enabled; fast-404 is enabled for static asset paths via regex

> **Status:** Page cache disabled is acceptable for development but is a **performance risk** for production. Per-component render cache and dynamic page cache are still active at sub-page level.

---

## AC-134: Image optimization — all image styles output WebP

**Priority:** Should
**Given** an image is uploaded to any image field
**When** an image style (`thumbnail` 100×100, `medium` 220×220, `large` 480×480, `wide` 1090px) is applied via `image_scale` + `image_convert_avif` effect
**Then** the output derivative is in **WebP** format (despite the effect name `image_convert_avif`, the extension is set to `webp`); upscaling is disabled for all styles
**And** the original file is preserved; only the derivative is in WebP

---

## AC-135: Error display hidden from end users

**Priority:** Must
**Given** `config/sync/system.logging.yml` has `error_level: hide`
**When** a PHP error or exception occurs server-side
**Then** no error backtrace, file path, or internal detail is displayed to end users; errors are logged to `watchdog` (dblog) and accessible at `/admin/reports/dblog` by administrators only

---

## AC-136: SMTP disabled — sendmail fallback (as-built — pre-production gap)

**Priority:** Must (document current state)
**Given** `config/sync/smtp.settings.yml` has `smtp_on: false`
**When** the system sends an email (OTP via `lms_mobile_api`, notification broadcast via `lms_notification`, certificate via `lms_certificate`)
**Then** Drupal falls back to `system.mail.yml` default mailer `php_mail` using `sendmail`; host and from address are placeholder/unset
**And** reliable email delivery in production requires SMTP to be configured (`smtp_on: true`, host, port, credentials) before launch

---

## AC-137: Security headers and session hardening (target-state / planned)

**Priority:** Must
**Given** the EverLMS application is deployed to production behind nginx (DDEV profile: `nginx-fpm`)
**When** any HTTP response is served
**Then** the following security headers are expected: `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, and a `Content-Security-Policy` to be defined
**And** session cookies should be set with `HttpOnly`, `Secure`, and `SameSite=Strict` flags

> **Status:** These headers are not verified in `config/sync/` — they would be set at the nginx layer or via Drupal's `sites/default/services.yml` `session.storage.options`. This criterion is **target-state / planned** — current state is «unverified».

---

## Local traceability (F-012 only)

| AC ID  | Test Case | Implementing Story / Module                         | Status  |
|--------|-----------|-----------------------------------------------------|---------|
| AC-126 | T-126     | — / Drupal core routing + lms_mobile_api            | PENDING |
| AC-127 | T-127     | — / Drupal roles config + module ACL handlers       | PENDING |
| AC-128 | T-128     | — / Drupal filter formats config + lms_progress bug | PENDING |
| AC-129 | T-129     | — / Drupal Form API                                 | PENDING |
| AC-130 | T-130     | — / Drupal password service + password_policy       | PENDING |
| AC-131 | T-131     | — / RISK: plaintext secrets in committed config     | PENDING |
| AC-132 | T-132     | — / lms_stripe (test mode, no webhooks)             | PENDING |
| AC-133 | T-133     | — / config:system.performance (cache disabled)      | PENDING |
| AC-134 | T-134     | — / config:image.style.* (WebP via AVIF effect)     | PENDING |
| AC-135 | T-135     | — / config:system.logging (error_level: hide)       | PENDING |
| AC-136 | T-136     | — / config:smtp.settings (SMTP disabled)            | PENDING |
| AC-137 | T-137     | — / nginx config (target-state: security headers)   | PENDING |

> Cross-feature traceability lives in `E05-traceability.md`.
