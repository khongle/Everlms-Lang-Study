# Task: Monitoring Agent — Grafana + Prometheus Stack

**ID:** TASK-100
**Unit:** — (infra: cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P0
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Cài đặt monitoring agent trên app server và DB server. Scrape PHP-FPM stats, MariaDB metrics, Redis metrics, và system metrics (CPU, RAM, disk, network). Wire tất cả vào Grafana Cloud (hoặc self-hosted Grafana) với Prometheus làm data source. Import và customize initial dashboards từ `H07-observability-as-code/` (hiện có 2 dashboards, 3 alerts, 3 SLOs).

Đây là foundation cho toàn bộ observability stack — TASK-098 alerts, TASK-101 log correlation, và TASK-105 healthcheck monitoring đều phụ thuộc vào stack này.

## Acceptance Criteria
- [ ] `node_exporter` v1.7+ cài trên cả app server + DB server; metrics accessible tại `:9100/metrics`
- [ ] `php-fpm_exporter` cài trên app server; scrape PHP-FPM status page; metrics `:9253/metrics`
- [ ] `mysqld_exporter` v0.15+ cài trên DB server với user `exporter` (GRANT SELECT, PROCESS, REPLICATION CLIENT)
- [ ] `redis_exporter` cài trên Redis node; metrics `:9121/metrics`
- [ ] Prometheus scrape config bao gồm tất cả 4 exporters với 15s interval
- [ ] Grafana dashboards imported từ `H07-observability-as-code/dashboards/`:
  - [ ] System overview (CPU, RAM, disk, network per host)
  - [ ] Application dashboard (PHP-FPM queue, request rate, error rate)
  - [ ] Database dashboard (QPS, connections, slow queries, replication lag)
  - [ ] Redis dashboard (hit rate, evictions, memory usage, connected clients)
- [ ] 3 existing SLOs từ H07 configured trong Grafana (availability, latency, error rate)
- [ ] 3 existing alerts từ H07 firing correctly: test bằng cách simulate threshold breach
- [ ] Alert routing: PagerDuty hoặc email tới SA + Dev Lead cho P0 alerts
- [ ] Retention: Prometheus data 30 ngày; Grafana Cloud hoặc remote_write configured

## Technical Notes
- PHP-FPM status page: enable trong `www.conf` — `pm.status_path = /status`
- Nginx: expose `/fpm-status` chỉ cho internal IPs (127.0.0.1, monitoring server IP)
- mysqld_exporter user: `CREATE USER 'exporter'@'localhost' IDENTIFIED BY '...' WITH MAX_USER_CONNECTIONS 3;`
- Redis `redis.conf`: `latency-monitor-threshold 100` để bật latency tracking
- Alertmanager config: `H07-observability-as-code/alerts/` làm source of truth
- Grafana dashboards as code: `.json` files committed tại `H07-observability-as-code/dashboards/`
- Tham chiếu: `H07-observability-as-code/` (existing 2 dashboards + 3 alerts + 3 SLOs)

## Dependencies
- TASK-095 (prod servers phải exist)

## Definition of Done
- [ ] Code complete (exporter configs, Prometheus config, Grafana dashboard JSONs committed)
- [ ] G1-G4 gates passed
- [ ] Runbook tại `H02-runbooks/RUN-010-monitoring-agent.md`
- [ ] Tests written + passing (alert fire test documented)
- [ ] All 4 exporters scraping successfully — Grafana Explore query returns data
- [ ] No new debt introduced
