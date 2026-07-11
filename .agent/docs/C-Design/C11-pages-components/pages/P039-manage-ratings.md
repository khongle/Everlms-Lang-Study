---
page: Manage ratings
drupal_concept: list + moderation actions
mode: as-built
owner_module: lms_rating
route_group: manage
route_name: lms_rating.workspace
prototype_type: workspace-list
adequacy: PASS
standalone: tiện ích kiểm duyệt rating (F-014), không được mô hình hoá thành một workflow B04 riêng
---

# C11 Page - Manage ratings

## Purpose
Kiểm duyệt review tại `/workspace/ratings` (workspace-list): duyệt (approve), gắn nổi bật (feature), gỡ bỏ (remove) review.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace/ratings` (`{rating}/{action}`) |
| Route name | `lms_rating.workspace` |
| Primitive | list + action callbacks |
| Access | content_editor/manager |
| Cache | tags: rating list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Review rows | `lms_rating` | moderate ratings | rating tag | approve → shows on course |

## Page States
Normal: list · Empty: none · Access denied: non-staff → 403 · Validation error: n/a · Loading: paged.

## Listing behavior

| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| `created`, `field_score` (rating), `field_approved` (status) | `created` DESC (mới nhất trước — khớp thứ tự "2 days ago" → "2 weeks ago" trong prototype) | 20/trang (giá trị mặc định hợp lý — 41,860 review / 840 trang hiển thị trong pager của prototype không chia hết cho một số tròn nên không suy ra chính xác được) | `equals(field_approved)` qua tab segmented (All/Pending/Approved/Flagged); `contains(reviewer_name, course_title, excerpt)` qua ô tìm kiếm | `url.query_args` (tab trạng thái + từ khóa tìm kiếm phản ánh qua query string, chia sẻ/bookmark được) | "No reviews match these filters." + hành động "Clear filters" (suy luận hợp lý — Page States ghi "Empty: none" cho toàn danh sách, nhưng chưa có mockup riêng cho kết quả lọc/tìm kiếm rỗng trong prototype) |

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Search placeholder | "Search reviews…" | prototype P039-manage-ratings.html — `input[type=search]` placeholder |
| Row action — approve | "Approve" (aria-label nút icon) | prototype P039-manage-ratings.html — `.rowbtns button[aria-label]` |
| Row action — reject | "Reject" (aria-label nút icon) | prototype P039-manage-ratings.html — `.rowbtns button[aria-label]` |
| Status badge | "Pending" / "Approved" / "Flagged" | module string the module / giá trị field approved |
| Toast — approve success | "Review approved." | suy luận hợp lý (prototype tĩnh không có toast) / C04 the module.md field approved |
| Access denied (403) | "Access denied. You do not have permission to view this page." | Drupal core mặc định (không tùy biến trong prototype) |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (<576px) | `.kpis` còn 1 cột (`@media(max-width:479.98px)`); `.toolbar` xuống dòng — tab `.segmented` và ô tìm kiếm xếp chồng (flex-wrap); bảng `.wtable` không gập thành card, cuộn ngang trong `.wtable__wrap{overflow-x:auto}`; `.pager` bọc dòng |
| tablet (768–991px) | `.kpis` 2 cột (`@media(max-width:991.98px)`); `.container` co về `--container-md`; bảng `.wtable` vẫn cuộn ngang khi cần |
| desktop (992–1199px) | `.kpis` đủ 4 cột; `.toolbar` một hàng ngang (tab + tìm kiếm cạnh nhau); bảng hiển thị đủ 6 cột (Reviewer/Course/Rating/Excerpt/Status/hành động) không cần cuộn ở độ rộng thường gặp |
| wide (≥1200px) | `.container` đạt `--container-xl`; layout giữ nguyên, chỉ nới lề |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Moderation | done | approve/feature flags |
