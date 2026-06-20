---
page: My courses
drupal_concept: controller list
mode: as-built
owner_module: lms_progress
route_group: App
route_name: lms_progress.my_courses
prototype_type: live-site
adequacy: PASS
---

# C11 Page - My courses

## Purpose
Enrolled courses at `/my-courses` (listing): progress + resume.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/my-courses` |
| Route name | `lms_progress.my_courses` |
| Primitive | controller list of own `lms_user_course` |
| Access | authenticated (own) |
| Cache | contexts: `user`; tags: `lms_user_course_list:{uid}` |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Enrolled + % | `lms_user_course` (own) | own | per-user | enroll → row |

## Page States
Normal: list · Empty: none → catalog CTA · Access denied: anon → login · Validation: n/a · Loading: lazy.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| My courses | done | resume link |
