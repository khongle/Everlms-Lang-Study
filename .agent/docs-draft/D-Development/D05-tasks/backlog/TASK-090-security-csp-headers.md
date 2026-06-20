# Task: Security Headers — CSP, HSTS, X-Frame-Options, Referrer-Policy

**ID:** TASK-090
**Unit:** — (cross-cutting)
**Group:** G-HARDENING
**Priority:** P1
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement đầy đủ security response headers cho EverLMS: Content Security Policy (report-only mode trước, enforce sau 2 tuần không có violation), HSTS, X-Frame-Options, X-Content-Type-Options, và Referrer-Policy. Implementation qua Drupal middleware hoặc `lms_theme` custom module — không hardcode trong `.htaccess` để dễ test + override.

Approach 2-phase cho CSP: (1) `Content-Security-Policy-Report-Only` với `report-uri` trong 2 tuần, (2) sau khi analyze violations → switch sang enforce mode.

## Acceptance Criteria
- [ ] **CSP (phase 1 — report-only):**
  - [ ] Header `Content-Security-Policy-Report-Only` present trên mọi response
  - [ ] CSP policy: `default-src 'self'; script-src 'self' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data: *.cloudfront.net; font-src 'self' fonts.gstatic.com; frame-ancestors 'none'; report-uri /csp-report`
  - [ ] `POST /csp-report` endpoint ghi violations vào Drupal watchdog
- [ ] **CSP (phase 2 — enforce, sau ≥2 tuần report-only):**
  - [ ] Switch tới `Content-Security-Policy` header
  - [ ] Violation count = 0 trên staging trước khi enforce production
- [ ] **Other headers (enforce ngay từ phase 1):**
  - [ ] `Strict-Transport-Security: max-age=31536000; includeSubDomains`
  - [ ] `X-Frame-Options: DENY`
  - [ ] `X-Content-Type-Options: nosniff`
  - [ ] `Referrer-Policy: strict-origin-when-cross-origin`
  - [ ] `Permissions-Policy: geolocation=(), microphone=(), camera=()`
- [ ] SecurityHeaders.com score ≥ A (documented screenshot)
- [ ] Bootstrap 5.3 CDN (nếu dùng) trong CSP allowlist
- [ ] Admin pages không bị broken bởi CSP (CKEditor, file manager)

## Technical Notes
- Implementation options (chọn 1):
  - Option A: Drupal Event Subscriber (`KernelEvents::RESPONSE`) trong `lms_base/src/EventSubscriber/SecurityHeadersSubscriber.php`
  - Option B: `.htaccess` + `web/sites/default/services.yml` middleware
  - Option A ưu tiên vì dễ test với PHPUnit kernel test
- CSP allowlist: Bootstrap CDN nằm tại `cdn.jsdelivr.net`; Google Fonts tại `fonts.gstatic.com`
- CKEditor 5 (Drupal 11 built-in) cần `blob:` trong `worker-src` cho spell-check worker
- CSP report endpoint: Drupal route trong `lms_base.routing.yml`
- Test: `curl -I https://staging.everlms.vn | grep -i "content-security"`

## Dependencies
- None (infrastructure task)
- TASK-086 (security regression — add CSP header assertion)

## Definition of Done
- [ ] Code complete — SecurityHeadersSubscriber + CSP report endpoint
- [ ] G1-G4 gates passed
- [ ] Tất cả 6 headers present trên production-equivalent staging
- [ ] SecurityHeaders.com score ≥ A
- [ ] Phase 2 timeline documented (2 tuần report-only minimum)
