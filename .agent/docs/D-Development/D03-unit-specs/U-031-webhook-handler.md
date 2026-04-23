# D03 — Unit Spec: Webhook Handler + Signature Verification

**Unit ID:** U-031
**Group:** G-COMMERCE
**Title:** Webhook handler + signature verification
**Status:** SPEC
**Priority:** P1
**Assigned:** unassigned

---

## Scope

Implement webhook receiver endpoints for Stripe and PayPal. Each endpoint must: verify payload signature before processing, handle key event types (payment_intent.succeeded, charge.failed, subscription events), update Order status, and be idempotent. Note: signature verification is also covered at hardening level in U-060/U-061 — this unit implements it from scratch.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Controller/StripeWebhookController.php` | Create | lms_stripe |
| `src/Controller/PayPalWebhookController.php` | Create | lms_paypal |
| `src/Service/WebhookProcessorService.php` | Create | lms_payment |
| `src/Service/StripeSignatureVerifier.php` | Create | lms_stripe |
| `src/Service/PayPalSignatureVerifier.php` | Create | lms_paypal |
| `lms_stripe.routing.yml` | Modify | lms_stripe |
| `lms_paypal.routing.yml` | Modify | lms_paypal |

---

## Interfaces / Services

- `WebhookVerifierInterface` — `verify(string $payload, string $signature, string $secret): bool`
- `PaymentManager` (U-027) — order status update
- Raw HTTP request handling (no CSRF token for webhook routes — use signature instead)

---

## Acceptance Criteria

- [ ] `/webhook/stripe` endpoint rejects requests with invalid signature (HTTP 400)
- [ ] `/webhook/paypal` endpoint rejects requests with invalid signature (HTTP 400)
- [ ] `payment_intent.succeeded` → Order status set to `completed`
- [ ] `payment_intent.payment_failed` → Order status set to `failed`
- [ ] Duplicate webhook events do not create duplicate state changes (idempotency)
- [ ] Webhook routes exempt from CSRF middleware (explicit exemption documented)
- [ ] Errors logged to watchdog before returning HTTP 500

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Signature verification unit tests required |
| G4 Adversarial | PENDING | Replay attack test required |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-025 | Order entity for status updates |
| U-028 | Stripe gateway config (webhook secret) |
| U-029 | PayPal gateway config (webhook ID) |

---

**Feature Flag:** None
**Risk Ref:** RISK-004 (High)

*End of U-031-webhook-handler.md*
