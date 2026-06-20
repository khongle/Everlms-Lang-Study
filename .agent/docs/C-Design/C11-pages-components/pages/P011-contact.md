---
page: Contact
drupal_concept: Form API
mode: as-built
owner_module: lms_course
route_group: Public
route_name: lms_course.contact
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Contact

## Purpose
Contact page/form at `/contact` (marketing + form).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/contact` |
| Route name | `lms_course.contact` |
| Primitive | controller + contact form |
| Access | anonymous |
| Cache | uncacheable (form) |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Contact info/copy | content block/config | content_editor | block tag | edit → updates |
| Form | Form API | — | — | submit → email/notification |

## Page States
Normal: form · Empty: n/a · Access denied: n/a · Validation: required fields → errors · Loading: submit.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Contact | done | delivery via INT-008 |
