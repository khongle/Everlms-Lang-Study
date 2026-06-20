---
block: Course card
drupal_concept: Views row / SDC component
owner_module: lms_course
region: content
block_type: component
adequacy: PASS
---

# C11 Block - Course card

## Purpose
Reusable card showing a course (thumbnail, title, instructor, price, rating). Used on catalog, category, homepage, dashboard.

## Drupal 11 Contract
| Concern | Contract |
| --- | --- |
| Block ID | course_card |
| Drupal primitive | Views row plugin / Twig SDC |
| Owner module/theme | lms_course / lms_theme |
| Placed on | P002, P004, P005, P001, P017 |
| Cache | tags: `lms_course:{id}`, rating tag; contexts: `user` (wishlist state) |

## Configuration / Fields
| Item | Requirement |
| --- | --- |
| Data | course entity fields (name, thumbnail, price, rating, instructor) |

## Rendering Responsibilities
| Responsibility | Requirement |
| --- | --- |
| Twig/theme | card markup/classes only |
| Access | published courses; hide price if free |
| Responsive | grid-friendly |
| Editing | content from `lms_course` fields (editor-managed) |

## Prototype Evidence
| Evidence | Path |
| --- | --- |
| Live | `/courses` cards |

## Tests / Checks
- Renders all card fields; wishlist toggle reflects state; cache tag invalidates on course edit.
