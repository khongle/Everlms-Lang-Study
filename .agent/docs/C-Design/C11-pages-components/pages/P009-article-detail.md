---
page: Article detail
drupal_concept: entity view
mode: as-built
owner_module: lms_blog
route_group: public
route_name: lms_blog.article
prototype_type: detail
adequacy: PASS
standalone: trang nội dung blog công khai (F-016), không được mô hình hoá thành một trong 10 workflow B04
---

# C11 Page - Article detail

## Purpose
Bài viết blog tại `/news/{node}` (detail archetype): nội dung (body), SEO, bài liên quan (related).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/news/{node}` |
| Route name | `lms_blog.article` |
| Primitive | entity view controller |
| Access | anonymous |
| Cache | tags: `lms_article:{id}` |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Article body/SEO | `lms_article` fields | content_editor | article tag | edit → updates |

## Page States
Normal: article · Empty: n/a · Access denied: unpublished → 403 · Validation error: n/a · Loading: lazy media.

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| CTA chính (băng CTA cuối bài) | "Browse courses" | the module Twig template (article CTA band) |
| CTA phụ (băng CTA cuối bài) | "Log in" | the module Twig template (article CTA band) |
| CTA thẻ tác giả (aside) | "View profile" | the module Twig template (author card component) |
| Hành động chia sẻ bài viết (aside) | "Copy link" | the module Twig template (share widget aria-label) |
| Tiêu đề băng CTA cuối bài | "Ready to put this into practice?" | the module Twig template (article CTA band) |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile | `.container` rộng 100% viewport (≤575.98px). `.article` đã ở dưới ngưỡng `991.98px` nên xếp 1 cột: `.article__aside` (thẻ tác giả, mục "On this page", chia sẻ, related posts) chuyển `position:static` và nằm ngay dưới nội dung bài viết thay vì cột phụ. Ảnh hero giữ tỉ lệ 21/9 full-width. |
| tablet | `.container` dùng `max-width:var(--container-sm)`/`--container-md` tuỳ mốc 767.98/991.98px. `.article` vẫn ở trong ngưỡng `≤991.98px` nên vẫn xếp 1 cột, `.article__aside` vẫn `position:static` nằm dưới bài viết. |
| desktop | Vượt ngưỡng `991.98px` nên `.article` chuyển về `grid-template-columns: minmax(0,1fr) 300px` — 2 cột: nội dung bài viết bên trái, `.article__aside` bên phải rộng 300px với `position:sticky; top:90px` (dính khi cuộn). |
| wide | Giữ bố cục 2 cột như desktop; `.container` mở rộng thêm (`--container-lg`/`--container-xl` theo mốc 1199.98/1399.98px), khoảng trắng 2 bên tăng nhưng cấu trúc `.article` + aside sticky không đổi. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Article detail | done | SEO fields rendered |
