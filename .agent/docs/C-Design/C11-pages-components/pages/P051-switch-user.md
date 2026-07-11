---
page: Switch user
drupal_concept: controller action
mode: as-built
owner_module: open_switcher
route_group: admin
route_name: open_switcher.switch
prototype_type: auth-util
adequacy: PASS
---

# C11 Page - Switch user

## Purpose
Impersonation (giả lập đăng nhập) tại `/admin/switch-user` (+ `/app/switch-user`) (auth-util): chuyển sang một user, chuyển về (switch back) (F-021).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/admin/switch-user` (`/{user}`), `/admin/switch-back`, `/app/switch-user` |
| Route name | `open_switcher.switch` |
| Primitive | controller action (session switch) |
| Access | dedicated switch permission (support/admin) |
| Cache | uncacheable |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Switch UI + banner | session state | switch permission | — | switch → browse as user; back → restored |

## Page States
Normal: switch list/banner · Empty: n/a · Access denied: no switch perm → 403 · Validation error: invalid user → error · Loading: redirect.

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Giải thích tính năng impersonation | "About impersonation. Switching lets you act on behalf of a learner or instructor to troubleshoot an issue. Every switch and every action taken while impersonating is logged under your own administrator account. Always switch back when you're done." | prototype static text (`.alert.alert--info`) |
| Banner đang impersonate — CTA quay lại | "Browsing as Alex Morgan" / "Switch back to Dana Whitfield" | prototype static text (nút trên banner impersonation) |
| CTA chuyển sang user trong bảng | "Switch to" | prototype static text (wtable `.rowbtns`) |
| Ô tìm kiếm user | "Find a user by name or email…" | prototype static text (input placeholder, `.nav__search`) |
| Lỗi validation — chuyển sang user không hợp lệ | "User not found or no longer active." | the switcher module module string (controller action — cần xác nhận copy thật, hiện chỉ có mô tả state) |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (<576px) | Banner "Browsing as …" (`.card .flex.items-center`) xếp dọc do `flex-wrap`, nút "Switch back to …" xuống dưới thông tin user; toolbar tìm kiếm (`.form--inline`) xếp dọc — ô search, dropdown role và nút Search wrap từng dòng; bảng `.wtable` (danh sách user) cuộn ngang qua `.wtable__wrap`; panel "Recent switches" giữ 1 cột danh sách. |
| tablet (768–991px) | Toolbar tìm kiếm đủ chỗ nằm 1 hàng (ô search + select role + nút Search); banner impersonation nằm gọn 1 hàng nếu tên đủ ngắn; bảng user hiển thị đủ cột (User, Role, Last active, hành động) không cần cuộn. |
| desktop (992–1199px) | Toàn bộ trang là 1 cột full-width theo `.container` (không có sidebar/aside riêng); banner, toolbar tìm kiếm và bảng user hiển thị đầy đủ trên 1 hàng. |
| wide (≥1200px) | Như desktop, `.container` mở rộng theo `--container-xl`; bảng user và panel "Recent switches" có thêm khoảng trắng hai bên. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Impersonation | done | switch-back restores original |
