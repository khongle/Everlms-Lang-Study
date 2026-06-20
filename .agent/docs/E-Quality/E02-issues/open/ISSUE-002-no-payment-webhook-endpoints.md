# ISSUE-002 — No Payment Webhook Endpoints (Stripe / PayPal / ZaloPay)

| Field | Value |
|---|---|
| ID | `ISSUE-002` |
| Title | No webhook routes anywhere — Stripe/PayPal/ZaloPay cannot push async payment events |
| Source | G4-Architecture / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `HIGH` |
| Affected page / route | N/A — routes are absent |
| Owning Epic | E-10 (G-HARDENING) |
| Owning Story | [`payment-webhooks`](../../../D-Development/D03-stories/payment-webhooks.md) |
| Owning module(s) | `lms_stripe`, `lms_paypal`, `lms_payment` |

## Summary

No webhook receiver routes exist in any payment module. The `lms_stripe`, `lms_paypal`, and `lms_payment` modules define no routing for incoming webhook events. `StripeGateway::handleWebhook()` and the `PayPalGateway::handleWebhook()` (in `lms_payment`) both exist in code but only return placeholder `Response` objects and include a `// TODO: Implement webhook verification.` comment. Without webhook endpoints:

- Stripe cannot notify the backend of asynchronous payment events (`payment_intent.succeeded`, `charge.refunded`).
- PayPal IPNs and webhook notifications cannot be received or verified.
- Auto-renewal of memberships relies entirely on `hook_cron` polling `field_end_date`, and the cron hook comment explicitly defers webhook-based renewal: _"auto renewal needs to be handled via payment gateway webhook"_.
- No HMAC-SHA256 signature verification is implemented for any gateway, so if a webhook URL were added without the signature check, it would be trivially spoofable.

## Reproduction

1. Search all `*.routing.yml` files under `web/modules/custom/lms_stripe/`, `lms_paypal/`, `lms_payment/`:
   ```
   find web/modules/custom -name "*.routing.yml" | xargs grep -l "webhook"
   ```
   Returns no results.
2. Open `web/modules/custom/lms_stripe/src/Plugin/PaymentGateway/StripeGateway.php` → `handleWebhook()`:
   body logs receipt and returns `Response('Webhook not implemented.', 500)` with TODO comment.
3. Open `web/modules/custom/lms_payment/src/Plugin/PaymentGateway/PayPalGateway.php` → `handleWebhook()`:
   returns `new Response('', 200)` (no-op stub).
4. Attempt to configure a Stripe webhook endpoint in the Stripe dashboard pointing to `https://everlms.ddev.site/api/lms/payment/stripe/webhook` — Drupal returns 404 (route not found).

## Root Cause

Webhook handling was marked as a TODO during initial payment gateway development. `PaymentGatewayBase::handleWebhook()` is an abstract stub. No concrete implementation was written for Stripe (the only production-ready gateway). The `lms_payment` module's `Service\PaymentManager` (a thin registry) exists but is not wired into the DI container, and there is no event-dispatch mechanism for incoming webhook events. The `lms_membership.module hook_cron` explicitly notes that auto-renewal via webhook is not implemented.

## Proposed Fix

Per Story [`payment-webhooks`](../../../D-Development/D03-stories/payment-webhooks.md):

1. **Stripe** — create `POST /api/lms/payment/stripe/webhook` route in `lms_stripe.routing.yml`; implement `StripeSignatureVerifier::verify()` using `Stripe\WebhookSignature::verifyHeader()` (or equivalent HMAC-SHA256 + timestamp-tolerance check); store webhook signing secret via Key module (not config YAML — see [ISSUE-001](ISSUE-001-secrets-in-config.md)).
2. **PayPal** — create `POST /api/lms/payment/paypal/webhook` route in `lms_paypal.routing.yml` (after fixing ISSUE-003 — dead-code module); call PayPal REST `POST /v1/notifications/verify-webhook-signature`.
3. **ZaloPay** — implement skeleton endpoint with HMAC-SHA256 using `zalopay_key2`; mark partial until credentials provided.
4. **Idempotency** — create `lms_webhook_log` table to deduplicate events by `event_id` per gateway.
5. **Webhook routes must be CSRF-exempt** — add `_csrf_token: FALSE` (or equivalent) to routing.yml with an inline comment explaining signature-based auth replaces CSRF.

## Trace links

- **Story:** `D-Development/D03-stories/payment-webhooks.md`
- **C05 modules:** `C-Design/C05-modules/lms_stripe.md`, `C-Design/C05-modules/lms_paypal.md`, `C-Design/C05-modules/lms_payment.md`
- **Facts base:** `modules-commerce.md` §Cross-module notes — Webhook handling summary
- **Related issues:** [ISSUE-001](ISSUE-001-secrets-in-config.md) (webhook signing secrets must not be added to config YAML), [ISSUE-003](ISSUE-003-paypal-module-dead-code.md) (PayPal module must be fixed before PayPal webhook can be wired), [ISSUE-004](ISSUE-004-stripe-paymentintent-on-form-build.md) (Stripe PaymentIntent bug — same session scope)
