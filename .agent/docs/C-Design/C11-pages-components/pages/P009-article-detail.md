---
page: Article detail
drupal_concept: entity view
mode: as-built
owner_module: lms_blog
route_group: Public
route_name: lms_blog.article
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Article detail

## Purpose
Blog article at `/news/{node}` (detail archetype): body, SEO, related.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/news/{node}` |
| Route name | `lms_blog.article` |
| Primitive | entity view controller |
| Access | anonymous |
| Cache | tags: `lms_article:{id}` |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Article body/SEO | `lms_article` fields | content_editor | article tag | edit → updates |

## Page States
Normal: article · Empty: n/a · Access denied: unpublished → 403 · Validation: n/a · Loading: lazy media.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Article detail | done | SEO fields rendered |
