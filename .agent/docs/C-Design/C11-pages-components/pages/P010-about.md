---
page: About
drupal_concept: controller + editor blocks
mode: as-built
owner_module: lms_course
route_group: Public
route_name: lms_course.about
prototype_type: live-site
adequacy: PASS
---

# C11 Page - About

## Purpose
Marketing About page at `/about` (marketing): editor-managed content blocks.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/about` |
| Route name | `lms_course.about` |
| Primitive | controller + placed content blocks / node |
| Access | anonymous |
| Cache | tags: block content |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| About copy | content block / page node (`node.type` page) | content_editor | block/node tag | edit → updates |

## Page States
Normal: content · Empty: n/a · Access denied: n/a · Validation: n/a · Loading: n/a.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| About page | done | editor-managed |
