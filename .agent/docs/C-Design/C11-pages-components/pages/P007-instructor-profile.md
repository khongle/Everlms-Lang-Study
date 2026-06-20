---
page: Instructor profile
drupal_concept: user view controller
mode: as-built
owner_module: lms_course
route_group: Public
route_name: lms_course.instructor
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Instructor profile

## Purpose
Public instructor page at `/instructor/{user}` (marketing): bio + their courses.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/instructor/{user}` |
| Route name | `lms_course.instructor` |
| Primitive | controller (user + their courses) |
| Access | anonymous |
| Cache | tags: user, `lms_course_list`; contexts: `url` |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Bio/avatar | `user` fields (`field_bio`,`field_job_title`,`user_picture`) | instructor (own profile) | user tag | edit bio → updates |
| Courses | `lms_course` where `field_instructor`=user | — | course list | publish → appears |

## Page States
Normal: bio + courses · Empty: no courses · Access denied: blocked user → 404 · Validation: n/a · Loading: lazy.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Instructor page | done | — |
