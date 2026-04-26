# Task: API Contract Tests — I01 Public API + lms_mobile_api

**ID:** TASK-082
**Unit:** — (cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P2
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Xây dựng API contract test suite cho toàn bộ public API endpoints: I01 REST/JSON:API layer và `lms_mobile_api` endpoints (được hardened trong TASK-074). Approach: OpenAPI schema-driven validation — generate request/response pairs từ OpenAPI spec, assert actual responses conform to schema. Dùng Dredd hoặc custom PHPUnit + JsonSchema validator.

Contract tests đảm bảo API không bị breaking change silently khi refactor backend. Đặc biệt quan trọng khi mobile app (TASK-072) consume `lms_mobile_api`.

## Acceptance Criteria
- [ ] OpenAPI spec tại `.agent/docs/I-Interfaces/I01-api-spec.yaml` (hoặc tương đương) được dùng làm source of truth
- [ ] Contract tests cover tất cả endpoints trong `lms_mobile_api`:
  - [ ] `POST /api/v1/auth/login` — response schema validated
  - [ ] `GET /api/v1/courses` — pagination, filter params
  - [ ] `GET /api/v1/courses/{id}` — course detail schema
  - [ ] `GET /api/v1/courses/{id}/lessons` — lesson list
  - [ ] `POST /api/v1/progress/mark-complete` — request + response schema
  - [ ] `GET /api/v1/quiz/{id}` — quiz schema (questions, options)
  - [ ] `POST /api/v1/quiz/{id}/submit` — submit + result schema
  - [ ] `GET /api/v1/notifications` — notification list
  - [ ] `GET /api/v1/profile` — profile schema
- [ ] Mỗi endpoint: test 200 (success), 4xx (error codes), schema mismatch = CI fail
- [ ] Breaking change detection: nếu field bị remove/rename → test fail với clear error message
- [ ] Authentication: Bearer token tested (valid + expired + missing cases)
- [ ] Run trong CI (TASK-069) gate G2

## Technical Notes
- Module: `lms_mobile_api`
- Test tool options: Dredd CLI (reads OpenAPI) hoặc PHPUnit + `justinrainbow/json-schema`
- OpenAPI spec location: xác định từ I-Interfaces layer docs
- Nếu dùng PHPUnit: test path `web/modules/custom/lms_mobile_api/tests/src/Functional/`
- Rate limiting (từ TASK-091) phải được bypass trong contract test (test mode header)
- Auth token: `lms_mobile_api/src/Authentication/` — dùng test API key trong `phpunit.xml`
- Group annotation: `@group api-contract`

## Dependencies
- TASK-074 (lms_mobile_api hardening — endpoint list finalized)
- TASK-069 (CI integration)
- TASK-091 (rate limit — bypass mechanism for tests)

## Definition of Done
- [ ] Code complete — contract tests cho 9+ endpoints
- [ ] G1-G4 gates passed
- [ ] OpenAPI spec validated as parseable
- [ ] Breaking change scenario tested + produces clear fail message
- [ ] CI integrated + gate G2 enforced
