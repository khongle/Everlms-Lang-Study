---
block: Quiz player
drupal_concept: block plugin + JS
owner_module: lms_quiz
region: content
block_type: plugin
adequacy: PASS
---

# C11 Block - Quiz player

## Purpose
Render câu hỏi quiz (8 loại), timer, và submit trên trang quiz-take.

## Drupal 11 Contract
| Concern | Contract |
| --- | --- |
| Block ID | quiz_player |
| Drupal primitive | block plugin + JS |
| Owner module | lms_quiz |
| Placed on | P020 |
| Cache | uncacheable (per-attempt) |

## Configuration / Fields
| Item | Requirement |
| --- | --- |
| Data | `lms_quiz.field_questions`, per-type widgets |

## Rendering Responsibilities
| Responsibility | Requirement |
| --- | --- |
| Twig/theme | question widgets + timer |
| Access | enrolled + prereqs met |
| Responsive | yes |
| Editing | questions from entities |

## Prototype Evidence
| Evidence | Path |
| --- | --- |
| Live | `/quiz/{id}/take` |

## Tests / Checks
- Cả 8 loại widget đều render đúng; timer áp giới hạn thời gian; submit gửi sang chấm điểm (grading).
