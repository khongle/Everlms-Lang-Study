# Task: Per-Endpoint Rate Limiting — Drupal Flood Control

**ID:** TASK-091
**Unit:** — (cross-cutting)
**Group:** G-HARDENING
**Priority:** P1
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement per-endpoint rate limiting dùng Drupal flood control service (`\Drupal\Core\Flood\FloodInterface`) cho các public endpoints nhạy cảm của EverLMS. Mỗi endpoint có quota riêng — document đầy đủ trong I06 (API limits reference). Thiết kế phải cho phép whitelist IP cho performance tests (TASK-084) và CI tests.

Rate limiting bổ sung cho brute-force login protection (TASK-092) — TASK-092 focus vào auth, TASK-091 focus vào API throughput/abuse prevention.

## Acceptance Criteria
- [ ] Flood control áp dụng cho 6 endpoint groups:
  - [ ] **Login** (`/user/login`, `/api/v1/auth/login`): 10 attempts/IP/hour; 5 attempts/user/15min
  - [ ] **Register** (`/user/register`): 5 registrations/IP/hour
  - [ ] **Password reset** (`/user/password`): 3 requests/IP/hour; 3/user/hour
  - [ ] **Search** (`/search`, `/api/v1/courses?search=`): 60 requests/IP/min
  - [ ] **Checkout** (`/checkout/*`, `POST /api/v1/orders`): 10 requests/user/hour
  - [ ] **Quiz submit** (`POST /quiz/*/submit`, `POST /api/v1/quiz/*/submit`): per `max_attempts` config + 30 req/IP/hour global cap
- [ ] Rate limit exceeded → HTTP 429 với `Retry-After` header
- [ ] Response body: `{"error": "rate_limit_exceeded", "retry_after": 3600}`
- [ ] IP whitelist: `$settings['rate_limit_whitelist']` trong `settings.php` (CIDR support)
- [ ] Quotas documented tại `.agent/docs/I-Interfaces/I06-api-limits.md`
- [ ] Test: PHPUnit kernel test verify 429 on exceed, 200 on reset
- [ ] Redis backend cho flood storage (faster than DB): `lms_base.services.yml` flood override

## Technical Notes
- Drupal flood service: `\Drupal::flood()` — methods `register()`, `isAllowed()`, `clear()`
- Custom service: `web/modules/custom/lms_base/src/Service/RateLimitService.php`
- Middleware hoặc EventSubscriber để intercept trước controller
- Redis flood: `drupal/redis` module cần flood backend — check `lms_base.services.yml`
- `Retry-After` header value = seconds until flood window resets
- Mobile API rate limit thêm vào `lms_mobile_api` controller layer (TASK-074)
- Log flood events: watchdog `lms_rate_limit` channel

## Dependencies
- TASK-074 (lms_mobile_api — rate limit integration)
- TASK-092 (brute force login — shared flood infrastructure)
- TASK-084 (performance tests — IP whitelist needed)

## Definition of Done
- [ ] Code complete — RateLimitService + 6 endpoint group configs
- [ ] G1-G4 gates passed
- [ ] 429 + Retry-After tested + working
- [ ] I06 limits document written
- [ ] Redis flood backend configured
