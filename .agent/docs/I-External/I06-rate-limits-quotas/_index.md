# I06-rate-limits-quotas - Rate Limits & Quotas

⚠️ **Not implemented today** (R-007 / [ISSUE-0008](../../E-Quality/E02-issues/open/ISSUE-0008-no-api-rate-limiting.md)). `/api/v1` is currently unthrottled.

## Target limits
| Scope | Limit (target) | Rationale |
| ----- | -------------- | --------- |
| OTP request/verify | e.g. 5 / 10 min / IP+identifier | brute-force/abuse prevention |
| Login (`/oauth/token`) | e.g. 10 / 5 min / IP | credential stuffing |
| Authenticated reads | e.g. 120 / min / user | fair use |
| Search | e.g. 30 / min / user | DB protection |

On exceed: HTTP 429 + `Retry-After`. Implement via a middleware/flood control; document here when live.
