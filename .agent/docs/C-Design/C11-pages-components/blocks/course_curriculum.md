---
block: Curriculum outline
drupal_concept: block plugin
owner_module: lms_course
region: content
block_type: plugin
adequacy: PASS
---

# C11 Block - Curriculum outline

## Purpose
Chapter/lesson outline for a course; navigable in the player, read-only on detail.

## Drupal 11 Contract
| Concern | Contract |
| --- | --- |
| Block ID | course_curriculum |
| Drupal primitive | block plugin |
| Owner module | lms_course |
| Placed on | P003, P019 |
| Cache | tags: `lms_course:{id}`, chapter/lesson tags; contexts: `user` (completion) |

## Configuration / Fields
| Item | Requirement |
| --- | --- |
| Data | `field_chapters` → `field_lessons` |

## Rendering Responsibilities
| Responsibility | Requirement |
| --- | --- |
| Twig/theme | nested list markup |
| Access | locked lessons shown locked (drip/enrollment) |
| Responsive | collapsible |
| Editing | structure from course entity (editor-managed) |

## Prototype Evidence
| Evidence | Path |
| --- | --- |
| Live | course detail / player sidebar |

## Tests / Checks
- Reflects chapter/lesson order; completed lessons marked; locked state honors gating.
