# Task: Log Aggregation — Loki + Promtail

**ID:** TASK-101
**Unit:** — (infra: cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Thiết lập log aggregation tập trung dùng Grafana Loki + Promtail. Ship các log sources sau: Drupal `watchdog` (via Drupal logger → file → Promtail), Nginx access log + error log, PHP-FPM error log, MariaDB slow query log. Retention 30 ngày. Search UI trong Grafana cho SA và Dev Lead.

Structured logging: Drupal watchdog phải log dưới dạng JSON (dùng `syslog` module + JSON formatter hoặc custom logger service). Nginx log format phải là JSON để dễ parse.

## Acceptance Criteria
- [ ] Loki instance running (self-hosted hoặc Grafana Cloud Loki)
- [ ] Promtail cài trên app server + DB server, scraping:
  - [ ] Drupal watchdog: `/var/log/drupal/watchdog.log` (JSON format)
  - [ ] Nginx access log: `/var/log/nginx/access.log` (JSON format)
  - [ ] Nginx error log: `/var/log/nginx/error.log`
  - [ ] PHP-FPM error log: `/var/log/php8.3-fpm.log`
  - [ ] MariaDB slow query log: `/var/log/mysql/slow.log` (threshold: `long_query_time = 1`)
- [ ] Grafana Loki datasource configured; LogQL queries working
- [ ] Drupal logger: `syslog` module bật; custom JSON formatter hoặc `drupal/monolog` module
- [ ] Nginx log format JSON: `log_format json_combined '{"time":"$time_iso8601",...}'`
- [ ] Labels/metadata: `{host="app-01", app="everlms", env="prod"}` trên tất cả log streams
- [ ] Retention: Loki compaction rule xóa logs > 30 ngày
- [ ] Grafana dashboard "Logs Overview": error rate per service, top error messages, slow query heatmap
- [ ] Search UI accessible cho SA + Dev Lead role trong Grafana (RBAC)
- [ ] Alert: Loki alert khi Drupal `severity >= critical` xuất hiện > 5 lần/5 phút

## Technical Notes
- Drupal module `syslog`: route watchdog tới `/var/log/drupal/watchdog.log`
- Hoặc: `composer require drupal/monolog` + channel config trong `services.yml`
- Promtail pipeline stages: `json` stage để parse JSON logs, `labels` stage để extract severity
- MariaDB slow query: `SET GLOBAL slow_query_log = 'ON'; SET GLOBAL long_query_time = 1;` — persist trong `my.cnf`
- Loki retention: `retention_period: 720h` trong `loki-config.yaml`
- Self-hosted Loki sizing: 1 vCPU, 2 GB RAM cho ≤5 GB/day ingestion
- Tham chiếu: `H07-observability-as-code/` cho existing alert format

## Dependencies
- TASK-100 (Grafana phải exist để add Loki datasource)
- TASK-095 (prod servers)

## Definition of Done
- [ ] Code complete (Promtail configs, Nginx log format, Drupal logger config committed)
- [ ] G1-G4 gates passed
- [ ] Runbook tại `H02-runbooks/RUN-011-log-aggregation.md`
- [ ] Tests written + passing (generate test log entries, verify appear in Grafana within 60s)
- [ ] No new debt introduced
