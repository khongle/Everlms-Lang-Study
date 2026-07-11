---
page: Notification broadcast
drupal_concept: Form API
mode: as-built
owner_module: lms_notification
route_group: admin
route_name: lms_notification.broadcast
prototype_type: config
adequacy: PASS
standalone: tiện ích admin gửi broadcast, hỗ trợ nhiều workflow chứ không phải một bước riêng của một workflow
---

# C11 Page - Notification broadcast

## Purpose
Broadcast dành cho admin tại `/admin/lms/notifications/broadcast` (config/form): gửi một thông báo tới một nhóm user (segment).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/admin/lms/notifications/broadcast` (settings `/manage/settings/notifications`) |
| Route name | `lms_notification.broadcast` |
| Primitive | Form API + batch |
| Access | administer notifications |
| Cache | uncacheable |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Broadcast form | recipients + message | administrator | — | send → in-app notifications created (email/push partial R-005) |

## Page States
Normal: form · Empty: n/a · Access denied: non-admin → 403 · Validation error: empty message → error · Loading: batch.

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| CTA gửi broadcast | "Send broadcast" | prototype static text (nút submit chính) |
| CTA lưu nháp | "Save as draft" | prototype static text (nút phụ) |
| Lỗi validation — message trống | "Message field is required." | the module (Form API `#required` trên trường Message — cần xác nhận copy thật) |
| Trạng thái các broadcast đã gửi (bảng Past broadcasts) | "Sent" / "Scheduled" / "Draft" | prototype static text (wtable status badges) |
| Ước tính phạm vi tiếp cận theo audience | "Users in \"All users\"" (480,240) | prototype static text (panel "Estimated reach", tính theo lựa chọn Audience) |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (<576px) | `.editor` (form chính + aside "Live preview"/"Estimated reach") đã gập về 1 cột từ ≤991px nên preview và reach nằm dưới form; `.formgrid--2` (Audience/Delivery) về 1 cột ở ≤639px; `.switchrow` (Channels: Email/Push/In-app) vẫn dạng hàng label+toggle nhưng co hẹp theo chiều rộng; `.wtable` (Past broadcasts) cuộn ngang; `.btn-row` (Save as draft/Send broadcast) xếp dọc do `flex-wrap`. |
| tablet (768–991px) | `.editor` vẫn 1 cột (ngưỡng gập ≤991.98px chưa qua); `.formgrid--2` vẫn 2 cột (ngưỡng gập ≤639px chưa chạm). |
| desktop (992–1199px) | `.editor` trở lại 2 cột, aside (Live preview + Estimated reach) sticky bên phải form; `.formgrid--2` giữ 2 cột. |
| wide (≥1200px) | Bố cục như desktop, `.container` mở rộng theo `--container-xl`; panel "Live preview" có thêm khoảng trắng. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Broadcast | partial | in-app ok; email/push R-005 |
