# Task: Public Status Page

**ID:** TASK-209
**Unit:** — (lms_status_page)
**Group:** G-OPERATIONS
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Build a public-facing status page at `/status` in the style of statuspage.io. It displays real-time health of all EverLMS system components (App, DB, Redis, Stripe, PayPal, AI providers, Zoom) and an incident history. Component health data is sourced from existing healthchecks (TASK-105) and monitoring metrics (TASK-100). No authentication required.

## Acceptance Criteria

- [ ] **Route:** `/status` — publicly accessible, no login required
- [ ] **Component list and health indicators:**
  - [ ] Components: Application, Database (MariaDB), Cache (Redis), Payment — Stripe, Payment — PayPal, AI — OpenAI/Anthropic, Video — Zoom, Email delivery
  - [ ] Status per component: Operational (green), Degraded (yellow), Partial Outage (orange), Major Outage (red)
  - [ ] Overall system status banner at top (worst-case of all components)
- [ ] **Health data source:**
  - [ ] Calls `HealthCheckService::getAll()` from TASK-105; results cached 30 seconds (Redis)
  - [ ] External services (Stripe, PayPal, Zoom) checked via their status APIs or lightweight ping; timeout 3s; failure = Degraded
  - [ ] Page still loads if a check throws; failed checks display as "Unknown" not error
- [ ] **Incident history:**
  - [ ] `StatusIncident` config entity: `title`, `components_affected` (array), `status` (investigating/identified/monitoring/resolved), `body` (timeline of updates), `started_at`, `resolved_at`
  - [ ] `/status` shows last 10 incidents (all statuses)
  - [ ] `/status/incidents/{id}` shows full incident detail + timeline
  - [ ] Admin creates/updates incidents at `/admin/config/lms/status/incidents`
- [ ] **Auto-refresh:** page JS polls `/api/lms/status` every 30 seconds; updates component statuses without full reload
- [ ] **Meta tags:** `<meta name="robots" content="noindex">` — do not index this page
- [ ] PHPUnit test: component aggregation logic; "worst status wins" for overall banner

## Technical Notes

- New module: `web/modules/custom/lms_status_page/`
- Controller: `StatusPageController` (public route, no `_user_is_logged_in` requirement)
- JSON API endpoint: `GET /api/lms/status` → `{"overall": "operational", "components": [{...}], "last_updated": "..."}`
- `StatusIncident` config entity stored in `lms_status_page` config
- External service ping: `GuzzleHttp\Client` with 3s `connect_timeout`; catch `ConnectException`
- Cache: `\Drupal::cache('data')->set('lms_status_page_checks', $data, time() + 30)`
- Twig template: `lms_status_page/templates/status-page.html.twig`
- CSS: use Drupal theme color variables; no external CDN dependencies

## Dependencies

- TASK-100 (monitoring — metrics source)
- TASK-105 (health checks — `HealthCheckService`)

## Definition of Done

- [ ] Code complete — controller, JSON endpoint, incident entity + admin, Twig template, JS refresh
- [ ] G1-G4 gates passed
- [ ] Page accessible without login; 30s cache verified via `Age` response header
- [ ] Incident creation → appears on `/status` within 30 seconds
- [ ] Tests written + passing
- [ ] No new debt introduced
