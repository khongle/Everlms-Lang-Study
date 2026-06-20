# H07 Alerts

Alert definitions (target — not yet wired).

| Alert | Condition | Severity | Source |
| ----- | --------- | -------- | ------ |
| payment-failure-spike | gateway error rate > threshold | P1 | lms_payment logs |
| cert-render-failure | mPDF render errors | P2 | lms_certificate |
| cron-stalled | cron not run in N hours (notifications/expiry) | P1 | drush/watchdog |
| ai-provider-errors | `@AiProvider` error rate / quota | P2 | lms_ai |
| email-bounce-spike | bounces > threshold | P2 | INT-008 |
| api-5xx | `/api/v1` 5xx rate | P1 | access logs |

> Wire to the monitoring stack of choice; until then, watch `drush watchdog:show` / server logs.
