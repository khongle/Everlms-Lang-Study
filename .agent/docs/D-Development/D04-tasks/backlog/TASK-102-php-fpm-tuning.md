# Task: PHP-FPM Pool Tuning + OPcache/APCu Config

**ID:** TASK-102
**Unit:** — (infra: cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Tối ưu PHP-FPM 8.3 cho production EverLMS. Tính toán `pm.max_children` dựa trên RAM available (trừ MariaDB + OS overhead). Cấu hình OPcache để cache toàn bộ Drupal core + custom modules. Bật APCu cho Drupal cache backend nếu chưa có Redis. Tắt `display_errors`. Benchmark process memory size để xác định `pm.max_children` chính xác.

## Acceptance Criteria
- [ ] `pm = dynamic` với `pm.max_children` = `(available_RAM - 512MB) / avg_process_size`
- [ ] `pm.start_servers`, `pm.min_spare_servers`, `pm.max_spare_servers` calculated (tỷ lệ 10%/25%/75% max_children)
- [ ] `pm.max_requests = 500` (tránh memory leak accumulation)
- [ ] OPcache config tại `/etc/php/8.3/fpm/conf.d/10-opcache.ini`:
  - [ ] `opcache.memory_consumption = 256` (MB)
  - [ ] `opcache.max_accelerated_files = 20000`
  - [ ] `opcache.validate_timestamps = 0` (production — manual reset sau deploy)
  - [ ] `opcache.revalidate_freq = 0`
- [ ] APCu bật: `apc.shm_size = 64M` — verify qua `phpinfo()` hoặc `apcu_sma_info()`
- [ ] `display_errors = Off` và `log_errors = On` trong production `php.ini`
- [ ] `error_log` trỏ tới `/var/log/php8.3-fpm.log` (feed vào TASK-101)
- [ ] Benchmark: đo avg process memory size (`ps aux | grep php-fpm`) + document
- [ ] PHP-FPM status page bật: `pm.status_path = /status` (feed vào TASK-100)
- [ ] Load test sau tuning: 50 concurrent users, verify không có 502 errors

## Technical Notes
- Pool config: `/etc/php/8.3/fpm/pool.d/everlms.conf`
- OPcache warm-up sau deploy: `php -r "opcache_reset();"` hoặc `drush cr` (triggers include_once)
- `pm.max_children` calculation example: 8 GB RAM, 512 MB reserved for OS/MariaDB = 7.5 GB; avg PHP process = 50 MB → max_children = 150
- Nếu dùng Redis cho session + cache (TASK-104), APCu chỉ dùng cho PHP object cache
- Drupal settings: `$settings['php_storage_path']` cho compiled Twig templates
- Tham chiếu: `H01-infrastructure.md` cho server specs; TASK-100 cho PHP-FPM exporter

## Dependencies
- TASK-095 (prod server specs cần biết để calculate)
- TASK-100 (PHP-FPM status page feed vào monitoring)

## Definition of Done
- [ ] Code complete (pool config + opcache config committed)
- [ ] G1-G4 gates passed
- [ ] Benchmark results documented (avg process size, pm.max_children value)
- [ ] Load test results: 0 502 errors @ 50 concurrent users
- [ ] No new debt introduced
