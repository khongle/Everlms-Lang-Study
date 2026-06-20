# E02 — Issues (Source of Truth Index)

> Canonical issue log for EverLMS. Every confirmed bug, security gap, dead-code finding, and technical-debt item is registered here. Sources: G4 adversarial review, codebase inspection, risk-register findings, and test failures.
>
> **Per-issue files** live in `open/`, `in-progress/`, `resolved/`, or `wont-fix/` sub-folders. The table below is the single authoritative registry — keep it in sync with every status change.

---

## Registry (severity-sorted)

| ID | Title | Severity | Status | Module(s) | Source | Story | Date Found |
|----|-------|----------|--------|-----------|--------|-------|------------|
| [ISSUE-001](open/ISSUE-001-secrets-in-config.md) | Third-party secrets committed in config YAML — no Key module (OpenAI, Gemini, Google SA RSA key, Stripe, Zoom, PayPal) | `CRITICAL` | open | `lms_ai`, `lms_payment`, `lms_stripe`, `lms_zoom` | G4-Security | [hardening-security](../../D-Development/D03-stories/hardening-security.md) | 2026-06-20 |
| [ISSUE-002](open/ISSUE-002-no-payment-webhook-endpoints.md) | No webhook routes anywhere — Stripe/PayPal/ZaloPay cannot push async payment events; no signature verification | `HIGH` | open | `lms_stripe`, `lms_paypal`, `lms_payment` | G4-Architecture | [payment-webhooks](../../D-Development/D03-stories/payment-webhooks.md) | 2026-06-20 |
| [ISSUE-003](open/ISSUE-003-paypal-module-dead-code.md) | `lms_paypal` implements wrong interface + has no `@PaymentGateway` annotation — never discovered by `PaymentGatewayManager` | `HIGH` | open | `lms_paypal`, `lms_payment` | G4-Architecture | [payment-webhooks](../../D-Development/D03-stories/payment-webhooks.md) | 2026-06-20 |
| [ISSUE-004](open/ISSUE-004-stripe-paymentintent-on-form-build.md) | Stripe `PaymentIntent::create()` called in `buildForm()` — orphaned intent created on every page view | `HIGH` | open | `lms_order`, `lms_stripe` | G4-Architecture | [payment-webhooks](../../D-Development/D03-stories/payment-webhooks.md) | 2026-06-20 |
| [ISSUE-005](open/ISSUE-005-anonymous-notification-endpoint.md) | `GET /api/lms/notifications/unread-count` uses `_access: TRUE` — accessible without authentication | `HIGH` | open | `lms_notification` | G4-Security | [hardening-security](../../D-Development/D03-stories/hardening-security.md) | 2026-06-20 |
| [ISSUE-007](open/ISSUE-007-progress-cascade-race-condition.md) | Concurrent `mark-complete` requests for same user+course produce incorrect progress — no DB lock / transaction | `HIGH` | open | `lms_progress`, `lms_mobile_api` | G4-Data Integrity | [progress-race-condition](../../D-Development/D03-stories/progress-race-condition.md) | 2026-06-20 |
| [ISSUE-009](open/ISSUE-009-forum-controller-hard-debug-crash.md) | `ForumController::viewTopic` throws literal `"HARD DEBUG!"` exception; `LmsForumController::viewTopic` leaks debug `addMessage` to all users | `HIGH` | open | `lms_forum` | G4-Code Quality | [hardening-security](../../D-Development/D03-stories/hardening-security.md) | 2026-06-20 |
| [ISSUE-010](open/ISSUE-010-rating-missing-runtime-routes.md) | `lms_rating.collection`, `toggle_approve`, `toggle_featured` routes referenced but absent from `routing.yml` — `RouteNotFoundException` | `HIGH` | open | `lms_rating` | G4-Code Quality | [hardening-security](../../D-Development/D03-stories/hardening-security.md) | 2026-06-20 |
| [ISSUE-011](open/ISSUE-011-zoom-generatesignature-wrong-service-id.md) | `ZoomController::generateSignature` injects `@lms_zoom.zoom_service` (non-existent; correct ID is `@lms_zoom.service`) — `ServiceNotFoundException` for Zoom hosts | `HIGH` | open | `lms_zoom` | G4-Code Quality | [hardening-security](../../D-Development/D03-stories/hardening-security.md) | 2026-06-20 |
| [ISSUE-014](open/ISSUE-014-ai-no-rollback-compensation.md) | Partial AI course-builder failure leaves orphan entities — no rollback or compensation mechanism | `HIGH` | open | `lms_ai` | G4-Architecture | [ai-rollback-compensation](../../D-Development/D03-stories/ai-rollback-compensation.md) | 2026-06-20 |
| [ISSUE-006](open/ISSUE-006-progress-formalt-grants-textformats.md) | `hook_form_alter` mutates and saves `authenticated` Role entity — grants `full_html` to all logged-in users | `MEDIUM` | open | `lms_progress` | G4-Security | [hardening-security](../../D-Development/D03-stories/hardening-security.md) | 2026-06-20 |
| [ISSUE-008](open/ISSUE-008-mobile-api-undeclared-deps.md) | `lms_mobile_api` uses `lms_quiz`/`lms_wishlist` undeclared in `info.yml`; `getAuthenticatedUser()` copy-pasted in 3 controllers; unused `oauth_*` config keys | `MEDIUM` | open | `lms_mobile_api` | G4-Architecture | [hardening-security](../../D-Development/D03-stories/hardening-security.md) | 2026-06-20 |
| [ISSUE-012](open/ISSUE-012-badge-no-award-logic.md) | `lms_badge` entity definitions exist but zero award mechanism — `field_criteria` JSON never evaluated | `MEDIUM` | open | `lms_badge` | G4-Architecture | — (planned, layer F — not yet materialized) | 2026-06-20 |
| [ISSUE-013](open/ISSUE-013-notification-email-push-stubs.md) | `lms_notification` email channel only writes a log entry (TODO); push channel is stub — neither delivers | `MEDIUM` | open | `lms_notification` | G4-Architecture | — (planned, layer F — not yet materialized) | 2026-06-20 |
| [ISSUE-015](open/ISSUE-015-certificate-expiry-varchar.md) | `lms_user_certificate.field_expiry_date` stored as `varchar(20)` not `int` — timestamp sort/range queries silently fail | `MEDIUM` | open | `lms_certificate` | G4-Data Integrity | [hardening-security](../../D-Development/D03-stories/hardening-security.md) | 2026-06-20 |
| [ISSUE-016](open/ISSUE-016-quiz-hardcoded-threshold-and-starttime.md) | `lms-quiz-result.html.twig` hardcodes 70% pass threshold (ignores `field_threshold`); `field_start_time` hardcoded to `now-60` instead of actual start | `MEDIUM` | open | `lms_quiz` | G4-Code Quality | — | 2026-06-20 |
| [ISSUE-017](open/ISSUE-017-order-double-field-items-set.md) | `CheckoutController::createAndFulfillFreeMembershipOrder` calls `$order->set('field_items', ...)` twice — second call silently overwrites first | `LOW` | open | `lms_order` | G4-Code Quality | — | 2026-06-20 |
| [ISSUE-018](open/ISSUE-018-lesson-chapter-field-naming-inconsistency.md) | Schema inconsistency — demo generator uses `lesson.field_chapter` (child→parent) while importer uses `chapter.field_lessons` (parent→child); `lms_lesson` hook references `field_chapter` absent from `baseFieldDefinitions` | `MEDIUM` | open | `lms_lesson`, `lms_chapter`, `lms_content_tools` | G4-Architecture | — | 2026-06-20 |

---

## Issue Summary

| Severity | Open | In Progress | Resolved | Won't Fix |
|----------|------|-------------|----------|-----------|
| CRITICAL | 1 | 0 | 0 | 0 |
| HIGH | 9 | 0 | 0 | 0 |
| MEDIUM | 7 | 0 | 0 | 0 |
| LOW | 1 | 0 | 0 | 0 |
| **Total** | **18** | **0** | **0** | **0** |

**Rule:** Zero open CRITICAL/HIGH issues required before CP5 gate sign-off.

---

## Coverage by Module

| Module | Issue IDs | Highest Severity |
|--------|-----------|-----------------|
| `lms_ai` | ISSUE-001, ISSUE-014 | CRITICAL |
| `lms_badge` | ISSUE-012 | MEDIUM |
| `lms_certificate` | ISSUE-015 | MEDIUM |
| `lms_forum` | ISSUE-009 | HIGH |
| `lms_mobile_api` | ISSUE-007, ISSUE-008 | HIGH |
| `lms_notification` | ISSUE-005, ISSUE-013 | HIGH |
| `lms_order` | ISSUE-004, ISSUE-017 | HIGH |
| `lms_payment` | ISSUE-002, ISSUE-003 | HIGH |
| `lms_paypal` | ISSUE-003 | HIGH |
| `lms_progress` | ISSUE-006, ISSUE-007 | HIGH |
| `lms_quiz` | ISSUE-016 | MEDIUM |
| `lms_rating` | ISSUE-010 | HIGH |
| `lms_stripe` | ISSUE-001, ISSUE-002, ISSUE-004 | CRITICAL |
| `lms_zoom` | ISSUE-001, ISSUE-011 | CRITICAL |
| `lms_lesson` | ISSUE-018 | MEDIUM |
| `lms_chapter` | ISSUE-018 | MEDIUM |
| `lms_content_tools` | ISSUE-018 | MEDIUM |

---

## Batch Attribution

- **ISSUE-001 through ISSUE-008** — written by batch agent (this file's scope).
- **ISSUE-009 through ISSUE-018** — written by parallel batch agent; covered: `lms_forum` debug crash, `lms_rating` missing routes, `lms_zoom` wrong service ID, `lms_badge` near-stub, `lms_notification` email/push stubs, `lms_ai` no rollback, `lms_certificate` varchar field, `lms_quiz` hardcoded threshold, `lms_order` double-set bug, `lms_lesson`/`lms_chapter` field-naming inconsistency.

---

## Links

- **Test strategy:** [`E01-test-strategy.md`](../E01-test-strategy.md)
- **Story specs:** [`D03-stories/`](../../D-Development/D03-stories/)
- **Module specs (C05):** [`C05-modules/`](../../C-Design/C05-modules/)
- **Facts base (ground truth):** `config-inventory.md`, `modules-commerce.md`, `modules-progress-comms.md`, `modules-mobile.md` (under `the 2026-06-20 code ground-truth scan` — ephemeral; canonical source is the live codebase under `web/modules/custom/`)
- **Risk register:** G02-risk-register.md (planned, layer G — not yet materialized)
