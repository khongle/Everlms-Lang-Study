# Task: Nginx Config Hardening

**ID:** TASK-103
**Unit:** — (infra: cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Hardening Nginx config cho production EverLMS. TLS 1.2+ only với strong cipher suite. Bật gzip + brotli compression. Tăng `client_max_body_size` cho SCORM package uploads (≤500 MB). Rate limit zones cho login và API. Security headers align với TASK-090 (CSP, HSTS, X-Frame-Options). Cấu hình FastCGI cache cho anonymous page requests để giảm PHP-FPM load.

## Acceptance Criteria
- [ ] TLS: `ssl_protocols TLSv1.2 TLSv1.3;` — SSLv3, TLS 1.0, TLS 1.1 disabled
- [ ] Cipher suite: `ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:...` (Mozilla Intermediate)
- [ ] `ssl_session_cache shared:SSL:10m; ssl_session_timeout 1d; ssl_session_tickets off;`
- [ ] gzip bật: `gzip_types text/plain text/css application/javascript application/json image/svg+xml`
- [ ] Brotli module (ngx_brotli): `brotli_comp_level 6; brotli_types ...` (cùng types với gzip)
- [ ] `client_max_body_size 512m;` (SCORM packages có thể lớn)
- [ ] `client_body_timeout 300s; client_header_timeout 30s;`
- [ ] Rate limit zones:
  - [ ] `limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;` — áp dụng `/user/login`
  - [ ] `limit_req_zone $binary_remote_addr zone=api:10m rate=60r/m;` — áp dụng `/api/*`
- [ ] Security headers (align TASK-090):
  - [ ] `X-Frame-Options: SAMEORIGIN`
  - [ ] `X-Content-Type-Options: nosniff`
  - [ ] `Referrer-Policy: strict-origin-when-cross-origin`
  - [ ] `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- [ ] FastCGI cache: cache anonymous requests (không có `SESS*` cookie) cho 10s để absorb bursts
- [ ] `server_tokens off;` — không expose Nginx version

## Technical Notes
- Config files: `/etc/nginx/nginx.conf`, `/etc/nginx/sites-available/everlms.conf`
- Brotli: compile Nginx với `--add-module=ngx_brotli` hoặc dùng dynamic module
- FastCGI cache: `fastcgi_cache_path /var/cache/nginx/fastcgi levels=1:2 keys_zone=everlms:10m max_size=1g;`
- Cache bypass: `fastcgi_cache_bypass $cookie_SESS$cookie_everlms_session;`
- Drupal clean URLs: `try_files $uri /index.php?$query_string;`
- SCORM upload endpoint: `/api/lms-scorm/upload` hoặc admin upload — verify path
- Tham chiếu: Mozilla SSL Config Generator (Intermediate profile); TASK-090 CSP; TASK-096 TLS certs

## Dependencies
- TASK-096 (TLS certs phải tồn tại)
- TASK-090 (CSP headers — align không conflict)

## Definition of Done
- [ ] Code complete (Nginx config committed)
- [ ] G1-G4 gates passed
- [ ] SSL Labs A+ verified sau khi apply cipher config
- [ ] `curl -I https://everlms.vn` verify tất cả security headers present
- [ ] Rate limit test: `ab -n 20 -c 20 https://everlms.vn/user/login` → 429 responses
- [ ] No new debt introduced
