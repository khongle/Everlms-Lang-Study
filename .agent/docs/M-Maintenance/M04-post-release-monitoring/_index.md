# M04-post-release-monitoring - Post-Release Monitoring

After each release, watch (see [H07](../../H-Operations/H07-observability-as-code/alerts/README.md)):
| Signal | Where | Threshold |
| ------ | ----- | --------- |
| Payment success rate | lms_payment logs / orders | > 98% |
| API 5xx / latency | access logs | p95 < 500ms; low 5xx |
| Cert issuance success | lms_certificate | > 99% |
| Cron health | `drush watchdog:show` | runs on schedule |
| Email/push delivery (once live) | INT-008/009 | low bounce/failure |
| Error log volume | watchdog | no spike |

First 24–48h: heightened watch on the just-changed area; roll back ([H02](../../H-Operations/H02-runbooks/rollback.md)) on P0.
