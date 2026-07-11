---
page: Zoom settings
drupal_concept: config form
mode: as-built
owner_module: lms_zoom
route_group: admin
route_name: lms_zoom.settings
prototype_type: config
adequacy: PASS
standalone: cấu hình tích hợp Zoom cho live session, không thuộc một trong 10 workflow B04 đã đặt tên
---

# C11 Page - Zoom settings

## Purpose
Cấu hình Zoom tại `/admin/config/lms/zoom` (config): thông tin xác thực API (credentials), giá trị mặc định.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/admin/config/lms/zoom` |
| Route name | `lms_zoom.settings` |
| Primitive | config form |
| Access | administer (admin) |
| Cache | uncacheable |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Zoom creds | `lms_zoom.settings.yml` | administrator | config | set → meetings create |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| --- | --- | --- |
| Zoom creds in config | ⚠️ R-001 | BLK-001 |

## Page States
Normal: form · Empty: defaults · Access denied: non-admin → 403 · Validation error: bad creds → error on use · Loading: save.

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Lưu cấu hình thành công | "Configuration saved." | the module (form submit handler) |
| CTA kết nối lại tài khoản Zoom | "Reconnect" | prototype static text (nút cạnh "Disconnect", tab Connection) |
| Trạng thái kết nối (header + aside) | "Connected" — "Linked account: admin@everlms.com — Plan: Zoom Business · 500 participants" | prototype static text (status badge + panel--soft) |
| Lỗi validation — API secret không hợp lệ (masked) | "API secret ••••••••••••••••••••••••••••• is invalid. Reconnect your Zoom account." | the module (API credential validation callback) |
| Trạng thái phiên live (bảng Upcoming live sessions) | "Live now" / "Scheduled" / "Ended" | prototype static text (wtable status badges, tab Sessions) |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (<576px) | `.tabs` (Connection/Defaults/Sessions) cuộn ngang; `.editor` (Connection: creds + aside "Connection status"; Defaults: session defaults + aside "Meeting behavior") đã gập về 1 cột từ ≤991px; `.formgrid--2` (API key/SDK key, Default duration/timezone) về 1 cột ở ≤639px; `.wtable` (Upcoming live sessions) cuộn ngang; toolbar Discard/Save xếp dọc. |
| tablet (768–991px) | `.editor` vẫn 1 cột (ngưỡng gập ≤991.98px chưa qua); `.formgrid--2` vẫn 2 cột (ngưỡng gập ≤639px chưa chạm). |
| desktop (992–1199px) | `.editor` trở lại 2 cột (panel + aside 340px sticky `top:90px`); `.formgrid--2` giữ 2 cột; `.tabs` hiển thị đủ 3 tab không cuộn. |
| wide (≥1200px) | Bố cục như desktop, `.container` mở rộng theo `--container-xl`; bảng Sessions có thêm khoảng trắng, các cột (Course, Host, Date, Join URL, Status) hiển thị đủ không cần cuộn. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Zoom config | done | ⚠️ secrets in config; F-022 partial |
