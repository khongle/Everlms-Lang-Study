# Task: Execute PROTO-002 — Payment Webhook Signature Spike

**ID:** TASK-050
**Unit:** U-060 + U-061 + U-062 (precedes implementation)
**Group:** G-HARDENING
**Priority:** P0
**Estimate:** 12h (timebox per PROTO-002)
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Run the PROTO-002 spike defined in `C-Design/C10-prototypes/PROTO-002-payment-webhook-signature.md` to validate Stripe + PayPal webhook signature verification, idempotency, and atomic order+enrollment fulfillment in Drupal. Without spike pass, TASK-029 (Stripe webhook), TASK-030 (PayPal webhook), and TASK-031 (idempotency) cannot start safely.

## Acceptance Criteria
- [ ] **Stripe path:** `\Stripe\Webhook::constructEvent()` verifies signature against raw body; tampered body → 400; valid → 200
- [ ] **Stripe replay:** 50 retries of same `event.id` → 1 fulfillment + 49 idempotent no-ops (200 OK)
- [ ] **Stripe latency:** P95 end-to-end (verify + DB insert + dispatch event) ≤ 200ms
- [ ] **PayPal path:** Verify-API approach (Approach A) returns `SUCCESS` for valid; `FAILURE` for tampered
- [ ] **PayPal latency:** P95 end-to-end ≤ 500ms (verify API roundtrip included)
- [ ] **Idempotency table:** `lms_payment_webhook_log` schema (event_id UNIQUE) shared across both providers
- [ ] **Atomicity:** Order + enrollment created in single DB transaction; mid-transaction kill rolls back both (resolves FIND-005)
- [ ] **FIND-009 resolved:** `git grep -c "class PayPalGateway"` returns 1 (single canonical copy in `lms_paypal`)
- [ ] **Routing:** `lms_paypal.routing.yml` has `lms_paypal.webhook` route entry (resolves FIND-002 routing gap)
- [ ] PROTO-002 `Outcome`/`Result`/`Decision`/`Artifacts` populated

## Technical Notes
- Modules: `lms_stripe`, `lms_paypal`, `lms_payment` (shared idempotency service)
- New table migration: `lms_payment_webhook_log` (event_id, provider, received_at, payload_hash) — Phase 1 in spike via `hook_update_N`
- SDK: `composer require stripe/stripe-php:^14.0` on spike branch
- Stripe CLI for local: `stripe listen --forward-to https://everlms.local/lms/webhook/stripe`
- PayPal Developer sandbox app for `webhook_id`
- Outputs: `.agent/generated/proto-002/{stripe-bench.csv, paypal-bench.csv, stripe-events.json, paypal-events.json}`

## Dependencies
- ADR-007 ACCEPTED (PaymentGatewayInterface stable)
- HTTPS local environment (mkcert / ngrok) — webhooks require HTTPS

## Definition of Done
- [ ] Spike branch `spike/proto-002-payment-webhook` pushed
- [ ] PROTO-002 file populated with measurements + decision
- [ ] FIND-001/002/005/009 status updated in G02-risk-register.md (still OPEN until implementation; spike unlocks it)
- [ ] Unblocks TASK-029, TASK-030, TASK-031, TASK-055 (FIND-009 dedup)
