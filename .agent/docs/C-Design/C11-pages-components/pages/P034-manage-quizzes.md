---
page: Manage quizzes
drupal_concept: View list + entity ops
mode: as-built
owner_module: lms_quiz
route_group: manage
route_name: lms_course.workspace_quizzes
prototype_type: workspace-list
adequacy: PASS
---

# C11 Page - Manage quizzes

## Purpose
Danh sách/tạo/chỉnh sửa quiz tại `/workspace/quizzes` (workspace-list).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace/quizzes` (create, `{quiz}/edit`) |
| Route name | `lms_course.workspace_quizzes` |
| Primitive | list + entity form |
| Access | instructor/manager |
| Cache | tags: quiz list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Quiz rows | `lms_quiz` | manage quizzes | quiz list | create → row |

## Page States
Normal: list · Empty: none → create · Access denied: non-staff → 403 · Validation error: n/a · Loading: paged.

## Listing behavior
| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| Newest (created), Most attempts, Highest avg score — 3 lựa chọn của dropdown `.select` trong `.toolbar`; các cột `title`/`course`/`questions`/`status` trong `.wtable` không có control sort riêng | Newest (option đầu tiên của `.select`, tương ứng `created` desc) | 6 dòng/trang trong prototype (11 quiz tổng → `.pager` hiển thị 2 trang: 1, 2) | equals(status) qua `.segmented` (All (11) / Published (9) / Draft (2)); contains(title) qua ô tìm kiếm `.nav__search` (placeholder “Search quizzes…”) | url.query_args (filter chia sẻ/bookmark được) | “No results for a search? Try clearing filters — new quizzes appear here as soon as you create them from a course’s Curriculum tab.” (khối `.alert` cuối bảng, gợi ý xoá filter hoặc tạo quiz mới từ tab Curriculum của khoá học) |

## Message copy inventory
| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| CTA chính (tạo quiz mới) | "New quiz" | local action link trong `.pagehead__actions` (route tạo quiz, module the module) |
| Toolbar filter theo trạng thái | "All (11)" / "Published (9)" / "Draft (2)" | `.segmented` — nhãn + đếm động theo view/query (module the module) |
| Ô tìm kiếm | "Search quizzes…" | placeholder input `.nav__search` (View exposed filter label, `contains(title)`) |
| Trạng thái quiz (badge) | "Published" / "Draft" | base field `status` (published/unpublished) — xem C04 the module.md ## Baseline fields |
| Empty/no-results | "No results for a search? Try clearing filters — new quizzes appear here as soon as you create them from a course’s Curriculum tab." | View empty-text config / block alert tĩnh trong template manage quizzes |

## Responsive behavior
| Breakpoint | Behavior |
| --- | --- |
| mobile (≤575.98px) | `.pagehead` (tiêu đề + CTA "New quiz") wrap xuống dòng; `.toolbar` (`.segmented` + `.nav__search` + `.select` sort) xếp chồng nhiều hàng full-width; `.wtable__wrap{overflow-x:auto}` cho bảng cuộn ngang thay vì chuyển card; `.pager` wrap, canh giữa |
| tablet (768–991.98px) | `.container` co theo `container-sm`/`container-md`; `.toolbar` wrap nếu `.segmented` + tìm kiếm + sort không đủ chỗ trên 1 hàng; bảng `.wtable` vẫn cuộn ngang (`.wtable__wrap`) khi cột không vừa viewport |
| desktop (992–1199.98px) | `.container` = `container-lg`; `.pagehead`, `.toolbar` và `.tablecard` hiển thị đầy đủ trên 1 hàng theo layout chuẩn workspace-list |
| wide (≥1200px) | `.container` = `container-xl` (≥1400px full); toàn bộ 7 cột bảng (Quiz, Course, Questions, Attempts, Avg score, Status, actions) hiển thị không cần cuộn ngang |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Manage quizzes | done | attach questions |
