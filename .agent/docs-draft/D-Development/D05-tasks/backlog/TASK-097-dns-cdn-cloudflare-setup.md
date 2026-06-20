# Task: DNS + CloudFlare CDN Setup

**ID:** TASK-097
**Unit:** — (infra: cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Cấu hình CloudFlare làm DNS authoritative + CDN layer cho `everlms.vn`. Bật WAF rules, rate limiting ở edge, HTTP/3 (QUIC), image transforms (Polish/Mirage). Thiết lập origin shielding để giảm load trực tiếp tới app server. Cấu hình cache rules: static assets cached tại edge, API endpoints + admin pages bypassed.

CloudFlare sẽ là điểm vào duy nhất cho traffic public. Origin server chỉ accept kết nối từ CloudFlare IPs (xác thực qua Authenticated Origin Pulls). Tham chiếu C01-design-spec cho CDN strategy.

## Acceptance Criteria
- [ ] DNS zone `everlms.vn` transferred/configured tại CloudFlare — nameservers updated
- [ ] Orange-cloud (proxied) records: `@`, `www`, `app` — gray-cloud cho `mail`, `smtp`
- [ ] HTTP/3 / QUIC bật tại CloudFlare dashboard
- [ ] WAF: CloudFlare Managed Ruleset bật (OWASP Core + CF Managed); mode = Block
- [ ] Rate limit rule: ≤60 req/min per IP cho `/api/*` và `/user/login`; action = challenge
- [ ] Image Polish + Mirage bật (lossy, WebP conversion)
- [ ] Cache rules:
  - [ ] Cache `*.css`, `*.js`, `*.woff2`, `*.jpg`, `*.png`, `*.webp` — TTL 1 tuần tại edge
  - [ ] Bypass cache: `/api/*`, `/admin/*`, `/user/*`, `/cart/*`, `/checkout/*`
  - [ ] Bypass cache: requests có cookie `SESS*` (Drupal session)
- [ ] Authenticated Origin Pulls bật: Nginx verify CloudFlare client cert
- [ ] CloudFlare IPs allowlist trên origin firewall (UFW rule cập nhật khi CF IP ranges thay đổi)
- [ ] `Page Speed` score từ CloudFlare Observatory documented

## Technical Notes
- CloudFlare plan: Pro (minimum) cho WAF + image optimization
- Drupal `reverse_proxy_addresses` trong `settings.php` cần bao gồm CF IPs để `$_SERVER['REMOTE_ADDR']` chính xác
- `X-Forwarded-For` header: Drupal phải tin tưởng từ CF, cấu hình trong `settings.php`
- Cache bypass cho session cookie: rule `Cookie contains SESS` → Cache Level: Bypass
- Terraform CloudFlare provider có thể quản lý rules as code (`cloudflare/terraform-provider-cloudflare`)
- Tham chiếu: `C01-design-spec.md` cho CDN strategy; `H01-infrastructure.md` để update topology

## Dependencies
- TASK-095 (prod server IP phải biết để config A record)
- TASK-096 (HTTPS phải hoạt động ở origin trước khi bật CF proxy)

## Definition of Done
- [ ] Code complete (Terraform/config files committed)
- [ ] G1-G4 gates passed
- [ ] WAF test: `curl -A "sqlmap"` bị block tại CF edge
- [ ] Cache test: static asset trả `CF-Cache-Status: HIT` sau lần thứ 2
- [ ] `H01-infrastructure.md` updated với CDN topology
- [ ] No new debt introduced
