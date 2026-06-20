---
page: Course editor
drupal_concept: entity form
mode: as-built
owner_module: lms_course
route_group: Manage
route_name: lms_course.workspace_course_edit
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Course editor

## Purpose
Create/edit a course and its curriculum at `/workspace/courses/{lms_course}/edit`: metadata, chapters, lessons, pricing. The primary authoring surface (F-002/WF-006).

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/workspace/courses/{lms_course}/edit` (create `/workspace/courses/create`) |
| Route name | `lms_course.workspace_course_edit` |
| Drupal page primitive | entity form (course) + inline chapter/lesson management |
| Owner module | lms_course |
| Access | instructor (own) / content_editor / manager |
| Cache | uncacheable (form) |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| course_curriculum (editable) | Curriculum builder | content | add/reorder chapters/lessons |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Fields | name, descriptions, thumbnail/banner, category/level/language/tags, price/type, marketing sections, instructor |
| Curriculum | manage `field_chapters` → chapters → `field_lessons` |
| AI assist | link to AI builder (P044) |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Course fields | `lms_course` | manage courses | `lms_course:{id}` | save → detail updates |
| Chapters/lessons | `lms_chapter`/`lms_lesson` | manage courses | course tag | add lesson → appears in player |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| Field labels | form chrome (i18n) | interface translation |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | form + curriculum builder |
| Empty | new course → blank form |
| Access denied | not owner/staff → 403 |
| Validation error | required title/price → inline errors |
| Loading | autosave/AJAX |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Authoring form + curriculum | done | editorial-usability anchor |
