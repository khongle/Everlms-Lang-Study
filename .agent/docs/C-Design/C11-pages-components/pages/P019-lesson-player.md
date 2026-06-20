---
page: Lesson player
drupal_concept: controller + JS
mode: as-built
owner_module: lms_lesson
route_group: App
route_name: lms_progress.learn
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Lesson player

## Purpose
The learning surface at `/learn/{lms_course}/{lms_lesson}`: play lesson media, show curriculum sidebar, take notes, mark complete, advance. The core delivery page (F-003/F-005).

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/learn/{lms_course}/{lms_lesson}` |
| Route name | `lms_progress.learn` |
| Drupal page primitive | controller + JS (video, progress AJAX) |
| Owner module | lms_lesson + lms_progress |
| Access | **enrolled only** (or free-preview lesson / membership) |
| Cache | contexts: `user`, `route`; uncacheable progress via AJAX |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| [course_curriculum](../blocks/course_curriculum.md) | Curriculum | sidebar_first | navigate lessons |
| [progress_bar](../blocks/progress_bar.md) | Progress | content_top | course % |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Media | video (url/file), SCORM, H5P, attachments |
| Actions | mark-complete (AJAX → `/api/lms/progress/mark-complete`), next/prev |
| Notes | side panel (`lms_note`, video timestamp) |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Lesson content/media | `lms_lesson` fields | enrolled | `lms_lesson:{id}` | edit lesson → updates |
| Completion state | `lms_user_lesson` (own) | own | per-user | mark complete → checkmark |
| Notes | `lms_note` (own) | own | per-user | save note → appears |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| None | lesson content in entity | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | media + curriculum + controls |
| Empty | lesson without media → text only |
| Access denied | not enrolled (and not preview) → gate/CTA |
| Validation error | quiz-gate not met → locked |
| Loading | media buffering |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Player + progress + notes | done | drip/free-preview honored |
