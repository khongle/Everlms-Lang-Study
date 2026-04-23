# S01 — Service Catalog

> Mọi runtime unit của EverLMS được liệt kê ở đây.
> EverLMS là monolithic deployment — một Drupal app duy nhất, không phải microservices.

---

## Services

| Service ID | Name | Runtime | Owner | Health Endpoint | DR Tier | Critical Path? |
|-----------|------|---------|-------|-----------------|---------|----------------|
| `everlms-web` | EverLMS Web Application | PHP-FPM 8.3 / Nginx 1.24+ | hainn | `/health` (PROPOSED) | Tier 1 | Yes |
| `everlms-cron` | Drupal Cron / Queue Worker | PHP CLI 8.3 / cron daemon | hainn | N/A (log-based) | Tier 2 | No |

---

## Service Details

### everlms-web

**Runtime:** PHP-FPM 8.3, served by Nginx 1.24+.
**Application:** Drupal 11, 26 custom modules, 29 entities.
**Database:** MariaDB 10.11 (single instance — local dev: DDEV; production: TBD VPS/k8s).
**Cache:** Redis (planned; currently Drupal file cache fallback).
**Frontend:** Server-rendered Twig 3.x + Bootstrap 5.3. No SPA.
**Health endpoint:** `GET /health`

> **Status: PROPOSED** — `/health` endpoint does not exist yet. Drupal's `system.ping` (`/system/ping`) returns `OK` and can serve as interim. A dedicated `/health` route returning JSON `{"status":"ok","db":"ok","cache":"ok"}` is recommended.

**What breaks when this service is down:** Everything. All user flows stop.

---

### everlms-cron

**Runtime:** PHP CLI 8.3, triggered by system cron (`crontab`).
**Role:** Runs Drupal's cron tasks and processes queue workers.
**Key queue workers:**
- `lms_ai`: `CourseContentGenerator` — AI-powered course content generation.
- Drupal core queues: mail queue, entity update queue.

**Health monitoring:** Check cron last-run time via `state.system.cron_last`.
**What breaks when this service is down:** AI queue jobs stall; scheduled notifications delayed. User-facing flows unaffected.

---

## Change Protocol

Adding or removing a service requires:
1. Update this table in the same PR as the deploy.
2. Add S06 SLO file for Tier 1/2 services.
3. Update S05 dependency matrix.
4. Confirm H02 runbook exists for alert scenarios.

---

## Not-Yet-Deployed Components

| Component | Purpose | Status |
|-----------|---------|--------|
| Redis | Cache backend + session store | PROPOSED |
| CDN (CloudFlare) | Static asset delivery | PROPOSED |
| Backup agent | Database + file backups | PROPOSED |
