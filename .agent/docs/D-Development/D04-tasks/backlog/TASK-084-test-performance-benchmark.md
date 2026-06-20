# Task: Performance Benchmark Suite — k6 / wrk Baselines

**ID:** TASK-084
**Unit:** — (cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Thiết lập performance benchmark suite dùng k6 (primary) cho 5 critical paths của EverLMS. Capture P50/P95/P99 latency thresholds và throughput baselines. Suite chạy nightly trong CI (TASK-069) và alert nếu P95 vượt ngưỡng. Redis cache được enable, MariaDB 10.11 với production-equivalent dataset.

Mục tiêu: phát hiện regression performance sớm — không để production load test là lần đầu tiên phát hiện bottleneck.

## Acceptance Criteria
- [ ] k6 scripts tại `tests/performance/` cho 5 scenarios:
  - [ ] `course-detail.js` — `GET /courses/{slug}` (anonymous, cached): P95 < 200ms at 100 VU
  - [ ] `lesson-view.js` — `GET /courses/{slug}/learn/{lesson}` (authenticated): P95 < 400ms at 50 VU
  - [ ] `quiz-submit.js` — `POST /quiz/{id}/submit` (authenticated): P95 < 500ms at 30 VU
  - [ ] `progress-complete.js` — `POST /api/v1/progress/mark-complete`: P95 < 300ms at 50 VU
  - [ ] `checkout.js` — `POST /checkout/{id}/confirm` (authenticated): P95 < 800ms at 20 VU
- [ ] Thresholds được define trong `tests/performance/thresholds.json` (versioned)
- [ ] CI nightly job: chạy với 10% load (smoke) mỗi push; full load weekly
- [ ] Report output: `tests/performance/results/` — HTML report + `summary.json`
- [ ] Redis cache hiệu quả: cache hit rate >80% trên course-detail scenario
- [ ] Alert: Slack webhook khi P95 vượt threshold (CI env var `SLACK_PERF_WEBHOOK`)
- [ ] Baseline documented trong `tests/performance/BASELINES.md`

## Technical Notes
- Tool: k6 v0.50+ (docker image `grafana/k6`)
- DDEV: `ddev exec docker run --rm grafana/k6 run /scripts/course-detail.js`
- MariaDB 10.11: seed script tạo 100 courses, 1000 lessons, 5000 users trước khi benchmark
- Redis: verify via `SHOW STATUS LIKE 'cache_%'` hoặc Redis `INFO stats`
- Authentication trong k6: setup stage login → lấy session cookie → reuse trong VU
- Rate limit (TASK-091) phải được disable/whitelist trong perf test environment
- Metrics exported: `http_req_duration`, `http_req_failed`, `iterations`
- Thresholds example: `{ http_req_duration: ['p(95)<400'], http_req_failed: ['rate<0.01'] }`

## Dependencies
- TASK-069 (CI pipeline — nightly job)
- TASK-091 (rate limit — whitelist perf test IPs)

## Definition of Done
- [ ] Code complete — 5 k6 scripts + threshold config
- [ ] Baselines captured + documented
- [ ] CI nightly job configured
- [ ] Slack alert hoạt động
- [ ] G1-G4 gates passed
