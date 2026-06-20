---
page: AI course builder
drupal_concept: Form API + AI service
mode: as-built
owner_module: lms_ai
route_group: Manage
route_name: lms_ai.course_builder
prototype_type: live-site
adequacy: PASS
---

# C11 Page - AI course builder

## Purpose
Generate course/lesson/quiz drafts from a prompt at `/manage/courses/ai-builder` (F-018/WF-010), review, and save into entities.

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/manage/courses/ai-builder` |
| Route name | `lms_ai.course_builder` |
| Drupal page primitive | Form API + `CourseContentGenerator` (AJAX) |
| Owner module | lms_ai |
| Access | instructor / content_editor |
| Cache | uncacheable (form) |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| — | (form only) | content | — |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Input | topic/prompt + provider select (OpenAI/Gemini/Pollinations) |
| Output | structured draft (outline/lessons/quiz) to review |
| Save | accept draft → create course/lesson/quiz entities |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Generated draft | `@AiProvider` response | editor roles | — | generate → draft shown |
| Provider options | `lms_ai` config | administrator | config | configure provider → option |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| None | drafts are AI output; saved into entities | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | prompt + result |
| Empty | no prompt → idle |
| Access denied | non-editor → 403 |
| Validation error | provider error → graceful message (no corrupt save) |
| Loading | generating spinner |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| AI draft + save | done | human-in-the-loop; keys in config (R-001) |
