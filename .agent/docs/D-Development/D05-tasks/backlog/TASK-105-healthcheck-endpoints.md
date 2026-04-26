# Task: Health-Check Endpoints (Liveness + Readiness)

**ID:** TASK-105
**Unit:** — (new — raise trong lms_base)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement hai health-check endpoints trong module `lms_base`: `/health/live` (liveness) và `/health/ready` (readiness). Liveness check trả về 200 OK nếu PHP process đang chạy. Readiness check kiểm tra kết nối DB, Redis, và file storage trước khi trả về 200. Load balancer (CloudFlare hoặc HAProxy) dùng readiness endpoint để route traffic. Monitoring stack (TASK-100) scrape cả hai.

Endpoints phải lightweight: không cần auth, không tạo Drupal session, không log vào watchdog để tránh noise.

## Acceptance Criteria
- [ ] Route `GET /health/live` → HTTP 200 `{"status":"ok","timestamp":"..."}` — luôn pass nếu PHP running
- [ ] Route `GET /health/ready` → HTTP 200 nếu tất cả checks pass; HTTP 503 nếu bất kỳ check fail
- [ ] Readiness checks:
  - [ ] DB: `SELECT 1` trên MariaDB kết nối — fail → 503
  - [ ] Redis: `PING` trên Redis connection — fail → 503 (non-blocking: timeout 200ms)
  - [ ] File storage: `is_writable(PublicStream::basePath())` — fail → 503
- [ ] Response body JSON có `checks` object chi tiết từng component:
  ```json
  {"status":"ready","checks":{"db":"ok","redis":"ok","files":"ok"}}
  ```
- [ ] Không có auth requirement (bypass Drupal access system)
- [ ] Không tạo Drupal session (không set cookie)
- [ ] Không log vào `watchdog` (tránh log spam từ LB health checks mỗi 10s)
- [ ] Response time ≤ 100ms (P95) dưới load bình thường
- [ ] Nginx: deny log cho 2 routes này (`access_log off;` trong location block)
- [ ] PHPUnit test: `HealthCheckControllerTest` cover cả happy path + DB fail + Redis fail scenarios

## Technical Notes
- Module: `web/modules/custom/lms_base/`
- Controller: `src/Controller/HealthCheckController.php`
- Route file: `lms_base.routing.yml`:
  ```yaml
  lms_base.health_live:
    path: '/health/live'
    defaults: { _controller: '\Drupal\lms_base\Controller\HealthCheckController::live' }
    requirements: { _access: 'TRUE' }
  lms_base.health_ready:
    path: '/health/ready'
    defaults: { _controller: '\Drupal\lms_base\Controller\HealthCheckController::ready' }
    requirements: { _access: 'TRUE' }
  ```
- Dùng `\Drupal::database()` cho DB check, `\Drupal::cache()` backend ping cho Redis
- Prometheus scrape: `blackbox_exporter` probe tới `/health/ready` → alert khi non-200

## Dependencies
- TASK-100 (monitoring cần endpoint này để scrape)
- TASK-104 (Redis phải configured để ready check hoạt động)

## Definition of Done
- [ ] Code complete (Controller + routing + tests committed)
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (`HealthCheckControllerTest` — unit + kernel)
- [ ] Nginx config: access_log off cho health endpoints (committed)
- [ ] Smoke test trên prod: `curl https://everlms.vn/health/ready` → 200
- [ ] No new debt introduced
