---
page: Manage content
drupal_concept: View list + entity forms
mode: as-built
owner_module: lms_course
route_group: Manage
route_name: lms_course.workspace_content
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Manage content

## Purpose
Generic content admin at `/workspace/content/{content_type}` (workspace-list): FAQs, pages, nodes, bulk ops.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace/content/{content_type}` (add, `{node}/edit/view`, bulk; FAQs `/workspace/content/faqs/*`) |
| Route name | `lms_course.workspace_content` |
| Primitive | typed list + entity forms |
| Access | content_editor/manager |
| Cache | tags: per content type |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Content rows | nodes / `lms_faq` by type | manage content | list tag | add → row |

## Page States
Normal: list · Empty: none → add · Access denied: non-editor → 403 · Validation: required → errors · Loading: paged.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Manage content | done | FAQ + node types |
