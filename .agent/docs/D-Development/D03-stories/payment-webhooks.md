# D03 — Story Spec: Payment Webhooks

**Story ID:** payment-webhooks
**Story type:** integration
**Title:** Payment Webhooks — endpoints + signature verification for Stripe, PayPal, ZaloPay
**Primary source:** `C-Design/C05-modules/lms_stripe.md`, `C-Design/C05-modules/lms_paypal.md`, `C-Design/C05-modules/lms_payment.md`
**Owning module(s):** lms_stripe, lms_paypal, lms_payment
**Service(s):** StripeSignatureVerifier (lms_stripe), PayPalSignatureVerifier (lms_paypal), PaymentGatewayManager (lms_payment)
**Epic:** E-10 (Quality & Hardening)
**Contributes to Feature(s):** F-004 (Order & Membership), F-012 (Security & Performance)
**Design source(s):** `C-Design/C05-modules/lms_stripe.md`, `C-Design/C05-modules/lms_paypal.md`, `C-Design/C05-modules/lms_payment.md`
**Round:** N/A
**Driver:** NFR gap; G02 risk register (RISK-004); FIND-001 (no Stripe webhook endpoint), FIND-002 (no PayPal webhook endpoint), FIND-005 (no idempotency)
**Status:** SPEC
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `story: payment-webhooks` — maps to draft TASK-002, TASK-029, TASK-030, TASK-031

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 2-3
**Splitting strategy:**
- S1: Stripe webhook endpoint + HMAC-SHA256 signature verification + idempotency key table
- S2: PayPal webhook endpoint + PayPal REST API verification method (different from HMAC)
- S3 (if needed): ZaloPay endpoint + HMAC-SHA256 (ZaloPay uses `key2` HMAC), integration tests for all three

## Scope (from primary source)

Implement and harden webhook receiver endpoints for all three payment gateways. Currently **no webhook endpoints exist anywhere** in the codebase — this is a complete greenfield implementation.

**Sub-scope:**

1. **Stripe webhook endpoint** (`POST /api/lms/payment/stripe/webhook`):
   - `StripeSignatureVerifier::verify(string $rawBody, string $sigHeader, string $secret): bool`
   - Uses HMAC-SHA256: `hash_hmac('sha256', "{ts}.{rawBody}", $webhookSecret)` — compare constant-time
   - Timestamp tolerance: reject if `|now − stripe_ts| > 300s` (replay protection)
   - Webhook secret stored in `lms_stripe.settings:webhook_secret` (admin-configurable, NOT hardcoded)
   - Request without `Stripe-Signature` header → HTTP 400
   - Invalid signature → HTTP 400
   - Raw body preserved for signing (not re-encoded JSON)
   - Supported events: `payment_intent.succeeded`, `payment_intent.payment_failed`, `charge.refunded`

2. **PayPal webhook endpoint** (`POST /api/lms/payment/paypal/webhook`):
   - `PayPalSignatureVerifier::verify(Request $request, string $webhookId): bool`
   - Calls PayPal REST API `POST /v1/notifications/verify-webhook-signature` with `auth_algo`, `cert_url`, `transmission_id`, `transmission_sig`, `transmission_time`, `webhook_id`, `webhook_event`
   - Caches PayPal OAuth token (TTL = token expiry) — not re-requested per webhook
   - If PayPal verification API unreachable → log error + HTTP 503 (do not process)
   - `webhook_id` stored in `lms_paypal.settings:webhook_id`
   - Sandbox vs production URL controlled by `lms_paypal.settings:sandbox_mode`
   - **Note:** `lms_paypal` module is currently dead code (wrong interface, no `@PaymentGateway` annotation). This Story must also fix the gateway registration or declare PayPal explicitly deferred.

3. **ZaloPay webhook endpoint** (`POST /api/lms/payment/zalopay/webhook`):
   - HMAC-SHA256 using `zalopay_key2`; current config shows `zalopay_app_id`, `zalopay_key1`, `zalopay_key2` all empty — ZaloPay is stub/not yet configured
   - Implement endpoint stub with signature verification skeleton; mark PARTIAL until ZaloPay credentials supplied
   - Keys stored in `lms_payment.settings:zalopay_key2`

4. **Idempotency** — all three webhook handlers must be idempotent:
   - Create `lms_webhook_log` DB table: `(id, gateway, event_id, event_type, status, received_at, processed_at)`
   - Before processing any event: check `event_id` in log table. If already `processed` → return HTTP 200 (no-op)
   - After successful processing: mark `processed`. On failure: mark `failed` (allow retry)
   - Stripe uses `event.id`; PayPal uses `resource.id`; ZaloPay uses `apptransid`

5. **Order status updates** — on verified `payment_intent.succeeded`: update `lms_order.status` → `completed`, activate membership if applicable, trigger certificate issuance event. Use DB transaction; log to `LoggingService` (from Story `hardening-security`).

**Real implementation status (as-built):**
- No webhook endpoints exist in `lms_stripe`, `lms_paypal`, or anywhere in the codebase.
- Stripe `PaymentIntent` is currently created on form `BUILD` (one per page load) — this is a known bug (creates orphaned PaymentIntents). This Story must fix: create PaymentIntent only on form submit.
- `lms_paypal` module: wrong interface (`PaymentGatewayInterface` not implemented), no `@PaymentGateway` annotation — never discovered by `PaymentGatewayManager`. This module is dead code. This Story must either fix the registration or explicitly mark PayPal as planned/deferred with a bug issue filed.
- ZaloPay: no credentials configured, no gateway class — pure stub.
- Stripe and PayPal credentials stored in plaintext config YAML — `«secret in config — see security risk»`; do not add webhook secrets to config YAML in the same way — instead, use Key module or environment variable for the webhook signing secret.

## Source Documents

| Source | Why loaded |
|---|---|
| `C-Design/C05-modules/lms_stripe.md` | Stripe gateway implementation details |
| `C-Design/C05-modules/lms_paypal.md` | PayPal gateway (dead code) — understand state before implementing |
| `C-Design/C05-modules/lms_payment.md` | PaymentGatewayManager plugin host; order status lifecycle |
| `C-Design/C05-modules/lms_order.md` | Order entity status machine; completion trigger |
| `C-Design/C05-modules/lms_membership.md` | Membership activation on order completion |
| `B06-integration-requirements/` | INT-004 (Stripe), INT-005 (PayPal), INT-006 (ZaloPay) |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Stripe webhook must verify HMAC-SHA256 signature before processing | B06-INT-004; FIND-001 | Integration |
| PayPal webhook must call PayPal verification API before processing | B06-INT-005; FIND-002 | Integration |
| Webhook handlers must be idempotent (duplicate event_id = no-op) | FIND-005 | Integration |
| Unverified webhook returns HTTP 400; PayPal API unreachable returns 503 | B06 | Integration |
| Stripe PaymentIntent must be created on form submit (not build) | bug fix | NFR |
| Order status updated to `completed` on verified payment success event | B04-business-workflows/WF-004-order-management.md | Acceptance |
| Webhook signing secrets must not be committed to config YAML | B02-technical-requirements.md §Security | Non-Functional |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_stripe/src/Controller/StripeWebhookController.php` | Create | Webhook endpoint; dispatches to event handler after signature verify | TASK-029 | — |
| `web/modules/custom/lms_stripe/src/Service/StripeSignatureVerifier.php` | Create | HMAC-SHA256 verification + timestamp tolerance | TASK-029 | — |
| `web/modules/custom/lms_stripe/lms_stripe.routing.yml` | Modify | Add POST route `/api/lms/payment/stripe/webhook`; CSRF-exempt | TASK-029 | — |
| `web/modules/custom/lms_stripe/config/install/lms_stripe.settings.yml` | Modify | Add `webhook_secret` key (value: empty; set via admin UI) | TASK-029 | — |
| `web/modules/custom/lms_stripe/src/Controller/StripeCheckoutController.php` | Modify | Move PaymentIntent creation from form build → form submit handler | TASK-029 | — |
| `web/modules/custom/lms_paypal/src/Controller/PayPalWebhookController.php` | Create | Webhook endpoint; calls PayPal verification API | TASK-030 | — |
| `web/modules/custom/lms_paypal/src/Service/PayPalSignatureVerifier.php` | Create | PayPal REST API verification; token cache | TASK-030 | — |
| `web/modules/custom/lms_paypal/lms_paypal.routing.yml` | Modify | Add POST route `/api/lms/payment/paypal/webhook`; CSRF-exempt | TASK-030 | — |
| `web/modules/custom/lms_payment/src/Entity/WebhookLog.php` | Create | Idempotency log entity (or raw DB table via hook_schema) | TASK-031 | — |
| `web/modules/custom/lms_payment/src/Service/WebhookIdempotencyService.php` | Create | Check/mark event_id in webhook_log table | TASK-031 | — |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| Story `hardening-security` — provides LoggingService used in webhook handlers | Intra-Epic | this D03-stories/ |
| `lms_payment::PaymentGatewayManager` — order status update after webhook | Intra-project | `C-Design/C05-modules/lms_payment.md` |
| `lms_order` — order entity storage for status update | Intra-project | `C-Design/C05-modules/lms_order.md` |
| `lms_membership` — activation trigger on order completion | Intra-project | `C-Design/C05-modules/lms_membership.md` |
| Stripe PHP SDK (`stripe/stripe-php`) | External | composer.json |
| PayPal REST SDK or Guzzle HTTP client for verification API calls | External | composer.json |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| `D00-conventions/php-drupal.md` | All PHP files | PHPCS Drupal standard clean |
| `D00-conventions/api-error-format.md` | Webhook error responses (400, 503) | JSON error shape |
| Webhook routes explicitly CSRF-exempt with inline comment | `*.routing.yml` | Code review |

## Editorial Usability / Entity-First Evidence

Not applicable — this Story implements backend webhook receivers only.

## Feature Flags Introduced or Read

| Flag ID | Introduced / Read | Purpose | Rollout plan link |
|---------|-------------------|---------|-------------------|
| — | — | No feature flags for this Story | — |

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-PW-001 | Unit | Stripe webhook without `Stripe-Signature` header → HTTP 400 | TASK-029 AC |
| T-PW-002 | Unit | Stripe webhook with invalid signature → HTTP 400 | TASK-029 AC |
| T-PW-003 | Unit | Stripe webhook with timestamp > 5 min old → HTTP 400 (replay protection) | TASK-029 AC |
| T-PW-004 | Unit | Valid Stripe webhook → order status updated + log entry created | TASK-029 AC |
| T-PW-005 | Unit | Duplicate Stripe event_id → HTTP 200 no-op (idempotency) | TASK-031 AC |
| T-PW-006 | Unit | PayPal webhook → PayPal verification API called; mock SUCCESS → event processed | TASK-030 AC |
| T-PW-007 | Unit | PayPal verification API unreachable → HTTP 503, no processing | TASK-030 AC |
| T-PW-008 | Unit | StripeCheckoutController creates PaymentIntent on submit (not build) | TASK-029 AC |
| T-PW-009 | Integration | Verified payment success webhook → `lms_order.status = completed`, membership activated | TASK-031 AC |
| T-PW-010 | Functional | Webhook routes are accessible without CSRF token (CSRF-exempt) | TASK-029 AC |

## Gate Results (per Story, see A02 §3)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PENDING | — | 0/3 | — |
| G2 Lint | PENDING | — | 0/3 | — |
| G3 Tests | PENDING | Coverage: — | 0/3 | — |
| G4 Adversarial | PENDING | 7 categories: signature bypass, replay attack, idempotency bypass | 0/3 | — |

## Notes

- **PayPal dead code:** `lms_paypal` module is confirmed dead code — wrong interface, no `@PaymentGateway` annotation. Before implementing the PayPal webhook, the gateway registration must be fixed or PayPal must be formally deferred. File E02 issue for this before the session opens.
- **ZaloPay credentials:** `zalopay_app_id`, `zalopay_key1`, `zalopay_key2` are all empty in current config. ZaloPay webhook endpoint is a skeleton only until credentials are provided by the business.
- **Secret management:** Do NOT store the Stripe webhook signing secret in `config/sync/` YAML. Use `settings.php` environment variable or Key module. This is distinct from the Stripe API keys already in config (those are the existing risk documented in `G02-risk-register.md`).
- **Stripe PaymentIntent bug:** creating PaymentIntent on form build means N abandoned PaymentIntents in Stripe dashboard per page view. Fix is to move creation to `::submitForm()`. This is tracked as a separate bug in `E02-issues/` but is addressed in the same session as the webhook work.
- Story `hardening-security` must be at least S1-complete (LoggingService exists) before S1 of this Story begins.
