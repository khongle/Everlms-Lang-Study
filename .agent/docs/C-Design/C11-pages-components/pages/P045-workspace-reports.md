---
page: Workspace reports
drupal_concept: controller report
mode: as-built
owner_module: lms_course
route_group: Manage
route_name: lms_course.workspace_reports
prototype_type: live-site
adequacy: NOT_RUN
---

# C11 Page - Workspace reports

## Purpose
Manager reports at `/workspace/reports` (report): course/learner summaries (overlaps lms_analytics).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace/reports` |
| Route name | `lms_course.workspace_reports` |
| Primitive | controller (aggregate) |
| Access | manager |
| Cache | short-TTL |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Report metrics | aggregate over progress/orders | manager | live | enroll → count |

## Page States
Normal: report · Empty: zeros · Access denied: non-manager → 403 · Validation: n/a · Loading: query.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Workspace reports | partial | overlaps admin analytics (F-020) |
