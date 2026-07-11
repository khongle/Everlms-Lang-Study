---
page: Lesson player
drupal_concept: controller + JS
mode: as-built
owner_module: lms_lesson
route_group: app
route_name: lms_progress.learn
prototype_type: player
adequacy: PASS
---

# C11 Page - Lesson player

## Purpose
Bề mặt học tập (learning surface) tại `/learn/{lms_course}/{lms_lesson}`: phát media của lesson, hiển thị sidebar curriculum, ghi note, đánh dấu hoàn thành, chuyển tiếp. Trang delivery cốt lõi (F-003/F-005).

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

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Điều hướng quay lại | "My courses" (link `.player__topbar`) | module string |
| Tab labels | "Overview" / "Notes" / "Resources" / "Q&A" | module string |
| CTA làm quiz | "Take quiz" | module string |
| Điều hướng bài học | "Prev: Props & components" / "Next: Handling events" | dynamic (tên bài trước/sau từ the module) |
| Empty (Q&A chưa có câu hỏi) | "No questions yet on this lesson" / "Be the first to ask Sarah Chen or the community about state and useState." | module string (Q&A block empty-text, tên giảng viên động) |
| CTA/placeholder ghi chú | "Save note" · placeholder "Type what you want to remember from this moment in the lesson…" | module string (Notes form) |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile | Ở ≤575.98px `.player__stage` giữ aspect-ratio 16/9 và co theo chiều rộng; `.player` (2 cột: nội dung + rail curriculum) đã gộp 1 cột từ ≤1100px nên rail luôn nằm dưới nội dung bài học; `.tabs` (Overview/Notes/Resources/Q&A) cuộn ngang khi không đủ chỗ. |
| tablet | 768–991px: `.player` vẫn 1 cột (ngưỡng gộp riêng của player là 1100px, rộng hơn ngưỡng tablet thường) nên "Course curriculum" tiếp tục nằm dưới nội dung. |
| desktop | 992–1199px: dưới 1100px `.player` vẫn 1 cột; từ 1100px trở lên `.player` chuyển sang 2 cột (nội dung trái, rail curriculum phải, cuộn độc lập). |
| wide | ≥1200px: `.player` giữ 2 cột đầy đủ; rail curriculum cuộn riêng, không ảnh hưởng cuộn trang chính. |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Player + progress + notes | done | drip/free-preview honored |
