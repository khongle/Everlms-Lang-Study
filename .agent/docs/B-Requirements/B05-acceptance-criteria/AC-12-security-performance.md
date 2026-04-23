# AC-12 — Security & Performance

**Feature ID:** F-012
**Epic:** EPIC-B / EPIC-H — Cross-cutting NFRs
**Epic Owner:** Admin / System
**Feature Doc:** [B03 F-012 Security & Performance](../B03-features/12-security-performance.md)

---

## Acceptance Criteria

### AC-SEC01 — Authentication & session security

**Priority:** Must
**Given** Any user attempting to access protected resources
**When** User is not authenticated or session expired
**Then** Redirect to login page; after login, redirect back to original destination
**And** Session cookie: HttpOnly, Secure, SameSite=Strict

### AC-SEC02 — Authorization — no privilege escalation

**Priority:** Must
**Given** Student cố access /admin routes hoặc instructor resources của người khác
**When** Request sent với student session
**Then** 403 Forbidden; không leak data về resource existence
**And** Drupal access hooks kiểm tra ownership cho tất cả CRUD

### AC-SEC03 — Input validation & XSS prevention

**Priority:** Must
**Given** Malicious user submit form với XSS payload `<script>alert('xss')</script>`
**When** Content saved và displayed
**Then** Script stripped/escaped; không execute trong browser
**And** Drupal text format filters applied consistently

### AC-SEC04 — CSRF protection

**Priority:** Must
**Given** All state-changing forms (checkout, progress, settings)
**When** Form submitted
**Then** CSRF token validated server-side; reject if missing/invalid
**And** Drupal form API token mechanism active

### AC-PERF01 — Page load times

**Priority:** Must
**Given** Production-like dataset (10K users, 200 courses)
**When** Average user loads common pages
**Then** Course catalog ≤2s (p95); Lesson page ≤2s; Dashboard ≤3s
**And** Drupal cache (render cache, dynamic page cache) enabled

### AC-PERF02 — Database query optimization

**Priority:** Must
**Given** Progress cascade update triggered
**When** Student completes lesson
**Then** Max 5 DB queries per mark-complete request
**And** No N+1 query patterns trong lesson listing hoặc progress calculations

---

## Edge Cases

| Case | Expected Behavior |
|------|-----------------|
| DDoS / flood requests | Rate limiting via reverse proxy; 429 responses |
| SQL injection attempt | Drupal parameterized queries prevent; log attempt |
| Expired session during checkout | Payment form re-validate session; không charge rồi mất session |
| Large concurrent logins (>1000) | Session storage (database/redis) scale accordingly |

## NFR Tie-ins

| NFR | Target |
|-----|--------|
| Security | OWASP Top 10 addressed: XSS, CSRF, SQLi, Auth, IDOR |
| Performance | Drupal cron không impact user-facing response times |
| Monitoring | Error rate < 0.1% (p99); alerts on >1% |
| Logging | All auth events, payment events, permission denials logged |
