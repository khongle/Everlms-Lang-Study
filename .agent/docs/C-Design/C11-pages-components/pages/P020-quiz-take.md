---
page: Quiz take
drupal_concept: controller + Form API + JS
mode: as-built
owner_module: lms_quiz
route_group: App
route_name: lms_quiz.take
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Quiz take

## Purpose
Attempt a quiz at `/quiz/{lms_quiz}/take`: render questions (8 types), enforce time limit, submit to grading (F-006/WF-002).

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/quiz/{lms_quiz}/take` (submit: `/quiz/{lms_quiz}/submit`) |
| Route name | `lms_quiz.take` |
| Drupal page primitive | controller + Form API + JS timer |
| Owner module | lms_quiz |
| Access | enrolled; prerequisites met (`field_prerequisite_quizzes`) |
| Cache | uncacheable (per-attempt) |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| [quiz_player](../blocks/quiz_player.md) | Quiz player | content | renders questions + timer |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Question render | per-type widget (choice/true-false/matching/ordering/essay/…) |
| Timing | `field_time_limit` countdown |
| Submit | grade via `GradingStrategyFactory` → `lms_quiz_result` |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Questions/options | `lms_question` + `lms_answer` | enrolled | quiz tag | edit question → updates |
| Attempt limit | `field_take_quiz_number` | — | — | exceed → blocked |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| None | questions in entities | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | question form + timer |
| Empty | quiz with no questions → message |
| Access denied | not enrolled / prereq unmet → locked |
| Validation error | unanswered required → prompt |
| Loading | render questions |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Take + auto-grade | done | essay → pending review |
