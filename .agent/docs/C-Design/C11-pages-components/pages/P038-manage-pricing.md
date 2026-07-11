---
page: Manage pricing / plans
drupal_concept: entity list + form
mode: as-built
owner_module: lms_membership
route_group: manage
route_name: lms_membership.workspace_pricing
prototype_type: workspace-list
adequacy: PASS
---

# C11 Page - Manage pricing / plans

## Purpose
Quản trị gói thuê bao (membership plan) tại `/workspace/pricing` (workspace-list): tạo/chỉnh sửa/bật-tắt plan + membership của user.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace/pricing` (add, `{plan}/edit/toggle`); `/workspace/user-memberships` |
| Route name | `lms_membership.workspace_pricing` |
| Primitive | entity list + forms (controller-based) |
| Deviation | controller-based, not a View — [ADR-006](../../../G-Governance/G01-adrs/ADR-006-controller-based-listings.md) |
| Access | manager |
| Cache | tags: plan list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Plans + memberships | `lms_membership_plan`/`lms_user_membership` | manage memberships | plan tag | add plan → P006 card |

## Page States
Normal: list · Empty: none → add · Access denied: non-manager → 403 · Validation error: required → errors · Loading: paged.

## Listing behavior
| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| n/a — prototype không có sort control trên `.wtable` (header `<th>` không phải link/button sortable) | n/a — thứ tự insertion trong markup (bảng "All plans": Free → Pro → Teams → Pro (Legacy Annual); bảng "Coupons": WELCOME20 → TEAMS10 → INSTRUCTOR100 → BLACKFRIDAY50 → SPRING25) | n/a — prototype render toàn bộ danh sách (4 plans + 5 coupons), không có pager | n/a — prototype không có `.filter`/`.segmented` control (không tìm thấy trong HTML, chỉ có `.toolbar` với heading + CTA) | url.query_args (filter chia sẻ/bookmark được) | n/a — prototype không hiển thị trạng thái empty (luôn có dữ liệu mẫu: 4 plans, 5 coupons); nếu rỗng thực tế thì "All plans"/"Coupons" table nên hiện copy rỗng + CTA "New plan"/"New coupon" tương ứng |

## Message copy inventory
| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Page intro (plan/subscriber counts) | "3 active plans · 12,190 active subscribers" | the module (workspace pricing template) |
| Plan preview section hint | "This is exactly how learners see these plans on the public pricing page. Edit a card to change price, features or availability." | the module (workspace pricing template) |
| Primary CTA (new plan) | "New plan" | the module (workspace pricing template) |
| Plan row actions (`.rowbtns` aria-label) | "Edit plan" / "Archive plan" / "Restore plan" | the module (`.rowbtns` aria-label markup) |
| Coupons section CTA | "New coupon" | the module (workspace pricing template) |
| Coupon row actions (`.rowbtns` aria-label) | "Edit coupon" / "Delete coupon" | the module (`.rowbtns` aria-label markup) |
| Validation error (giá/coupon không hợp lệ) | n/a — prototype không hiển thị form/lỗi validate (sửa giá qua trang "Edit plan" riêng, `href="#"` chưa build) | C04 the module.md ## Validation rules |
| Empty state (0 plans/0 coupons) | n/a — không có markup empty-state trong prototype | the module (View/controller empty-text config, cần bổ sung) |

## Responsive behavior
| Breakpoint | Behavior |
| --- | --- |
| mobile | <576px: `.plans` đã sập còn 1 cột từ ngưỡng ≤991.98px (`grid-template-columns:1fr;max-width:460px;margin-inline:auto`), `.plan--feature` bỏ `transform:scale(1.02)`; bảng `.wtable` cuộn ngang trong `.wtable__wrap{overflow-x:auto}` (không chuyển thành card); `.pagehead` (flex-wrap) xếp tiêu đề + counts trên, nút "New plan" xuống dưới; `.toolbar` của khối Coupons cũng wrap nút "New coupon" xuống dòng khi chật; `.container` = 100% width. |
| tablet | 768–991px: vẫn trong ngưỡng `@media(max-width:991.98px)` nên `.plans` còn 1 cột như mobile; `.container` giới hạn theo `--container-sm`/`--container-md`; `.wtable` vẫn scroll ngang trong `.wtable__wrap` nếu bảng rộng hơn viewport. |
| desktop | 992–1199px: vượt ngưỡng 991.98px nên `.plans` trở lại `repeat(3,1fr)` (3 cột), `.plan--feature` phóng `scale(1.02)` trở lại; `.container` giới hạn theo `--container-lg`; `.pagehead` và `.toolbar` nằm cùng hàng (`justify-content:space-between`), không wrap. |
| wide | ≥1200px: `.container` mở theo `--container-xl` (đến 1399.98px) rồi theo max-width cố định của `.container`; `.plans` giữ 3 cột; bảng `.wtable` đủ chỗ hiển thị toàn bộ cột (Plan/Price/Interval/Subscribers/Status/actions hoặc Code/Discount/Uses/Expiry/Status/actions) không cần cuộn ngang. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Manage plans | done | toggle active |
