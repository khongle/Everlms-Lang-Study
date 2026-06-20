---
block: Rating & reviews
drupal_concept: block plugin
owner_module: lms_rating
region: content
block_type: plugin
adequacy: PASS
---

# C11 Block - Rating & reviews

## Purpose
Shows aggregate rating + review list and a submit form (enrolled learners) on course detail.

## Drupal 11 Contract
| Concern | Contract |
| --- | --- |
| Block ID | rating_widget |
| Drupal primitive | block plugin + Form API |
| Owner module | lms_rating |
| Placed on | P003 |
| Cache | tags: rating list, `lms_course:{id}` |

## Configuration / Fields
| Item | Requirement |
| --- | --- |
| Data | `lms_rating` (approved), aggregate |

## Rendering Responsibilities
| Responsibility | Requirement |
| --- | --- |
| Twig/theme | stars + reviews + form |
| Access | submit only for enrolled |
| Responsive | yes |
| Editing | reviews are user content (moderated) |

## Prototype Evidence
| Evidence | Path |
| --- | --- |
| Live | course detail reviews |

## Tests / Checks
- Aggregate matches approved ratings; submit gated to enrolled; one-per-user.
