---
page: Manage blog
drupal_concept: entity list + form
mode: as-built
owner_module: lms_blog
route_group: manage
route_name: lms_blog.manage
prototype_type: workspace-list
adequacy: PASS
---

# C11 Page - Manage blog

## Purpose
Quản trị bài viết tại `/manage/blog` (workspace-list): tạo/chỉnh sửa/xoá `lms_article`.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/manage/blog` (add, `{article}/edit/delete`) |
| Route name | `lms_blog.manage` |
| Primitive | entity list + form |
| Access | content_editor |
| Cache | tags: article list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Article rows | `lms_article` | manage blog | article list | create → P008 listing |

## Page States
Normal: list · Empty: none → add · Access denied: non-editor → 403 · Validation error: required → errors · Loading: paged.

## Listing behavior
| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| title, status, date (published/scheduled) — các `<th>` của `.wtable` (Title/Category/Author/Status/Date/Views) là text tĩnh, prototype không có link/icon sort, nhưng đây là field hợp lý cho danh sách quản trị bài viết | date DESC theo cập nhật/xuất bản gần nhất (khớp gần đúng thứ tự hiển thị: Jul 8 → Jul 5 → Jul 15 scheduled → Jun 29 → Jun 24; các dòng Draft "Not scheduled" chèn theo thời điểm sửa gần nhất) | 20 | contains(title) qua ô "Search posts…" (`.nav__search input[type=search]`); equals(status) qua `.segmented` (All/Published/Draft/Scheduled); range(date) trên cột Date (chưa có control hiển thị trong prototype, suy luận hợp lý cho lọc theo khoảng ngày xuất bản/lên lịch) | url.query_args (filter chia sẻ/bookmark được) | "No posts match your filters" / "Try clearing the search or switching back to "All" — or start a new post." (`.alert` cuối danh sách, link "start a new post" trỏ tới action tạo bài mới) |

## Message copy inventory
| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Tiêu đề trang + số liệu tổng quan (Normal) | "36 posts · 2 scheduled · 4 drafts" | the module (workspace blog template, `.pagehead__t p`) |
| CTA chính (tạo bài mới) | "New post" | the module (`.pagehead__actions .btn--primary`) |
| CTA phụ (soạn bằng AI) | "Draft with AI" | the module (`.pagehead__actions .btn--ghost`, trỏ sang P042 content tools) |
| Bộ lọc trạng thái (`.segmented`) | "All (36)" / "Published (30)" / "Draft (4)" / "Scheduled (2)" | the module (view/filter labels; số đếm động theo dữ liệu) |
| Ô tìm kiếm | "Search posts…" | the module (placeholder `input[type=search]`) |
| Hành động theo dòng (`.rowbtns` aria-label) | "Edit" / "View" | the module (`.rowbtns a[aria-label]`) — dòng Draft/Scheduled chỉ có "Edit" (chưa publish nên không có "View") |
| Empty result (không có bài khớp bộ lọc) | "No posts match your filters" / "Try clearing the search or switching back to "All" — or start a new post." | the module (`.alert` cuối danh sách) |
| Validation error (thiếu trường bắt buộc khi lưu bài) | "Title field is required." | Form API `#required` constraint cho field `title` — chưa có mẫu tĩnh lỗi trong prototype (chỉ có markup danh sách), suy luận từ C04 the module.md ## Baseline fields (title là field cốt lõi) và dòng "Validation error \| required → errors" ở ## Page States |

## Responsive behavior
| Breakpoint | Behavior |
| --- | --- |
| mobile | <576px: `.toolbar` (flex-wrap) xếp `.segmented` (All/Published/Draft/Scheduled) trên; ô tìm kiếm dùng chung class `.nav__search` với thanh tìm kiếm header nên bị ẩn hẳn (`display:none`) từ ngưỡng `@media(max-width:991.98px)`, `.toolbar` không có control tìm kiếm thay thế ở mobile; bảng `.wtable` không chuyển thành card mà cuộn ngang trong `.wtable__wrap{overflow-x:auto}`; `.pager` (flex-wrap) xuống dòng khi không đủ chỗ; `.pagehead` xếp tiêu đề/số liệu trên, nút "Draft with AI"/"New post" xuống dòng dưới; `.container` co về 100% width. |
| tablet | 768–991px: vẫn trong vùng `@media(max-width:991.98px){.nav__search{display:none}}` nên ô tìm kiếm "Search posts…" vẫn bị ẩn như mobile, chỉ `.segmented` còn dùng được để lọc; bảng `.wtable` vẫn cuộn ngang nếu rộng hơn viewport; `.container` giới hạn theo `--container-sm`/`--container-md`. |
| desktop | 992–1199px: vượt ngưỡng 991.98px nên ô tìm kiếm `.nav__search` hiển thị trở lại (không còn `display:none`); do input có style nội tuyến `min-width:220px` nên độ rộng cố định 220px, không co theo rule `@media(max-width:1100px){.nav__search{min-width:150px}}` (specificity của style nội tuyến cao hơn); `.toolbar` đủ chỗ để `.segmented` và ô tìm kiếm nằm cùng hàng; `.container` giới hạn theo `--container-lg`. |
| wide | ≥1200px: `.container` mở theo `--container-xl` (tới 1399.98px) rồi theo max-width mặc định ở ≥1400px; bảng `.wtable` đủ rộng hiển thị toàn bộ cột (Title/Category/Author/Status/Date/Views/actions) không cần cuộn ngang; `.toolbar`, `.pagehead` và `.pager` giữ nguyên một hàng, không wrap. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Manage blog | done | AI drafting available |
