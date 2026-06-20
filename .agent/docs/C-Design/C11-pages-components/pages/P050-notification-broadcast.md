---
page: Notification broadcast
drupal_concept: Form API
mode: as-built
owner_module: lms_notification
route_group: Admin
route_name: lms_notification.broadcast
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Notification broadcast

## Purpose
Admin broadcast at `/admin/lms/notifications/broadcast` (config/form): send a notification to a user segment.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/admin/lms/notifications/broadcast` (settings `/manage/settings/notifications`) |
| Route name | `lms_notification.broadcast` |
| Primitive | Form API + batch |
| Access | administer notifications |
| Cache | uncacheable |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Broadcast form | recipients + message | administrator | — | send → in-app notifications created (email/push partial R-005) |

## Page States
Normal: form · Empty: n/a · Access denied: non-admin → 403 · Validation: empty message → error · Loading: batch.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Broadcast | partial | in-app ok; email/push R-005 |
