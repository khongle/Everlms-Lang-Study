---
page: Quiz result
drupal_concept: entity view
mode: as-built
owner_module: lms_quiz
route_group: App
route_name: lms_quiz.result
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Quiz result

## Purpose
Attempt result at `/quiz-result/{lms_quiz_result}` (detail): score, pass/fail, per-question review.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/quiz-result/{lms_quiz_result}` |
| Route name | `lms_quiz.result` |
| Primitive | entity view controller |
| Access | own result (or instructor) |
| Cache | per-user; tags: result |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Score + responses | `lms_quiz_result`/`lms_question_response` | own/instructor | result tag | submit → result |

## Page States
Normal: result · Empty: n/a · Access denied: other user → 403 · Validation: n/a · Loading: lazy.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Result view | done | shows correct answers if enabled |
