# D03 — Unit Spec: PayPalGateway (stub)

**Unit ID:** U-029
**Group:** G-COMMERCE
**Title:** PayPalGateway (stub)
**Status:** DONE (PARTIAL)
**Priority:** P1

---

## Scope

Implements two separate `PayPalGateway` classes: one in `lms_payment` (service-level interface) and one in `lms_paypal` (dedicated module). Both are scaffold stubs with TODO markers on payment creation, webhook verification, and refund. The `lms_paypal` module is the authoritative plugin-based implementation.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_paypal/lms_paypal.info.yml` | Module declaration |
| `web/modules/custom/lms_paypal/src/Plugin/PaymentGateway/PayPalGateway.php` | PayPalGateway plugin (authoritative) |
| `web/modules/custom/lms_payment/src/Plugin/PaymentGateway/PayPalGateway.php` | PayPalGateway in lms_payment (secondary) |

---

## Entities / Services / Controllers / Templates

**`lms_paypal` PayPalGateway implements `PaymentGatewayInterface`:**
- `getId(): string`
- `getLabel(): string`
- `createPayment(float $amount, string $currency, array $metadata): array` — TODO stub
- `verifyPayment(array $data): array` — TODO stub
- `refund(string $transactionId, float $amount): bool` — TODO stub

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Plugin registers and is discoverable |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Stub methods callable without fatal errors |
| G4 Adversarial | PARTIAL | All methods are TODO stubs |

---

## Acceptance Criteria

- [x] `PayPalGateway` plugin discoverable by `PaymentGatewayManager`
- [x] Stub methods return without fatal errors
- [ ] Real PayPal REST API calls implemented (PARTIAL — TODO stubs)
- [ ] Webhook/IPN verification implemented (PARTIAL — TODO stub)
- [ ] Refund endpoint implemented (PARTIAL — TODO stub)

---

## Known Debt / Issues

- All three methods (`createPayment`, `verifyPayment`, `refund`) are TODO stubs — full implementation pending checkout workflow (U-030) and webhook hardening (U-061).
- Duplicate `PayPalGateway` class exists in both `lms_payment` and `lms_paypal` — consolidation needed.

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-027 | PaymentGatewayInterface implemented here |
| U-061 | G-HARDENING: PayPal webhook signature verification |

---

*End of U-029-paypal-gateway.md*
