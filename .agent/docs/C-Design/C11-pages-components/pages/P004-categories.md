---
page: Categories
drupal_concept: View page
mode: as-built
owner_module: lms_course
route_group: public
route_name: lms_course.categories
prototype_type: listing
adequacy: PASS
standalone: trang browse danh mục công khai (marketing/discovery), không gắn với một workflow giao dịch cụ thể
---

# C11 Page - Categories

## Purpose
Trang chỉ mục category tại `/categories` (archetype: listing — xem [_common](../_common.md)). Liệt kê các term `course_category` kèm số lượng/thumbnail.

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/categories` | 
| Route name | `lms_course.categories` |
| Primitive | controller/View term list |
| Deviation | controller-based, not a View — [ADR-006](../../../G-Governance/G01-adrs/ADR-006-controller-based-listings.md) |
| Access | anonymous |
| Cache | tags: taxonomy term list; contexts: `url` |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Category tiles | `course_category` taxonomy (+`field_category_thumbnail`,`field_is_featured`) | content_editor | term cache | add term → tile |

## Page States
Normal: tiles · Empty: none → message · Access denied: n/a · Validation error: n/a · Loading: lazy.

## Listing behavior

| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| Không có sort do người dùng chọn — không có sort dropdown trên UI. Thứ tự tile trong khối "All categories" theo term weight (thứ tự hiển thị do admin cấu hình trên vocabulary `course_category`); khối "Most popular categories" là tập con lọc riêng theo `field_is_featured` = 1 | term weight ASC theo cấu hình admin (không phải alphabetical — thứ tự thật trong prototype: Web Development, AI & Machine Learning, Data & Analytics, Design & UX, Business, Marketing, Photography & Video, Personal Development — không theo A-Z lẫn không theo course count giảm dần) | 8/8 categories hiển thị hết trên 1 trang; không phân trang, không "Load more" (vocabulary `course_category` nhỏ, cố định) | Không có filter — không có ô tìm kiếm/dropdown lọc trên trang này (khác P002 course catalog có filter đầy đủ theo level/price/rating) | n/a — route `/categories` không mang query string; không có filter/sort/pagination nên không cần persist state qua URL | Vocabulary `course_category` rỗng → không tile nào render, thay bằng thông báo dạng `.alert.alert--info` (mẫu dùng chung toàn site cho listing rỗng, ví dụ P025 My Wishlist) kèm CTA điều hướng sang catalog; state này không xuất hiện trong prototype demo (8 term luôn có sẵn) nên copy chính xác cần xác nhận khi cấu hình View empty text |

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Hero eyebrow | "40+ topics" | page hero block markup (theme template `pagehero`) |
| Hero title | "Browse courses by category" | page hero block markup |
| Hero lede (giới thiệu trang) | "1,200+ courses organized into 8 core tracks, from your first line of code to advanced machine learning. Pick a category to see every course inside it." | page hero block markup |
| Featured badge trên tile trending | "Trending" | theme badge label gắn với field is featured trên term course category |
| Prompt cuối trang (chưa chọn được category) | "Not sure where to start?" | static CTA block config trên trang |
| CTA thứ cấp điều hướng sang catalog đầy đủ | "Browse all courses" | static CTA block config, link route the module.catalog` (P002) |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile | <640px (breakpoint CSS thật: `max-width:639.98px`): grid `.grid--3` (Most popular categories) và `.grid--4` (All categories) đều rút về 1 cột; `.container` full width 100% |
| tablet | 640–991px (`max-width:991.98px`): `.grid--3` và `.grid--4` cùng chuyển thành 2 cột (`repeat(2,1fr)`); `.container` co theo `container-sm`/`container-md` |
| desktop | 992–1199px: grid quay lại số cột gốc — `.grid--4` (All categories) đủ 4 cột, `.grid--3` (Most popular categories) đủ 3 cột; `.container` theo `container-lg` (`max-width:1199.98px`) |
| wide | ≥1200px: `.container` mở rộng theo `container-xl` (≤1399.98px) rồi full ở ≥1400px; số cột grid giữ nguyên 4/3, chỉ tăng khoảng đệm hai bên |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Category index | done | — |
