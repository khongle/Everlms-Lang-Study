---
page: AI course builder
drupal_concept: Form API + AI service
mode: as-built
owner_module: lms_ai
route_group: Manage
route_name: lms_ai.course_builder_form
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
| Route name | `lms_ai.course_builder_form` |
| Drupal page primitive | Form API + `CourseBuilderService` + private tempstore |
| Owner module | lms_ai |
| Access | `create lms_course` (instructor/content_editor) |
| Cache | uncacheable (form) |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| — | (form only) | content | — |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Input | topic/prompt + audience + tone + provider select (OpenAI/Gemini/Pollinations) |
| Output | sanitized structured draft preview (outline/lessons/quiz) from private tempstore |
| Save | accept draft -> unpublished course/chapter/lesson/quiz entities + lesson generation queue |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Generated draft | `@AiProvider` response normalized by `CourseBuilderService` | editor roles | private tempstore | generate -> draft shown |
| Provider options | `lms_ai` config | administrator | config | configure provider → option |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| None | drafts are AI output; saved into entities | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | prompt form + provider select |
| Empty | no prompt → idle |
| Access denied | non-editor → 403 |
| Draft review | generated outline appears with save/discard actions |
| Validation error | provider error -> red graceful message (no corrupt save) |
| Loading | form submission waits for provider response |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| AI draft + save | done | human-in-the-loop; route uses `create lms_course`; keys in config (R-001) |
