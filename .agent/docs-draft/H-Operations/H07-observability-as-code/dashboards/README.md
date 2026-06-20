# H07 — Dashboards

> Grafana dashboard definitions versioned as code (PROPOSED — chưa triển khai Grafana).
> Mỗi file là một dashboard spec. Khi Grafana sẵn sàng, import từ đây.
>
> **Trạng thái:** PROPOSED. Không có live metrics. Spec để chuẩn bị trước CP6.

---

## Dashboard Index

| File | Dashboard name | Signals | Audience |
|------|---------------|---------|----------|
| [01-app-latency.md](01-app-latency.md) | EverLMS — App Latency | Drupal request latency (p50/p95/p99), error rate | SA / DevOps |
| [02-db-queries.md](02-db-queries.md) | EverLMS — DB Slow Queries | MariaDB slow query count, query time histogram | SA / DevOps |

---

## Grafana Plan (PROPOSED)

### Stack

| Tool | Role | Status |
|------|------|--------|
| Grafana | Dashboard + alerting UI | PROPOSED |
| Prometheus | Metrics scrape & storage | PROPOSED |
| Loki | Log aggregation | PROPOSED |
| PHP-FPM exporter | PHP-FPM metrics | PROPOSED |
| MariaDB exporter | DB metrics | PROPOSED |
| Drupal custom metrics | Business-level signals | PROPOSED |

### Signal Coverage Plan

| Signal Category | Panels planned | Dashboard |
|----------------|---------------|-----------|
| RED — request latency | p50, p95, p99 per route | 01-app-latency |
| RED — error rate | 4xx/5xx rate | 01-app-latency |
| RED — request rate | rpm by endpoint | 01-app-latency |
| USE — DB query time | slow query histogram | 02-db-queries |
| USE — DB connections | active vs max | 02-db-queries |
| USE — PHP-FPM workers | active / idle / queue | 01-app-latency |
| Business — quiz submissions | submissions/min | FUTURE |
| Business — payment success rate | % success | FUTURE |
| Business — AI queue depth | queue depth | FUTURE |

---

## Dashboard Rules

- Mỗi dashboard có `owner` tag
- Versioned trong repo; không edit trực tiếp trên Grafana UI (drift = bug)
- PR review bắt buộc cho dashboard changes
- Không có orphan panels (panel không có owner tag)
- Deploy annotation: mỗi deploy được mark trên tất cả dashboard

---

## Log Retention Policy (PROPOSED)

| Log level | Hot retention | Cold retention | PII handling |
|-----------|--------------|----------------|-------------|
| ERROR | 90 ngày | 1 năm | Required redaction |
| WARN | 30 ngày | 6 tháng | Required redaction |
| INFO | 14 ngày | — | Redact user-identifiable fields |
| DEBUG | 3 ngày | — | Required redaction |
| AUDIT | 7 năm (immutable) | — | Redact non-audit PII |

---

*Cập nhật lần cuối: 2026-04-16*
