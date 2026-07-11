---
page: AI course builder
drupal_concept: Form API + AI service
mode: as-built
owner_module: lms_ai
route_group: manage
route_name: lms_ai.course_builder_form
form_mode: manage_edit
prototype_type: form
adequacy: PASS
---

# C11 Page - AI course builder

## Purpose
Sinh bản nháp course/lesson/quiz từ một prompt tại `/manage/courses/ai-builder` (F-018/WF-010), xem lại rồi lưu vào entity.

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

## Message copy inventory
| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Empty (chưa generate) — subtitle mở đầu trang | "Describe the course you want to teach — get a draft outline in seconds." | the module — Twig template `course-builder-form` (chuỗi tĩnh, dịch qua UI string translation) |
| Banner cảnh báo — AI-assisted drafting | "AI-assisted drafting. The outline below is generated automatically and may need edits. Nothing is saved until you accept — review sections and lessons, regenerate anything that misses the mark, then send it to the course editor." | the module — Twig template `course-builder-form` (`.alert.alert--info`) |
| CTA chính — sinh outline từ prompt | "Generate outline" | the module — Twig template `course-builder-form`, submit form → `CourseBuilderService` |
| Draft review — badge trạng thái vừa sinh | "Generated just now" | the module — Twig template `course-builder-form`, cập nhật theo timestamp phản hồi `CourseBuilderService` |
| CTA phụ — sinh lại toàn bộ outline | "Regenerate all" | the module — Twig template `course-builder-form` |
| CTA từng mục curriculum — sinh lại 1 lesson | "Regenerate" | the module — Twig template `course-builder-form` (`.curri__item`) |
| CTA chấp nhận — lưu draft & mở editor | "Accept & open in editor" | the module — Twig template `course-builder-form`, submit sang the module.course editor form |
| Validation error — field "Topic" bắt buộc | "Topic is required." | Form API `#required` trên field `topic` / `CourseBuilderService::validateForm()` — chưa có chuỗi cụ thể trong prototype tĩnh, viết hợp lý theo mẫu lỗi field bắt buộc chuẩn Drupal Form API |
| Validation error — provider trả lỗi (`@AiProvider`) | "We couldn't generate a draft right now. Please try again or choose a different AI provider." | `CourseBuilderService` bắt lỗi `@AiProvider` — chưa có chuỗi cụ thể trong prototype tĩnh, viết hợp lý theo Page States "provider error -> red graceful message" |

## Responsive behavior
| Breakpoint | Behavior |
| --- | --- |
| mobile (<576px) | `.container` co về 100% chiều rộng; `.editor` (nội dung form + draft review co giãn cùng aside 340px) đã gộp 1 cột từ ngưỡng ≤991.98px nên ở mobile luôn xếp dọc 1 cột, aside "Generation pipeline"/"Provider" nằm dưới cùng sau panel "2. Review the draft"; `.formgrid--2` (Target audience/Level, Number of modules/Tone) rút xuống 1 cột ở ≤639.98px; các dòng curriculum (`.curri__item`) và nút "Regenerate" tự xuống dòng theo flex-wrap. |
| tablet (768–991px) | `.container` giới hạn 720px; `.editor` vẫn 1 cột (ngưỡng gộp là ≤991.98px) nên aside "Generation pipeline"/"Provider" tiếp tục nằm dưới nội dung chính; `.formgrid--2` đã quay lại 2 cột vì ngưỡng rút gọn chỉ áp dụng ở ≤639.98px. |
| desktop (992–1199px) | `.container` giới hạn 960px; `.editor` chuyển sang layout 2 cột: khối form + draft review co giãn (`minmax(0,1fr)`) cạnh aside "Generation pipeline"/"Provider" cố định 340px, `position:sticky;top:90px`. |
| wide (≥1200px) | `.container` giới hạn 1140px (đến 1399.98px) rồi rộng hơn ở khổ rất lớn; layout giữ nguyên 2 cột như desktop (form/draft co giãn + aside 340px sticky), chỉ nới thêm khoảng trắng hai bên. |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| AI draft + save | done | human-in-the-loop; route uses `create lms_course`; keys in config (R-001) |
