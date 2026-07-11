---
page: Quiz take
drupal_concept: controller + Form API + JS
mode: as-built
owner_module: lms_quiz
route_group: app
route_name: lms_quiz.take
prototype_type: player
adequacy: PASS
---

# C11 Page - Quiz take

## Purpose
Làm một lượt quiz tại `/quiz/{lms_quiz}/take`: render câu hỏi (8 loại), áp dụng giới hạn thời gian, submit để chấm điểm (grading) (F-006/WF-002).

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

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Ngữ cảnh quiz | "Complete Modern React & Next.js Bootcamp · Section 2" / "10 questions · Passing score 70% · Attempt 1 of 3" | dynamic (từ the module + field time limit/field take quiz number) |
| Tiến độ câu hỏi | "Question 3 of 10" + đồng hồ đếm ngược "14:52" | JS timer (quiz player block) |
| Cảnh báo/validation chưa trả lời hết | "Answered 3 of 10 · unanswered questions are marked incorrect at submission." | module string (quiz player) |
| CTA điều hướng | "‹ Previous" / "Next ›" | module string |
| CTA nộp bài | "Submit quiz" | module string |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile | Ở ≤575.98px `.container--narrow` (max-width 920px) co về 100% width; `.quiz__nav` (Previous/Submit/Next) là flex có `flex-wrap` nên các nút tự xuống dòng khi thiếu chỗ; `.quiz__dots` (question navigator, 10 ô) wrap nhiều dòng. |
| tablet | 768–991px: padding của `.quiz__q` co theo `clamp(1.4rem,3vw,2.2rem)`; `.quizwrap` (max-width 760px) vẫn căn giữa trong `.container--narrow`. |
| desktop | 992–1199px: `.quiz__q` đạt padding tối đa (2.2rem); `.quiz__dots` đủ chỗ hiển thị 1 hàng 10 ô. |
| wide | ≥1200px: `.quizwrap` giữ nguyên max-width 760px căn giữa trang; phần thừa hai bên chỉ là khoảng trắng của `.container`. |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Take + auto-grade | done | essay → pending review |
