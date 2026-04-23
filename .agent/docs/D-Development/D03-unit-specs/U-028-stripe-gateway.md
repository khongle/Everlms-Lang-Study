# D03 — Unit Spec: StripeGateway (stub)

**Unit ID:** U-028
**Group:** G-COMMERCE
**Title:** StripeGateway (stub)
**Status:** DONE (PARTIAL)
**Priority:** P1

---

## Scope

Implements the `StripeGateway` plugin class in the `lms_stripe` module, extending `PaymentGatewayBase`. Provides a scaffold-level implementation of `processPayment()` and `handleWebhook()` with a TODO marker on webhook verification. Stripe Elements JS is bundled in `lms_order`.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_stripe/lms_stripe.info.yml` | Module declaration |
| `web/modules/custom/lms_stripe/src/Plugin/PaymentGateway/StripeGateway.php` | StripeGateway plugin class |

---

## Entities / Services / Controllers / Templates

**`StripeGateway extends PaymentGatewayBase`**
- `processPayment($order, array $payment_details): array` — scaffold implementation
- `handleWebhook($request)` — stub; TODO comment: "Implement webhook verification"

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Plugin registers and is discoverable |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | processPayment returns array without error |
| G4 Adversarial | PARTIAL | Webhook verification not implemented |

---

## Acceptance Criteria

- [x] `StripeGateway` plugin discoverable by `PaymentGatewayManager`
- [x] `processPayment()` scaffold callable without fatal errors
- [ ] Webhook signature verification implemented (PARTIAL — TODO stub)
- [ ] Real Stripe API call wired (PARTIAL — stub)

---

## Known Debt / Issues

- `handleWebhook()` has `// TODO: Implement webhook verification.` comment — full implementation is U-060 in G-HARDENING.
- No real Stripe SDK call; payment details processing is scaffold only.

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-027 | PaymentGatewayInterface implemented here |
| U-060 | G-HARDENING: Stripe webhook signature verification |

---

*End of U-028-stripe-gateway.md*
