# SLO: everlms-cron

**Service:** EverLMS Cron Runner (`everlms-cron`)
**DR Tier:** Tier 2 (degraded tolerance — no immediate user impact)
**Owner:** hainn
**SLO Version:** 0.1 (initial, pre-production)

> **Status: PROPOSED** — SLO targets are design goals, not measured commitments.
> Tracked by `system.cron_last` Drupal state; no external probing configured.

---

## Why a Dedicated SLO

Cron runs background tasks critical to operational correctness:
- Notification dispatch (email digest, reminders)
- Progress expiration / re-cascade for stalled learners
- Stripe/PayPal webhook retry (when implemented — FIND-001)
- Token cleanup (password reset, CSRF)
- Session garbage collection
- Search index rebuild (Drupal Search API)
- SLA timers for pending essay grading

When cron drifts > 1h beyond schedule, user-visible symptoms appear (delayed emails, stale search results) — still Tier 2 because no payment or auth path depends on cron synchronously.

---

## Service-Level Indicators (SLIs)

| SLI | Definition | Data Source (planned) |
|-----|-----------|----------------------|
| Freshness | `now() - system.cron_last` | Drupal state value (polled from external monitor) |
| Success rate | `successful_runs / total_runs` per 24h | Drupal dblog `type='cron'` severity filter |
| Duration p95 | 95th percentile runtime per invocation | Custom timing log |

---

## Objectives (30-day rolling window)

| SLI | Target | Error Budget | Alert Threshold |
|-----|--------|--------------|-----------------|
| Freshness | ≥ 98% of polls show `cron_last < 30 min` | 2% may exceed (configured schedule every 15 min) | Freshness > 60 min for 2 consecutive polls → warn; > 120 min → page |
| Success rate | **≥ 99%** | ~7 failed runs / month (if 4 per hour) | 3+ consecutive failures → alert |
| Duration p95 | **< 300s** (5 min) | — | p95 > 600s for 10+ min → investigate (blocking task in queue) |

---

## Rationale for Targets

- **Freshness 30 min target** because schedule is every 15 min → one missed run = 30 min max drift before next should fire. >60 min means back-to-back misses.
- **99% success rate** leaves room for transient DB/network blips; sustained failures indicate a broken cron task that SA must triage.
- **p95 < 300s** because cron runs as PHP-CLI with `max_execution_time = 600s` default. A cron taking > 5 min usually means a queue-worker task (search index, bulk mail) should be split off.

---

## SLIs Not Yet Tracked

| SLI | Reason Not Tracked |
|-----|-------------------|
| Per-task duration | Drupal cron doesn't emit per-hook timing by default |
| Queue depth | Queue worker lag not instrumented (see H07 SLO) |
| Memory per run | CLI memory sampling not configured |

---

## Alert Wiring (PROPOSED)

When monitoring configured:
- **Freshness alert:** External check (every 5 min) compares `/admin/reports/status` "Cron maintenance tasks" timestamp to now.
- **Success alert:** Query `watchdog WHERE type='cron' AND severity=3` in last 1h > 0 → alert.
- **Duration alert:** Custom timing hook wraps `\Drupal::service('cron')->run()`.

Runbook: `H-Operations/H02-runbooks/cron-not-running.md` (to be created — currently referenced only).

---

## Reporting

- Weekly cron health summary in ops review.
- Monthly review of stale tasks (tasks that keep failing).

---

## Change Protocol

- **Tightening freshness SLO** (30 min → 15 min) requires reducing schedule interval or adding external triggered runs.
- Any change to cron task set (add/remove) should re-evaluate this SLO and update here.

---

*End of SLO: everlms-cron*
