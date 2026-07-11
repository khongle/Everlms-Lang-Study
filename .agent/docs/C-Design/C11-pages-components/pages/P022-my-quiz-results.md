---
page: My quiz results
drupal_concept: controller list
mode: as-built
owner_module: lms_quiz
route_group: app
route_name: lms_quiz.my_results
prototype_type: listing
adequacy: PASS
---

# C11 Page - My quiz results

## Purpose
Lịch sử kết quả tại `/my-quiz-results` (listing).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/my-quiz-results` |
| Route name | `lms_quiz.my_results` |
| Primitive | controller list (own results) |
| Deviation | controller-based, not a View — [ADR-006](../../../G-Governance/G01-adrs/ADR-006-controller-based-listings.md) |
| Access | authenticated (own) |
| Cache | per-user |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Result rows | `lms_quiz_result` (own) | own | per-user | attempt → row |

## Page States
Normal: list · Empty: none · Access denied: anon → login · Validation error: n/a · Loading: paged.

## Listing behavior

| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| Không có sortable header trên `.wtable`; dữ liệu mẫu nhóm theo course | ngầm định theo `date` DESC trong từng course (không có control đổi sort) | không phân trang trong prototype — 9/9 dòng hiển thị hết | segmented equals(status: all\|passed\|failed) qua `.segmented` — "All (9)" / "Passed (7)" / "Failed (2)" | prototype dùng `href="#"` (chưa gắn query arg thật); as-built cần `?status=` để chia sẻ/bookmark được | "No attempts match this filter" / "Try a different filter, or take a quiz from any lesson to see your results appear here." + CTA "Go to my courses" |

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Hero (động theo dữ liệu) | "9 attempts across 5 courses. Keep an eye on Next.js & Full-Stack and Model Evaluation — a retake is available any time." | the module my results controller |
| Trạng thái hàng | "Passed" / "Failed" | module string (status--success / status--error) |
| Empty (lọc không có kết quả) | "No attempts match this filter" / "Try a different filter, or take a quiz from any lesson to see your results appear here." | View empty-text config |
| CTA chính / phụ | "Go to my courses" / "Back to dashboard" | module string |
| Action icon hàng | aria-label "View result" | module string |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile | Ở ≤575.98px `.kpis` (Total attempts/Average score/Passed/Best score) về 1 cột ở ≤479.98px; `.segmented` (All/Passed/Failed) wrap khi thiếu chỗ; `.wtable` cuộn ngang trong `.wtable__wrap{overflow-x:auto}` thay vì vỡ layout. |
| tablet | 768–991px: `.kpis` hiển thị 2 cột; bảng `.wtable` vẫn có thể cuộn ngang do nhiều cột (Quiz, Course, Score, Status, Date, action). |
| desktop | 992–1199px: `.kpis` đủ 4 cột; bảng hiển thị đủ các cột không cần cuộn với độ dài tên khoá hiện tại. |
| wide | ≥1200px: `.container` mở max-width đầy đủ; bảng có nhiều khoảng trắng hơn giữa các cột. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Results history | done | — |
