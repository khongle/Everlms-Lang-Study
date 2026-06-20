# C05 Module - open_switcher

**Name:** User impersonation / switcher · **Phase:** 1 · **Epic:** [E01](../../D-Development/D02-epics/E01-foundation.md) · **Status:** done

## Responsibility
Let an authorised support/admin user impersonate ("switch to") another user to reproduce issues, then switch back safely. Package "Open Platform" (a reusable utility, not LMS-specific).

## Owned / related entities
- None (operates on Drupal `user`).

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-021](../../B-Requirements/B03-features/F-021-users-roles-and-impersonation.md) | Users, roles & impersonation |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-007](../../B-Requirements/B04-business-workflows/WF-007-user-registration-and-onboarding.md) | User registration & onboarding (support flows) |

## Integrations / APIs (B06 / C06)
- — (none)

## Backing pages
| Label | URL |
| ----- | --- |
| Switch user | `/open-switcher/*` (~5 routes) |

## Cross references
- Roles/permissions: [C02](../C02-user-roles/_index.md).

## Dependencies
- none.

## Implementation notes
- ~5 routes; gated by a dedicated switch permission. Restore-original-session on switch back. Audit who-switched-to-whom is recommended (verify in code).
