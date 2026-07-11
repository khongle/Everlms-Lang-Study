---
page: Certificate verify
drupal_concept: controller + form
mode: as-built
owner_module: lms_certificate
route_group: public
route_name: lms_certificate.verify
prototype_type: auth-util
adequacy: PASS
---

# C11 Page - Certificate verify

## Purpose
Trang xác thực công khai tại `/certificate/verify`: nhập mã chứng chỉ để xác nhận tính xác thực (auth-util).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/certificate/verify` |
| Route name | `lms_certificate.verify` |
| Primitive | controller + lookup form |
| Access | anonymous |
| Cache | uncacheable (lookup) |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Verify result | `lms_user_certificate.field_certificate_number` | n/a | — | valid number → verified |

## Page States
Normal: form · Empty: no input · Access denied: n/a · Validation error: invalid number → "not found" · Loading: lookup.

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Input placeholder — mẫu mã chứng chỉ | "e.g. EVLMS-2026-0001-AX7Q" | the module (verify form — trường `cert-id`) |
| CTA tra cứu | "Verify" | the module (verify form submit button) |
| Success — chứng chỉ hợp lệ | "Verified — genuine certificate" | the module (verify controller — kết quả hợp lệ) |
| Success — nhãn trạng thái trong bảng chi tiết | "Valid" | the module (verify controller — nhãn trạng thái) |
| Error — không tìm thấy mã (not found) | "No certificate found for ID "EVLMS-2026-9999-ZZ00". Double-check the ID for typos, or contact the certificate holder to confirm it was copied correctly." | the module (verify controller — trạng thái not-found) |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (<576px) | `.container` co về 100% chiều rộng; form tra cứu `.form--inline` (input + nút "Verify") xuống dòng vì `flex-wrap:wrap` và input có `flex:1 1 240px`; khối kết quả `.split` (thẻ certcard + bảng chi tiết) xếp dọc 1 cột vì `.split` chuyển `grid-template-columns:1fr` ở ≤991.98px. |
| tablet (768–991px) | `.container` giới hạn theo `--container-sm`/`--container-md` (540–720px tuỳ mốc ≤767.98 hay ≤991.98); form tra cứu đủ rộng để input và nút "Verify" nằm cùng một hàng; khối kết quả `.split` vẫn xếp 1 cột vì ngưỡng chuyển 2 cột của `.split` là >991.98px. |
| desktop (992–1199px) | `.container` giới hạn theo `--container-lg` (960px); `.split` chuyển sang lưới 2 cột (certcard bên trái, bảng chi tiết bên phải) vì viewport vượt breakpoint 991.98px của `.split`. |
| wide (≥1200px) | `.container`/`.container--narrow` giữ độ rộng ổn định (tối đa ~920–1140px tuỳ breakpoint áp dụng); `.split` giữ nguyên 2 cột, layout không thay đổi thêm so với desktop. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Verify | done | revoked certs flagged |
