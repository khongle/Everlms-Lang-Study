# Task: Security Regression Test — OWASP ZAP Baseline + Custom Assertions

**ID:** TASK-086
**Unit:** — (cross-cutting)
**Group:** G-HARDENING
**Priority:** P1
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Tích hợp OWASP ZAP baseline scan vào CI pipeline (TASK-069) như một automated security regression gate. ZAP chạy ở passive scan mode (baseline) trên mọi PR — block nếu xuất hiện High risk finding mới. Ngoài ra, viết custom assertions cho các attack vector cụ thể của EverLMS (IDOR trên progress/quiz, SQLi trên search, CSRF trên admin forms).

Suite này bổ sung cho pentest engagement (TASK-087) — ZAP là continuous guard, pentest là periodic deep dive.

## Acceptance Criteria
- [ ] OWASP ZAP baseline scan script tại `tests/security/zap-baseline.sh`
- [ ] CI job `security-scan` chạy ZAP Docker scan: `ghcr.io/zaproxy/zaproxy:stable zap-baseline.py`
- [ ] Block PR trên High risk finding mới (so với `tests/security/zap-baseline.json` snapshot)
- [ ] Custom PHPUnit assertions (SecurityRegressionTest):
  - [ ] IDOR test: Student A không thể access progress record của Student B
  - [ ] IDOR test: Student không thể xem quiz result của người khác
  - [ ] SQL injection: search endpoint với `' OR 1=1` → không có DB error, safe response
  - [ ] XSS: tên course với `<script>alert(1)</script>` → escaped trong output
  - [ ] CSRF: POST tới admin form không có CSRF token → 403
  - [ ] Open redirect: `?destination=https://evil.com` → rejected hoặc sanitized
- [ ] ZAP report: `tests/security/results/zap-report.html` — CI artifact
- [ ] False positive suppression: `tests/security/zap-false-positives.conf`
- [ ] Integrate với TASK-089 (CVE scan) trong cùng CI security stage

## Technical Notes
- ZAP Docker: `docker run --rm -v $(pwd)/tests/security:/zap/wrk ghcr.io/zaproxy/zaproxy:stable zap-baseline.py -t http://lms.ddev.site -r zap-report.html`
- Custom assertions path: `web/modules/custom/lms_base/tests/src/Kernel/Security/SecurityRegressionTest.php`
- IDOR test: tạo 2 user accounts, kiểm tra `GET /api/v1/progress/{id}` với session của user khác → 403
- CSRF: Drupal có built-in CSRF protection — test verify không bị bypass với empty token
- Search SQLi: `web/modules/custom/lms_course/src/Controller/SearchController.php` — verify dùng parameterized query
- Group annotation: `@group security-regression`

## Dependencies
- TASK-069 (CI pipeline — security stage)
- TASK-087 (pentest — findings feed custom assertion backlog)
- TASK-089 (CVE scan — same CI security stage)

## Definition of Done
- [ ] Code complete — ZAP script + custom SecurityRegressionTest
- [ ] G1-G4 gates passed
- [ ] CI blocking on new High ZAP findings
- [ ] 6 custom assertion categories implemented + passing
- [ ] False positive config documented
