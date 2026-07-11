---
page: My wishlist
drupal_concept: controller list + AJAX
mode: as-built
owner_module: lms_wishlist
route_group: app
route_name: lms_wishlist.my_wishlist
prototype_type: listing
adequacy: PASS
standalone: tiện ích wishlist cá nhân, không thuộc một trong 10 workflow B04 đã đặt tên
---

# C11 Page - My wishlist

## Purpose
Các course đã lưu tại `/my-wishlist` (listing); thêm/xoá qua AJAX.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/my-wishlist` (AJAX `/api/lms/wishlist/{toggle,status}`) |
| Route name | `lms_wishlist.my_wishlist` |
| Primitive | controller list + AJAX toggle |
| Deviation | controller-based, not a View — [ADR-006](../../../G-Governance/G01-adrs/ADR-006-controller-based-listings.md) |
| Access | authenticated (own) |
| Cache | per-user |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Wishlist items | `lms_wishlist` (own) | own | per-user | toggle → add/remove |

## Page States
Normal: list · Empty: none → catalog · Access denied: anon → login · Validation error: n/a · Loading: AJAX.

## Listing behavior
| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| n/a — prototype không có sort control | n/a — không hiển thị trong prototype | n/a — prototype render toàn bộ danh sách (4 thẻ), không có phân trang | n/a — prototype không có filter/search control | url.query_args (filter chia sẻ/bookmark được) | "Your wishlist is empty. Save any course you're considering by tapping the heart icon on its card, and it will land here so you can come back and enroll whenever you're ready." + link "Browse the course catalog" |

## Message copy inventory
| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Page intro (count) | "4 courses saved. Tap the heart to remove one, or enroll straight from the card." | the module (controller template) |
| Empty | "Your wishlist is empty. Save any course you're considering by tapping the heart icon on its card, and it will land here so you can come back and enroll whenever you're ready." | the module (controller template) |
| Empty CTA | "Browse the course catalog" | the module (controller template) |
| Remove action (button label/tooltip) | "Remove from wishlist" | the module (button markup) |
| Primary CTA (per card) | "Enroll now" | the module (controller template) |
| Header CTA | "Browse more courses" | the module (controller template) |

## Responsive behavior
| Breakpoint | Behavior |
| --- | --- |
| mobile | <576px: `.grid--3` sập còn 1 cột (rule `@media(max-width:639.98px)`), mỗi `.ccard` chiếm full width; `.pagehead` (flex, wrap) xếp `.pagehead__t` (tiêu đề + count) trên, `.pagehead__actions` ("Browse more courses") xuống dưới; nút "Enroll now" giữ `btn--block` full width trong thẻ. |
| tablet | 768–991px: vẫn nằm trong ngưỡng `@media(max-width:991.98px)` nên `.grid--3` còn 2 cột (`repeat(2,1fr)`); `.container` giới hạn theo `--container-md`; `.pagehead` vẫn flex-wrap nên có thể xuống dòng nếu tiêu đề dài. |
| desktop | 992–1199px: vượt ngưỡng 991.98px nên `.grid--3` trở lại mặc định 3 cột (`repeat(3,1fr)`); `.container` giới hạn theo `--container-lg`; `.pagehead__t` và `.pagehead__actions` nằm cùng hàng (justify-content:space-between). |
| wide | ≥1200px: `.grid--3` giữ 3 cột; `.container` mở rộng theo `--container-xl` (đến 1399.98px) rồi max-width cố định của `.container`; layout `.pagehead` và grid thẻ không đổi thêm so với desktop. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Wishlist | done | convert to order |
