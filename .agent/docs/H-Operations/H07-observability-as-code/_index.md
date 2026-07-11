# H07-observability-as-code - Observability as Code

Định nghĩa observability dạng code cho EverLMS: alert rules, dashboards, SLOs. Nguồn tín hiệu chính là Drupal `dblog`/watchdog + uptime check.

| Subtree | Nội dung |
| ------- | -------- |
| [alerts/](alerts/README.md) | alert rule (watchdog/dblog + uptime/endpoint check) |
| [dashboards/](dashboards/README.md) | dashboard definition (traffic, error, latency, revenue) |
| [slos/](slos/README.md) | SLO definition (availability, latency, error budget) — mirror [S06](../../S-System/S06-slo-per-service/_index.md) |
