# C05 Module - lms_notification

**Name:** Notifications · **Phase:** 9 · **Epic:** [E09](../../D-Development/D02-epics/E09-notifications.md) · **Status:** in-progress

## Responsibility
Sở hữu entity notification + dispatch qua các kênh in_app / email / push, được kích hoạt bởi domain event + cron. ~4 route + Drush command.

## Owned / related entities
- [lms_notification](../C04-entities/lms_notification.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-017](../../B-Requirements/B03-features/F-017-notifications.md) | Notifications |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-008](../../B-Requirements/B04-business-workflows/WF-008-forum-discussion.md) | (and enroll/cert/order events) |

## Integrations / APIs (B06 / C06)
- [INT-008 SMTP](../C06-apis/INT-008-smtp-email.md), [INT-009 Push](../C06-apis/INT-009-push-firebase-apns.md)

## Backing pages
| Label | URL |
| ----- | --- |
| Notifications | `/notifications` |

## Cross references
- Entity [C04](../C04-entities/lms_notification.md); `LmsNotificationCommands` (Drush).

## Dependencies
- lms_base.

## Implementation notes
- `field_channel` (in_app/email/push); `field_reference_*` polymorphic. ⚠️ **in_app hoạt động; email/push chỉ một phần/stub (R-005)**. Dispatch qua cron; `src/Commands/LmsNotificationCommands.php`.
