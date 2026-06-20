# E02-issues - Issues

As-built defects / gaps, `ISSUE-NNNN`, under `open/` `resolved/` `wont-fix/`. Mirrors the [G02 risk register](../../G-Governance/G02-risk-register/_index.md) (issues are the concrete, fixable instances).

## Open
| Issue | Title | Severity | Risk | Task |
| ----- | ----- | -------- | ---- | ---- |
| [ISSUE-0001](open/ISSUE-0001-secrets-in-config-yaml.md) | Third-party secrets committed in config YAML | P0 | R-001 | E06S02T02 |
| [ISSUE-0002](open/ISSUE-0002-no-payment-webhooks.md) | No payment webhooks / signature verification | P0 | R-002 | E06S02T01 |
| [ISSUE-0003](open/ISSUE-0003-badge-award-logic-absent.md) | Badge award logic absent | P1 | R-003 | E05S02T01 |
| [ISSUE-0004](open/ISSUE-0004-zero-test-coverage.md) | ~0% automated test coverage | P1 | R-006 | X01 |
| [ISSUE-0005](open/ISSUE-0005-email-push-stubbed.md) | Email/push notification channels partial | P1 | R-005 | E09S01T01/02 |
| [ISSUE-0006](open/ISSUE-0006-duplicate-paypal-module.md) | Duplicate lms_paypal gateway (dead code) | P2 | R-004 | E06S04T01 |
| [ISSUE-0007](open/ISSUE-0007-analytics-partial.md) | Analytics KPIs partial / scoping unverified | P2 | — | E12S01T01 |
| [ISSUE-0008](open/ISSUE-0008-no-api-rate-limiting.md) | No rate limiting on `/api/v1` | P2 | R-007 | E11S01 |
| [ISSUE-0009](open/ISSUE-0009-zoom-depth-partial.md) | Zoom attendance/recording partial | P2 | — | E08S07 |
| [ISSUE-0010](open/ISSUE-0010-paypal-zalopay-stub.md) | PayPal/ZaloPay gateways stub/incomplete | P2 | R-008 | — |

## Resolved / Won't-fix
None yet (`resolved/`, `wont-fix/` empty).

> Severity P0=must-fix-before-release, P1=fix-before-scaling, P2=long-term (mirrors Z-review tiers).
