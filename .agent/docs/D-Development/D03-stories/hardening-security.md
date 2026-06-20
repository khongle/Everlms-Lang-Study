# D03 — Story Spec: Hardening & Security

**Story ID:** hardening-security
**Story type:** nfr
**Title:** Hardening & Security — input validation, access audit, CSRF, error handling, logging, DI cleanup
**Primary source:** `C-Design/C05-modules/lms_base.md`; G-HARDENING wave 1–3 (draft D02-group-specs/10-G-HARDENING.md)
**Owning module(s):** all lms_* modules (lms_base provides shared validators and exception classes)
**Service(s):** ValidationService, LoggingService, ExceptionSubscriber (lms_base)
**Epic:** E-10 (Quality & Hardening)
**Contributes to Feature(s):** F-012 (Security & Performance)
**Design source(s):** `C-Design/C05-modules/lms_base.md`, `C-Design/C05-modules/lms_payment.md`, `C-Design/C05-modules/lms_stripe.md`
**Round:** N/A
**Driver:** NFR gap; G02 risk register (RISK-001, RISK-002, RISK-004); security audit findings
**Status:** SPEC
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `story: hardening-security` — maps to draft TASK-015, TASK-016, TASK-018, TASK-019, TASK-024, TASK-025, TASK-020

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 4+
**Splitting strategy:**
- S1: Error handling standardization (LmsException hierarchy + ExceptionSubscriber) + ValidationService skeleton in lms_base
- S2: Input validation coverage — all write controllers in lms_course, lms_quiz, lms_order, lms_forum, lms_rating
- S3: Access control audit + CSRF audit (all routing.yml + forms)
- S4: DI cleanup (static \Drupal:: removal from all service classes) + entity access handler audit

Each session must end at G1-build pass.

## Scope (from primary source)

This Story hardens the entire EverLMS codebase against the highest-priority security and code-quality gaps identified in the risk register. It does NOT add features; it remediates existing code.

**Sub-scope areas:**

1. **Input validation** — add `ValidationService` in `lms_base`; apply Symfony Validator constraints to every POST/PUT/PATCH/DELETE controller across all lms_* modules. Invalid input returns HTTP 422 with structured `{"errors":[…]}`. XSS: HTML stripped on text fields. SQL injection: all queries already use entity API, but audit and document.

2. **Access control audit** — every route in all `*.routing.yml` files must have an explicit `_permission:`, `_role:`, or `_custom_access:` requirement. Ownership checks enforced for UserCourse, Order, Certificate, etc. Admin/manage routes require `manager` or `administrator` role. Produce an audit table in the session log.

3. **CSRF protection audit** — all Drupal Form API forms use built-in CSRF (verify not bypassed with `'#token' => FALSE`). Custom AJAX routes validated with `_csrf_token: TRUE` or manual token check. Webhook routes explicitly documented as CSRF-exempt (signature-based auth).

4. **Error handling standardization** — create `LmsException`, `ValidationException`, `AccessDeniedException`, `NotFoundException` hierarchy in `lms_base/src/Exception/`. `ExceptionSubscriber` catches and formats JSON error responses. Standard format: `{"error":{"code":"…","message":"…","fields":{…}}}`. No PHP stack traces leak to clients.

5. **Logging for critical operations** — `LoggingService` in `lms_base`: log payment gateway calls, order status changes, membership activate/expire, certificate issuance, webhook events, AI generation start/fail, access-denied events. No raw API keys or PII beyond `user_id` in log context.

6. **DI cleanup** — remove all `\Drupal::service()`, `\Drupal::entityTypeManager()`, `\Drupal::currentUser()`, `\Drupal::database()` static calls from every `src/Service/*.php` and `src/Plugin/**/*.php`. Replace with constructor injection; update `*.services.yml` `arguments:` lists.

**Real implementation status (as-built):**
- None of the above are implemented. The codebase has widespread `\Drupal::` static calls in service classes, no ValidationService, no standardized exception hierarchy, no LoggingService, and no systematic CSRF audit has been performed.
- `lms_forum::ForumController::viewTopic()` contains a live `"HARD DEBUG!"` exception throw (dead debug code — must be removed as part of this Story).
- Known live bugs feeding this Story: `lms_rating` has 3 missing runtime routes; these must be registered as part of the access control audit.

## Source Documents

| Source | Why loaded |
|---|---|
| `C-Design/C05-modules/lms_base.md` | Owning module design — ValidationService, LoggingService live here |
| `C-Design/C05-modules/lms_payment.md` | Payment gateway access patterns to harden |
| `C-Design/C05-modules/lms_stripe.md` | Stripe-specific access + error handling |
| `C-Design/C05-modules/lms_forum.md` | Known debug exception bug |
| `D00-conventions/` | PHP/Drupal coding standards to enforce during DI cleanup |
| `B02-technical-requirements.md` | NFR security requirements (TLS, input validation, CSRF) |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| All write endpoints must validate required fields and reject malformed input | B02-technical-requirements.md §Security | Non-Functional |
| No PHP exception stack trace exposed in any response | B02-technical-requirements.md §Security | Non-Functional |
| All state-changing forms must have CSRF protection active | B02-technical-requirements.md §Security | Non-Functional |
| All routes must have explicit access requirements | B02-technical-requirements.md §Security | Non-Functional |
| Service classes must use constructor injection | B02-technical-requirements.md §Code Quality | Non-Functional |
| Critical operations must produce watchdog log entries | B02-technical-requirements.md §Observability | Non-Functional |
| `\Drupal::` in service classes → DI (PHPCS Drupal standard) | D00-conventions/ | Non-Functional |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_base/src/Service/ValidationService.php` | Create | Symfony Validator wrapper; returns structured error arrays | TASK-015 | — |
| `web/modules/custom/lms_base/src/Service/LoggingService.php` | Create | Structured watchdog logger for critical events | TASK-020 | — |
| `web/modules/custom/lms_base/src/Exception/LmsException.php` | Create | Base exception class | TASK-025 | — |
| `web/modules/custom/lms_base/src/Exception/ValidationException.php` | Create | 422 validation errors | TASK-025 | — |
| `web/modules/custom/lms_base/src/Exception/AccessDeniedException.php` | Create | 403 JSON responses | TASK-025 | — |
| `web/modules/custom/lms_base/src/Exception/NotFoundException.php` | Create | 404 JSON responses | TASK-025 | — |
| `web/modules/custom/lms_base/src/EventSubscriber/ExceptionSubscriber.php` | Create | Catches LmsException subclasses; formats JSON response | TASK-025 | — |
| `web/modules/custom/lms_base/lms_base.services.yml` | Modify | Register ValidationService, LoggingService, ExceptionSubscriber | TASK-015 | — |
| `web/modules/custom/lms_forum/src/Controller/ForumController.php` | Modify | Remove `"HARD DEBUG!"` exception throw from `viewTopic()` | TASK-016 | — |
| `web/modules/custom/lms_rating/lms_rating.routing.yml` | Modify | Register 3 missing runtime routes | TASK-016 | — |
| All `*Service.php` across lms_* modules | Modify | Replace `\Drupal::` static calls with constructor injection | TASK-018 | — |
| All `*.services.yml` across lms_* modules | Modify | Add constructor `arguments:` for injected services | TASK-018 | — |
| All `*Controller.php` with write actions | Modify | Add ValidationService call before entity create/update | TASK-015 | — |
| All `*.routing.yml` across lms_* modules | Audit + Modify | Ensure explicit access requirements on all routes | TASK-016 | — |
| All `*Form.php` files across lms_* modules | Audit | Verify CSRF not disabled; document any intentional exemptions | TASK-024 | — |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| All write endpoints and routes must exist (G-CONTENT through G-ADDITIONAL) | Intra-project | D01-dev-overview.md §Build Order |
| Story `payment-webhooks` — webhook routes are CSRF-exempt; verify exemption is documented | Intra-Epic | this D03-stories/ |
| Story `progress-race-condition` — share ValidationService | Intra-Epic | this D03-stories/ |
| Symfony Validator component (already in Drupal core dependency chain) | External | composer.json |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| `D00-conventions/php-drupal.md` | All PHP files touched | PHPCS Drupal standard clean |
| `D00-conventions/api-error-format.md` | All controller error responses | Standardized JSON error shape |

## Editorial Usability / Entity-First Evidence

Not applicable — this Story does not render or modify business-owned visible content. It modifies backend service and controller code only.

## Feature Flags Introduced or Read

| Flag ID | Introduced / Read | Purpose | Rollout plan link |
|---------|-------------------|---------|-------------------|
| — | — | No feature flags for this Story | — |

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-HS-001 | Unit | POST endpoint with missing required field returns HTTP 422 + `{"errors":[…]}` | TASK-015 AC |
| T-HS-002 | Unit | HTML input sanitized on text fields (XSS prevention) | TASK-015 AC |
| T-HS-003 | Unit | LmsException subclass caught by ExceptionSubscriber → correct HTTP code + JSON | TASK-025 AC |
| T-HS-004 | Unit | Raw PHP exception logs to watchdog but returns generic 500 (no stack trace) | TASK-025 AC |
| T-HS-005 | Unit | Service class instantiated with mock constructor args (no \Drupal:: calls) | TASK-018 AC |
| T-HS-006 | Functional | CSRF attack simulation on AJAX endpoint returns 403 | TASK-024 AC |
| T-HS-007 | Functional | Role matrix test — student cannot access /manage/* routes | TASK-016 AC |
| T-HS-008 | Functional | LoggingService::critical() produces watchdog entry with correct context fields | TASK-020 AC |
| T-HS-009 | Functional | ForumController::viewTopic() no longer throws debug exception | TASK-016 AC |
| T-HS-010 | Integration | lms_rating 3 missing routes now return correct HTTP responses | TASK-016 AC |

## Gate Results (per Story, see A02 §3)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PENDING | — | 0/3 | — |
| G2 Lint | PENDING | PHPCS Drupal standard | 0/3 | — |
| G3 Tests | PENDING | Coverage: — | 0/3 | — |
| G4 Adversarial | PENDING | 7 categories examined, Critical/High: SQL injection + XSS + privilege escalation | 0/3 | — |

## Notes

- This is the largest Story in D03 by surface area — it touches all 27 lms_* modules. Use parallel sessions (S2 and S3 can run concurrently after S1 completes).
- The `lms_paypal` module is confirmed dead code (wrong interface, no `@PaymentGateway` annotation, never discovered by the plugin manager). DI cleanup on that module is low-value; document it and skip unless resurrecting.
- Webhook routes (`lms_stripe`, `lms_paypal`) must be explicitly CSRF-exempt in routing.yml with an inline comment explaining why (signature-based auth replaces CSRF).
- All third-party API keys are stored in plaintext config YAML — see `G02-risk-register.md` (RISK-001 through RISK-004). This Story does NOT migrate secrets to Key module — that is a separate infrastructure task. This Story adds `«secret in config — see security risk»` comments in relevant config files.
- PHPCS Drupal standard (`phpcs --standard=Drupal`) must pass on all files touched. Run as part of G2.
