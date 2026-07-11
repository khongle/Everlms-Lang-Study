---
page: Category detail
drupal_concept: View page (term)
mode: as-built
owner_module: lms_course
route_group: public
route_name: lms_course.category
prototype_type: listing
adequacy: PASS
standalone: trang browse danh mục công khai (marketing/discovery), không gắn với một workflow giao dịch cụ thể
---

# C11 Page - Category detail

## Purpose
Các course trong một category tại `/categories/{taxonomy_term}` (listing archetype).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/categories/{taxonomy_term}` |
| Route name | `lms_course.category` |
| Primitive | controller/View filtered by term |
| Deviation | controller-based, not a View — [ADR-006](../../../G-Governance/G01-adrs/ADR-006-controller-based-listings.md) |
| Access | anonymous |
| Cache | tags: `lms_course_list`, term; contexts: `url` |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Filtered courses | `lms_course` by `field_category` | n/a | `lms_course_list` | tag course → appears |

## Page States
Normal: course grid · Empty: "no courses" · Access denied: n/a · Validation error: invalid term → 404 · Loading: paged.

## Listing behavior

| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| Most popular; Newest; Highest rated; Price: low to high; Price: high to low (`#sort-select`) | Most popular | 12/trang (210 khóa học → 18 trang trong `.pager`, dạng `1 2 3 4 … 18 Next`) | `equals(field_category)` = term hiện tại của route (khóa cứng theo `{taxonomy_term}`, không phải option chọn được như P002); `in(field_subtopic)` qua checkbox nhóm "Subtopic" (HTML & CSS, React, Next.js, Node.js, TypeScript, PHP — kèm count) và song song qua `.chip-row` (All/HTML & CSS/JavaScript/React/Next.js/Node.js/TypeScript/PHP, chip `is-active`); `in(field_level)` (All Levels, Beginner, Intermediate, Advanced); `gte(field_rating)` (4.5 & up, 4.0 & up); `in(field_price_type)` (Free, Paid) | `url.query_args` — filter subtopic/level/rating/price + sort + page giữ trên URL để chia sẻ/bookmark được; slug category (`/categories/{taxonomy_term}`) cố định trong path, không nằm trong query | Copy hiện có trong Page States: `"no courses"` — prototype P005 chưa dựng sẵn khối empty-state riêng; áp dụng lại pattern "no results" dùng chung toàn site (card `card--soft center`, xem `P022-my-quiz-results.html`/`P025-my-wishlist.html`) với CTA quay lại `P004-categories.html` |

Khác biệt với P002 (course catalog toàn site): ở đây category KHÔNG phải một filter có thể bỏ chọn — nó là điều kiện cố định của route; trang bù lại bằng bộ filter "Subtopic" (con của category) + chip-row subtopic để đào sâu trong category. Khác biệt với P004 (categories index): P004 chỉ liệt kê các term category (tile + count), không có sort/filter/pagination ở cấp course.

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Page title (H1) | "Web Development courses" | course category term name (field category name) → page title callback |
| Aggregate eyebrow | "210 courses · 38 instructors" | computed cache field the module theo term, hiển thị ở `.pagehero .eyebrow` |
| Toolbar result count | "210 courses in Web Development" | `.toolbar__count`, render từ View/controller count + term label |
| Filter reset CTA | "Clear all" | catalog filter template (`.filter` block, the module) |
| Sort control label | "Sort by" | `.toolbar` select label, `#sort-select` |
| Wishlist CTA (per card, aria-label) | "Save Complete Modern React & Next.js Bootcamp to wishlist" | course card block ARIA string, nội suy theo field course title |
| Empty result | "no courses" | C11 Page States row này (P005) — nguồn sửa: View/controller "no results" text config |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile | ≤639.98px: `.grid--3` (course card) về 1 cột; `.catalog__aside` (bộ filter Subtopic/Level/Rating/Price) `display:none` — ẩn hoàn toàn, prototype chưa có nút/drawer thay thế để mở filter trên mobile; `.chip-row` subtopic wrap nhiều dòng; `.toolbar` (đếm kết quả + Sort by) wrap xuống dòng; `.pager` wrap; `.container` về 100% width (≤575.98px) |
| tablet | 768–991px: `.catalog{grid-template-columns:1fr}` (từ `280px 1fr`) và `.catalog__aside` vẫn `display:none` ở toàn dải ≤991.98px — nghĩa là bộ filter đầy đủ chỉ khả dụng từ desktop trở lên, tablet chỉ còn chip-row subtopic để lọc; `.grid--3` course card về 2 cột (kích hoạt cùng ngưỡng ≤991.98px); `.container` dùng `--container-md` |
| desktop | 992–1199px: `.catalog` khôi phục 2 cột (`280px` filter aside cố định + `1fr` kết quả), `.filter` sticky `top:90px`; `.grid--3` course card đủ 3 cột; `.container` dùng `--container-lg` |
| wide | ≥1200px: `.container` dùng `--container-xl` (đến 1399.98px) rồi mở hết ở ≥1400px; layout `.catalog` 2 cột + grid 3 cột giữ nguyên, chỉ nới max-width tổng thể trang |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Category browse | done | reuses catalog grid |
