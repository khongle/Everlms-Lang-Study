---
page: search-results
mode: target-state
owner_module: lms_course
route_group: public
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Search Results

**Canonical URL:** `/courses?search={query}` (course search) · `/search/node` (Drupal core search, inactive)
**Route name:** `lms_course.catalog` (course search via query params) · `lms_course.api_search` (AJAX JSON backend)
**Solution:** No dedicated `/search` or `/search-results` route exists. Course search is implemented as a filtered variant of the course catalog page (`/courses`): `CoursePublicController::catalog()` accepts `?search=`, `?category=`, `?level=`, `?price=`, `?rating=`, `?sort=` query parameters. An AJAX JSON endpoint (`/api/courses/search`) returns filtered course results for dynamic front-end updates. Drupal core `search` module is enabled with `node_search` plugin at `/search/node` but is not styled or customised for the LMS frontend — it is effectively unused by the custom LMS UI.
**Owner module:** `lms_course` (`web/modules/custom/lms_course/src/Controller/CoursePublicController.php`)
**Primary source:** `lms_course` entity (filtered query via `CoursePublicController::catalog()`)
**Editable source:** Course content managed at `/workspace/courses`; category/level/language taxonomy at `/admin/structure/taxonomy`
**Editor role:** `manager`, `administrator`
**Editor management UI:** `/workspace/courses` · `/workspace/courses/{id}/edit`
**Revision / moderation:** N/A (search is read-only display)
**Forms:** No dedicated search form — `?search=` query param on `/courses`
**Theme template:** `lms_course/templates/lms-catalog.html.twig` (shared with catalog page; filtered display)
**Access:** Public (anonymous) — `_permission: 'access content'`
**Cache:** Per-URL (query params vary cache) — `lms_course_list`, `taxonomy_term_list`
**Translation:** Monolingual EN
**Related entities:** [`lms_course`](../../C04-entities/), taxonomy_term (course_category, course_level)
**Prototype reference:** No dedicated search-results prototype in `C10-prototypes/html/public/` — see `courses.html` for filtered catalog
**Notes:** This spec is **target-state** because no standalone `/search-results` page exists. The search experience is embedded in `/courses` via URL query parameters. An LMS-native full-text search across courses + lessons + blog posts is a planned feature. Current `CoursePublicController::catalog()` supports multi-value filtering: `search` (text match on course name/description), `category` (taxonomy TID), `level` (taxonomy TID), `price` (free/paid), `rating` (range), `sort` (newest/price/default). Pagination: 12 per page. `CoursePublicController::searchApi()` at `/api/courses/search` returns JSON for AJAX updates (total, courses array, pager metadata). Drupal core `search.settings` configures `node_search` plugin at `/search/node` (content search) and `user_search` at `/search/user` — these use Drupal's default search index but are not themed for the LMS frontend.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined in [`../_common.md`](../_common.md) and not repeated here.

### 1. Search Input Bar

- **Scope:** Page-specific
- **Implementation mechanism:** HTML `<input type="search">` in `lms-catalog.html.twig`; submits as `?search=` GET param; AJAX variant calls `/api/courses/search`
- **Plugin / Module:** `lms_course` — `CoursePublicController::catalog()` + `searchApi()`
- **Source content type / Entity:** User input (no entity source)
- **Backed by fields / config keys:** `?search=` query parameter → LIKE match on `lms_course.name` and `field_description`
- **Editable source:** N/A (user input)
- **Editor role / permission:** N/A
- **Editor management UI:** N/A
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Type search term → page URL updates to `?search={term}` → results filtered
- **View mode / Projection:** Search input with submit button / real-time filter
- **Region / Placement:** Top of catalog / search results area
- **Notes:** The AJAX endpoint `/api/courses/search` enables live filtering without page reload. `CoursePublicController::searchApi()` returns JSON (total, courses array, pager metadata). The `search_form_block` (Drupal core) is also placed in `lms_theme` regions (`primary_menu` and `secondary_menu`) as `lms_theme_search_form_narrow` / `lms_theme_search_form_wide` — but this routes to `/search/node` (Drupal content search), not the LMS catalog search
- **How to implement (EN):** Render search input in lms-catalog.html.twig; on input change (debounced), call `/api/courses/search?search={term}&...`; update results area with returned JSON; fall back to full GET request on submit
- **How to implement (VI):** Render ô tìm kiếm trong lms-catalog.html.twig; khi input thay đổi (có debounce), gọi `/api/courses/search?search={term}&...`; cập nhật vùng kết quả với JSON trả về; fallback sang GET request khi submit
- **Solution Architect Request:** —

### 2. Filter Panel (Category, Level, Price, Rating)

- **Scope:** Page-specific
- **Implementation mechanism:** Filter chips / dropdowns in `lms-catalog.html.twig`; each filter submits as query param
- **Plugin / Module:** `lms_course` — `CoursePublicController::catalog()`
- **Source content type / Entity:** `taxonomy_term:course_category`, `taxonomy_term:course_level` (for filter options); filter applied to `lms_course` query
- **Backed by fields / config keys:** `field_category` (TID), `field_level` (TID), `field_price_type` (free/paid), `field_rating` (range min/max)
- **Editable source:** `taxonomy_term:course_category` at `/admin/structure/taxonomy/manage/course_category` · `taxonomy_term:course_level` at `/admin/structure/taxonomy/manage/course_level`
- **Editor role / permission:** `administrator`
- **Editor management UI:** `/admin/structure/taxonomy/manage/course_category/overview` · `/admin/structure/taxonomy/manage/course_level/overview`
- **Revision / moderation:** N/A (taxonomy)
- **Translation source:** N/A
- **Edit smoke:** Add taxonomy term "Machine Learning" to course_category → appears in category filter on `/courses`
- **View mode / Projection:** Filter chips or select dropdowns
- **Region / Placement:** Left sidebar or top filter bar (per catalog layout)
- **Notes:** Filter state serialized as query params. Multi-filter allowed (can combine category + level + price). `sort` parameter: newest / price / default. Results paged at 12/page. Taxonomy filter options loaded from vocabulary in `CoursePublicController::catalog()`
- **How to implement (EN):** Load course_category and course_level terms; render as filter chips; on change append to URL as query params; controller applies to entity query
- **How to implement (VI):** Tải các term course_category và course_level; render dưới dạng chip lọc; khi thay đổi, thêm vào URL dưới dạng query param; controller áp dụng vào entity query
- **Solution Architect Request:** —

### 3. Sort Control

- **Scope:** Page-specific
- **Implementation mechanism:** Select dropdown submitting `?sort=` query param in `lms-catalog.html.twig`
- **Plugin / Module:** `lms_course` — `CoursePublicController::catalog()`
- **Source content type / Entity:** N/A (UI control)
- **Backed by fields / config keys:** `?sort=` (newest | price | default)
- **Editable source:** N/A
- **Editor role / permission:** N/A
- **Editor management UI:** N/A
- **Revision / moderation:** N/A
- **Translation source:** Interface translation
- **Edit smoke:** Select "Price" sort → URL updates → courses re-ordered by price
- **View mode / Projection:** Dropdown select
- **Region / Placement:** Top-right of results area (per catalog layout)
- **Notes:** Three sort options: newest (created DESC), price (field_price ASC), default (entity ID)
- **How to implement (EN):** Render select dropdown with sort options; submit as ?sort= GET param; controller applies ORDER BY accordingly
- **How to implement (VI):** Render select dropdown với các tùy chọn sắp xếp; submit dưới dạng ?sort= GET param; controller áp dụng ORDER BY tương ứng
- **Solution Architect Request:** —

### 4. Results Grid (Course Cards)

- **Scope:** Page-specific
- **Implementation mechanism:** Twig loop in `lms-catalog.html.twig` over `courses` variable; each card rendered with `lms_course_card` sub-template
- **Plugin / Module:** `lms_course` — `CoursePublicController::catalog()` → `buildCourseCards()`
- **Source content type / Entity:** `lms_course` entities (filtered + paginated, 12/page)
- **Backed by fields / config keys:** `name`, `field_thumbnail`, `field_short_description`, `field_price`, `field_price_type`, `field_rating`, `field_level`, `field_category`, `field_instructor`, `field_duration`
- **Editable source:** `lms_course` entity — managed at `/workspace/courses/{id}/edit`
- **Editor role / permission:** `manager` / `administer lms_course`
- **Editor management UI:** `/workspace/courses` · `/workspace/courses/{id}/edit`
- **Revision / moderation:** Not configured (no workflow)
- **Translation source:** `lms_course` is translatable
- **Edit smoke:** Edit course title → save → reload search → card shows updated title
- **View mode / Projection:** Course card (lms_course_card sub-template)
- **Region / Placement:** Main results grid (3–4 columns)
- **Notes:** 0-result state ("No courses found") should be handled in template. `buildCourseCards()` private helper in `CoursePublicController` normalizes card data. Featured/bestseller flags (`field_bestseller`, `field_featured`) can show badges on cards
- **How to implement (EN):** Pass filtered `lms_course` entities through buildCourseCards(); render 12/page grid using lms-course-card.html.twig sub-template per item; include pager
- **How to implement (VI):** Truyền lms_course entities đã lọc qua buildCourseCards(); render grid 12 item/trang dùng sub-template lms-course-card.html.twig; bao gồm pager
- **Solution Architect Request:** —

### 5. Pagination

- **Scope:** Page-specific
- **Implementation mechanism:** Drupal pager or custom pager in `lms-catalog.html.twig`
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Computed from total results
- **Backed by fields / config keys:** Page count = ceil(total / 12)
- **Editable source:** N/A
- **Editor role / permission:** N/A
- **Editor management UI:** N/A
- **Revision / moderation:** N/A
- **Translation source:** Interface translation
- **Edit smoke:** Create 13+ courses matching search term → pager appears
- **View mode / Projection:** Previous / Next / page numbers
- **Region / Placement:** Below results grid
- **Notes:** `searchApi()` returns `pager` metadata (total, page, limit) for AJAX pagination support. Custom pager template at `lms_theme/templates/navigation/pager.html.twig`
- **How to implement (EN):** Use Drupal's pager system or pass total/page/limit to template; render custom pager at bottom of results
- **How to implement (VI):** Dùng hệ thống pager của Drupal hoặc truyền total/page/limit vào template; render pager tùy chỉnh ở cuối kết quả
- **Solution Architect Request:** —

### 6. Empty State

- **Scope:** Page-specific
- **Implementation mechanism:** Conditional block in `lms-catalog.html.twig` when `courses` array is empty
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** N/A
- **Backed by fields / config keys:** N/A
- **Editable source:** Static template
- **Editor role / permission:** Developer
- **Editor management UI:** N/A
- **Revision / moderation:** N/A
- **Translation source:** Interface translation
- **Edit smoke:** Search for a non-existent term → empty state displays
- **View mode / Projection:** Empty state card with suggestion
- **Region / Placement:** Main content area (replaces results grid)
- **Notes:** Implemented in `lms-catalog.html.twig` via `{% else %}` branch on `{% if courses|length > 0 %}`. Renders a `lms-empty-state` div with icon (`fas fa-search`), heading "No courses found", and subtext "Try adjusting your filters" (confirmed in `lms-catalog.html.twig` lines 131–137). No link to clear-all-filters is present — only the static message.
- **How to implement (EN):** Add Twig conditional `{% if courses is empty %}` block; show "No courses found" message with link to /courses (clear filters)
- **How to implement (VI):** Thêm điều kiện Twig `{% if courses is empty %}`; hiển thị thông báo "Không tìm thấy khóa học" với link đến /courses (xóa bộ lọc)
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Search Input | Text input, submit button, real-time results update | User input → `lms_course` entity query | ?search= → LIKE on name/description | N/A | N/A | N/A | Interface translation | Per-URL | Type query → courses filter |
| Filter Panel | Category, level, price, rating, sort filters | `taxonomy_term:course_category`, `taxonomy_term:course_level` | field_category TID, field_level TID, field_price_type, field_rating | `/admin/structure/taxonomy` | administrator | N/A | N/A | `taxonomy_term_list` | Add category → appears in filter |
| Sort Control | Sort dropdown (newest, price, default) | UI control | ?sort= query param | N/A | N/A | N/A | Interface translation | Per-URL | Select sort → order changes |
| Results Grid | Course cards (name, thumb, rating, price, level, instructor) | `lms_course` | name, field_thumbnail, field_short_description, field_price, field_rating, field_level | `/workspace/courses/{id}/edit` | manager / administer lms_course | N/A | EN | `lms_course_list` + per-nid | Edit course → card updates |
| Pagination | Page numbers, prev/next | Computed | total, page, limit | N/A | N/A | N/A | Interface translation | Per-URL | 13+ results → pager appears |
| Empty State | "No courses found" message + "Try adjusting your filters" | Static template (`lms-catalog.html.twig`) | — | Template edit | Developer | N/A | Interface translation | N/A | Search non-existent term → empty state (confirmed in template) |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Page size (12/page) | Controller constant | `CoursePublicController::catalog()` | Make configurable via lms_theme.settings if needed |
| Sort options (newest, price, default) | Controller enum | `CoursePublicController::catalog()` | Extend when additional sort criteria needed |
| Empty state copy ("No courses found") | Static template string | `lms-catalog.html.twig` | Move to theme settings or i18n string |
| Drupal core search at `/search/node` | Core feature, not integrated with LMS UI | Drupal core `search` module | Disable or restyle if LMS site search is needed |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Search via ?search= param on /courses | DONE | — | CoursePublicController::catalog() handles ?search= filter |
| AJAX search via /api/courses/search | DONE | — | CoursePublicController::searchApi() returns JSON |
| Filter Panel (category, level, price) | DONE | — | CoursePublicController::catalog() accepts all filter params |
| Sort Control | DONE | — | sort=newest|price|default supported |
| Results Grid (lms-catalog.html.twig) | DONE | — | Shared with /courses catalog page |
| Pagination | DONE | — | 12/page, pager metadata in searchApi() response |
| Empty State | DONE | — | `{% else %}` block in `lms-catalog.html.twig`: renders `lms-empty-state` div with "No courses found" heading and "Try adjusting your filters" text (confirmed in `lms-catalog.html.twig`). |
| Dedicated /search-results route | PLANNED | — | No standalone search-results page; search is catalog with filters |
| Full-text LMS search (courses + lessons + blog) | PLANNED | — | Not implemented; would require search_api contrib or Elasticsearch |
| Drupal core /search/node integration with LMS UI | NOT PLANNED | — | Core search is installed but not themed for LMS frontend |
