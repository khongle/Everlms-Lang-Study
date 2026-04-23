# Alert: HTTP 5xx Error Rate

> **PROPOSED** — Chưa active. Implement khi Prometheus + Grafana Alerting sẵn sàng.

---

## Metadata

| Field | Value |
|-------|-------|
| **Alert ID** | alert-everlms-error-rate |
| **Severity** | critical |
| **Owner** | SA |
| **Runbook** | `H-Operations/H02-runbooks/RUN-001-drupal-cache-rebuild.md` |
| **Created** | 2026-04-16 |
| **Status** | PROPOSED |

---

## Alert Definition (YAML)

```yaml
id: alert-everlms-error-rate
severity: critical
for: 5m
team: sa
runbook: H-Operations/H02-runbooks/RUN-001-drupal-cache-rebuild.md
suppress_during: [deploy]
notification:
  channels: [email, slack]
  auto_resolve: true
labels:
  service: everlms
  env: production

signal: |
  sum(rate(nginx_http_requests_total{status=~"5..", job="everlms"}[5m]))
  /
  sum(rate(nginx_http_requests_total{job="everlms"}[5m])) * 100 > 1
```

---

## Trigger Condition

| Metric | Operator | Threshold | Window | Action |
|--------|----------|-----------|--------|--------|
| HTTP 5xx rate | > | 1% | 5 phút liên tục | Fire alert |

**Ví dụ:** Nếu trong 5 phút gần nhất, 1 trong 100 request trả về 5xx → alert fire.

---

## Why This Threshold

- 0.5% 5xx là normal noise cho Drupal (cache warming, session race)
- > 1% sustained 5min = likely real problem, không phải noise
- SLO `01-course-detail-availability` target 99.5% → 1% error rate đang vi phạm SLO
- Pre-cohort estimate: < 1000 rpm → 1% = 10 errors/min = rõ ràng có vấn đề

---

## Triage Steps (khi alert fire)

1. Kiểm tra recent deploy (có annotation trên dashboard không?)
2. Xem Drupal watchdog: `drush watchdog:show --severity=error --count=50`
3. Kiểm tra PHP-FPM log: `/var/log/php-fpm/error.log`
4. Nếu cache-related: chạy RUN-001 (cache rebuild)
5. Nếu DB-related: kiểm tra MariaDB connections (dashboard `02-db-queries`)
6. Nếu code-related: rollback deploy nếu trong cửa sổ 30 phút
7. Log incident H01 nếu > 10 phút không resolve

---

## Suppress Rules

- **Suppress khi deploy:** Tắt 10 phút trước deploy, bật lại 5 phút sau deploy verify
- **Suppress khi drush updb:** Cache warm-up phase ~2–3 phút sau update hooks
- Suppress được ghi nhận trong H01 với lý do

---

## SLO Link

- Vi phạm alert này trực tiếp burn error budget của `slos/01-course-detail-availability.md`
- Nếu alert fire > 30 phút → SLO burn rate sẽ trigger riêng

---

## Related Alerts

| Alert | Quan hệ |
|-------|---------|
| `02-payment-webhook-fail` | Có thể cùng root cause (server overload) |
| `03-quiz-grading-latency` | Queue buildup có thể kéo 5xx |
