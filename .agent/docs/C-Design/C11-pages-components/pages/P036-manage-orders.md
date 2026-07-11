---
page: Manage orders
drupal_concept: View list + edit
mode: as-built
owner_module: lms_order
route_group: manage
route_name: lms_course.workspace_orders
prototype_type: workspace-list
adequacy: PASS
---

# C11 Page - Manage orders

## Purpose
Danh sách order tại `/workspace/orders` (workspace-list): xem/chỉnh sửa order, xác nhận chuyển khoản ngân hàng.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace/orders` (`{order}/edit`) |
| Route name | `lms_course.workspace_orders` |
| Primitive | order list + edit (controller-based) |
| Deviation | controller-based, not a View — [ADR-006](../../../G-Governance/G01-adrs/ADR-006-controller-based-listings.md) |
| Access | manager |
| Cache | tags: order list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Order rows | `lms_order` | manage orders | order list | order → row; confirm → status |

## Page States
Normal: list · Empty: none · Access denied: non-manager → 403 · Validation error: n/a · Loading: paged.

## Listing behavior
| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| n/a — prototype không có sort control tương tác trên header bảng (`<th>Order/Customer/Item/Amount/Status/Date</th>` tĩnh, không có link/icon sort) | Date DESC (suy luận từ thứ tự dòng trong prototype: Jul 10 → Jul 10 → Jul 9 → Jul 7 → Jul 6 → Jul 4 → Jul 3 → Jul 1) | 8 orders/trang (8 dòng hiển thị trong `.wtable`; pager tổng 393 trang khớp ceil(3,142/8) = 393) | equals(status) qua `.segmented` (All 3,142 / Paid 2,948 / Pending 46 / Refunded 148); range(date) qua `input[type=date]` "From"/"To"; contains(order_number, customer) qua ô tìm kiếm `.nav__search` placeholder "Order # or customer…" | url.query_args (filter chia sẻ/bookmark được) | n/a — prototype không thể hiện empty-state cho bảng orders (khớp Page States: "Empty: none") |

## Message copy inventory
| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Page intro (stat line) | "3,142 orders to date · $284,900 revenue in the last 30 days" | the module (workspace controller template) |
| Pending bank-transfer alert | "2 orders are pending bank-transfer confirmation. Review the Pending tab and confirm once funds are received to release course access." | the module (alert markup) |
| Primary CTA (header) | "Export CSV" | the module (workspace controller template) |
| Search field placeholder | "Order # or customer…" | the module (search field markup) |
| Row action — view (icon button, aria-label) | "View order" | the module (row action markup) |
| Row action — refund (icon button, aria-label) | "Refund order" | the module (row action markup) |
| Row action — confirm pending payment (icon button, aria-label) | "Confirm payment" | the module (row action markup) |

## Responsive behavior
| Breakpoint | Behavior |
| --- | --- |
| mobile | <576px: `.container` mở full 100% width (`@media(max-width:575.98px)`); `.kpis` (4 KPI: Revenue/Orders/Refunds/Avg. order value) sập còn 2 cột nếu ≥480px hoặc còn 1 cột nếu <480px (`@media(max-width:479.98px)`); `.toolbar` (`.segmented` tabs + date range + ô tìm kiếm) tự xuống dòng nhờ `flex-wrap:wrap` (không có breakpoint riêng); bảng `.wtable` không chuyển thành card — vẫn giữ cấu trúc bảng và cuộn ngang trong `.wtable__wrap{overflow-x:auto}`; `.pagehead` xếp tiêu đề trên, nút "Export CSV" xuống dưới. |
| tablet | 768–991px: `.container` giới hạn theo `--container-md` (`@media(max-width:991.98px)`) — CSS còn một mốc trung gian 767.98px chuyển `.container` sang `--container-sm` ngay dưới ngưỡng 768px; `.kpis` còn 2 cột (`@media(max-width:991.98px)`); `.wtable` vẫn cuộn ngang trong `.wtable__wrap` khi không đủ chỗ, đủ 7 cột (Order/Customer/Item/Amount/Status/Date/hành động). |
| desktop | 992–1199px: `.container` giới hạn theo `--container-lg` (`@media(max-width:1199.98px)`); `.kpis` trở lại 4 cột mặc định; toàn bộ `.toolbar` (segmented tabs + date range + tìm kiếm) hiển thị trên một hàng nếu đủ chỗ. |
| wide | ≥1200px: `.container` mở theo `--container-xl` (đến 1399.98px, `@media(max-width:1399.98px)`) rồi giữ max-width cố định trên 1400px; layout KPI 4 cột, toolbar và bảng `.wtable` không đổi thêm so với desktop. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Manage orders | done | ⚠️ no webhook reconcile (R-002) |
