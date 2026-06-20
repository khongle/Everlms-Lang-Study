---
page: Course catalog
drupal_concept: View page / controller list
mode: as-built
owner_module: lms_course
route_group: Public
route_name: lms_course.courses
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Course catalog

## Purpose
The public catalog at `/courses`: browse, filter (category/level/language/tag), search, and open a course. Top of the conversion funnel.

## Source Evidence
- **Canonical URL:** `/courses` · **Owner module:** lms_course
- **Editable source:** course entities + taxonomy; listing layout via View/controller
- **Editor management UI:** course entity forms; taxonomy term UI
- **Access evidence:** anonymous

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/courses` (search: `/api/courses/search`) |
| Route name | `lms_course.courses` |
| Drupal page primitive | Controller/View list with exposed filters |
| Owner module | lms_course |
| Theme/layout | `lms_theme`, grid of course_card blocks |
| Access | anonymous |
| Cache | tags: `lms_course_list`; contexts: `url.query_args` (filters), `user.roles` |

## Placed Blocks
| Block | Label | Region | Primitive | Why |
| ----- | ----- | ------ | --------- | --- |
| [course_card](../blocks/course_card.md) | Course card | content | Views row/component | each catalog item |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Filtering | exposed filters: category/level/language/tag + keyword |
| Sorting | newest / bestseller / rating / price |
| Pagination | paged (e.g. 12/page) |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Course cards | `lms_course` entities (published) | n/a | `lms_course_list` | publish course → card appears |
| Filters | taxonomy vocabularies | content_editor | term cache | add term → filter option |
| Aggregate rating | `lms_course.field_rating` | n/a | course tag | new rating → updates |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| None | catalog driven by entities + taxonomy | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | grid of courses |
| Empty | "no courses match" message |
| Access denied | n/a (public) |
| Validation error | invalid filter → ignored |
| Loading | skeleton/lazy load |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Catalog + filters + search | done | 40 courses seeded |
