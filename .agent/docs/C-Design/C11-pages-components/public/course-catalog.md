---
page: course-catalog
mode: as-built
owner_module: lms_course
route_group: public
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Course Catalog

**Canonical URL:** `/courses` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.catalog`
**Solution:** Custom controller (`CoursePublicController::catalog`) queries `lms_course` storage with multi-value filter parameters; renders paginated grid via `lms-catalog.html.twig`. AJAX search endpoint at `/api/courses/search` (`lms_course.api_search`) powers front-end live-filtering without page reload.
**Owner module:** [`lms_course`](../../C05-modules/lms_course.md)
**Primary source:** `lms_course` entity
**Editable source:** `lms_course` entity fields; `taxonomy_term` (course_category, course_level, course_language) for filter options
**Editor role:** administrator, manager, instructor
**Editor management UI:** `/workspace/courses` (list); `/workspace/courses/{id}/edit` (edit); `/workspace/courses/create` (create)
**Revision / moderation:** No (lms_course is not revisionable)
**Forms:** Search/filter form (GET parameters: `search`, `category`, `level`, `price`, `rating`, `sort`) — client-side rendered; submitted to `/api/courses/search` JSON endpoint
**Theme template:** `web/modules/custom/lms_course/templates/lms-catalog.html.twig` *(per [`../../C09-skins.md`](../../C09-skins.md))*
**Access:** Public — `_permission: 'access content'`
**Cache:** Anonymous-cacheable — invalidated by `lms_course_list` cache tag; per-user context for enrollment/wishlist status overlay
**Translation:** Monolingual EN
**Related entities:** [`lms_course`](../../C04-entities/01-content-entities.md), [`taxonomy_term`](../../C04-entities/06-taxonomies.md)
**Prototype reference:** [`../../C10-prototypes/html/public/courses.html`](../../C10-prototypes/html/public/courses.html)
**Notes:** Pagination is 12 courses per page. The `searchApi()` method returns JSON (`total`, `courses[]`, pager metadata) for AJAX filtering. Skeleton loading cards are shown in the prototype during async fetch.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined ONCE in [`../_common.md`](../_common.md) and NOT repeated here.

### 1. Page Header / Breadcrumb — *Breadcrumb và tiêu đề trang*

- **Scope:** Page-specific
- **Implementation mechanism:** Twig block; breadcrumb built by `LmsCourseBreadcrumbBuilder` service
- **Plugin / Module:** `lms_course.breadcrumb` service → `lms_course`
- **Source content type / Entity:** Route context (static: Home > Courses)
- **Backed by fields / config keys:** Computed from route name
- **Editable source:** computed/read-only
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** No
- **Translation source:** Interface translation
- **Edit smoke:** N/A
- **View mode / Projection:** Breadcrumb bar + page title
- **Region / Placement:** Top of content area, below navbar
- **Notes:** Breadcrumb service applies to routes starting with `lms_course.`.
- **How to implement (EN):** `LmsCourseBreadcrumbBuilder::build()` adds Home > Courses crumbs for `lms_course.catalog`.
- **How to implement (VI):** `LmsCourseBreadcrumbBuilder::build()` thêm breadcrumb Home > Courses cho route `lms_course.catalog`.
- **Solution Architect Request:**

### 2. Search Bar + Sort Controls — *Thanh tìm kiếm và sắp xếp*

- **Scope:** Page-specific
- **Implementation mechanism:** HTML form (GET); AJAX call to `/api/courses/search` (`CoursePublicController::searchApi`) on input change
- **Plugin / Module:** `CoursePublicController::searchApi` → `lms_course`
- **Source content type / Entity:** GET parameters (`search`, `sort`); data from `lms_course` storage
- **Backed by fields / config keys:** `lms_course.name`, `lms_course.field_description`, sort options: newest/price/default
- **Editable source:** computed/read-only (search index over entity fields)
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** No
- **Translation source:** Interface translation (placeholder text)
- **Edit smoke:** Enter search term → courses grid updates via AJAX
- **View mode / Projection:** Search input + sort dropdown
- **Region / Placement:** Above the two-column layout (filters + grid)
- **Notes:** `?search=` GET parameter also accepted on direct page load (SSR). AJAX endpoint returns JSON consumed by front-end JS.
- **How to implement (EN):** On input change, call `GET /api/courses/search?search=...&sort=...`; replace grid with returned course cards.
- **How to implement (VI):** Khi input thay đổi, gọi `GET /api/courses/search?search=...&sort=...`; thay thế grid bằng danh sách course trả về.
- **Solution Architect Request:**

### 3. Filter Sidebar — *Bộ lọc bên trái*

- **Scope:** Page-specific
- **Implementation mechanism:** HTML checkbox/radio inputs (GET parameters); AJAX-driven filter update; filter options loaded from taxonomy terms
- **Plugin / Module:** `CoursePublicController::catalog` → `lms_course`
- **Source content type / Entity:** `taxonomy_term` — `course_category`, `course_level`, `course_language`; price type from `lms_course.field_price_type`
- **Backed by fields / config keys:** `taxonomy_term.name` (categories, levels, languages); `lms_course.field_price_type` (free/paid); rating range filter
- **Editable source:** `taxonomy_term:course_category`, `taxonomy_term:course_level`, `taxonomy_term:course_language` at `/admin/structure/taxonomy`
- **Editor role / permission:** administrator — `administer taxonomy`
- **Editor management UI:** `/admin/structure/taxonomy/manage/{vocab}/overview`
- **Revision / moderation:** No
- **Translation source:** Content translation (term labels)
- **Edit smoke:** Add a new category term → reload `/courses` → new filter option appears in sidebar
- **View mode / Projection:** Left sidebar with collapsible filter groups (Category, Level, Price, Language, Rating)
- **Region / Placement:** Left column (hidden on mobile — responsive)
- **Notes:** On mobile, the sidebar collapses. Filter state persisted in URL query parameters for shareability. Rating filter uses range (e.g. `rating=4` means 4 stars and above).
- **How to implement (EN):** Load taxonomy terms per vocabulary; render as checkbox groups; on change, append to `/api/courses/search` AJAX request.
- **How to implement (VI):** Load taxonomy term theo từng vocabulary; render dạng nhóm checkbox; khi thay đổi, gửi thêm vào request AJAX `/api/courses/search`.
- **Solution Architect Request:**

### 4. Course Grid — *Lưới khóa học*

- **Scope:** Page-specific
- **Implementation mechanism:** Twig loop of `lms-course-card.html.twig` sub-component; data built by `buildCourseCards()` helper; 12 per page
- **Plugin / Module:** `CoursePublicController::catalog` → `lms_course`
- **Source content type / Entity:** `lms_course` entities (filtered, paginated)
- **Backed by fields / config keys:** `name`, `field_thumbnail`, `field_price`, `field_price_type`, `field_rating`, `field_level`, `field_category`, `field_instructor`, `field_duration`, `field_bestseller`, `status=1`
- **Editable source:** `lms_course` entity fields at `/workspace/courses/{id}/edit`
- **Editor role / permission:** manager, administrator — `administer lms_course`
- **Editor management UI:** `/workspace/courses/{id}/edit`
- **Revision / moderation:** No
- **Translation source:** Content translation
- **Edit smoke:** Update course thumbnail/title → reload `/courses` → updated card visible in grid
- **View mode / Projection:** `lms_course_card` (card view with thumbnail, title, instructor, rating, price, level badge)
- **Region / Placement:** Right column (main content area)
- **Notes:** Skeleton loading cards shown during AJAX fetch (see prototype). Empty state shows "No courses found" when filters match zero results.
- **How to implement (EN):** Query `lms_course` with active filters + pagination (12/page); pass card data arrays to `lms-catalog.html.twig`; render each as `lms-course-card.html.twig` include.
- **How to implement (VI):** Query `lms_course` với filter đang active + phân trang (12/trang); truyền mảng card data vào `lms-catalog.html.twig`; render mỗi item qua `lms-course-card.html.twig`.
- **Solution Architect Request:**

### 5. Pagination — *Phân trang*

- **Scope:** Page-specific
- **Implementation mechanism:** Drupal pager render element; also returned in JSON from `searchApi` for AJAX-driven pagination
- **Plugin / Module:** Drupal core pager; `CoursePublicController::catalog`
- **Source content type / Entity:** computed (pager metadata from entity query)
- **Backed by fields / config keys:** computed — total count / page size (12) / current page
- **Editable source:** computed/read-only
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** No
- **Translation source:** Interface translation
- **Edit smoke:** N/A
- **View mode / Projection:** Numbered page links + prev/next
- **Region / Placement:** Below course grid
- **Notes:** Pager theme override at `lms_theme/templates/navigation/pager.html.twig`.
- **How to implement (EN):** Use `PagerSelectExtender` on the entity query; render `#type => pager` element in template; `searchApi` returns `total` + `per_page` for client-side pager.
- **How to implement (VI):** Dùng `PagerSelectExtender` trên entity query; render phần tử `#type => pager` trong template; `searchApi` trả về `total` + `per_page` cho client-side pager.
- **Solution Architect Request:**

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Page Header / Breadcrumb | "Courses" heading, breadcrumb trail | computed (route) | — | N/A | N/A | No | Interface translation | (page cache) | N/A |
| Search Bar + Sort Controls | Search input, sort dropdown (Newest / Price / Default) | computed (GET params) | `search`, `sort` | N/A | N/A | No | Interface translation | `lms_course_list` | Search term → AJAX grid updates |
| Filter Sidebar | Category checkboxes, Level checkboxes, Price radio, Language checkboxes, Rating range | `taxonomy_term:course_category/level/language`, `lms_course.field_price_type` | Term names, price_type values | `/admin/structure/taxonomy/manage/{vocab}/overview` | administrator / `administer taxonomy` | No | Content translation | `taxonomy_term_list` | Add category → reload → filter option appears |
| Course Grid | Course cards (thumbnail, title, instructor, rating stars, price, level badge, bestseller tag) | `lms_course` entity | `name`, `field_thumbnail`, `field_price`, `field_price_type`, `field_rating`, `field_level`, `field_instructor`, `field_bestseller` | `/workspace/courses/{id}/edit` | manager / `administer lms_course` | No | Content translation | `lms_course_list`, per-entity tags | Edit course → reload → card updated |
| Pagination | Page number links, prev/next controls | computed (pager) | total / page_size / page | N/A | N/A | No | Interface translation | (page cache) | Navigate to page 2 → correct courses shown |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Page size = 12 | Developer constant in `catalog()` | Code default — no ADR | Revisit if owner needs configurable page size |
| Sort option labels ("Newest", "Price", "Default") | UI chrome in template | Template default | Already interface-translatable |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Page Header / Breadcrumb | DONE | — | `LmsCourseBreadcrumbBuilder` implemented |
| Search Bar + Sort Controls | DONE | — | AJAX search via `/api/courses/search` implemented |
| Filter Sidebar | DONE | — | Multi-value filters: search, category, level, price, rating, sort in `catalog()` |
| Course Grid | DONE | — | `buildCourseCards()` + `lms-course-card.html.twig` implemented; 12/page |
| Pagination | DONE | — | `PagerSelectExtender` used; pager theme override in `lms_theme` |
