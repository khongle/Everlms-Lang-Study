# Task: Internal OWASP Top 10 (2021) Audit

**ID:** TASK-088
**Unit:** — (cross-cutting)
**Group:** G-HARDENING
**Priority:** P1
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Thực hiện internal audit theo OWASP Top 10 (2021) cho toàn bộ EverLMS codebase. Mỗi trong 10 category được review riêng với findings và mitigations. Audit này có scope rộng hơn các FINDs trong risk register hiện tại (G02) — bao quát toàn bộ 10 threat categories một cách có hệ thống.

Output: document tại `.agent/docs/S-Security/S01-owasp-audit-2021.md` với checklist trạng thái Pass/Fail/Partial cho từng category. Findings mới → task mới trong backlog.

## Acceptance Criteria
- [ ] **A01 Broken Access Control:** Kiểm tra IDOR trên tất cả entity CRUD; role matrix vs. code alignment
- [ ] **A02 Cryptographic Failures:** Verify password hashing (bcrypt/Argon2); sensitive data at rest/transit; no plaintext secrets in code
- [ ] **A03 Injection:** SQL (search, filter params); LDAP/Command injection N/A; Twig autoescape verify
- [ ] **A04 Insecure Design:** Enrollment access model; payment flow threat model; certificate issuance design
- [ ] **A05 Security Misconfiguration:** Drupal settings.php (`$settings['hash_salt']`, error display off); file permissions; debug modules off in prod
- [ ] **A06 Vulnerable Components:** Composer audit output; Drupal core + contrib version pinning; JS deps via `npm audit`
- [ ] **A07 Auth & Session Failures:** Login brute force (→ TASK-092); session timeout (→ TASK-094); password policy
- [ ] **A08 Software & Data Integrity Failures:** Composer integrity (verify sigs); CI pipeline integrity; file upload validation
- [ ] **A09 Logging & Monitoring Failures:** Audit log coverage (admin actions, failed logins, payment events)
- [ ] **A10 SSRF:** Check any URL-fetching code (course import, thumbnail fetch) untuk SSRF vectors
- [ ] Mỗi category: documented với evidence + Pass/Fail/Partial + mitigation reference
- [ ] Findings mới không trong G02 risk register → task draft tạo sẵn
- [ ] Audit doc committed tại `.agent/docs/S-Security/S01-owasp-audit-2021.md`

## Technical Notes
- OWASP reference: https://owasp.org/Top10/
- Code review focus modules: `lms_base` (auth), `lms_course` (access), `lms_payment`, `lms_mobile_api`
- SQL injection check: tìm raw `db_query()` calls không dùng placeholders
  - `grep -r "db_query" web/modules/custom/`
  - Check `SelectInterface` usage vs. raw concatenation
- A05 settings check: `web/sites/default/settings.php`, `settings.local.php`
- A06: chạy `composer audit` + `npm audit` + xem kết quả TASK-089
- Logging: `web/modules/custom/lms_*/src/EventSubscriber/` — check audit log subscribers

## Dependencies
- TASK-089 (CVE scan — feeds A06 section)
- TASK-086 (security regression — absorb A01/A03 findings)
- TASK-092 (brute force — A07 mitigation)

## Definition of Done
- [ ] 10 OWASP category sections documented
- [ ] G1-G4 gates passed
- [ ] All new Critical findings: task drafted
- [ ] Audit doc committed + SA reviewed
- [ ] No unmitigated Critical findings remaining
