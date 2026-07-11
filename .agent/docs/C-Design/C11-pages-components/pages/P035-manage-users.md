---
page: Manage users
drupal_concept: View list + user ops
mode: as-built
owner_module: lms_course
route_group: manage
route_name: lms_course.workspace_users
prototype_type: workspace-list
adequacy: PASS
---

# C11 Page - Manage users

## Purpose
Quản trị user tại `/workspace/users` (workspace-list): danh sách, tạo mới, chỉnh sửa, thao tác hàng loạt (bulk), roles.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace/users` (create, bulk, `{user}/admin-edit`) |
| Route name | `lms_course.workspace_users` |
| Primitive | user list + forms |
| Access | manager/administrator |
| Cache | tags: user list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| User rows + roles | `user` entities | administer users | user list | create → row |

## Page States
Normal: list · Empty: n/a · Access denied: non-manager → 403 · Validation error: dup email → error · Loading: paged.

## Listing behavior

| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| `<select aria-label="Sort users">` có 4 option: "Newest first" (theo cột Joined, DESC), "Name A–Z" (tên user, ASC), "Most active" (mức độ hoạt động, DESC — không có cột riêng hiển thị trong bảng mẫu), "Role" (vai trò) | "Newest first" (option đầu tiên trong `<select>`, không có option nào khác đánh dấu `selected`) — lưu ý data mẫu 8 dòng trong `.wtable` KHÔNG thực sự sắp theo Joined giảm dần (Jan 12 2025 → Mar 3 2025 → Feb 20 2025 → Aug 14 2023 → …), chỉ là dữ liệu demo tĩnh, cần đối chiếu khi build View/QueryBuilder thật | 8/trang — `<tbody>` render đúng 8 `<tr>`; pager mẫu có trang cuối "156", 156 × 8 = 1,248 khớp chính xác với "1,248 accounts" (tiêu đề trang) và KPI "Total users: 1,248" | `.segmented` = equals(role nhóm): "All (1,248)" / "Students (1,204)" / "Instructors (28)" / "Staff (16)" (1,204+28+16=1,248, khớp All — nhưng KPI card "Staff accounts: 44" phía trên lại lệch với tab "Staff (16)", chênh 28, có thể 2 phép đếm khác định nghĩa "staff"/instructor, data mẫu chưa nhất quán, cần chốt khi build); search `.nav__search input[type=search]` placeholder "Search name or email…" = contains(name) OR contains(email) theo `.cell-user`. Không có filter riêng theo Status (Active/Suspended) hay theo Joined trên toolbar dù cả 2 đều có cột trong bảng | url.query_args (filter chia sẻ/bookmark được) — lưu ý bảng Cache ở "Drupal 11 Page Definition" hiện chỉ khai `tags: user list`, chưa liệt kê `contexts: url.query_args`; cần bổ sung khi build thật để filter/sort trên URL không bị cache sai theo request khác | n/a — prototype không render trạng thái rỗng (1,248 user luôn có sẵn, bảng luôn có 8 dòng mẫu), khớp dòng Page States hiện có ("Empty: n/a"). Khi filter theo `.segmented`/search không khớp user nào, cần bổ sung block rỗng theo mẫu `.alert.alert--info` dùng chung toàn site (như P004 Categories, P025 My Wishlist) kèm CTA xoá filter — copy chính xác cần xác nhận khi cấu hình View empty text |

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| CTA chính (mời user mới) | "Invite user" | `.pagehead__actions` (`btn btn--primary`), template route the module.workspace users |
| CTA phụ (xuất dữ liệu) | "Export CSV" | `.pagehead__actions` (`btn btn--ghost`) |
| Ô tìm kiếm | placeholder "Search name or email…" / `aria-label="Search users"` | `.nav__search` trong toolbar, workspace users template |
| Ghi chú nghiệp vụ dưới bảng | "Suspended accounts cannot sign in until reactivated. Impersonation opens the Switch user tool and is logged for audit." | `.hint` string, workspace users template |
| Trạng thái tài khoản | "Active" (`.status--success`) / "Suspended" (`.status--error.status--dot`) | `.status` badge render logic theo field trạng thái của entity `user` |
| Hành động theo dòng | `aria-label` "Edit" / "Impersonate" (link sang P051 Switch user) / "Reactivate" (chỉ hiện ở dòng Suspended) | `.rowbtns` template, workspace users |
| Validation error (trùng email) | n/a trong prototype — Page States khai "dup email → error" nhưng P035 không có markup form/thông báo lỗi cụ thể; đây là ràng buộc unique email của entity `user` lõi Drupal | Drupal core `user` entity constraint (`UserMailUnique`), không phải chuỗi tùy biến của the module |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (≤575.98px) | `.container{max-width:100%}`; `.kpis` đã xuống 1 cột từ ngưỡng `≤479.98px` (`.kpis{grid-template-columns:1fr}`, nested trong tier mobile); `.toolbar` và `.segmented` là flex `flex-wrap:wrap` nên tự xuống nhiều dòng (segmented tabs, ô search, dropdown sort xếp chồng); `.wtable__wrap{overflow-x:auto}` — bảng KHÔNG chuyển thành card, chỉ scroll ngang để xem đủ 6 cột User/Role/Enrolled/Joined/Status/Actions; `.pager` wrap nhiều dòng |
| tablet (768–991.98px) | `.container` ở tier `container-md`; `.kpis` vẫn 2 cột (áp dụng toàn tier `≤991.98px`, `.kpis{grid-template-columns:repeat(2,1fr)}`); `.toolbar`/`.segmented` có thể wrap 2 dòng tuỳ độ dài; `.wtable__wrap` vẫn scroll ngang nếu nội dung vượt viewport (không có breakpoint riêng chuyển bảng sang dạng card) |
| desktop (992–1199.98px) | `.container` ở tier `container-lg`; `.kpis` trở lại đủ 4 cột (trên ngưỡng `991.98px`); `.toolbar` đủ chỗ hiển thị 1 hàng (segmented trái, search + sort phải); bảng `.wtable` hiển thị đầy đủ 6 cột, hiếm khi cần scroll ngang |
| wide (≥1200px) | `.container` mở theo tier `container-xl` (đến `1399.98px`) rồi không giới hạn thêm ở `≥1400px`; layout giữ nguyên như desktop (4 KPI, toolbar 1 hàng, bảng đủ cột), chỉ nới khoảng trắng/padding container |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Manage users | done | bulk + impersonate (P051) |
