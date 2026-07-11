# C05 Module - open_switcher

**Name:** User impersonation / switcher · **Phase:** 1 · **Epic:** [E01](../../D-Development/D02-epics/E01-foundation.md) · **Status:** done

## Responsibility
Cho phép user support/admin được ủy quyền impersonate ("switch to") một user khác để tái hiện issue, sau đó switch back an toàn. Thuộc package "Open Platform" (một utility tái sử dụng được, không đặc thù cho LMS).

## Owned / related entities
- Không có (thao tác trên `user` của Drupal).

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-021](../../B-Requirements/B03-features/F-021-users-roles-and-impersonation.md) | Users, roles & impersonation |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-007](../../B-Requirements/B04-business-workflows/WF-007-user-registration-and-onboarding.md) | User registration & onboarding (support flows) |

## Integrations / APIs (B06 / C06)
- — (không có)

## Backing pages
| Label | URL |
| ----- | --- |
| Switch user | `/open-switcher/*` (~5 routes) |

## Cross references
- Role/permission: [C02](../C02-user-roles/_index.md).

## Dependencies
- không có.

## Implementation notes
- ~5 route; được gate bởi một permission switch riêng. Restore lại session gốc khi switch back. Nên có audit ai-đã-switch-sang-ai (verify trong code).
