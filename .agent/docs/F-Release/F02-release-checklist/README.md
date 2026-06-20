# F02-release-checklist - Release Checklist

| # | Item | Owner | Status |
| - | ---- | ----- | ------ |
| 1 | Secrets moved to Key/env; secret-scan clean (R-001) | Architect | ☐ blocker |
| 2 | Payment webhooks + signature verification live (R-002) | Implementer | ☐ blocker |
| 3 | PHPUnit baseline green in CI (R-006) | QA | ☐ |
| 4 | Email/push channels working OR explicitly deferred (R-005) | Implementer | ☐ |
| 5 | `lms_paypal` duplicate removed (R-004) | Implementer | ☐ |
| 6 | API rate limiting on `/api/v1` (R-007) | Implementer | ☐ |
| 7 | `drush cim` clean on a fresh build | Architect | ☐ |
| 8 | DB backup taken (`H02 backup` runbook) | Ops | ☐ |
| 9 | Z-review verdict ≥ GO-WITH-FIXES, P0s closed | QA | ☐ |
| 10 | Changelog updated ([F03](../F03-changelog/_index.md)) | PM | ☐ |
| 11 | Accessibility spot-check (WCAG AA) on key pages | QA | ☐ |
| 12 | Cron scheduled (notifications, expiry) | Ops | ☐ |

> Items 1–2 are P0 release blockers per [E06](../../E-Quality/E06-implementation-status/_index.md) / [G02](../../G-Governance/G02-risk-register/_index.md).
