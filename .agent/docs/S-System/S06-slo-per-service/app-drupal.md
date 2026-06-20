# SLO - app-drupal

| SLO | Target | Window | Status |
| --- | ------ | ------ | ------ |
| Availability | 99.5% | 30d | TARGET (unmeasured) |
| Web page render (catalog/course) | p95 < 1s | 30d | TARGET |
| `/api/v1` availability | 99.5% | 30d | TARGET |
| `/api/v1` latency | p95 < 500ms | 30d | TARGET |
| Payment capture success | > 98% | 30d | TARGET |
| Certificate issuance success | > 99% | 30d | TARGET |

Error budget: 0.5% / 30d. Baseline once monitoring + perf measurement exist ([E12](../../E-Quality/E12-performance-results/README.md), [H07 slos](../../H-Operations/H07-observability-as-code/slos/README.md)). db-mariadb/web-nginx inherit availability with app-drupal (single deploy).
