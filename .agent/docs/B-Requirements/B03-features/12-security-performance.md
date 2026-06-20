---
id: F-012
name: Security & Performance
priority: Must
status: APPROVED
parent-requirements: [BR-016]
owning_modules: [lms_base, lms_payment, lms_stripe, lms_zoom, lms_ai, lms_mobile_api]
primary_source: mixed
story_anchors: []
---

# F-012 — Security & Performance

## User story

As a **platform administrator**, I want **the EverLMS platform to enforce access controls, protect user data, and serve pages within acceptable response times**, so that **learners' accounts and payments are safe and the learning experience is responsive**.

## Description

This feature spans two concern areas: security controls (authentication, authorization, secret management, input validation) and performance (caching, asset delivery, response targets). Several security controls are implemented by Drupal core and are genuinely as-built. However, **secret management is a critical open gap**: all third-party API keys and service credentials are stored in plaintext in committed configuration YAML files. This is the highest-severity security risk in the system and must be remediated before production deployment.

Performance configuration is partially optimized (CSS/JS aggregation enabled, image WebP conversion active) but page caching is disabled (`max_age: 0`) in the current config export — appropriate for development, must be revisited for production.

---

## Security Controls — as-built

### Authentication (Drupal web)

| Control | Status | Detail |
|---|---|---|
| Password hashing | as-built | Drupal 11 default: Argon2i/bcrypt via `password` service |
| CSRF protection | as-built | Drupal Form API token on all state-changing forms |
| XSS prevention | as-built | Twig auto-escaping; `Xss::filter()` available for manual use |
| SQL injection prevention | as-built | Drupal Database API prepared statements; `lms_analytics` uses raw SQL with `db->condition()` parameter binding |
| Secure session cookies | as-built | Drupal core HttpOnly + SameSite cookies |
| Login rate limiting | as-built | Drupal `user.flood` service throttles failed login attempts |
| Input validation | as-built | Drupal Form API server-side validation; mobile API uses manual JSON decode + field-level checks |

### Authentication (Mobile API)

| Control | Status | Detail |
|---|---|---|
| Bearer token auth | as-built | Custom `keyvalue.expirable` tokens; 7-day TTL |
| OTP for registration and password reset | as-built | 6-digit CSPRNG (`random_int`); 15-min TTL |
| Enumeration prevention (forgot password) | as-built | `/api/v1/user/forgot-password` always returns 200 regardless of email existence |
| Token not refreshable | gap | No refresh token mechanism; expired tokens require re-login |
| No rate limiting on API endpoints | gap | `/api/v1/` routes have no flood/throttle protection |

### Authorization

| Control | Status | Detail |
|---|---|---|
| Route-level permission checks | as-built | All admin routes use `_permission` requirement; Workspace routes use `_admin_route: TRUE` |
| Entity access control | as-built | All custom entities have `EntityAccessControlHandler`; Order access restricted to owner or admin |
| Enrolled-only content | as-built | `MembershipAccessSubscriber` redirects unenrolled users from `/learn/` and `/course/` paths; lesson drip-lock enforced in mobile API (HTTP 423) |
| Role hierarchy | as-built | 7 roles: anonymous, authenticated, student, instructor, manager, content_editor, administrator |
| Mobile API auth enforcement | as-built | In-controller `getAuthenticatedUser()` check; throws `AccessDeniedHttpException` on invalid/expired token |

### Webhook Security

| Gateway | Webhook Route | Signature Verification | Status |
|---|---|---|---|
| Stripe | None defined | `// TODO: Implement webhook verification.` | **critical gap** |
| PayPal | None defined | No-op 200 response | **stub / gap** |
| ZaloPay | None defined | Comment only ("Validate HMAC signature") | **stub / gap** |
| Bank Transfer | N/A | N/A (manual flow) | as-built |

No webhook endpoint is reachable for any gateway. Async payment events (3DS completion, subscription renewals, chargebacks) cannot be processed.

---

## Secret Management — CRITICAL OPEN GAP

**This is the highest-severity security risk in the EverLMS system.**

All third-party API credentials are stored as **plaintext in `config/sync/*.yml`** which is tracked in git. The Key module (`key`) is a declared dependency of `lms_ai` (`key:key` in `lms_ai.info.yml`) but **no `key.key.*.yml` files exist in `config/sync/`** — the module is required but not used for any secrets.

| Secret | Config file | Risk |
|---|---|---|
| OpenAI API key (`sk-proj-*`) | `lms_ai.settings.yml` | CRITICAL — key usable for unauthorized AI API calls |
| Gemini API key (`AIzaSy*`) | `lms_ai.settings.yml` | CRITICAL |
| Google service-account RSA private key (PEM) | `google_api_client.google_api_service_client.everlms_bot.yml` | CRITICAL — full access to Drive and Slides |
| Stripe publishable key (`pk_test_*`) | `lms_payment.settings.yml` | HIGH — test mode; must not go to production as-is |
| Stripe secret key (`sk_test_*`) | `lms_payment.settings.yml` | CRITICAL — enables server-side Stripe operations |
| PayPal client ID + secret | `lms_payment.settings.yml` | HIGH |
| Zoom account_id, client_id, client_secret, sdk_key, sdk_secret | `lms_zoom.settings.yml` | HIGH |
| Flutter `.env` (test Stripe + PayPal keys, Shield credentials, OneSignal app ID) | `app/.env` (committed) | HIGH |

Values are always redacted in this documentation. Never copy credentials from these files.

**Remediation path (target-state / planned):**

1. Enable Key module; create `key.key.*.yml` entries pointing to environment variables or a secrets backend (e.g., HashiCorp Vault, AWS SSM Parameter Store).
2. Update each module's settings form to reference a Key entity instead of storing raw values.
3. Rotate all exposed credentials immediately.
4. Add `app/.env` to `.gitignore`; use CI/CD secrets injection.
5. Remove `curriculum_suggestion` (~758 KB cached AI response) from `lms_ai.engine.settings.yml` — should not be stored in config YAML.

---

## File Security

| Control | Status | Detail |
|---|---|---|
| Insecure uploads blocked | as-built | `system.file.yml`: `allow_insecure_uploads: false` |
| PDF certificates | as-built | Stored at `public://certificates/issued/` — publicly accessible by URL; no access restriction |
| Payment receipts | as-built | Stored at `public://payment_receipts/YYYY-MM/` — publicly accessible by URL; no access restriction |
| Private file system | gap | Not configured; all uploads are in `public://` |

Certificate PDFs and payment receipts should be served via a private file system or signed URLs to prevent unauthorized access.

---

## Data Protection

| Control | Status | Detail |
|---|---|---|
| HTTPS enforcement | target-state / planned | DDEV provides HTTPS locally; production nginx/TLS config is outside repo scope |
| Error display to users | as-built | `system.logging.yml`: `error_level: hide` |
| Password policy | partial | `password_policy` module installed; `field_last_password_reset`, `field_password_expiration` fields exist; no active policy rules defined |
| GDPR / data export | target-state / planned | No data export or right-to-deletion implementation found |
| Audit logging | partial | Drupal `dblog` (`watchdog` view at `/admin/reports/dblog`); no application-level audit trail for admin entity changes |

---

## Performance — as-built Configuration

### Asset Delivery

| Control | Status | Detail |
|---|---|---|
| CSS aggregation + gzip | as-built | `system.performance.yml`: `css.preprocess: true`, `css.gzip: true` |
| JS aggregation + gzip | as-built | `js.preprocess: true`, `js.gzip: true` |
| Fast 404 | as-built | Enabled for static asset extensions |
| Image format | as-built | 4 image styles, all converting to WebP via `image_convert_avif` (output is `.webp`) |
| Bootstrap 5.3.3 via CDN | as-built | Loaded from `cdn.jsdelivr.net`; Google Fonts / Font Awesome also CDN; creates 3rd-party CDN dependency |

### Caching

| Control | Status | Detail |
|---|---|---|
| Page cache max age | gap (dev config) | `system.performance.yml`: `cache.page.max_age: 0` — **disabled**; must be set for production |
| Dynamic Page Cache | target-state | Module available in Drupal 11 core; not confirmed active from config |
| Render cache (tags + contexts) | as-built | Used by `lms_wishlist` (`lms_wishlist:{uid}` cache tag); other modules rely on Drupal entity cache defaults |
| Object cache backend (Redis / Memcache) | target-state / planned | Not configured in current DDEV setup |
| Views result caching | as-built | 15 Views defined; 14 use `cache: type: tag` (tag-based cache invalidation), 1 (`watchdog`) uses `cache: type: none` — confirmed in all `config/sync/views.view.*.yml` files |

### Known Performance Issues

| Issue | Severity | Detail |
|---|---|---|
| Stripe PaymentIntent created on `buildForm()` | High | Every page load of checkout creates a Stripe PaymentIntent, not just on submit — causes orphaned intents and unnecessary API calls |
| Raw SQL in `lms_analytics` | Medium | Direct DB queries without Views caching; acceptable for admin-only report pages |
| `AiEngineUserForm` (~2361 lines) | Medium | Large admin wizard with multiple AJAX calls; not a user-facing performance concern |
| No CDN for user-uploaded files | Medium | Course thumbnails, certificates, lesson attachments served from `public://` without CDN |

### Performance Targets (target-state)

| Metric | Target | Current Status |
|---|---|---|
| Time to First Byte (TTFB) | < 200 ms | Not measured; page cache disabled |
| Largest Contentful Paint (LCP) | < 2.5 s | Not measured |
| First Input Delay (FID) | < 100 ms | Not measured |
| Cumulative Layout Shift (CLS) | < 0.1 | Not measured |
| API response (`/api/v1/*`) | < 500 ms | Not measured |
| Page load (cached) | < 1 s | Cache disabled in dev |

---

## In scope (as-built)

- Drupal core security controls (CSRF, XSS, SQL injection prevention, session management, password hashing).
- Route-level and entity-level permission and access control.
- In-controller mobile API authentication with CSPRNG tokens and OTP.
- CSS/JS aggregation and gzip.
- WebP image conversion for all 4 image styles.
- Fast 404 for static assets.
- Error display hidden from users.

## Out of scope

- Key module secret management — not implemented (critical gap).
- Webhook signature verification for any payment gateway — not implemented.
- Private file system — not configured.
- Page cache for authenticated users — disabled (dev configuration).
- Redis / Memcache object cache — not configured.
- GDPR data export or right-to-deletion — not implemented.
- HTTPS enforcement (production nginx/TLS) — outside repo; DDEV provides local HTTPS.
- WAF (Web Application Firewall) — infrastructure-level; outside Drupal scope.
- Rate limiting on `/api/v1/*` endpoints — not implemented.

## Acceptance summary

An administrator verifies that all third-party credentials are stored via the Key module (not in config YAML), that Stripe webhook signature verification is implemented, and that the checkout page does not create a Stripe PaymentIntent until form submission. Full Given/When/Then criteria live in `../B05-acceptance-criteria/F-012.md`.

## Content ownership / editability

| Visible content family | Business owner | Editor role | Expected edit surface | Notes |
|---|---|---|---|---|
| API credentials (post-Key-module migration) | Platform admin | administrator | Key module admin UI | Must never appear in config export |
| Security policy documentation | Platform admin | administrator | CMS page or external docs | Outside EverLMS application scope |

## Dependencies (requirement-level only)

| Type | Reference | Reason |
|---|---|---|
| Feature | F-010 (Integrations) | Payment gateway webhook gaps are a subset of this feature |
| Feature | F-011 (Mobile App) | Mobile API auth and CORS gaps are security requirements |
| Feature | F-004 (Order & Membership) | Stripe PaymentIntent bug affects checkout security |
| Workflow | WF-004 (Order Management) | Webhook-less payment confirmation creates fulfillment risk |

## Open questions

- [ ] **CRITICAL:** When will Key module be wired up and all committed secrets rotated? This must happen before any public or production deployment.
- [ ] `app/.env` containing test Stripe/PayPal keys is committed to git — must be added to `.gitignore` and secrets injected via CI/CD; confirm timeline.
- [ ] Stripe PaymentIntent on `buildForm()` — fix to lazy/AJAX creation is required; who owns this bug?
- [ ] Webhook endpoints for Stripe/PayPal/ZaloPay — required before async payment flows (subscriptions, 3DS) can be supported; confirm priority.
- [ ] Private file system for certificates and payment receipts — should issued PDFs be publicly guessable by URL?
- [ ] Page cache `max_age` must be set for production — what is the target value (e.g., 900 s for authenticated, 3600 s for anonymous)?
- [ ] Mobile API rate limiting — `/api/v1/user/register` and `/api/v1/oauth/token` have no brute-force protection; add Drupal flood control or nginx rate limiting.
