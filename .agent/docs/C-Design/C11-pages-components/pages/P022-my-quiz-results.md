---
page: My quiz results
drupal_concept: controller list
mode: as-built
owner_module: lms_quiz
route_group: App
route_name: lms_quiz.my_results
prototype_type: live-site
adequacy: PASS
---

# C11 Page - My quiz results

## Purpose
Results history at `/my-quiz-results` (listing).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/my-quiz-results` |
| Route name | `lms_quiz.my_results` |
| Primitive | controller list (own results) |
| Access | authenticated (own) |
| Cache | per-user |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Result rows | `lms_quiz_result` (own) | own | per-user | attempt → row |

## Page States
Normal: list · Empty: none · Access denied: anon → login · Validation: n/a · Loading: paged.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Results history | done | — |
