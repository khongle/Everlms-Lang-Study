# S06 — SLO Per Service

> Service-Level Objectives cho từng runtime unit của EverLMS.
> EverLMS là monolith — SLO áp dụng ở mức service, không phải endpoint.

---

## SLO Files

| File | Service | DR Tier | Status |
|------|---------|---------|--------|
| `01-everlms-web.md` | everlms-web (Drupal app) | Tier 1 | PROPOSED |
| `02-everlms-cron.md` | Cron runner | Tier 2 | PROPOSED |
| `03-payment-webhook.md` | Stripe/PayPal webhook handler | Tier 1 | PROPOSED + BLOCKED (FIND-001, TASK-002) |
| `04-certificate-verify.md` | Public `/verify/{code}` | Tier 2 (brand-critical) | PROPOSED |
| `05-checkout-flow.md` | User-visible checkout funnel | Tier 1 | PROPOSED + BLOCKED (G-HARDENING) |

> Five SLOs cover all critical user-visible paths. The webhook and checkout SLOs are intentionally separate from `01-everlms-web.md` because their failure modes, traffic patterns, and consequences diverge meaningfully from "the whole app is up."

---

## SLO Philosophy

**Status: PROPOSED** — Không có production deployment nào tồn tại. SLO dưới đây là targets dùng để thiết kế infrastructure, chưa phải commitments đã được đo đạc.

Khi production được deploy:
1. Đo baseline metrics trong 2 tuần trước khi set SLO.
2. Review và tighten SLO sau 3 tháng vận hành.
3. Mọi thay đổi SLO phải có business sign-off (G01 ADR).

---

## Error Budget Concept

```
Error budget = 1 - SLO target
Example: 99.5% availability SLO → 0.5% error budget
         = ~3.6 hours downtime allowed per 30-day rolling window
```

Burn rate alert = khi error budget tiêu hết nhanh hơn dự kiến.
