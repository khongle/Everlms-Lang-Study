# Task: lms_mobile_api Module — Audit + Hardening + OpenAPI Spec

**ID:** TASK-074
**Unit:** new (raise after audit; likely extends U-057/U-058/U-066)
**Group:** G-HARDENING
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Module `lms_mobile_api` tồn tại tại `web/modules/custom/lms_mobile_api/` nhưng chưa được document, audit về security, hoặc covered bởi hardening tasks hiện tại (TASK-015..TASK-024 chủ yếu focus vào web API và core modules). Task này audit toàn bộ endpoints, apply cùng hardening standards, và produce OpenAPI 3.0 spec.

Kết quả audit cũng là input cho TASK-072 (mobile app strategy decision).

## Acceptance Criteria
- [ ] Audit report: list tất cả endpoints trong `lms_mobile_api.routing.yml` + controllers — document: path, method, auth scheme, input params, output format
- [ ] Input validation: tất cả endpoints có `RequestValidator` hoặc equivalent (per TASK-015 / U-057 standard)
- [ ] Auth hardening: tất cả non-public endpoints require authentication — token-based (verify scheme: session cookie vs API token vs OAuth)
- [ ] Race condition check: endpoints write (POST/PATCH/DELETE) cần transaction hoặc idempotency key (per TASK-017/U-059 standard)
- [ ] CSRF: stateless API endpoints dùng token auth (không cần CSRF); session-based endpoints có CSRF check (per TASK-024/U-066)
- [ ] Error handling: tất cả errors trả JSON `{"error": "...", "code": "..."}` nhất quán — không leak stack trace
- [ ] OpenAPI 3.0 spec tại `I-External/I01-public-api-spec/05-mobile-api.yaml` (hoặc `.md`) cover tất cả endpoints với: paths, parameters, request/response schemas, auth scheme, examples
- [ ] Security scan: không có endpoint cho phép anonymous access tới protected data
- [ ] Findings ghi vào `G-Governance/G02-risk-register.md` nếu có RISK/FIND mới

## Technical Notes
- Module path: `web/modules/custom/lms_mobile_api/`
- Routing: `lms_mobile_api.routing.yml` — đọc để list endpoints
- Controllers: `web/modules/custom/lms_mobile_api/src/Controller/`
- Auth scheme audit: check `_permission` và `_access` requirements trong routing.yml
- Input validation standard: tham khảo TASK-015 pattern trong `lms_api` module (nếu có `RequestValidator.php`)
- OpenAPI spec format: tham khảo `I-External/I01-public-api-spec/01-certificate-verify.md` cho style
- Race condition pattern: tham khảo TASK-017 / `lms_progress` `ProgressApiController.php`
- New RISK/FIND: follow format trong `G02-risk-register.md`

## Dependencies
- TASK-015 (U-057 API input validation — hardening standard reference)
- TASK-016 (U-058 Access control hardening — auth standard reference)
- TASK-017 (U-059 Race condition fix — transaction pattern reference)
- TASK-024 (U-066 CSRF audit — CSRF standard reference)

## Definition of Done
- [ ] Audit report committed (tại `G-Governance/G02-risk-register.md` và/hoặc separate doc)
- [ ] Tất cả identified findings hardened hoặc tracked trong risk register
- [ ] OpenAPI 3.0 spec file tạo và validated (openapi-generator hoặc swagger-cli validate)
- [ ] G1-G4 gates passed cho code changes
- [ ] Tests written + passing cho hardened endpoints
- [ ] TASK-072 (mobile strategy) có thể reference audit findings khi evaluate React Native vs PWA
