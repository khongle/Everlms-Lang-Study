---
page: Category detail
drupal_concept: View page (term)
mode: as-built
owner_module: lms_course
route_group: Public
route_name: lms_course.category
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Category detail

## Purpose
Courses in a category at `/categories/{taxonomy_term}` (listing archetype).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/categories/{taxonomy_term}` |
| Route name | `lms_course.category` |
| Primitive | controller/View filtered by term |
| Access | anonymous |
| Cache | tags: `lms_course_list`, term; contexts: `url` |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Filtered courses | `lms_course` by `field_category` | n/a | `lms_course_list` | tag course → appears |

## Page States
Normal: course grid · Empty: "no courses" · Access denied: n/a · Validation: invalid term → 404 · Loading: paged.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Category browse | done | reuses catalog grid |
