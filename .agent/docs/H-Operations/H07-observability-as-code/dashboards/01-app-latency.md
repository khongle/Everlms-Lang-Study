# Dashboard: EverLMS — App Latency

> **PROPOSED** — Grafana dashboard spec. Chưa có live data.
> Khi Grafana + Prometheus sẵn sàng, implement panels theo spec này.

---

## Metadata

| Field | Value |
|-------|-------|
| **Dashboard ID** | everlms-app-latency |
| **Owner** | SA |
| **Refresh rate** | 30s |
| **Default time range** | Last 1 hour |
| **Tags** | `everlms`, `drupal`, `latency`, `red-metrics` |

---

## Panel Definitions

### Panel 1: Request Rate (rpm)

| Field | Value |
|-------|-------|
| **Title** | Request Rate |
| **Type** | Time series |
| **Unit** | requests/min |
| **Signal (RED)** | Rate |

```promql
# PROPOSED PromQL — cần nginx/php-fpm exporter
rate(nginx_http_requests_total{job="everlms"}[1m]) * 60
```

**Thresholds:**
- Normal: < 800 rpm
- Warning: 800–1200 rpm
- Critical: > 1200 rpm (xem H06 scale triggers)

---

### Panel 2: Error Rate (%)

| Field | Value |
|-------|-------|
| **Title** | HTTP Error Rate (4xx + 5xx) |
| **Type** | Time series |
| **Unit** | percent (0–100) |
| **Signal (RED)** | Errors |

```promql
# PROPOSED
sum(rate(nginx_http_requests_total{status=~"5.."}[5m]))
/
sum(rate(nginx_http_requests_total[5m])) * 100
```

**Thresholds:**
- Normal: < 0.5%
- Warning: 0.5–1%
- Critical: > 1% (trigger alert 01-error-rate)

---

### Panel 3: Request Latency — p50 / p95 / p99

| Field | Value |
|-------|-------|
| **Title** | Drupal Request Latency |
| **Type** | Time series |
| **Unit** | milliseconds |
| **Signal (RED)** | Duration |

```promql
# PROPOSED — PHP-FPM hoặc Nginx upstream response time
histogram_quantile(0.50, rate(php_fpm_request_duration_seconds_bucket{job="everlms"}[5m])) * 1000
histogram_quantile(0.95, rate(php_fpm_request_duration_seconds_bucket{job="everlms"}[5m])) * 1000
histogram_quantile(0.99, rate(php_fpm_request_duration_seconds_bucket{job="everlms"}[5m])) * 1000
```

**Thresholds:**
- p95 normal: < 500ms
- p95 warning: 500ms–1s
- p95 critical: > 1s → xem SLO `slos/01-course-detail-availability.md`

---

### Panel 4: PHP-FPM Workers (USE)

| Field | Value |
|-------|-------|
| **Title** | PHP-FPM Worker Pool |
| **Type** | Bar gauge |
| **Unit** | count |
| **Signal (USE)** | Utilization + Saturation |

```promql
# PROPOSED — php-fpm exporter
php_fpm_active_processes{pool="www"}   # active
php_fpm_idle_processes{pool="www"}     # idle
php_fpm_listen_queue{pool="www"}       # queued (saturation signal)
```

**Thresholds:**
- listen_queue > 0 → warning (requests đang bị queue)
- active / (active + idle) > 80% → warning

---

### Panel 5: SLO Burn Rate

| Field | Value |
|-------|-------|
| **Title** | SLO Error Budget Burn Rate |
| **Type** | Stat panel |
| **Unit** | multiplier (1x = normal) |
| **Signal** | SLO burn |

```promql
# PROPOSED — dựa trên SLO 01-course-detail-availability (99.5% target)
# Burn rate 14.4x trong 1h = 2% budget consumed (critical)
# Implement sau khi SLO recording rules được setup
```

---

### Panel 6: Deploy Annotations

- Mỗi Git tag / deploy event được annotate lên tất cả panels
- Nguồn: Grafana annotation API hoặc CI/CD webhook
- **PROPOSED** — implement khi CI/CD pipeline sẵn sàng

---

## Alerts Linked

| Alert | File |
|-------|------|
| Error rate > 1% / 5min | `alerts/01-error-rate.md` |
| p95 latency > 1s | `alerts/03-quiz-grading-latency.md` (quiz-specific) |

---

*Cập nhật lần cuối: 2026-04-16 — PROPOSED, chưa có live implementation*
