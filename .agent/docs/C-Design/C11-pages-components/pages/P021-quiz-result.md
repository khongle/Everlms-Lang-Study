---
page: Quiz result
drupal_concept: entity view
mode: as-built
owner_module: lms_quiz
route_group: app
route_name: lms_quiz.result
prototype_type: detail
adequacy: PASS
---

# C11 Page - Quiz result

## Purpose
Kết quả một lượt làm bài tại `/quiz-result/{lms_quiz_result}` (detail): điểm số, pass/fail, review theo từng câu hỏi.

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
Normal: result · Empty: n/a · Access denied: other user → 403 · Validation error: n/a · Loading: lazy.

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Success (đạt) | "Passed" + "You scored above the 70% passing mark for this quiz." | module string (status--success) |
| Trạng thái không đạt (mô tả trong alert) | "If you don't pass" / "Scoring below 70% shows a Not passed badge here instead, with the same correct/incorrect breakdown, and lets you retake immediately — you have 2 attempts remaining before the quiz locks." | module string (`status--error` badge text = "Not passed") |
| Giải thích câu sai | "You selected A. Keys are a stability hint for React's reconciliation — they are not related to sort order or styling." | dynamic (feedback theo the module) |
| CTA làm lại | "Retake quiz" | module string |
| CTA xem chứng chỉ | "View certificate" | module string |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile | Ở ≤575.98px `.container--narrow` co về 100% width; `.kpis` (Correct/Incorrect/Time taken/Attempt used) xuống 1 cột ở ≤479.98px; `.toolbar` cuối trang ("Retake quiz"/"Back to course"/"View certificate") wrap xuống nhiều dòng; ring điểm số (`--sz:160px`) giữ kích thước cố định. |
| tablet | 768–991px: `.kpis` hiển thị 2 cột; `.container--narrow` (920px) đã full width dưới ngưỡng này. |
| desktop | 992–1199px: `.kpis` đủ 4 cột; nội dung căn giữa trong `.container--narrow` (920px). |
| wide | ≥1200px: bố cục không đổi thêm so với desktop, chỉ nới khoảng trắng ngoài `.container--narrow`. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Result view | done | shows correct answers if enabled |
