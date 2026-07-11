---
page: AI settings
drupal_concept: config form
mode: as-built
owner_module: lms_ai
route_group: admin
route_name: lms_ai.settings
prototype_type: config
adequacy: PASS
---

# C11 Page - AI settings

## Purpose
Cấu hình AI provider tại `/admin/config/lms/ai` (+ `/admin/everlms-ai-engine`) (config): chọn provider, thiết lập key, giá trị mặc định.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/admin/config/lms/ai`, `/admin/everlms-ai-engine` |
| Route name | `lms_ai.settings` |
| Primitive | config form |
| Access | administer (admin) |
| Cache | uncacheable (form) |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Provider + keys | `lms_ai.settings.yml`/`lms_ai.engine.settings.yml` | administrator | config | set provider → AI builder uses it |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| --- | --- | --- |
| API keys in config | ⚠️ R-001 — should move to Key/env | BLK-001 |

## Page States
Normal: form · Empty: unset → defaults · Access denied: non-admin → 403 · Validation error: bad key → error on use · Loading: save.

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Lưu cấu hình thành công | "Configuration saved." | the module (form submit handler) |
| CTA test kết nối provider | "Test connection" | prototype static text (nút btn--brand, tab Provider) |
| Trạng thái kết nối (header + aside) | "Provider connected" / "Connected" — "Last verified 12 minutes ago. Anthropic Claude · region: us-east." | prototype static text (status badge + panel--soft) |
| Lỗi validation — API key sai/thu hồi (masked) | "API key sk-ant-api03-••••••••••••••••••••7f3a is invalid or has been revoked." | the module (AJAX test-connection callback) |
| Hint bảo mật API key | "Stored in site configuration. Rotate the key immediately if it may have been exposed." | prototype static text (field hint dưới API key) |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (<576px) | `.tabs` (Provider/Models/Limits/Usage) cuộn ngang do `overflow-x:auto`; `.editor` (panel + aside) đã gập về 1 cột từ ≤991px nên "Connection status"/"Feature toggles" nằm dưới panel chính; `.formgrid--2` (Provider & credentials, Token & request limits) về 1 cột ở ≤639px; `.kpis` (tab Usage) về 1 cột ở ≤479px; toolbar cuối form (Discard changes/Save changes) xếp dọc theo `flex-wrap`. |
| tablet (768–991px) | `.editor` vẫn 1 cột (ngưỡng gập ≤991.98px chưa qua) nên aside vẫn nằm dưới panel; `.formgrid--2` giữ 2 cột (ngưỡng gập ≤639px chưa chạm); `.kpis` còn 2 cột. |
| desktop (992–1199px) | `.editor` trở lại 2 cột (panel co giãn + aside 340px cố định, `position:sticky;top:90px`); `.formgrid--2` 2 cột; `.kpis` 4 cột đầy đủ; `.tabs` hiển thị đủ 4 tab không cần cuộn. |
| wide (≥1200px) | Bố cục như desktop, `.container` mở rộng theo `--container-xl`; `.barchart` (tab Usage) và các panel có thêm khoảng trắng hai bên. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| AI config | done | ⚠️ secrets in config (R-001) |
