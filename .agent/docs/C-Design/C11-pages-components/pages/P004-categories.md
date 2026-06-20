---
page: Categories
drupal_concept: View page
mode: as-built
owner_module: lms_course
route_group: Public
route_name: lms_course.categories
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Categories

## Purpose
Category index at `/categories` (archetype: listing — see [_common](../_common.md)). Lists `course_category` terms with counts/thumbnails.

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/categories` | 
| Route name | `lms_course.categories` |
| Primitive | controller/View term list |
| Access | anonymous |
| Cache | tags: taxonomy term list; contexts: `url` |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Category tiles | `course_category` taxonomy (+`field_category_thumbnail`,`field_is_featured`) | content_editor | term cache | add term → tile |

## Page States
Normal: tiles · Empty: none → message · Access denied: n/a · Validation: n/a · Loading: lazy.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Category index | done | — |
