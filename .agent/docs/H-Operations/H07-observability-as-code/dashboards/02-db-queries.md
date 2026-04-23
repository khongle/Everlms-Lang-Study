# Dashboard: EverLMS — DB Slow Queries

> **PROPOSED** — Grafana dashboard spec cho MariaDB 10.11 monitoring.
> Chưa có live data. Implement khi MariaDB exporter được deploy.

---

## Metadata

| Field | Value |
|-------|-------|
| **Dashboard ID** | everlms-db-queries |
| **Owner** | SA |
| **Refresh rate** | 60s |
| **Default time range** | Last 6 hours |
| **Tags** | `everlms`, `mariadb`, `database`, `use-metrics` |

---

## Panel Definitions

### Panel 1: Slow Query Rate

| Field | Value |
|-------|-------|
| **Title** | Slow Queries / Minute |
| **Type** | Time series |
| **Unit** | queries/min |
| **Signal (USE)** | Errors (slow = saturation proxy) |

```promql
# PROPOSED — MariaDB exporter (mysqld_exporter)
rate(mysql_global_status_slow_queries[1m]) * 60
```

**Slow query threshold trong MariaDB config:**
```ini
# my.cnf / DDEV config
slow_query_log = ON
long_query_time = 1  # giây — query > 1s là slow
min_examined_row_limit = 100
```

**Thresholds:**
- Normal: 0 slow queries/min
- Warning: > 2/min
- Critical: > 10/min → investigate, xem H04 tech debt

---

### Panel 2: Query Execution Time Histogram

| Field | Value |
|-------|-------|
| **Title** | Query Execution Time (p50 / p95 / p99) |
| **Type** | Time series |
| **Unit** | seconds |
| **Signal (USE)** | Utilization |

```promql
# PROPOSED — Performance Schema metrics (nếu enabled)
histogram_quantile(0.50, rate(mysql_perf_schema_events_statements_seconds_bucket[5m]))
histogram_quantile(0.95, rate(mysql_perf_schema_events_statements_seconds_bucket[5m]))
histogram_quantile(0.99, rate(mysql_perf_schema_events_statements_seconds_bucket[5m]))
```

---

### Panel 3: Active DB Connections (USE)

| Field | Value |
|-------|-------|
| **Title** | DB Connections (Active / Max) |
| **Type** | Bar gauge |
| **Unit** | count |
| **Signal (USE)** | Utilization |

```promql
# PROPOSED
mysql_global_status_threads_connected          # active connections
mysql_global_variables_max_connections         # max_connections (default 100 trong MariaDB)
```

**Thresholds:**
- Normal: < 60 connections (60% of max 100)
- Warning: 60–80 connections
- Critical: > 80 connections → headroom breach (xem H06)

---

### Panel 4: Cache Hit Rate (Query Cache)

| Field | Value |
|-------|-------|
| **Title** | MariaDB Query Cache Hit Rate |
| **Type** | Stat |
| **Unit** | percent |
| **Signal (USE)** | Efficiency |

```promql
# PROPOSED
mysql_global_status_qcache_hits /
(mysql_global_status_qcache_hits + mysql_global_status_qcache_inserts) * 100
```

> Lưu ý: MariaDB 10.11 có Query Cache disabled by default (deprecated path).
> Drupal internal cache và Redis (PROPOSED) sẽ là cache layer chính.
> Panel này chuyển sang Drupal cache hit rate khi Redis được implement.

---

### Panel 5: InnoDB Buffer Pool Hit Rate

| Field | Value |
|-------|-------|
| **Title** | InnoDB Buffer Pool Hit Rate |
| **Type** | Stat |
| **Unit** | percent |
| **Signal (USE)** | Utilization |

```promql
# PROPOSED
(1 - rate(mysql_global_status_innodb_buffer_pool_reads[5m])
   / rate(mysql_global_status_innodb_buffer_pool_read_requests[5m])) * 100
```

**Thresholds:**
- Good: > 99%
- Warning: 95–99%
- Critical: < 95% → buffer pool quá nhỏ, cần tăng `innodb_buffer_pool_size`

---

### Panel 6: Drupal Cache Hit Rate (PROPOSED)

| Field | Value |
|-------|-------|
| **Title** | Drupal Cache Backend Hit Rate |
| **Type** | Time series |
| **Unit** | percent |
| **Notes** | Cần custom metric từ Drupal module |

```
PROPOSED — implement lms_monitoring module để emit:
  - drupal_cache_hits_total{bin="render"}
  - drupal_cache_misses_total{bin="render"}
  Tương tự cho bins: bootstrap, config, entity, menu, etc.
```

---

## Slow Query Log Location (DDEV)

```bash
# Xem slow query log trong DDEV
ddev exec cat /var/lib/mysql/slow.log 2>/dev/null || echo "Slow log chưa enable"

# Enable slow log trong DDEV
ddev exec mysql -u root -proot -e "
  SET GLOBAL slow_query_log = 'ON';
  SET GLOBAL long_query_time = 1;
  SET GLOBAL slow_query_log_file = '/tmp/mariadb-slow.log';"
```

---

## Alerts Linked

| Condition | Alert |
|-----------|-------|
| Slow query rate > 10/min | Chưa có alert — PROPOSED |
| DB connections > 80% | H06 scale trigger |

---

*Cập nhật lần cuối: 2026-04-16 — PROPOSED, chưa có live implementation*
