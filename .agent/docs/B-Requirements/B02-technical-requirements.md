# B02 — Technical Requirements

> Tech stack, NFRs, environment setup, security requirements, and contrib module inventory for EverLMS.
> Layer authority: B-Requirements. Grounded in `setup-infra.md`, `config-inventory.md`, `themes-app.md` (facts base 2026-06-20).
> Status as of: 2026-06-20.

---

## Tech Stack

### Core Platform

| Component | Choice | Version (as-built) | Rationale | Alternative Considered |
|-----------|--------|-------------------|-----------|----------------------|
| CMS / Application Framework | Drupal | **11.3.5** | Entity API, Plugin system, config management, mature ecosystem; community LMS precedent | Laravel (rejected: no content modelling); WordPress (rejected: plugin quality variance) |
| Language | PHP | **8.3** (DDEV-enforced) | Strict types, named arguments, enums, attributes; required by Drupal 11 | PHP 8.2 (rejected: Drupal 11 requires 8.3+) |
| Database | MariaDB | **10.11** (InnoDB, utf8mb4_general_ci) | Native Drupal support; proven relational store; db: `everlms_pro` | PostgreSQL (not tested with Drupal contrib ecosystem) |
| Web Server | nginx-fpm | DDEV default (nginx-fpm) | Standard PHP-FPM setup; DDEV-managed nginx config | Apache (viable; not used) |
| Template Engine | Twig | **3.22.2** (locked) | Drupal core; auto-escaping, sand-boxed | Blade (incompatible with Drupal) |
| Symfony components | Symfony | **7.4.x** (locked) | Drupal 11 dependency; HTTP Foundation, EventDispatcher, DI container | — |
| CSS Framework | Bootstrap | **5.3.3** (CDN — jsdelivr) | Responsive grid, dark mode utilities, component library; both themes depend on it | Tailwind (evaluated; rejected for Twig SSR workflow) |
| CSS Build Tool | Dart Sass | **^1.77.0** (`node_modules/` present in `lms_theme`) | SCSS variables/mixins for 15-skin theme system | node-sass (deprecated) |
| Rich Text Editor | CKEditor | **5** (Drupal core) | WYSIWYG for lesson content; `basic_html` + `full_html` formats configured | TinyMCE |
| Local Dev Environment | DDEV | Latest stable (`composer_version: 2`) | Docker-based; standardized PHP/DB versions; Mailpit included | Lando, bare Docker Compose |
| PHP Package Manager | Composer | **2.x** | Standard; `minimum-stability: stable`; no patches plugin | — |
| CLI Tool | Drush | **13.7.2** | Config import, db updates, php-script runner; 4 maintenance scripts depend on it | — |

### Key Libraries (non-Drupal)

| Library | Version (locked) | Purpose |
|---------|-----------------|---------|
| `mpdf/mpdf` | **8.3.1** | PDF certificate generation (invoked by `lms_certificate` event subscriber on `lms_quiz.passed`) |
| `stripe/stripe-php` | **19.4.1** | Stripe PaymentIntent creation in `lms_stripe` (`StripeGateway`); test keys currently |
| `firebase/php-jwt` | **7.0.4** | JWT utilities; present as dependency (mobile app uses custom bearer token via keyvalue.expirable + OTP, not OAuth2/JWT) |
| `google/apiclient` | **2.19.1** | Google Slides/Drive API for AI course builder (`lms_ai`); service account config present |
| `shuchkin/simplexlsxgen` | **1.5.15** | Excel export for analytics/reports |
| `guzzlehttp/guzzle` | **7.10.0** | HTTP client (Drupal core + contrib dependency) |
| `phpmailer/phpmailer` | **6.12.0** | Email (via SMTP module); SMTP disabled in config — falls back to `sendmail` |

### Mobile App (Flutter)

| Component | Choice | Version | Notes |
|-----------|--------|---------|-------|
| Mobile framework | Flutter | SDK `>=3.0.1 <4.0.0` | Android + iOS; app v1.0.10+33 (build 33) |
| App package | `com.weebpal.everlms` | — | White-label ready (domain switchable at runtime) |
| State management | Riverpod | **^2.6.1** | GetX fully migrated away |
| Navigation | GoRouter | **^14.6.2** | Declarative routing; 33 screens |
| HTTP client | Dio | **^5.7.0** | `ApiClient` wrapper; `X-Authorization` bearer token header |
| Auth strategy | Custom bearer token | — | keyvalue.expirable + OTP on backend; NOT OAuth2/JWT despite `oauth_*` config keys in repo |
| Push notifications | Firebase Cloud Messaging | `firebase_messaging: ^16.0.3` | + APNs key (`APN_AuthKey_49353B5A9S.p8`) for iOS |
| Video | better_player_plus, youtube_player_flutter, vimeo_video_player | Various | HLS/DASH + YouTube + Vimeo |
| Payment (mobile) | flutter_stripe + paypal_payment | `^10.2.0` / `^1.0.0+4` | Test credentials in `.env` (committed — see security risk) |
| Social auth (mobile) | Google, Facebook, Apple, Microsoft MSAL | Various | 4 providers wired; backend `lms-social-auth` endpoint |
| Offline | flutter_downloader + local storage | — | Downloaded content; `CourseDetailsOfflineScreen` |
| i18n | ARB-based flutter_localizations | en + vi | `app/lib/l10n/`; Vietnamese via `convert_vietnamese: ^0.0.9` |
| Environment | flutter_dotenv | `^5.0.2` | `.env` loaded at runtime; `.env` file committed with test keys (security risk) |

---

## Contrib Modules (18 modules, as-built)

All installed via Composer; sources from `https://packages.drupal.org/8`.

| Module | Locked Version | Purpose |
|--------|---------------|---------|
| `admin_toolbar` | 3.6.3 | Improved admin navigation; dropdown menus |
| `ajax_comments` | 1.0.0-beta7 (alpha constraint) | AJAX comment submission for lesson/forum pages |
| `google_api_client` | 4.5.0 | Google Slides/Drive API config entity (`everlms_bot` service account) |
| `h5p` | 2.0.0-beta1 (alpha constraint) | Interactive H5P content in lessons |
| `ief_popup` | 2.2.2 | Inline entity form popup variant |
| `inline_entity_form` | 3.0.0-rc21 (RC constraint) | Inline entity creation/editing within parent forms |
| `key` | 1.22.0 | Secret key management (installed but **zero key entities configured** — secrets are in plaintext config YAML; this is the secrets-management gap) |
| `md_viewer` | 2.0.1 | Markdown file rendering |
| `metatag` | 2.2.0 | SEO meta tags; defaults configured for node/taxonomy/user/403/404 |
| `password_policy` | 4.0.3 | Password complexity enforcement; cron threshold: 250 |
| `pathauto` | 1.14.0 | URL alias generation; blog pattern `/blog/[node:title]` configured |
| `pdf` | 1.3.0 | PDF file field rendering |
| `smtp` | 1.4.0 | SMTP mail transport; **disabled** in config — falls back to `sendmail` / Mailpit in dev |
| `social_api` | 4.0.2 | Social authentication framework |
| `social_auth` | 4.1.2 | Social auth provider integration |
| `social_auth_facebook` | 4.0.4 | Facebook OAuth |
| `social_auth_google` | 4.0.3 | Google OAuth |
| `token` | 1.17.0 | Token API for pathauto and metatag patterns |

Indirect contrib (pulled by dependencies, not directly required):
- `drupal/ctools: 4.1.0`, `drupal/rat: 1.0.1`

Dev-only:
- `drupal/devel_entity_updates: ^5.0`

---

## Non-Functional Requirements

| NFR ID | Category | Requirement | Target | Priority | Measurement | Status |
|--------|----------|-------------|--------|----------|------------|--------|
| NFR-001 | Performance | Homepage load time (cold cache, p95) | < 2 s | Must | Browser DevTools / Lighthouse; load test | Target — page cache disabled (`max_age: 0`); must enable for production |
| NFR-002 | Performance | Drupal page response (server-side, p95) | < 500 ms | Must | Load test (wrk / k6) | Target — no cache/Redis active |
| NFR-003 | Performance | Mobile API response (`/api/v1/`, p95) | < 500 ms | Must | Dio interceptor timing; APM | Target |
| NFR-004 | Performance | Course catalog page load (with filters) | < 3 s | Should | Load test | Target |
| NFR-005 | Performance | Certificate PDF generation (mPDF, per cert) | < 5 s | Should | Timed integration test | Target |
| NFR-006 | Availability | System uptime (production) | >= 99.5% | Must | Uptime monitoring (tool TBD for production) | Target — no production hosting configured yet |
| NFR-007 | Security | Secrets management | All third-party credentials stored in Key module or environment variables; zero plaintext secrets in VCS | Must (production blocker) | Config audit: `key.key.*` present + no API keys in config YAML | Gap — currently ALL secrets (OpenAI, Gemini, Google RSA key, Stripe, PayPal, Zoom) are in committed config YAML; `.env` with Stripe/PayPal test keys also committed |
| NFR-008 | Security | Payment webhook signature verification | Stripe webhook signature verified on every callback | Must | Integration test of webhook handler | Gap — no webhook endpoints exist; `WebhookController` is a TODO |
| NFR-009 | Security | Authentication (web) | Drupal session-based + CSRF token on all state-changing requests | Must | Security audit | As-built |
| NFR-010 | Security | Authentication (mobile) | Bearer token via keyvalue.expirable + OTP; sent as `X-Authorization` header | Must | API integration test | As-built |
| NFR-011 | Security | Authorization | RBAC: 7 roles (anonymous, authenticated, student, instructor, manager, content_editor, administrator); custom access hooks in `lms_*` modules | Must | Permission matrix audit | As-built |
| NFR-012 | Security | Input validation | Schema validation on all form inputs; Drupal Form API + Entity API validation; JSON API endpoints validated | Must | Security audit / OWASP review | As-built (Drupal framework provides baseline) |
| NFR-013 | Security | Transport encryption | TLS 1.2+ for all HTTP traffic | Must | SSL scan (ssllabs.com) | Target — DDEV provides HTTPS locally; production TLS depends on hosting config |
| NFR-014 | Security | Stripe PaymentIntent creation timing | PaymentIntent must NOT be created on form BUILD (current bug: one PI per page load) | Must | Code fix + integration test | Bug — currently created on `buildForm()`, not on submit |
| NFR-015 | Maintainability | Module boundaries | 27 `lms_*` modules + `open_switcher`; each module owns its entity/service/route namespace | Must | Architecture review; no circular dependencies | As-built (enforced by Drupal module structure) |
| NFR-016 | Maintainability | Coding standards | Drupal + DrupalPractice (PHPCS) | Must | `phpcs --standard=Drupal` gate | Target — no CI pipeline configured |
| NFR-017 | Maintainability | Config sync cleanliness | `drush config:status` returns no diffs before release | Must | Pre-release checklist | As-built practice |
| NFR-018 | Reliability | Progress update atomicity | Lesson completion updates wrapped in DB transaction; no partial progress states | Must | Concurrency / integration test | Target |
| NFR-019 | Reliability | AI generation rollback | Failed `CourseBuilder` queue jobs must not leave partial courses | Must | Queue worker test | Gap — no rollback/compensation implemented in `lms_ai` |
| NFR-020 | Accessibility | WCAG 2.1 AA baseline | Bootstrap 5 structural compliance; semantic HTML in Twig templates | Should | Automated a11y scan (axe-core) | Target — no formal audit done |
| NFR-021 | Internationalization | Backend locale | English (`en`) primary; `Asia/Bangkok` timezone default; user-configurable timezone | Should | Config review | As-built |
| NFR-022 | Internationalization | Mobile locale | English + Vietnamese (ARB l10n); language sent as `X-localization` header | Should | l10n file completeness audit | As-built |
| NFR-023 | Observability | Drupal watchdog | All auth, payment, grading, certificate, and AI events logged to `watchdog` | Must | Log review in `/admin/reports/dblog` | As-built (Drupal core) |
| NFR-024 | Observability | Structured APM | Application Performance Monitoring for production | Should | APM dashboard (tool TBD) | Target — not yet deployed |
| NFR-025 | Scalability | Concurrent learners | Support >= 500 simultaneous active learners without degradation | Should | Stress test | Target — no load test baseline yet |

---

## Environment Requirements

| Environment | Purpose | Infrastructure | Access Control | Notes |
|-------------|---------|---------------|----------------|-------|
| Local | Development | DDEV (Docker: nginx-fpm, PHP 8.3, MariaDB 10.11); URL: `https://everlms.ddev.site` | Developer (SH-001) | Mailpit on port 8025; xdebug disabled; no env vars injected via DDEV web_environment |
| CI | Automated testing | Not yet configured | Automated | No `phpunit.xml` present; no GitHub Actions / GitLab CI configured |
| Staging | Pre-production validation | Not yet configured | Team | Should mirror production infra |
| Production | Live tenant site | Not yet configured | Restricted | Requires: secrets migration to Key module or env vars; TLS; page cache enabled; Redis for cache backends |

### Local Setup Sequence (as-built)

```bash
# 1. Start DDEV (generates settings.ddev.php automatically)
ddev start

# 2. Install PHP dependencies
ddev composer install

# 3a. Import existing DB dump (fastest)
ddev import-db --file=database/db.sql   # or db.sql.gz (git-tracked)
ddev drush updatedb
ddev drush cache:rebuild

# 3b. OR fresh install from config
ddev drush site:install --existing-config -y
ddev drush config:import -y
ddev drush cache:rebuild

# 4. Optional: apply data-fix scripts if seed data is corrupted
ddev drush php-script scripts/fix_all_categories.php
ddev drush php-script scripts/regenerate_all_certs.php
```

Config sync directory: `config/sync/` (repo root, 396 YAML files, git-tracked).

---

## Security Requirements

- **Authentication (web):** Drupal session-based authentication + CSRF token (`X-CSRF-Token`) on all state-changing requests. Social auth via `social_auth` + Google/Facebook OAuth providers.
- **Authentication (mobile):** Custom bearer token issued by `lms_mobile_api` using `keyvalue.expirable` store + OTP verification. Sent as `X-Authorization: Bearer {token}` to avoid conflict with HTTP Basic Auth (Drupal Shield). OAuth2 password grant endpoint (`oauth/token`) also present but is a secondary path.
- **Authorization:** RBAC with 7 roles. `administrator` bypasses all checks (Drupal convention). `manager` has `switch users` permission via `open_switcher`. LMS entity access (course view, lesson progress, quiz submission) is gated by custom access hooks in `lms_*` modules, not standard Drupal permissions.
- **Input Validation:** Drupal Form API validation on all forms; Entity API constraint validation on all custom entities; JSON input validation on all `/api/v1/` endpoints in `lms_mobile_api`.
- **Output Encoding:** Twig auto-escaping on all rendered output; JSON serialization for API responses.
- **Audit Logging:** Drupal watchdog captures authentication events, payment transactions, certificate issuance, AI generation jobs, and grading events. Accessible at `/admin/reports/dblog`.
- **Secret Management (REQUIRED REMEDIATION):** The `key` module (v1.22.0) is installed but **zero key entities are configured**. All production secrets — OpenAI API key, Gemini API key, Google service account RSA private key, Stripe test keys, PayPal credentials, Zoom credentials — are stored **in plaintext in committed `config/sync/*.yml` files**. The mobile app `.env` file with Stripe/PayPal test keys is also committed to the repo. **This is a production blocker.** Remediation: configure `key` module entities for each secret and reference them from module config; add `config/sync/key.key.*.yml` and secret values to `.gitignore`; rotate all exposed keys before production launch.
- **Dependency Scanning:** `composer audit` should run pre-release; Drupal security advisory feed for contrib modules.
- **Payment Security:** Stripe operates in test mode (pk_test_ / sk_test_ keys). No webhook endpoint exists — Stripe webhook signature verification must be implemented before production. PaymentIntent is currently created on form BUILD (bug: one PI per page load) — must be moved to form SUBMIT.
- **OWASP Top 10:** Address all categories in adversarial reviews (planned, E-Quality layer — not yet materialized).

---

## Known Security Gaps (as-built, 2026-06-20)

These are documented here as requirements that must be resolved before production. Canonical tracker: `G02-risk-register.md` (planned, layer G — not yet materialized).

| Gap ID | Description | Severity | Required Remediation |
|--------|-------------|----------|---------------------|
| SEC-001 | OpenAI + Gemini API keys in `lms_ai.settings.yml` (committed config YAML) | Critical | Move to `key` module; rotate keys |
| SEC-002 | Google service account RSA private key in `google_api_client.google_api_service_client.everlms_bot.yml` | Critical | Move to `key` module or env var; rotate key |
| SEC-003 | Stripe + PayPal credentials in `lms_payment.settings.yml` (Stripe in TEST mode) | High | Move to `key` module; switch to live keys in production |
| SEC-004 | Zoom `account_id`, `client_id`, `client_secret`, `sdk_key`, `sdk_secret` in `lms_zoom.settings.yml` | High | Move to `key` module |
| SEC-005 | Mobile app `.env` file with test Stripe/PayPal keys committed to repo | High | Add to `.gitignore`; use CI secrets injection |
| SEC-006 | No Stripe webhook endpoint or signature verification | High | Implement `WebhookController`; verify `Stripe-Signature` header |
| SEC-007 | Stripe PaymentIntent created on form BUILD (one PI per page load) | High | Move PI creation to form SUBMIT handler |
| SEC-008 | `lms_paypal` module is dead code (wrong interface, no `@PaymentGateway` annotation) | Medium | Remove or fix to prevent misleading payment routing |
