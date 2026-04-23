# SLO: everlms-web

**Service:** EverLMS Web Application (`everlms-web`)
**DR Tier:** Tier 1 (primary user-facing service)
**Owner:** hainn
**SLO Version:** 0.1 (initial, pre-production)

> **Status: PROPOSED** — SLO targets are design goals, not measured commitments.
> No production data exists as of 2026-04-16. Metrics to be instrumented on first deploy.

---

## Service-Level Indicators (SLIs)

| SLI | Definition | Data Source (planned) |
|-----|-----------|----------------------|
| Availability | `successful_requests / total_requests` where 5xx = failure | Nginx access log or APM |
| Latency p95 | 95th percentile end-to-end response time (server-side) | PHP-FPM slowlog / APM |
| Error rate | `5xx_count / total_requests` | Nginx access log |

---

## Objectives (30-day rolling window)

| SLI | Target | Error Budget | Alert Threshold |
|-----|--------|--------------|-----------------|
| Availability | **99.5%** | 0.5% ≈ 3h 36m / month | Burn rate > 5× → investigate |
| Latency p95 | **< 800 ms** | 5% of requests may exceed | Burn rate > 10× → page |
| Error rate | **< 0.5%** | 0.5% of requests | Any spike > 1% sustained → alert |

---

## Rationale for Targets

- **99.5% availability:** Single-AZ, single-VPS deployment. No redundancy = cannot commit to 99.9%+. 3.6h/month downtime budget allows for planned maintenance windows.
- **p95 < 800ms:** Drupal with full page cache hit should render < 100ms. Without cache (logged-in user), typical Twig render + DB = 200–500ms. 800ms p95 allows for occasional slow entity loads and AI-assisted pages.
- **< 0.5% error rate:** Matches availability budget. PHP exceptions, Twig errors, missing entities are the primary 5xx sources.

---

## SLIs Not Yet Tracked

| SLI | Reason Not Tracked |
|-----|-------------------|
| Database query p95 latency | No query monitoring configured |
| Cache hit ratio | Redis not yet deployed |
| Queue worker lag | No queue monitoring configured |
| Cron last-run freshness | State value exists but no alert |

---

## Alert Wiring (PROPOSED)

When monitoring is configured:
- **Availability burn:** Alert when 2h rolling burn rate > 5× budget.
- **Latency p95 burn:** Alert when p95 > 1500ms for 10+ minutes.
- **Error rate spike:** Alert when 5xx rate > 1% for 5 minutes.

Alerts should page `hainn` via configured channel (Slack / PagerDuty / email).
Runbook: `H-Operations/H02-runbooks/everlms-web-slo-burn.md` (to be created).

---

## Reporting

- Review SLO status monthly once production metrics are available.
- Corrective actions for budget burns → tracked in tech-debt log.

---

## Change Protocol

- **Tightening SLO** (e.g., 99.5% → 99.9%): requires capacity proof (dedicated host, redundancy).
- **Loosening SLO**: requires business sign-off + ADR entry.
- **Deleting SLO**: only on service decommission.
