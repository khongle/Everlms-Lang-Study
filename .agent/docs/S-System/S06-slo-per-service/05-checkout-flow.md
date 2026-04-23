# SLO: checkout-flow

**Service:** Checkout & Order Creation Flow (`checkout-flow`)
**DR Tier:** Tier 1 (primary revenue path — all paid conversions go through this)
**Owner:** hainn
**SLO Version:** 0.1 (initial, **pre-production — blocked by G-HARDENING**)

> **Status: PROPOSED + BLOCKED** — depends on TASK-001 (checkout impl), TASK-015 (access control), TASK-017 (atomicity), U-060 (Stripe). SLO applies to the user-visible checkout funnel, not the webhook (see `03-payment-webhook.md`).

---

## Why a Dedicated SLO

Checkout is the single critical path that converts traffic into revenue. It has unique failure modes separate from general app availability:
- Payment provider timeout (retry UX matters)
- CAPTCHA friction (user-visible)
- Cart abandonment (visible to analytics but not a "failure")
- Order + enrollment atomicity (FIND-005)

A fast, available general app doesn't matter if checkout is down.

---

## Service-Level Indicators (SLIs)

| SLI | Definition | Data Source (planned) |
|-----|-----------|----------------------|
| Availability | 2xx rate on `/checkout/*` endpoints | Nginx log |
| Conversion completion rate | `order_completed / checkout_started` per session | Analytics (GA4 / custom event) |
| Time-to-payment p95 | User lands on `/checkout` → payment confirmation page | Structured log |
| Atomicity correctness | `orders_committed_without_enrollment / total_completed` | Nightly audit query |

---

## Objectives (30-day rolling window)

| SLI | Target | Error Budget | Alert Threshold |
|-----|--------|--------------|-----------------|
| Availability | **≥ 99.9%** | ~43 min / month | Burn rate > 3× → page |
| Conversion completion | **≥ 70%** (of starts that reach payment step — not overall cart-to-buy) | 30% can drop out | Drop > 10 pp vs 7-day baseline → investigate |
| Time-to-payment p95 | **< 8s** (includes Stripe redirect round-trip) | 5% may exceed | p95 > 15s for 10 min → investigate |
| Atomicity | **100%** | 0 — violating = lost money / unhappy user | Any detected breach → page SA |

---

## Rationale for Targets

- **99.9% availability (stricter than `everlms-web`)** because downtime here = direct revenue loss, visible to users immediately, and triggers support tickets.
- **Conversion SLO is informational not hard** because abandonment happens for business reasons (price shock, competitor found). Anomaly alert only (sudden drop = likely technical issue).
- **Time-to-payment < 8s** because checkout UX research says users abandon after 10-15s of "nothing happens". Includes Stripe redirect ~2-3s + our server render ~500ms + network.
- **100% atomicity** because violating means a user paid without enrolling (refund request + support ticket + brand damage) or enrolled without paying (revenue leak).

---

## SLIs Not Yet Tracked (blocked)

| SLI | Reason Not Tracked |
|-----|-------------------|
| Step-by-step funnel | Analytics events not yet wired for each checkout step |
| Card decline rate by reason | Stripe webhook (stub) doesn't forward metadata yet |
| Coupon error breakdown | No structured coupon event logging |
| A/B test impact | No experimentation framework wired |

---

## Alert Wiring (PROPOSED)

When checkout is live:
- **5xx rate on `/checkout/*`:** > 0.5% for 5 min → page.
- **Session errors:** `X-Checkout-Error` header > 1% → warn.
- **Time-to-payment p95:** > 15s for 10 min → investigate upstream (Stripe side).
- **Atomicity audit:** Nightly `SELECT orders.id FROM orders LEFT JOIN user_course ON … WHERE orders.status='completed' AND user_course.id IS NULL` — any row = page.

Runbook (to create):
- `H-Operations/H02-runbooks/checkout-errors.md`
- `H-Operations/H02-runbooks/order-enrollment-mismatch.md`

---

## Prerequisites to Activate

1. **TASK-001** — checkout flow real integration (not stub).
2. **TASK-015** — `/checkout` routes hardening (FIND-004 fix).
3. **TASK-017** — order + enrollment transaction (FIND-005 fix).
4. **U-060** — Stripe webhook signature verify.
5. Structured checkout-step logging.
6. Nightly atomicity audit job (cron task).

---

## Change Protocol

- Tightening availability: requires multi-AZ deployment.
- Loosening conversion: adjust based on measured baseline after 90 days live.
- Adding provider (VNPAY, Momo): clone this SLO, adapt SLIs, SA sign-off.

---

*End of SLO: checkout-flow*
