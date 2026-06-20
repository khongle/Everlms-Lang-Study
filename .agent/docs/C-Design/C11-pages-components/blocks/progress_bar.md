---
block: Course progress
drupal_concept: block plugin
owner_module: lms_progress
region: content_top
block_type: plugin
adequacy: PASS
---

# C11 Block - Course progress

## Purpose
Shows the learner's percent-complete for a course; used in player and my-courses/dashboard.

## Drupal 11 Contract
| Concern | Contract |
| --- | --- |
| Block ID | progress_bar |
| Drupal primitive | block plugin |
| Owner module | lms_progress |
| Placed on | P019, P018, P017 |
| Cache | contexts: `user`; per-user (uncacheable or user-scoped) |

## Configuration / Fields
| Item | Requirement |
| --- | --- |
| Data | `lms_user_course.field_progress` |

## Rendering Responsibilities
| Responsibility | Requirement |
| --- | --- |
| Twig/theme | progress bar markup |
| Access | own progress only |
| Responsive | yes |
| Editing | computed (read-only) |

## Prototype Evidence
| Evidence | Path |
| --- | --- |
| Live | player header |

## Tests / Checks
- Percent updates after lesson completion; never shows other users' progress.
