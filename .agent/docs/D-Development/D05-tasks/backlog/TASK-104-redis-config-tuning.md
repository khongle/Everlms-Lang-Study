# Task: Redis Config Tuning + DB Segmentation

**ID:** TASK-104
**Unit:** — (infra: cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Tối ưu Redis config cho production EverLMS. Cấu hình `maxmemory` + LRU eviction policy phù hợp cho cache layer. Tắt `appendonly` (AOF không cần thiết cho cache). Phân tách logical DB: DB 0 cho Drupal cache, DB 1 cho session, DB 2 cho queue worker. Cấu hình persistence (RDB snapshot) cho session DB. Chuẩn bị config cho Redis replication (HA) trong tương lai.

## Acceptance Criteria
- [ ] `maxmemory` set = 80% available RAM (ví dụ 1 GB node → `maxmemory 800mb`)
- [ ] `maxmemory-policy allkeys-lru` (evict ít-dùng nhất khi đầy — phù hợp cache)
- [ ] `appendonly no` — AOF disabled cho cache DB (DB 0)
- [ ] RDB snapshot cho session DB (DB 1): `save 900 1` (nếu >1 key changed trong 15 phút)
- [ ] DB phân tách trong `settings.php`:
  - [ ] DB 0: Drupal cache (`$settings['redis.connection']['database'] = 0`)
  - [ ] DB 1: Session (`PhpRedis::database = 1`)
  - [ ] DB 2: Queue (Drupal queue worker backend)
- [ ] `requirepass` bật với strong password (lưu trong secrets manager)
- [ ] `bind 127.0.0.1 <app_server_private_ip>` — không bind `0.0.0.0`
- [ ] `protected-mode yes`
- [ ] `slowlog-log-slower-than 10000` (10ms) — feed vào TASK-101 monitoring
- [ ] `latency-monitor-threshold 100` (100ms) — bật latency tracking cho TASK-100
- [ ] Drupal Redis module: `composer require drupal/redis` + cấu hình trong `settings.php`
- [ ] Verify: `redis-cli -n 0 DBSIZE` trả về số keys cache sau warm-up; hit rate > 80% sau 1h

## Technical Notes
- Redis config file: `/etc/redis/redis.conf`
- Drupal module: `drupal/redis` — sử dụng PhpRedis extension (không phải Predis)
- `settings.php` snippet:
  ```php
  $settings['redis.connection']['interface'] = 'PhpRedis';
  $settings['redis.connection']['host'] = '127.0.0.1';
  $settings['redis.connection']['port'] = 6379;
  $settings['redis.connection']['password'] = getenv('REDIS_PASSWORD');
  $settings['cache']['default'] = 'cache.backend.redis';
  ```
- Replication prep: `replica-lazy-flush yes; replica-read-only yes` — comment out, uncomment khi add replica
- Tham chiếu: TASK-100 (redis_exporter sẽ scrape metrics); TASK-021 (cache strategy)

## Dependencies
- TASK-095 (Redis node phải exist)
- TASK-021 (cache strategy — align với cache bin config)

## Definition of Done
- [ ] Code complete (Redis config + Drupal settings snippets committed)
- [ ] G1-G4 gates passed
- [ ] Redis CLI verify: correct maxmemory, correct eviction policy
- [ ] Drupal cache working: `drush cr` → cache rebuild dùng Redis backend
- [ ] No new debt introduced
