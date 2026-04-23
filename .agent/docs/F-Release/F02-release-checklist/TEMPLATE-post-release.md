# Post-Release Checklist — v{VERSION}

> Complete within 24 hours after production deploy.
> Feeds into M04 post-release monitoring (7-day watch window).

---

**Release:** v{VERSION}
**Deploy Date/Time:** {YYYY-MM-DD HH:MM} UTC
**Release Manager:** {SA / Agent_PM}
**Deploy Method:** {rolling / blue-green / maintenance window}
**Feature Flag State at Deploy:** All OFF / See flag table below

---

## Smoke Tests (immediately post-deploy)

Execute within 15 minutes of deploy completion.

| Check | Expected | Actual | Pass? |
|-------|----------|--------|-------|
| Health endpoint: `GET {prod_url}/health` | 200 OK + `{"status":"ok"}` | | [ ] |
| Homepage loads | 200, no PHP errors | | [ ] |
| User login flow | Login succeeds, session created | | [ ] |
| Course listing page | Lists courses, no 500 | | [ ] |
| Lesson content load | Lesson renders correctly | | [ ] |
| Quiz access | Quiz loads, attempt can be started | | [ ] |
| Admin dashboard | `/admin` accessible to admin role | | [ ] |
| Drush status | `drush status` shows connected DB + bootstrap OK | | [ ] |

---

## Error Rate Baseline (first 60 minutes)

- [ ] Error rate at T+15min: `____%` (acceptable: < `{threshold}%`)
- [ ] Error rate at T+30min: `____%`
- [ ] Error rate at T+60min: `____%`
- [ ] PHP error log: no new fatal errors (`drush watchdog:show --severity=error`)
- [ ] 4xx rate stable: no unexpected spike in 404/403/422
- [ ] 5xx rate: `___` per minute (threshold: < `{N}` per minute)

**Verdict at T+60:** GREEN (proceed) | YELLOW (monitor) | RED (rollback)

---

## Alert Mute Revert

- [ ] Deploy-window alert suppressions reverted (if any muted during deploy)
- [ ] All H07 dashboards active and showing data
- [ ] All H07 alerts active (test alert fired and received: [ ])
- [ ] On-call pager active for next 48 hours
- [ ] Log aggregation verified: logs flowing to `{logging system}`

---

## Feature Flag State Post-Deploy

| Flag ID | Feature | State at Deploy | First Rollout Target | Scheduled By |
|---------|---------|----------------|---------------------|-------------|
| FF_{TYPE}_{ID} | {feature} | OFF | {date / sprint} | SA |

- [ ] All flags confirmed OFF in H05
- [ ] Rollout schedule communicated to SA
- [ ] First rollout cohort date set in calendar

---

## Monitoring Baseline

Capture baseline metrics immediately post-deploy for the M04 watch window.

| Metric | Baseline Value | Threshold | Source |
|--------|---------------|-----------|--------|
| API latency p95 | ___ms | < {N}ms | H07 dashboard |
| API latency p99 | ___ms | < {N}ms | H07 dashboard |
| Error rate | ___% | < {N}% | H07 dashboard |
| DB connections active | ___ | < {N} | H07 dashboard |
| Cache hit rate | ___% | > {N}% | H07 dashboard |
| Memory usage | ___MB | < {N}MB | H07 dashboard |

---

## Documentation Updates

- [ ] F03 changelog: add deploy date and any last-minute notes
- [ ] A-Project-Information/A03-project-context.md: update phase to MAINTENANCE (if first production release)
- [ ] D04 session status: append release event
- [ ] H01 incident log: document any issues during deploy (if none, note "deploy clean")
- [ ] H05 feature flags: update "last deployed" date for all flags

---

## Retrospective Scheduled

- [ ] Retro meeting scheduled: {YYYY-MM-DD HH:MM}
- [ ] Retro file pre-created: `E-Quality/E07-retrospectives/release-v{VERSION}.md`
- [ ] Participants confirmed: SA + Agent_PM (+ Agent_Coder if issues arose)

---

## 7-Day Watch Window

Hand off to M04-post-release-monitoring.md at this point.

- [ ] M04 Day-0 checklist started
- [ ] Monitoring owner confirmed: {Agent_PM / SA}
- [ ] Escalation path confirmed (per A05)

---

## Sign-Off

| Role | Name | Signed | Date/Time |
|------|------|--------|-----------|
| SA | {name} | [ ] | {date} |
| Agent_PM | Agent | [ ] | {date} |

**Release Status:** STABLE | MONITORING | ROLLED_BACK

---

*Monitoring protocol: M04-post-release-monitoring.md. Incident protocol: A05. Alert reference: H07.*
