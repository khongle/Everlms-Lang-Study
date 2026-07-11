---
page: Course catalog
drupal_concept: View page / controller list
mode: as-built
owner_module: lms_course
route_group: public
route_name: lms_course.courses
prototype_type: listing
adequacy: PASS
---

# C11 Page - Course catalog

## Purpose
Catalog công khai tại `/courses`: duyệt, lọc (category/level/language/tag), tìm kiếm, và mở một course. Đầu phễu chuyển đổi (conversion funnel).

## Source Evidence
- **Canonical URL:** `/courses` · **Owner module:** lms_course
- **Editable source:** course entities + taxonomy; listing layout via View/controller
- **Editor management UI:** course entity forms; taxonomy term UI
- **Access evidence:** anonymous
- **Deviation:** controller-based listing, not a Drupal View — [ADR-006](../../../G-Governance/G01-adrs/ADR-006-controller-based-listings.md)

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/courses` (search: `/api/courses/search`) |
| Route name | `lms_course.courses` |
| Drupal page primitive | Controller/View list with exposed filters |
| Owner module | lms_course |
| Theme/layout | `lms_theme`, grid of course_card blocks |
| Access | anonymous |
| Cache | tags: `lms_course_list`; contexts: `url.query_args` (filters), `user.roles` |

## Placed Blocks
| Block | Label | Region | Primitive | Why |
| ----- | ----- | ------ | --------- | --- |
| [course_card](../blocks/course_card.md) | Course card | content | Views row/component | each catalog item |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Filtering | exposed filters: category/level/language/tag + keyword |
| Sorting | newest / bestseller / rating / price |
| Pagination | paged (e.g. 12/page) |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Course cards | `lms_course` entities (published) | n/a | `lms_course_list` | publish course → card appears |
| Filters | taxonomy vocabularies | content_editor | term cache | add term → filter option |
| Aggregate rating | `lms_course.field_rating` | n/a | course tag | new rating → updates |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| None | catalog driven by entities + taxonomy | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | grid of courses |
| Empty | "no courses match" message |
| Access denied | n/a (public) |
| Validation error | invalid filter → ignored |
| Loading | skeleton/lazy load |

## Listing behavior

| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| `sort` select (`#sort-select`) có 5 option: "Most popular", "Newest", "Highest rated", "Price: low to high", "Price: high to low" → field tương ứng: popularity (enrollment count), created DESC, rating DESC, price ASC, price DESC | "Most popular" (option đầu tiên, không có filter/sort nào set thì catalog sort theo popularity) | 12/course/trang (khớp Main Content Contract "paged e.g. 12/page"; pager mẫu hiển thị trang cuối "104" — 104×12≈1,248 ≈ tổng "1,247 courses found") | Category = `in()` (checkbox nhiều chọn theo taxonomy term, vd `checked` mẫu: Web Development); Level = `in()` (All Levels/Beginner/Intermediate/Advanced); Rating = `gte()` (ngưỡng "4.5 & up" / "4.0 & up" / "3.5 & up"); Price = `equals()` (Free/Paid, checkbox loại trừ nhau theo nghiệp vụ); Language = `in()` (English/Spanish/Vietnamese) | `url.query_args` — khớp cache context đã khai ở bảng "Drupal 11 Page Definition" (`contexts: url.query_args (filters)`); filter áp dụng hiển thị dạng chip xoá được (`.chip-row` "Applied filters") nên URL có thể chia sẻ/bookmark còn nguyên bộ lọc | "no courses match" (theo bảng Page States) — copy suy ra từ mẫu no-match dùng chung của cùng hệ thiết kế trên trang listing khác (P043 Manage blog: "No posts match your filters"), áp dụng cho catalog: "No courses match your filters." kèm CTA giống link "Clear all" đã có sẵn trong `.chip-row`/panel filter (`href="P002-course-catalog.html"`) |

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Result count (toolbar) | "1,247 courses found" | catalog toolbar template (Views/controller result count, the module) |
| Empty / no match | "No courses match your filters." (suy ra từ Page States "no courses match" + mẫu chung P043 "No posts match your filters") | View empty-text config / the module |
| Clear all filters (CTA) | "Clear all" | catalog filter panel template string (`.filter` header và `.chip-row`) |
| Bookmark CTA (per course card) | "Save Complete Modern React &amp; Next.js Bootcamp to wishlist" (mẫu `aria-label` động theo tên khoá học) | course card block template (Twig), the module |
| Free price badge | "Free" | course card price render logic (the module.field price = 0 → `.ccard__price--free`) |
| Sort control label | "Sort by" (label) với các option "Most popular" / "Newest" / "Highest rated" / "Price: low to high" / "Price: high to low" | catalog sort `<select>` template string |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (≤639.98px) | Grid course card `.grid--3` xuống còn 1 cột (kế thừa từ tier ≤991.98px xuống tiếp ở ≤639.98px); `.catalog__aside` (filter panel) đã ẩn hoàn toàn từ tier tablet; `.container` co về 100% width; pager wrap nhiều dòng (`.pager{flex-wrap:wrap}`) |
| tablet (640–991.98px) | `.catalog{grid-template-columns:280px 1fr}` sập về `1fr` (bỏ layout 2 cột) và `.catalog__aside` chuyển `display:none` — panel Category/Level/Rating/Price/Language bị ẩn khỏi luồng chính (prototype tĩnh chưa có nút "Filters" để mở lại, cần bổ sung filter-drawer/toggle khi build thật); course grid `.grid--3` còn 2 cột |
| desktop (992–1199.98px) | Layout đầy đủ quay lại: `.catalog` 2 cột (aside 280px sticky `top:90px` + kết quả), course grid 3 cột đúng như `.grid--3`; `.container` giới hạn theo tier `container-lg` |
| wide (≥1200px) | Giữ nguyên layout 2 cột catalog + grid 3 cột course card, `.container` mở rộng theo tier `container-xl` (≥1200px) rồi không giới hạn thêm ở ≥1400px; khoảng trắng/spacing rộng rãi hơn, sticky filter aside vẫn cố định theo scroll |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Catalog + filters + search | done | 40 courses seeded |
