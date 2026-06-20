# Test Definition - gating-matrix

**Status:** TARGET (Kernel/Functional) · **Covers:** F-004, F-012 · **Risk if unmet:** revenue leakage / paywall bypass

## Matrix
| Scenario | Course type | Expected |
| -------- | ----------- | -------- |
| Anonymous | any | catalog only; lesson content denied |
| Authenticated, not enrolled | free | enroll CTA; content locked until enroll |
| Authenticated, not enrolled | paid | checkout CTA; content locked |
| Enrolled (free/order) | any | content unlocked |
| Active membership | plan-scoped | content unlocked without per-course purchase |
| Expired membership | plan-scoped | access revoked |
| Free-preview lesson | paid course | preview accessible to non-enrolled |
| Drip-locked lesson | enrolled | locked until `field_drip_days` |

## Assert
Web routes (`/learn/...`) AND `/api/v1` return consistent allow/deny per row.
