---
page: Manage blog
drupal_concept: entity list + form
mode: as-built
owner_module: lms_blog
route_group: Manage
route_name: lms_blog.manage
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Manage blog

## Purpose
Article admin at `/manage/blog` (workspace-list): create/edit/delete `lms_article`.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/manage/blog` (add, `{article}/edit/delete`) |
| Route name | `lms_blog.manage` |
| Primitive | entity list + form |
| Access | content_editor |
| Cache | tags: article list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Article rows | `lms_article` | manage blog | article list | create → P008 listing |

## Page States
Normal: list · Empty: none → add · Access denied: non-editor → 403 · Validation: required → errors · Loading: paged.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Manage blog | done | AI drafting available |
