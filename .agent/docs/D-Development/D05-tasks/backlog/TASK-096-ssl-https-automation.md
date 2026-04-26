# Task: SSL/HTTPS Automation via Let's Encrypt

**ID:** TASK-096
**Unit:** — (infra: cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P0
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Tự động hóa cấp phát và gia hạn chứng chỉ SSL/TLS cho `everlms.vn` và `*.everlms.vn` dùng Let's Encrypt. Sử dụng Certbot với DNS-01 challenge (để support wildcard) hoặc cert-manager nếu dùng Kubernetes. Cấu hình auto-renew (cron hoặc systemd timer). Sau 30 ngày ổn định, bật HSTS preload header.

Tất cả traffic HTTP phải redirect sang HTTPS. Không có mixed content. TLS 1.2+ only (xem TASK-103 cho cipher suite chi tiết). Certificate phải cover cả apex domain và wildcard subdomain.

## Acceptance Criteria
- [ ] Let's Encrypt wildcard cert (`*.everlms.vn` + `everlms.vn`) được cấp thành công qua DNS-01 challenge
- [ ] Certbot auto-renew hoạt động: `certbot renew --dry-run` pass, cron/systemd timer configured
- [ ] Nginx config redirect `http://` → `https://` (301 permanent) cho tất cả vhosts
- [ ] SSL Labs score ≥ A (test tại `ssllabs.com/ssltest/`)
- [ ] Zero mixed content warnings trên top 5 pages (check với browser DevTools)
- [ ] HSTS header: `Strict-Transport-Security: max-age=86400` (1 ngày) ngay từ đầu; sau 30 ngày stable nâng lên `max-age=31536000; includeSubDomains; preload`
- [ ] HSTS preload submission documented (submit tới `hstspreload.org` sau 30 ngày)
- [ ] Renewal alert: monitoring alert khi cert expiry < 14 ngày (wire vào TASK-100)
- [ ] Cert paths và renewal config documented trong `H01-infrastructure.md`

## Technical Notes
- DNS-01 challenge yêu cầu API access vào DNS provider (CloudFlare nếu dùng TASK-097)
- Certbot plugin: `certbot-dns-cloudflare` hoặc tương đương
- Credentials cho DNS API phải lưu trong secrets manager, không hardcode
- Wildcard cert: `certbot certonly --dns-cloudflare -d everlms.vn -d '*.everlms.vn'`
- Nginx snippet tham chiếu cho TLS config: `/etc/nginx/snippets/ssl-params.conf`
- Drupal `trusted_host_patterns` trong `settings.php` phải include domain mới
- Xem TASK-103 cho Nginx TLS cipher và protocol config chi tiết

## Dependencies
- TASK-095 (prod server phải tồn tại)
- TASK-097 (CloudFlare DNS setup — nếu dùng CF cho DNS-01 challenge)

## Definition of Done
- [ ] Code complete (Nginx config + certbot scripts committed)
- [ ] G1-G4 gates passed
- [ ] SSL Labs A+ hoặc A documented với screenshot
- [ ] Runbook entry tại `H02-runbooks/RUN-XXX-ssl-renewal.md`
- [ ] Tests written + passing (smoke test HTTPS redirect)
- [ ] No new debt introduced
