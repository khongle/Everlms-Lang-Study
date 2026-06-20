---
page: Profile
drupal_concept: user form/view
mode: as-built
owner_module: user
route_group: App
route_name: lms_course.profile
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Profile

## Purpose
User profile at `/profile` (dashboard): edit personal info, avatar, notification prefs.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/profile` |
| Route name | `lms_course.profile` |
| Primitive | user entity form/view (themed) |
| Access | authenticated (own) |
| Cache | per-user |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Profile fields | `user` fields (`field_first_name`,`field_last_name`,`field_bio`,`user_picture`,`field_receive_notifications`,…) | own | user tag | edit → updates |

## Page States
Normal: form · Empty: n/a · Access denied: anon → login · Validation: required → errors · Loading: submit.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Profile | done | notification prefs gate INT-008/009 |
