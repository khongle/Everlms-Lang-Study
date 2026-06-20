---
page: Manage courses
drupal_concept: View list + entity ops
mode: as-built
owner_module: lms_course
route_group: Manage
route_name: lms_course.workspace_courses
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Manage courses

## Purpose
Back-office course list at `/workspace/courses`: instructors/editors find, create, edit, publish/unpublish, delete courses.

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/workspace/courses` (create `/workspace/courses/create`, edit `/workspace/courses/{id}/edit`) |
| Route name | `lms_course.workspace_courses` |
| Drupal page primitive | View/controller list + row operations |
| Owner module | lms_course |
| Access | instructor/content_editor/manager (scoped: instructor = own) |
| Cache | tags: `lms_course_list`; contexts: `user` |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| course_card (compact) | Course row | content | list row |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| List | title, status, enrollments, rating, actions |
| Ops | create, edit, publish toggle, delete |
| Scope | instructor sees own; manager sees all |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Course rows | `lms_course` | manage courses | `lms_course_list` | create → row appears |
| Row actions | permission-gated | per role | — | hidden if no perm |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| None | — | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | course table |
| Empty | "no courses" + create CTA |
| Access denied | non-staff → 403 |
| Validation error | n/a (list) |
| Loading | paged |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Manage list + ops | done | scope by role |
