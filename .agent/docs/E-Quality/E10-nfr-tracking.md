# E10 — NFR Tracking

**Layer:** E-Quality
**Purpose:** Non-functional requirement register for EverLMS — each NFR from [B02-technical-requirements.md](../B-Requirements/B02-technical-requirements.md) is tracked here with current status, measured actuals, and gap analysis.
**Last updated:** 2026-06-20
**Authority source:** [B02-technical-requirements.md](../B-Requirements/B02-technical-requirements.md) §"Non-Functional Requirements"

**Important note on current state:** Most NFRs have NOT been measured. There is no CI pipeline, no load test baseline, and no automated security scan configured as of 2026-06-20. The status column reflects honest as-built reality; "PENDING" = target not yet measured; "GAP" = known breach with no remediation in place.

---

## NFR Registry

| NFR ID | Category | Requirement | Target | Actual | Status | Verified Date | Method | Notes |
|---|---|---|---|---|---|---|---|---|
| NFR-001 | Performance | Homepage load time (cold cache, p95) | < 2 s | — | PENDING | — | Lighthouse / k6 | Page cache disabled (`max_age: 0`) in current config — must enable before meaningful test |
| NFR-002 | Performance | Drupal page server response (p95) | < 500 ms | — | PENDING | — | k6 load test | No Redis; no page cache active |
| NFR-003 | Performance | Mobile API `/api/v1/*` response (p95) | < 500 ms | — | PENDING | — | k6 / Dio interceptor timing | Bearer token auth adds one extra `keyvalue.expirable` lookup per request |
| NFR-004 | Performance | Course catalog page load (with filters, p95) | < 3 s | — | PENDING | — | k6 load test | N+1 query risk in catalog entity list |
| NFR-005 | Performance | Certificate PDF generation (mPDF, per cert) | < 5 s | — | PENDING | — | Timed integration test | mPDF 8.3.1; blocking on request thread |
| NFR-006 | Availability | System uptime (production) | >= 99.5% | — | PENDING | — | Uptime monitor (tool TBD) | No production hosting configured yet; DDEV is local-only |
| NFR-007 | Security | Secrets management — zero plaintext secrets in VCS | All credentials in Key module or env vars | BREACHED | GAP | 2026-06-20 | Config audit | OpenAI key, Gemini key, Google RSA private key, Stripe test keys, PayPal creds, Zoom secrets — ALL in committed `config/sync/*.yml`; Key module installed (`drupal/key: 1.22.0`) but zero `key.key.*` config objects present; `.env` with test Stripe/PayPal keys is committed. This is a production blocker. See [hardening-security](../D-Development/D03-stories/hardening-security.md) story. |
| NFR-008 | Security | Stripe webhook signature verification on every callback | Verified on every callback | BREACHED | GAP | 2026-06-20 | Code review | No webhook endpoints exist anywhere. `WebhookController` is TODO. No signature verification implemented. See [payment-webhooks](../D-Development/D03-stories/payment-webhooks.md) story. |
| NFR-009 | Security | Web authentication | Drupal session-based + CSRF token on all state-changing requests | Met (Drupal core) | OK | 2026-06-20 | Architecture review | Drupal Form API provides CSRF by default; must audit for `'#token' => FALSE` bypasses (Story [hardening-security](../D-Development/D03-stories/hardening-security.md)) |
| NFR-010 | Security | Mobile authentication | Bearer token via `keyvalue.expirable` + OTP; sent as `X-Authorization` header | As-built | OK | 2026-06-20 | Code review | Custom token, NOT OAuth2/JWT despite `oauth_*` config key names in config |
| NFR-011 | Security | Authorization (RBAC) | 7 roles; custom access hooks in `lms_*` modules | As-built (partial) | IN_PROGRESS | 2026-06-20 | Config + code review | 7 roles present in config. `student` and `instructor` have thin Drupal permissions; LMS-specific access in module code. Audit of all 137 routes not yet done. Story [hardening-security](../D-Development/D03-stories/hardening-security.md) |
| NFR-012 | Security | Input validation — all form inputs | Schema validation; Form API + Entity API; JSON API endpoints | Partial | IN_PROGRESS | 2026-06-20 | Code review | Drupal framework baseline active. No `ValidationService` in `lms_base`. No structured error format on custom controllers. Story [hardening-security](../D-Development/D03-stories/hardening-security.md) |
| NFR-013 | Security | Transport encryption | TLS 1.2+ on all HTTP traffic | Local (DDEV HTTPS) | PENDING | — | SSL scan (ssllabs.com) | DDEV provides HTTPS locally. Production TLS depends on hosting config (not yet configured). |
| NFR-014 | Security | Stripe `PaymentIntent` creation timing | Created on form SUBMIT, not form BUILD | BREACHED | GAP | 2026-06-20 | Code review | `PaymentIntent` currently created in `buildForm()` — one PI created per page load. Must move to `submitForm()`. Story [lms_stripe](../D-Development/D03-stories/lms_stripe.md) |
| NFR-015 | Maintainability | Module boundaries | 27 `lms_*` + `open_switcher`; each module owns its namespace; no circular deps | As-built | OK | 2026-06-20 | Architecture review | 28 modules confirmed at `web/modules/custom/`; namespace isolation enforced by Drupal module structure |
| NFR-016 | Maintainability | Coding standards (PHPCS) | Drupal + DrupalPractice clean | Target | PENDING | — | `phpcs --standard=Drupal,DrupalPractice web/modules/custom/` | No CI pipeline; PHPCS available via `vendor/bin/phpcs`; run not yet enforced |
| NFR-017 | Maintainability | Config sync cleanliness | `drush config:status` returns no diffs before release | As-built practice | OK | 2026-06-20 | Pre-release checklist | 396 YAML files tracked in `config/sync/` |
| NFR-018 | Maintainability | Automated test coverage | >= 60% MVP → 80% stable on custom modules | 0% (no tests exist) | GAP | 2026-06-20 | PHPUnit coverage | Zero test files in any `lms_*/tests/` directory. Story [quality-test-suite](../D-Development/D03-stories/quality-test-suite.md) |
| NFR-019 | Reliability | Progress update atomicity | Lesson completion updates wrapped in DB transaction; no partial progress states | Not implemented | GAP | 2026-06-20 | Code review | `ProgressCascadeService` has no DB transaction wrapping. Race condition possible under concurrent requests. Story [progress-race-condition](../D-Development/D03-stories/progress-race-condition.md) |
| NFR-020 | Reliability | AI generation rollback | Failed `CourseBuilder` queue jobs must not leave partial courses | Not implemented | GAP | 2026-06-20 | Code review | No rollback/compensation on partial failure in `lms_ai`. Story [ai-rollback-compensation](../D-Development/D03-stories/ai-rollback-compensation.md) |
| NFR-021 | Reliability | Bootstrap CDN dependency | Bootstrap 5.3.3 loaded via CDN; CDN unavailability breaks frontend rendering | External dependency | RISK | 2026-06-20 | Architecture review | `lms_theme` loads Bootstrap 5.3.3 from CDN. No local fallback. CDN outage = broken UI. Story [cache-performance](../D-Development/D03-stories/cache-performance.md) (local asset bundling) |
| NFR-022 | Accessibility | WCAG 2.1 AA baseline | Bootstrap 5 structural compliance; semantic HTML in Twig | Target | PENDING | — | Playwright + axe-core | No formal audit done. Bootstrap 5.3.3 provides structural baseline. Story [quality-test-suite](../D-Development/D03-stories/quality-test-suite.md) |
| NFR-023 | Internationalization | Backend locale | English primary; `Asia/Bangkok` timezone default; user-configurable timezone | As-built | OK | 2026-06-20 | Config review | `system.date.yml`: timezone = `Asia/Bangkok`, user-configurable = true, langcode = `en` |
| NFR-024 | Internationalization | Mobile locale | English + Vietnamese (ARB l10n); `X-localization` header | As-built | OK | 2026-06-20 | l10n file review | Flutter app `app/lib/l10n/`; `convert_vietnamese: ^0.0.9`; mobile API reads `X-localization` |
| NFR-025 | Observability | Drupal watchdog logging | Auth, payment, grading, certificate, AI events logged to `watchdog` | Partial | IN_PROGRESS | 2026-06-20 | Log review in `/admin/reports/dblog` | Drupal core provides watchdog. `lms_*` modules do not have a consistent `LoggingService`; critical events may be missing. Story [hardening-security](../D-Development/D03-stories/hardening-security.md) |
| NFR-026 | Observability | Structured APM (production) | APM dashboard for production performance monitoring | Target | PENDING | — | APM tool (TBD) | Not deployed; no production hosting yet |
| NFR-027 | Scalability | Concurrent active learners | >= 500 simultaneous learners without degradation | Target | PENDING | — | k6 stress test | No load test baseline. No Redis/caching layer active. |

---

## Verification Status Summary

| Category | Total | Verified (OK) | Failing / GAP | In Progress | Pending (unmeasured) |
|---|---|---|---|---|---|
| Performance | 5 | 0 | 0 | 0 | 5 |
| Availability | 1 | 0 | 0 | 0 | 1 |
| Security | 8 | 2 | 3 | 2 | 1 |
| Maintainability | 4 | 2 | 1 | 0 | 1 |
| Reliability | 3 | 0 | 2 | 0 | 0 (RISK: 1) |
| Accessibility | 1 | 0 | 0 | 0 | 1 |
| Internationalization | 2 | 2 | 0 | 0 | 0 |
| Observability | 2 | 0 | 0 | 1 | 1 |
| Scalability | 1 | 0 | 0 | 0 | 1 |
| **Total** | **27** | **6** | **6** | **4** | **10 + 1 RISK** |

---

## Failing / GAP NFRs (Must Fix Before CP5)

| NFR ID | Gap Description | Impact | Remediation Plan |
|---|---|---|---|
| NFR-007 | OpenAI key, Gemini key, Google RSA private key, Stripe test keys, PayPal creds, Zoom secrets stored in plaintext in committed `config/sync/*.yml`; `.env` with test keys also committed; Key module present but unused | **Production blocker** — credential exposure risk if repo is ever public or access is breached | Migrate all secrets to `key.key.*` config objects backed by environment variables or a secrets manager. Story [hardening-security](../D-Development/D03-stories/hardening-security.md). |
| NFR-008 | No webhook endpoints exist; `WebhookController` is a TODO; no Stripe webhook signature verification | Payment reconciliation impossible in production; replay attack risk | Implement `WebhookController` with Stripe signature verification via `stripe/stripe-php` SDK `Webhook::constructEvent()`. Story [payment-webhooks](../D-Development/D03-stories/payment-webhooks.md). |
| NFR-014 | Stripe `PaymentIntent` created in `buildForm()` → one `PaymentIntent` per page load (browser back button = duplicate PIs) | Stripe account accumulates orphan PIs; potential double-charge risk | Move `PaymentIntent` creation to `submitForm()` (after user confirmation). Story [lms_stripe](../D-Development/D03-stories/lms_stripe.md). |
| NFR-018 | Zero automated test files in any `lms_*/tests/`; 0% coverage | Cannot verify regressions; no quality gate enforced | Write kernel + functional + E2E tests. Setup CI pipeline. Story [quality-test-suite](../D-Development/D03-stories/quality-test-suite.md). |
| NFR-019 | `ProgressCascadeService` has no DB transaction around cascade update; concurrent lesson completions can produce inconsistent progress state | Data integrity risk for multi-device learners | Wrap cascade update in a DB transaction with `lockForUpdate()`. Story [progress-race-condition](../D-Development/D03-stories/progress-race-condition.md). |
| NFR-020 | `lms_ai` `CourseBuilder` queue worker has no rollback on partial failure; a failed mid-run leaves orphan chapters/lessons in DB | Pollutes course catalog; AI generation unreliable in production | Implement compensation (delete orphan entities on job failure) or saga pattern. Story [ai-rollback-compensation](../D-Development/D03-stories/ai-rollback-compensation.md). |

---

## Risk NFRs (Tracked, Not Production Blockers Yet)

| NFR ID | Risk Description | Impact | Mitigation |
|---|---|---|---|
| NFR-021 | Bootstrap 5.3.3 loaded via CDN (`lms_theme`); no local fallback | Full frontend failure if CDN is unreachable | Bundle Bootstrap locally in `lms_theme`; Story [cache-performance](../D-Development/D03-stories/cache-performance.md) |
| NFR-011 | `student` and `instructor` roles have minimal Drupal permissions; LMS-specific access is enforced by module-level code only — not yet systematically audited | Potential privilege escalation if access hooks contain gaps | Complete access control audit (all 137 routes); Story [hardening-security](../D-Development/D03-stories/hardening-security.md) |

---

## NFRs Confirmed OK (as-built)

| NFR ID | Evidence |
|---|---|
| NFR-009 | Drupal Form API provides CSRF by default; web session-based auth as-built |
| NFR-010 | Custom bearer token via `keyvalue.expirable` + OTP confirmed in `lms_mobile_api` code |
| NFR-015 | 28 modules at `web/modules/custom/`; Drupal enforces namespace isolation; no circular dependencies found |
| NFR-017 | 396 YAML tracked in `config/sync/`; `drush config:import -y` workflow is the standard release checklist step |
| NFR-023 | `system.date.yml`: timezone `Asia/Bangkok`, user-configurable, langcode `en` — confirmed in config export |
| NFR-024 | Flutter `app/lib/l10n/` with ARB files for `en` + `vi`; `convert_vietnamese: ^0.0.9` in pubspec |

---

## Additional Known NFR-Adjacent Issues (not in B02 registry, discovered during codebase review)

| Issue | Category | Description | Story / Tracker |
|---|---|---|---|
| `lms_user_certificate.field_expiry_date` stored as varchar not int | Data integrity | Schema mismatch — date field stored as string. Date arithmetic will fail. | E02-issues/ (planned) |
| `ForumController::viewTopic()` throws `"HARD DEBUG!"` exception | Reliability | Live debug exception in production code path. Any forum topic view crashes. | [hardening-security](../D-Development/D03-stories/hardening-security.md) |
| `lms_rating` missing 3 runtime routes | Reliability | Rating list/edit/delete routes not registered; returns 404 in production. | [hardening-security](../D-Development/D03-stories/hardening-security.md) |
| `lms_paypal` is dead code | Reliability | Wrong interface, no `@PaymentGateway` annotation, never discovered by plugin manager. Customers who select PayPal will silently fail. | [lms_paypal](../D-Development/D03-stories/lms_paypal.md) |
| `lms_badge` has zero award logic | Reliability | Badge entities exist but are never awarded. Feature is non-functional. | [lms_badge](../D-Development/D03-stories/lms_badge.md) |
| Zoom `generateSignature()` references wrong service ID | Reliability | Zoom Web SDK signature generation will fail at runtime for live class join. | [lms_zoom](../D-Development/D03-stories/lms_zoom.md) |
| `lms_notification` email + push channels are stubs | Reliability | Only in-app notifications work; email and push silently do nothing. | [lms_notification](../D-Development/D03-stories/lms_notification.md) |

---

**RULE:** All Must-priority NFRs must PASS before CP5 (pre-release quality checkpoint). GAP items above (NFR-007, NFR-008, NFR-014, NFR-018, NFR-019, NFR-020) are Must-priority and are production blockers.
