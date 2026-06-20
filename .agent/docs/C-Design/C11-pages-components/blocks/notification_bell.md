---
block: Notification bell
drupal_concept: block plugin + AJAX
owner_module: lms_notification
region: header
block_type: plugin
adequacy: PASS
---

# C11 Block - Notification bell

## Purpose
Header bell with unread count + dropdown of recent notifications (in-app).

## Drupal 11 Contract
| Concern | Contract |
| --- | --- |
| Block ID | notification_bell |
| Drupal primitive | block plugin + AJAX (`/api/lms/notifications/unread-count`) |
| Owner module | lms_notification |
| Placed on | all authenticated pages (header) |
| Cache | contexts: `user`; per-user |

## Configuration / Fields
| Item | Requirement |
| --- | --- |
| Data | own `lms_notification` (unread) |

## Rendering Responsibilities
| Responsibility | Requirement |
| --- | --- |
| Twig/theme | bell + badge + dropdown |
| Access | authenticated; own notifications |
| Responsive | yes |
| Editing | computed |

## Prototype Evidence
| Evidence | Path |
| --- | --- |
| Live | site header (authed) |

## Tests / Checks
- Unread count accurate; never leaks others' notifications; mark-read updates badge.
