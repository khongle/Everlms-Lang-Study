# C04 Entity - lms_course

**Display name:** Course
**Owning module(s):** [lms_course](../C05-modules/lms_course.md)
**Mode:** as-built (Content Entity, translatable)

## Purpose
The catalog unit and root of the learning hierarchy (Course → Chapter → Lesson). Holds marketing metadata, pricing, taxonomy classification, instructor, and the chapter list.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | owner/author (instructor) |
| langcode, default_langcode | translation |
| name | course title |
| field_description, field_short_description | full + short body (formatted) |
| field_thumbnail, field_banner | catalog image + hero (image fields) |
| field_category, field_level, field_language | taxonomy refs (`course_category`/`course_level`/`course_language`) |
| field_price, field_price_type | price + free/paid type |
| field_duration | total duration |
| field_bestseller, field_featured | catalog flags |
| field_what_learn, field_prerequisites, field_who_for | formatted marketing sections |
| field_rating | cached aggregate rating |
| field_chapters | **multi-ref → lms_chapter** (curriculum) |
| field_instructor | **multi-ref → user** |
| field_tags | **multi-ref → taxonomy (`course_tags`)** |
| status, created, changed | publish + timestamps |

## Relationships
- 1→N `lms_chapter` (via `field_chapters`); chapters 1→N `lms_lesson`.
- N→1 instructor (`user`), N→N tags/category taxonomy.
- Referenced by: `lms_user_course` (enrollment), `lms_rating`, `lms_wishlist`, `lms_order_item`, `lms_course_bundle.field_courses`, `lms_forum_topic.field_course`.

## Editorial/data management
Instructors/content_editors edit via the Course entity form (management UI). All learner-visible copy lives in fields (`name`, `field_description`, marketing sections) — no hardcoded text. Validation: required title/price; revisions/translation supported (translatable). Cache: invalidated on save via entity cache tags (`lms_course:<id>`). See [C02 editorial matrix](../C02-user-roles/_index.md), [C16 sheet 01](../C16-item-solutions/01-entities.md).
