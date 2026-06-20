# SLO: payment-webhook

**Service:** Payment Webhook Handler (`payment-webhook`)
**DR Tier:** Tier 1 (primary revenue path — user-visible if fails)
**Owner:** hainn
**SLO Version:** 0.1 (initial, **pre-production — not enabled**)

> **Status: PROPOSED + BLOCKED** — webhook handler is a stub (FIND-001). SLO cannot be measured until U-060 + TASK-002 complete the signature-verified handler.

---

## Why a Dedicated SLO

The webhook path is a distinct SLO from `everlms-web` because:
- Different user cohort (payment providers, not human users)
- Different SLA expectation (minutes, not milliseconds — but idempotency matters)
- Different failure mode (silent data loss if webhook dropped → unfulfilled enrollment)
- Different source of traffic spikes (end-of-billing-cycle surges)

The `/payment/stripe/webhook` and `/payment/paypal/ipn` endpoints are the primary source of "was this order fulfilled?" truth for commerce.

---

## Service-Level Indicators (SLIs)

| SLI | Definition | Data Source (planned) |
|-----|-----------|----------------------|
| Delivery success | `webhook_events_processed / webhook_events_received` | Custom counter + Stripe dashboard |
| Signature validity | `signed_verified / total_received` | Custom log |
| Processing latency p95 | Time from request arrival → enrollment row committed | Structured log timing |
| Idempotency correctness | No duplicate order / enrollment per unique event ID | Audit query against webhook log table |

---

## Objectives (30-day rolling window)

| SLI | Target | Error Budget | Alert Threshold |
|-----|--------|--------------|-----------------|
| Delivery success | **≥ 99.9%** | ~43 events / month assuming 1M events | Any 5xx spike > 0.1% for 10 min → page |
| Signature validity | **100%** (reject invalid immediately) | 0 — invalid = security incident | Any `signature_mismatch` > 0 → page SA |
| Processing latency p95 | **< 3s** | 5% may exceed | p95 > 10s for 10 min → investigate (DB contention?) |
| Idempotency | **100%** | 0 duplicates | Any duplicate `order.id` with different `enrollment.id` → alert |

---

## Rationale for Targets

- **99.9% success** is Stripe's own stated SLA for webhook delivery. Handler must match it or revenue data drifts. Budget allows for <43 events lost per month in a 1M-event scenario.
- **100% signature validity** is non-negotiable — any invalid-but-accepted webhook is a security vulnerability (attacker forges payment). Zero tolerance.
- **p95 < 3s** because Stripe retries on non-2xx with exponential backoff; slow responses trigger retry storms and amplify load.
- **Idempotency** — same event ID must produce at most one enrollment. Webhook retries are common; duplicate processing = duplicate enrollment + charge dispute liability.

---

## SLIs Not Yet Tracked (blocked on stub fix)

| SLI | Reason Not Tracked |
|-----|-------------------|
| End-to-end (payment → enrollment) latency | Webhook stub short-circuits; no downstream handler |
| Per-provider breakdown (Stripe vs PayPal) | PayPal entirely stub (FIND-002) |
| Retry success from Stripe side | Stripe dashboard integration not wired |

---

## Alert Wiring (PROPOSED)

When U-060 merged:
- **Signature mismatch:** Page SA immediately; attempted tamper = security incident.
- **5xx rate:** > 1% for 5 min → page (Stripe will retry, but sustained fail = revenue loss).
- **Processing p95:** > 5s for 10 min → investigate (often DB lock or AI webhook side-effect).
- **Idempotency breach:** Nightly audit query → any duplicate `order × event` → page + file incident.

Runbook (to be created): `H-Operations/H02-runbooks/stripe-webhook-down.md`, `paypal-webhook-down.md`.

---

## Prerequisites to Activate

1. U-060 Stripe webhook signature verification implementation.
2. TASK-002 webhook handler with idempotency guard.
3. Structured logging (event_id, order_id, enrollment_id, duration_ms).
4. TASK-003 PayPal handler rewrite.
5. External monitoring (Datadog / Grafana Loki) wired to the log.

---

## Change Protocol

- Tightening signature validity: not possible (already 100%).
- Loosening success SLO: requires business sign-off + ADR (means accepting revenue loss).
- Adding new provider (e.g., VNPAY): clone this file, adapt SLIs, get SA sign-off.

---

*End of SLO: payment-webhook*
