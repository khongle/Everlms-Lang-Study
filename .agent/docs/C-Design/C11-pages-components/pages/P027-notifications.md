---
page: Notifications
drupal_concept: controller list + AJAX
mode: as-built
owner_module: lms_notification
route_group: App
route_name: lms_notification.list
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Notifications

## Purpose
Notification center at `/notifications` (auth/util): list, mark read, unread count.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/notifications` (AJAX `/api/lms/notifications/unread-count`) |
| Route name | `lms_notification.list` |
| Primitive | controller list + AJAX count |
| Access | authenticated (own) |
| Cache | per-user |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Notifications | `lms_notification` (own) | own | per-user | event → notification (in-app ok; email/push partial R-005) |

## Page States
Normal: list · Empty: none · Access denied: anon → login · Validation: n/a · Loading: AJAX.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| In-app | done | ⚠️ email/push partial (R-005) |
