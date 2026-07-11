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
Outline chapter/lesson của một course; có thể điều hướng trong player, chỉ đọc (read-only) ở trang detail.

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
- Phản ánh đúng thứ tự chapter/lesson; lesson đã hoàn thành được đánh dấu; trạng thái locked tuân theo gating.
