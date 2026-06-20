# SLO: certificate-verify

**Service:** Public Certificate Verification Endpoint (`certificate-verify`)
**DR Tier:** Tier 2 (public-facing, not revenue-critical, but brand-critical)
**Owner:** hainn
**SLO Version:** 0.1 (initial, pre-production)

> **Status: PROPOSED** — targets below, not yet measured.

---

## Why a Dedicated SLO

The `/verify/{code}` endpoint is **public** (no auth), **cache-heavy** (certificate content is immutable once issued), and **brand-facing** (learners paste URL on LinkedIn / CVs).

It has a different traffic pattern from the main app:
- Bursty (spike around job-search seasons, cohort graduations)
- Cache-friendly (immutable unless revoked)
- Low write volume (revokes are rare)
- Public — needs better-than-authenticated availability

---

## Service-Level Indicators (SLIs)

| SLI | Definition | Data Source (planned) |
|-----|-----------|----------------------|
| Availability | 2xx/3xx rate excluding 404 (404 is valid for unknown codes) | Nginx access log |
| Latency p95 | End-to-end response time | Nginx log or APM |
| Cache hit ratio | `X-Cache: HIT` rate for verify URLs | Nginx cache log / CDN analytics |

---

## Objectives (30-day rolling window)

| SLI | Target | Error Budget | Alert Threshold |
|-----|--------|--------------|-----------------|
| Availability | **≥ 99.9%** | ~43 min / month | Burn rate > 5× → page |
| Latency p95 | **< 300 ms** | 5% may exceed | p95 > 500 ms for 10 min → warn |
| Cache hit ratio | **≥ 95%** | — | < 80% for 30 min → investigate |

---

## Rationale for Targets

- **99.9% availability (stricter than `everlms-web` 99.5%)** because verify is public and a dead link damages brand credibility disproportionately. Recruiter checking a candidate's cert doesn't know (or care) about internal issues.
- **p95 < 300ms** because the query path is a single indexed entity load + Twig render. Should be deeply cached; any slowness is a red flag.
- **Cache hit ratio ≥ 95%** because certificates are immutable. If hit rate drops, someone is busting cache incorrectly (e.g., forgetting cache tags on revoke).

---

## SLIs Not Yet Tracked

| SLI | Reason Not Tracked |
|-----|-------------------|
| Per-country latency (CDN edges) | No CDN configured yet |
| Revoke propagation time | Cache-tag invalidation not measured |

---

## Alert Wiring (PROPOSED)

- **Availability:** External uptime probe (5 min interval) against known-valid code + known-invalid-code pair.
- **Latency p95:** APM or log-based metric.
- **Cache miss alert:** `X-Cache: MISS` rate > 20% for 30 min → investigate (likely someone removed `#cache[tags]` from the render).

Runbook (to create): `H-Operations/H02-runbooks/cert-verify-slow.md`.

---

## Special Considerations

- **Revoked certificates** MUST invalidate cache immediately. Drupal cache tag: `user_certificate:{id}`. Revoke path must call `Cache::invalidateTags([...])`.
- **Unknown codes** (typo on URL) SHOULD return 404, not 500 — must be handled explicitly so 404 doesn't count against availability SLO.
- **Rate limiting:** If scraping attempts appear, enable per-IP rate limit at Nginx/CDN level. Public but not unlimited.

---

## Prerequisites to Activate

1. TASK-008 public cert verify route implemented.
2. Cache tags on `UserCertificate` render verified.
3. External uptime monitor (UptimeRobot / Pingdom / Datadog Synthetics).
4. CDN in front (CloudFront / Fastly) — optional but strongly recommended.

---

## Change Protocol

- Tightening availability to 99.95%: requires multi-region deployment.
- Loosening targets: requires business sign-off + ADR.

---

*End of SLO: certificate-verify*
