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
Hiển thị phần trăm hoàn thành của learner cho một course; dùng trong player và my-courses/dashboard.

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
- Phần trăm cập nhật sau khi hoàn thành lesson; không bao giờ hiển thị progress của user khác.
