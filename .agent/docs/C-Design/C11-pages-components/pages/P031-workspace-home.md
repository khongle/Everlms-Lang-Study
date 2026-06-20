---
page: Workspace home
drupal_concept: controller dashboard
mode: as-built
owner_module: lms_course
route_group: Manage
route_name: lms_course.workspace
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Workspace home

## Purpose
Staff dashboard at `/workspace` (dashboard): entry to manage courses/quizzes/users/orders + KPIs.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace` |
| Route name | `lms_course.workspace` |
| Primitive | controller (links + KPIs) |
| Access | instructor/content_editor/manager |
| Cache | contexts: `user.roles` |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| KPI tiles + nav | aggregate + menu | staff | short-TTL | role change → nav changes |

## Page States
Normal: dashboard · Empty: new staff → empty KPIs · Access denied: non-staff → 403 · Validation: n/a · Loading: lazy.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Workspace home | done | role-scoped nav |
