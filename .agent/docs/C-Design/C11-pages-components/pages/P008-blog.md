---
page: Blog / News
drupal_concept: View page
mode: as-built
owner_module: lms_blog
route_group: Public
route_name: lms_blog.news
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Blog / News

## Purpose
Article listing at `/news` (listing archetype).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/news` |
| Route name | `lms_blog.news` |
| Primitive | controller/View list of `lms_article` |
| Access | anonymous |
| Cache | tags: article list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Article cards | `lms_article` (published) | content_editor | article list | publish → card |
| Tag filter | `blog_tags` taxonomy | content_editor | term cache | add tag → filter |

## Page States
Normal: article grid · Empty: none · Access denied: n/a · Validation: n/a · Loading: paged.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Blog listing | done | — |
