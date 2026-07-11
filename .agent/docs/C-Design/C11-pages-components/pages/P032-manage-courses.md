---
page: Manage courses
drupal_concept: View list + entity ops
mode: as-built
owner_module: lms_course
route_group: manage
route_name: lms_course.workspace_courses
prototype_type: workspace-list
adequacy: PASS
---

# C11 Page - Manage courses

## Purpose
Danh sách khoá học phía back-office tại `/workspace/courses`: instructor/editor tìm kiếm, tạo mới, chỉnh sửa, publish/unpublish, xoá khoá học.

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/workspace/courses` (create `/workspace/courses/create`, edit `/workspace/courses/{id}/edit`) |
| Route name | `lms_course.workspace_courses` |
| Drupal page primitive | View/controller list + row operations |
| Deviation | controller-based, not a View — [ADR-006](../../../G-Governance/G01-adrs/ADR-006-controller-based-listings.md) |
| Owner module | lms_course |
| Access | instructor/content_editor/manager (scoped: instructor = own) |
| Cache | tags: `lms_course_list`; contexts: `user` |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| course_card (compact) | Course row | content | list row |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| List | title, status, enrollments, rating, actions |
| Ops | create, edit, publish toggle, delete |
| Scope | instructor sees own; manager sees all |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Course rows | `lms_course` | manage courses | `lms_course_list` | create → row appears |
| Row actions | permission-gated | per role | — | hidden if no perm |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| None | — | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | course table |
| Empty | "no courses" + create CTA |
| Access denied | non-staff → 403 |
| Validation error | n/a (list) |
| Loading | paged |

## Listing behavior
| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| `select.select` 4 lựa chọn: Newest (cập nhật/tạo), Most enrolled (Enrolled), Top rated (Rating), Revenue | Newest (option đầu tiên trong `<select>`, không option nào khác đánh dấu `selected`) | 5 dòng/trang (`tbody` render 5 khóa học trong prototype; `.pager` dừng ở trang "5", khớp `ceil(24/5)=5`) | `equals(status)` qua `.segmented` (All 24 / Published 20 / Draft 3 / In review 1); `contains(title)` qua `input[type=search]` placeholder "Search courses…" | url.query_args (filter chia sẻ/bookmark được) | Prototype P032 không render markup 0-kết-quả trực tiếp; theo `## Page States` ở trên: "no courses" + CTA "New course"; pattern chia sẻ cùng archetype workspace-list với P034 (Manage quizzes), vốn dùng khối `.alert`: "No results for a search? Try clearing filters — new quizzes appear here as soon as you create them from a course's Curriculum tab." (áp dụng tương tự khi filter/search courses không khớp) |

## Message copy inventory
| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Page intro (count) | "24 courses · 3 drafts · 1 pending review" | the module (View/controller template header) |
| Primary CTA | "New course" | the module (`.pagehead__actions`) |
| Secondary CTA | "Build with AI" | the module (`.pagehead__actions`) |
| Status filter tabs | "All (24)" / "Published (20)" / "Draft (3)" / "In review (1)" | View exposed filter (status) labels + count aggregation |
| Search input | placeholder "Search courses…" (aria-label "Search courses") | View exposed filter (title contains) placeholder string |
| Sort control | "Newest" / "Most enrolled" / "Top rated" / "Revenue" | View sort criteria exposed as `<select>` options |
| Row status badge | "Published" / "In review" / "Draft" | Content moderation / the module workflow state labels |
| Row actions (icon buttons) | aria-label "Edit" / aria-label "View" | the module (row action markup) |
| Empty-state (0 courses) | paraphrase theo `## Page States`: "no courses" + CTA "New course" (không có markup literal trong file HTML này) | View empty-text config (`views.view.*` empty region) |
| Pagination | "Prev" / "Next" | Pager theme (View pager labels) |

## Responsive behavior
| Breakpoint | Behavior |
| --- | --- |
| mobile (<576px) | `.container` co về 100% chiều rộng (rule ≤575.98px); `.kpis` (4 tile KPI) rút xuống 1 cột dọc ở ≤479.98px; `.pagehead` (flex, wrap) xếp `.pagehead__t` (tiêu đề + count "24 courses · 3 drafts · 1 pending review") ở trên, `.pagehead__actions` ("Build with AI", "New course") xuống dưới; `.toolbar` (segmented + search + sort) là flex-wrap nên `.segmented` và cụm search/sort tự xuống dòng khi không đủ chỗ; bảng `.wtable` KHÔNG chuyển sang dạng card — `.wtable__wrap{overflow-x:auto}` giữ nguyên cấu trúc bảng và cho scroll ngang trong khung `.tablecard`. |
| tablet (768–991px) | `.container` giới hạn theo `--container-md` (720px, rule ≤991.98px); `.kpis` còn 2 cột (rule ≤991.98px); `.pagehead` vẫn flex-wrap nên có thể xuống dòng nếu tiêu đề + actions không vừa 1 hàng; ô tìm kiếm `.nav__search` co còn `min-width:150px` từ mốc ≤1100px; bảng `.wtable` (7 cột: Course/Status/Enrolled/Rating/Revenue/Updated/actions) vẫn scroll ngang qua `.wtable__wrap` nếu không vừa `--container-md`. |
| desktop (992–1199px) | `.container` giới hạn theo `--container-lg` (960px, rule ≤1199.98px); `.kpis` quay lại đủ 4 cột; `.pagehead__t` và `.pagehead__actions` nằm cùng hàng (`justify-content:space-between`); `.toolbar` đủ chỗ nên `.segmented` (4 tab) và cụm search+sort thường nằm cùng 1 hàng; bảng `.wtable` hiển thị đủ 7 cột, thường không cần scroll. |
| wide (≥1200px) | `.container` giới hạn theo `--container-xl` (1140px, đến 1399.98px) rồi `--container-xxl` (1280px) ở khổ rất rộng; layout giữ nguyên như desktop (KPI 4 cột, `.pagehead` 1 hàng, `.toolbar` 1 hàng, bảng 7 cột không scroll), chỉ nới thêm khoảng trắng hai bên. |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Manage list + ops | done | scope by role |
