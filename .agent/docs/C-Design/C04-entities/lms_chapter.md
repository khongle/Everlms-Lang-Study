# C04 Entity - lms_chapter

**Display name:** Chapter
**Owning module(s):** [lms_chapter](../C05-modules/lms_chapter.md)
**Mode:** as-built (Content Entity, translatable)

## Purpose
The mid level of the learning hierarchy — groups ordered lessons within a course.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| langcode, default_langcode | translation |
| name | chapter title |
| field_description | formatted description |
| field_lessons | **multi-ref → lms_lesson** (ordered lessons) |
| weight | ordering within the course |
| status, created, changed | publish + timestamps |

## Relationships
- Parent: referenced by `lms_course.field_chapters`.
- Children: 1→N `lms_lesson` via `field_lessons`.

## Editorial/data management
Instructors edit chapters inside the course-authoring UI; ordering via `weight`. All copy in fields, no hardcoded text. Cache invalidated on save; parent course cache tag bubbled. See [C16 sheet 01](../C16-item-solutions/01-entities.md).
