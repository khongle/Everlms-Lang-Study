# D03 — Unit Spec: Webhook Signature Verification (Stripe)

**Unit ID:** U-060
**Group:** G-HARDENING
**Title:** Webhook signature verification (Stripe)
**Status:** SPEC
**Priority:** P0
**Assigned:** unassigned
**Risk Ref:** RISK-004 (High — Payment gateway stubs)

---

## Scope

Harden the Stripe webhook endpoint (implemented in U-031) to ensure HMAC-SHA256 signature verification is production-grade. If U-031 has not been implemented yet, this unit also creates the endpoint. Covers: raw body preservation, timestamp tolerance, and test key separation.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Service/StripeSignatureVerifier.php` | Modify/Create | lms_stripe |
| `src/Controller/StripeWebhookController.php` | Modify/Create | lms_stripe |
| `config/install/lms_stripe.settings.yml` | Modify | lms_stripe |

---

## Interfaces / Services

- `StripeSignatureVerifier::verify(string $rawBody, string $sigHeader, string $secret): bool`
  - Uses `Stripe\Webhook::constructEvent()` or manual HMAC-SHA256 with `hash_hmac('sha256', $ts . '.' . $rawBody, $secret)`
  - Timestamp tolerance: reject if `|now - stripe_ts| > 300s`
  - Reject requests with no `Stripe-Signature` header
- Webhook secret stored in `lms_stripe.settings:webhook_secret` (not hardcoded)
- Test/live secret configured separately (test mode vs live mode setting)

---

## Acceptance Criteria

- [ ] Request without `Stripe-Signature` header returns HTTP 400
- [ ] Request with invalid signature returns HTTP 400 (not 403)
- [ ] Request with timestamp > 5 min old returns HTTP 400 (replay protection)
- [ ] Valid signature + payload proceeds to event processing
- [ ] Raw body used for signature (not re-encoded JSON)
- [ ] Webhook secret read from config (admin-configurable)

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Replay attack test, invalid sig test |
| G4 Adversarial | PENDING | Signature bypass attempts |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-031 | Webhook endpoint (may implement jointly) |
| U-028 | Stripe gateway config |

---

**Feature Flag:** None

*End of U-060-stripe-webhook-sig.md*
