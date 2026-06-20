---
page: Manage quizzes
drupal_concept: View list + entity ops
mode: as-built
owner_module: lms_quiz
route_group: Manage
route_name: lms_course.workspace_quizzes
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Manage quizzes

## Purpose
Quiz list/create/edit at `/workspace/quizzes` (workspace-list).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace/quizzes` (create, `{quiz}/edit`) |
| Route name | `lms_course.workspace_quizzes` |
| Primitive | list + entity form |
| Access | instructor/manager |
| Cache | tags: quiz list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Quiz rows | `lms_quiz` | manage quizzes | quiz list | create → row |

## Page States
Normal: list · Empty: none → create · Access denied: non-staff → 403 · Validation: n/a · Loading: paged.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Manage quizzes | done | attach questions |
