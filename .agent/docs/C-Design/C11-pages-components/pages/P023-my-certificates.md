---
page: My certificates
drupal_concept: controller list
mode: as-built
owner_module: lms_certificate
route_group: app
route_name: lms_progress.my_certificates
prototype_type: listing
adequacy: PASS
---

# C11 Page - My certificates

## Purpose
Các chứng chỉ đã đạt được tại `/my-certificates` (listing): xem/tải PDF.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/my-certificates` |
| Route name | `lms_progress.my_certificates` |
| Primitive | controller list (own `lms_user_certificate`) |
| Deviation | controller-based, not a View — [ADR-006](../../../G-Governance/G01-adrs/ADR-006-controller-based-listings.md) |
| Access | authenticated (own) |
| Cache | per-user |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Certificate rows + PDF | `lms_user_certificate` (own) | own | per-user | pass quiz → cert |

## Page States
Normal: list · Empty: none yet · Access denied: anon → login · Validation error: n/a · Loading: lazy.

## Listing behavior

| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| Không có control sort/filter trong prototype (không sortable header, không dropdown) | issued_on DESC (chứng chỉ mới cấp trước); thẻ "in progress/locked" luôn xếp cuối | 5 thẻ mẫu (4 đã cấp + 1 đang khoá), không phân trang trong prototype | không có (gap so với các trang listing khác — chưa có filter earned/locked khi số chứng chỉ nhiều) | n/a — không có filter nên không cần persist qua URL | không có state "0 certificate" trong prototype; trạng thái gần nhất là thẻ khoá "In progress · Finish Section 4 to unlock" + CTA "Continue" |

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Hero (động theo dữ liệu) | "4 certificates earned so far, issued to Alex Morgan. One more is within reach — finish Complete Modern React & Next.js Bootcamp to unlock it." | the module my certificates controller |
| Nhãn thẻ chứng chỉ | "Certificate of completion" + ID vd "EVL-PFE-70231" | dynamic (the module.field certificate id) |
| Trạng thái chưa đạt / khoá | "In progress" / "Finish Section 4 to unlock" (kèm % tiến độ) | dynamic (theo the module progress) |
| CTA thẻ đang khoá | "Continue" | module string |
| Action icon | aria-label "Download certificate PDF" / "Verify certificate" / "Share certificate" | module string |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile | Ở ≤639.98px `.grid--3` (certcard) về 1 cột; `.cert-preview` giữ aspect-ratio 1.4/1; `.certcard__body` (nhãn + ID + icon action) có thể wrap ở màn rất hẹp. |
| tablet | 768–991px: `.grid--3` xuống 2 cột (rule chung `.grid--3,.grid--4` ở ≤991.98px). |
| desktop | 992–1199px: `.grid--3` đủ 3 cột theo mặc định. |
| wide | ≥1200px: giữ 3 cột; `.container` mở max-width nên mỗi thẻ certcard có nhiều khoảng trắng hơn xung quanh. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| My certificates | done | download `field_pdf_file` |
