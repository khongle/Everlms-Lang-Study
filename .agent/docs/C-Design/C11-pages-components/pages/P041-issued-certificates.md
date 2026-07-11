---
page: Issued certificates
drupal_concept: list + revoke/regenerate
mode: as-built
owner_module: lms_certificate
route_group: manage
route_name: lms_certificate.issued
prototype_type: workspace-list
adequacy: PASS
---

# C11 Page - Issued certificates

## Purpose
Quản trị certificate tại `/workspace/certificates` (workspace-list): danh sách certificate đã cấp, tạo lại (regenerate), thu hồi (revoke).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace/certificates` (`{cert}/regenerate`; revoke `/admin/lms/certificates/{cert}/revoke`) |
| Route name | `lms_certificate.issued` |
| Primitive | list + actions |
| Access | manager |
| Cache | tags: cert list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Cert rows | `lms_user_certificate` | manage certificates | cert tag | issue → row; revoke → flagged |

## Page States
Normal: list · Empty: none · Access denied: non-manager → 403 · Validation error: revoke reason required · Loading: regen.

## Listing behavior

| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| `field_issued_at`, `uid` (holder), `field_course`, `field_revoked` (status) | `field_issued_at` DESC (mới nhất trước — khớp Jul 9 → Jun 15 trong prototype) | 20/trang (suy ra chính xác từ prototype: 19,340 chứng chỉ / 967 trang = 20/trang) | `equals(field_revoked)` qua tab segmented (All/Active/Revoked); `contains(uid.name, field_certificate_number)` qua ô tìm kiếm "Search by holder or cert ID…" | `url.query_args` (tab trạng thái + từ khóa tìm kiếm phản ánh qua query string, chia sẻ/bookmark được) | "No certificates match these filters." + hành động "Clear filters" (suy luận hợp lý — Page States ghi "Empty: none" cho toàn danh sách, nhưng chưa có mockup riêng cho kết quả lọc/tìm kiếm rỗng trong prototype) |

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Search placeholder | "Search by holder or cert ID…" | prototype P041-issued-certificates.html — `input[type=search]` placeholder |
| Row action — view | "View" (aria-label nút icon, mở trang xác thực công khai) | prototype P041-issued-certificates.html — `.rowbtns a[aria-label]` |
| Row action — revoke | "Revoke" (aria-label nút icon) | prototype P041-issued-certificates.html — `.rowbtns button[aria-label]` |
| Status badge | "Active" / "Revoked" | module string the module / giá trị field revoked |
| Validation error — revoke reason | "Please provide a reason for revoking this certificate." | suy luận hợp lý — field field revoke reason bắt buộc (khớp Page States "revoke reason required") / C04 the module.md |
| Toast — revoke success | "Certificate revoked." | suy luận hợp lý (prototype tĩnh không có toast) / C04 the module.md field revoked |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (<576px) | `.kpis` còn 1 cột (`@media(max-width:479.98px)`); `.toolbar` xuống dòng — tab `.segmented` và ô tìm kiếm xếp chồng (flex-wrap); bảng `.wtable` không gập thành card, cuộn ngang trong `.wtable__wrap{overflow-x:auto}`; `.pager` bọc dòng |
| tablet (768–991px) | `.kpis` 2 cột (`@media(max-width:991.98px)`); `.container` co về `--container-md`; bảng `.wtable` vẫn cuộn ngang khi cần |
| desktop (992–1199px) | `.kpis` đủ 4 cột; `.toolbar` một hàng ngang (tab + tìm kiếm cạnh nhau); bảng hiển thị đủ 6 cột (Holder/Course/Cert ID/Issued/Status/hành động) không cần cuộn ở độ rộng thường gặp |
| wide (≥1200px) | `.container` đạt `--container-xl`; layout giữ nguyên, chỉ nới lề |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Cert admin | done | bulk regen via script |
