# D03 — Unit Spec: Webhook Signature Verification (PayPal)

**Unit ID:** U-061
**Group:** G-HARDENING
**Title:** Webhook signature verification (PayPal)
**Status:** SPEC
**Priority:** P0
**Assigned:** unassigned
**Risk Ref:** RISK-004 (High — Payment gateway stubs)

---

## Scope

Harden the PayPal webhook endpoint to verify authenticity using PayPal's REST API verification method. PayPal does not use simple HMAC — it provides a `/v1/notifications/verify-webhook-signature` API that must be called with the event payload and headers.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Service/PayPalSignatureVerifier.php` | Modify/Create | lms_paypal |
| `src/Controller/PayPalWebhookController.php` | Modify/Create | lms_paypal |
| `config/install/lms_paypal.settings.yml` | Modify | lms_paypal |

---

## Interfaces / Services

- `PayPalSignatureVerifier::verify(Request $request, string $webhookId): bool`
  - Calls PayPal API: `POST /v1/notifications/verify-webhook-signature`
  - Passes: `auth_algo`, `cert_url`, `transmission_id`, `transmission_sig`, `transmission_time`, `webhook_id`, `webhook_event`
  - Caches PayPal OAuth token (TTL = token expiry) to avoid re-auth on each webhook
  - Returns false + logs warning if PayPal API call fails
- `webhook_id` stored in `lms_paypal.settings:webhook_id`

---

## Acceptance Criteria

- [ ] Webhook endpoint calls PayPal verification API before processing event
- [ ] Unverified webhook returns HTTP 400
- [ ] PayPal API auth token cached (not re-requested per webhook)
- [ ] If PayPal verification API is unreachable: log error, return HTTP 503 (do not process)
- [ ] Sandbox vs production PayPal API URL controlled by `lms_paypal.settings:sandbox_mode`

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Mock PayPal verification API response |
| G4 Adversarial | PENDING | — |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-031 | PayPal webhook endpoint |
| U-029 | PayPal gateway config |

---

**Feature Flag:** None

*End of U-061-paypal-webhook-sig.md*
