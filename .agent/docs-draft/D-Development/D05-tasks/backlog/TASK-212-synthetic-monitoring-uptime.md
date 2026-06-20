# Task: Synthetic Monitoring (Uptime Checks) and Alerts

**ID:** TASK-212
**Unit:** — (H07-observability-as-code documentation + infra config)
**Group:** G-OPERATIONS
**Priority:** P1
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Set up external synthetic monitoring that pings the `/health/ready` endpoint (TASK-105) every minute. Alert fires after 2 consecutive failures. Document the configuration as code in `H07-observability-as-code/`. Preferred tool: self-hosted Uptime Kuma (Docker Compose service); alternative: UptimeRobot or Better Uptime if self-hosting is not feasible.

## Acceptance Criteria

- [ ] **Monitor configured:**
  - [ ] Target URL: `https://{SITE_DOMAIN}/health/ready`
  - [ ] Check interval: 60 seconds
  - [ ] Alert threshold: 2 consecutive failures before alert fires
  - [ ] HTTP method: GET; expected status: 200; expected response body contains `"status":"ok"`
- [ ] **Uptime Kuma (preferred path):**
  - [ ] Docker Compose service added to `docker-compose.monitoring.yml` (separate from app compose)
  - [ ] Persistent volume for Kuma DB; exposed on internal port only (not public)
  - [ ] Nginx proxy pass `/uptime/` to Kuma (authenticated path)
  - [ ] Kuma monitor exported as JSON config file: `.agent/infra/uptime-kuma-monitors.json`
- [ ] **Alert channels:**
  - [ ] Channel 1: Email to ops team (configurable via env var `ALERT_EMAIL`)
  - [ ] Channel 2: Slack webhook (env var `SLACK_WEBHOOK_URL`) — if available
  - [ ] Alert message includes: monitor name, failure time, last response code, URL
- [ ] **Documentation in `H07-observability-as-code/`:**
  - [ ] New file `H07-observability-as-code/synthetic-monitoring.md` covering: setup steps, monitor config, alert channels, how to add a new monitor
  - [ ] `uptime-kuma-monitors.json` committed to `.agent/infra/`
- [ ] **Verification:**
  - [ ] Temporarily take `/health/ready` offline (return 503) → confirm alert fires within 3 minutes
  - [ ] Restore → confirm recovery alert fires

## Technical Notes

- Uptime Kuma Docker image: `louislam/uptime-kuma:1`
- Docker Compose file: `docker-compose.monitoring.yml` in project root (not merged with app compose to keep separation)
- Kuma REST API (`/api/monitor`) can be used to import `uptime-kuma-monitors.json` programmatically on startup via init script
- Alternative (UptimeRobot): use their free tier (50 monitors, 5min interval); config documented in `synthetic-monitoring.md` with API key stored in `.env.local` (gitignored)
- `/health/ready` must return JSON `{"status":"ok"}` — verify TASK-105 response format matches

## Dependencies

- TASK-105 (health checks — the pinged endpoint)
- TASK-100 (monitoring stack — Kuma can optionally push metrics to Prometheus push gateway)

## Definition of Done

- [ ] Monitor running and checking every 60s
- [ ] Alert fires on 2 consecutive failures (tested)
- [ ] `synthetic-monitoring.md` committed to `H07-observability-as-code/`
- [ ] `uptime-kuma-monitors.json` committed to `.agent/infra/`
- [ ] G1-G4 gates passed (documentation + infra config review)
- [ ] No new debt introduced
