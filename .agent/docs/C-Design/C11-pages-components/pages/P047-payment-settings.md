---
page: Payment settings
drupal_concept: config form
mode: as-built
owner_module: lms_payment
route_group: admin
route_name: lms_payment.settings
prototype_type: config
adequacy: PASS
---

# C11 Page - Payment settings

## Purpose
Cấu hình gateway tại `/admin/config/everlms/payment` (config): bật gateway, thiết lập key.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/admin/config/everlms/payment` |
| Route name | `lms_payment.settings` |
| Primitive | config form |
| Access | administer (admin) |
| Cache | uncacheable |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Gateway toggles + keys | `lms_payment.settings.yml` | administrator | config | enable → checkout option |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| --- | --- | --- |
| Gateway keys in config | ⚠️ R-001 | BLK-001 |

## Page States
Normal: form · Empty: defaults · Access denied: non-admin → 403 · Validation error: bad key → error on use · Loading: save.

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Lưu cấu hình thành công | "Configuration saved." | the module (form submit handler) |
| CTA test kết nối gateway | "Test connection" | prototype static text (nút — bị `disabled` cho PayPal khi gateway đang tắt) |
| Lỗi validation — secret key không hợp lệ (masked) | "Secret key sk live 51NcQ••••••••••••••••••9fRz could not be verified. Check the key and try again." | the module (AJAX test-connection callback) |
| Trạng thái giao nhận webhook (bảng Recent webhook deliveries) | "Delivered" / "Failed" / "Retrying" | prototype static text (wtable status badges) |
| CTA gửi sự kiện thử webhook | "Send test event" | prototype static text (tab Webhooks) |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (<576px) | `.tabs` (Gateways/Currency & tax/Webhooks/Payouts) cuộn ngang; `.grid.grid--2` (thẻ Stripe + PayPal) về 1 cột ở ≤639px; `.formgrid--2` (Currency & tax) về 1 cột ở ≤639px; `.editor` (tab Payouts: bảng payout + aside "Platform payout account") đã gập về 1 cột từ ≤991px; `.wtable__wrap` (webhook deliveries, payout history) cuộn ngang thay vì vỡ layout; toolbar cuối form xếp dọc. |
| tablet (768–991px) | `.grid.grid--2` vẫn giữ 2 cột (Stripe/PayPal cạnh nhau, ngưỡng gập ≤639px chưa chạm); `.formgrid--2` vẫn 2 cột; `.editor` (Payouts) vẫn 1 cột do ngưỡng gập ≤991.98px chưa qua. |
| desktop (992–1199px) | `.editor` (Payouts) trở lại 2 cột, aside "Platform payout account" sticky bên phải; `.grid.grid--2` và `.formgrid--2` giữ 2 cột; `.tabs` hiển thị đủ 4 tab. |
| wide (≥1200px) | Bố cục như desktop với `.container` mở rộng theo `--container-xl`; các bảng `.wtable` (webhook deliveries, payout history) có thêm khoảng trắng, ít khi cần cuộn ngang. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Payment config | done | ⚠️ no webhook (R-002), keys in config (R-001) |
