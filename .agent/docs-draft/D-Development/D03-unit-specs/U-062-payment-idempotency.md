# D03 — Unit Spec: Idempotency Keys for Payment Callbacks

**Unit ID:** U-062
**Group:** G-HARDENING
**Title:** Idempotency keys for payment callbacks
**Status:** SPEC
**Priority:** P0
**Assigned:** unassigned
**Risk Ref:** RISK-004 (High — Payment gateway stubs)

---

## Scope

Ensure that processing the same payment webhook event multiple times (retries, duplicate deliveries) does not result in duplicate order completions, duplicate membership activations, or duplicate enrollments. Implement an idempotency log table.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Service/IdempotencyService.php` | Create | lms_payment |
| `src/Controller/StripeWebhookController.php` | Modify | lms_stripe |
| `src/Controller/PayPalWebhookController.php` | Modify | lms_paypal |
| `lms_payment.install` (hook_schema) | Modify | lms_payment |

---

## Interfaces / Services

- `IdempotencyService::hasProcessed(string $eventId): bool`
- `IdempotencyService::markProcessed(string $eventId, string $gateway, string $result): void`
- DB table: `lms_payment_idempotency` — columns: `event_id` (PK), `gateway`, `processed_at`, `result`
- `event_id` = Stripe `event.id` or PayPal `resource.id`
- Processed events retained for 30 days, then purged by cron

---

## Acceptance Criteria

- [ ] Duplicate Stripe event ID processed only once (second call returns HTTP 200 with "already processed")
- [ ] Duplicate PayPal event resource ID processed only once
- [ ] `lms_payment_idempotency` table created on module install
- [ ] Concurrent duplicate webhooks do not race: DB INSERT with unique constraint on `event_id`
- [ ] Old idempotency records purged after 30 days via cron
- [ ] No second enrollment, membership activation, or order status change on duplicate

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Concurrent duplicate webhook test |
| G4 Adversarial | PENDING | — |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-031 | Webhook controllers — modified |
| U-060 | Stripe sig verification must pass before idempotency check |
| U-061 | PayPal sig verification must pass before idempotency check |

---

**Feature Flag:** None

*End of U-062-payment-idempotency.md*
