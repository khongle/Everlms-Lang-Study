# G02-risk-register - Risk Register

**Last updated:** 2026-06-21 · Tiers: P0 fix-before-release · P1 fix-before-scaling · P2 long-term.

| ID | Risk | Tier | Likelihood | Impact | Mitigation / task | Issue |
| -- | ---- | ---- | ---------- | ------ | ----------------- | ----- |
| R-001 | Third-party secrets committed in config YAML (no Key module) | P0 | High | Credential exposure; blocks public release | Move to Key/env ([E06S02T02](../../D-Development/D04-tasks/todo/E06S02T02-move-secrets-to-key.md)) | [ISSUE-0001](../../E-Quality/E02-issues/open/ISSUE-0001-secrets-in-config-yaml.md) |
| R-002 | No payment webhooks / signature verification | P0 | High | Orders desync from gateway truth | Add webhooks ([E06S02T01](../../D-Development/D04-tasks/todo/E06S02T01-add-payment-webhooks.md)) | [ISSUE-0002](../../E-Quality/E02-issues/open/ISSUE-0002-no-payment-webhooks.md) |
| R-003 | Badge award logic absent | P1 | Certain | F-008 non-functional | Award subscriber ([E05S02T01](../../D-Development/D04-tasks/todo/E05S02T01-implement-badge-award-subscriber.md)) | [ISSUE-0003](../../E-Quality/E02-issues/open/ISSUE-0003-badge-award-logic-absent.md) |
| R-004 | Duplicate `lms_paypal` gateway (dead code) | P2 | Certain | Maintenance confusion | Dedup ([E06S04T01](../../D-Development/D04-tasks/todo/E06S04T01-dedupe-paypal-gateway.md)) | [ISSUE-0006](../../E-Quality/E02-issues/open/ISSUE-0006-duplicate-paypal-module.md) |
| R-005 | Email/push notification channels partial/stubbed | P1 | High | Reminders not delivered | Implement channels ([E09S01T01](../../D-Development/D04-tasks/todo/E09S01T01-implement-email-channel.md)/[T02](../../D-Development/D04-tasks/todo/E09S01T02-implement-push-channel.md)) | [ISSUE-0005](../../E-Quality/E02-issues/open/ISSUE-0005-email-push-stubbed.md) |
| R-006 | ~0% automated test coverage | P1 | Certain | Regressions undetected; G3 vacuous | Test baseline ([X01](../../D-Development/D04-tasks/todo/X01-establish-test-baseline.md)) | [ISSUE-0004](../../E-Quality/E02-issues/open/ISSUE-0004-zero-test-coverage.md) |
| R-007 | No rate limiting on `/api/v1` (incl. OTP) | P2 | Medium | Brute-force/abuse | Add throttling | [ISSUE-0008](../../E-Quality/E02-issues/open/ISSUE-0008-no-api-rate-limiting.md) |
| R-008 | PayPal/ZaloPay gateways stub/incomplete | P2 | Medium | Reduced payment reach | Complete flows | [ISSUE-0010](../../E-Quality/E02-issues/open/ISSUE-0010-paypal-zalopay-stub.md) |

> Mirrors [E02 issues](../../E-Quality/E02-issues/README.md). Active blockers (P0/P1) tracked in [G03](../G03-blockers/_index.md) and [A03](../../A-Project-Information/A03-project-context/_index.md).
