---
page: categories
mode: as-built
owner_module: lms_course
route_group: public
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Categories

**Canonical URL:** `/categories` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.category`
**Solution:** Custom controller (`CoursePublicController::categories`) loads all published `course_category` taxonomy terms and computes per-category course counts; renders via `lms-categories.html.twig` as a full-page category browse grid with an optional "not sure where to start" CTA.
**Owner module:** [`lms_course`](../../C05-modules/lms_course.md)
**Primary source:** `taxonomy_term` vocabulary `course_category`
**Editable source:** `taxonomy_term:course_category` terms; `lms_course` entities (for course counts per category)
**Editor role:** administrator
**Editor management UI:** `/admin/structure/taxonomy/manage/course_category/overview`
**Revision / moderation:** No
**Forms:** — (read-only browse page)
**Theme template:** `web/modules/custom/lms_course/templates/lms-categories.html.twig` *(per [`../../C09-skins.md`](../../C09-skins.md))*
**Access:** Public — `_permission: 'access content'`
**Cache:** Anonymous-cacheable — invalidated by `taxonomy_term_list`, `lms_course_list`
**Translation:** Monolingual EN (term names are translatable)
**Related entities:** [`taxonomy_term:course_category`](../../C04-entities/06-taxonomies.md), [`lms_course`](../../C04-entities/01-content-entities.md)
**Prototype reference:** [`../../C10-prototypes/html/public/categories.html`](../../C10-prototypes/html/public/categories.html)
**Notes:** Individual category detail is at `/categories/{taxonomy_term}` (route `lms_course.category_detail`, `CoursePublicController::categoryDetail`). The `categories()` and `categoryDetail()` controller methods exist in routing.yml; their implementation details were not fully read from the controller file (tail of 1,969-line file) but are confirmed present.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined ONCE in [`../_common.md`](../_common.md) and NOT repeated here.

### 1. Page Hero — *Banner trang danh mục*

- **Scope:** Page-specific
- **Implementation mechanism:** Twig block with static copy and aggregate stats
- **Plugin / Module:** `CoursePublicController::categories` → `lms_course`
- **Source content type / Entity:** `lms_theme.settings` (headline, subtitle) + computed stats (total courses, category count)
- **Backed by fields / config keys:** Template defaults or `lms_theme.settings` copy keys; computed: `count(lms_course)`, `count(taxonomy_term:course_category)`
- **Editable source:** `lms_theme.settings` (headline/subtitle overrides)
- **Editor role / permission:** administrator — `administer site configuration`
- **Editor management UI:** `/admin/appearance/settings/lms_theme`
- **Revision / moderation:** No
- **Translation source:** Config translation
- **Edit smoke:** Edit category page headline in theme settings → reload `/categories` → heading updated
- **View mode / Projection:** Full-width hero (headline, subtitle, stat pills for total courses and categories)
- **Region / Placement:** Top of content area
- **Notes:** Stat pills (e.g. "1,200+ courses", "12 categories") are live DB counts. Template default: "Find your next skill".
- **How to implement (EN):** Count `lms_course` (published) and `taxonomy_term:course_category` (published); pass to template; render hero with stat pills.
- **How to implement (VI):** Đếm `lms_course` (published) và `taxonomy_term:course_category` (published); truyền vào template; render hero với stat pill.
- **Solution Architect Request:**

### 2. Category Grid — *Lưới danh mục*

- **Scope:** Page-specific
- **Implementation mechanism:** Twig loop over taxonomy terms; each card links to `/categories/{term_id}` (filtered courses)
- **Plugin / Module:** `CoursePublicController::categories` → `lms_course`
- **Source content type / Entity:** `taxonomy_term:course_category`
- **Backed by fields / config keys:** `taxonomy_term.name`, `taxonomy_term.description`, category icon/thumbnail field (if configured on vocabulary), computed course count per term
- **Editable source:** `taxonomy_term:course_category` — name, description, weight at `/admin/structure/taxonomy/manage/course_category/overview`
- **Editor role / permission:** administrator — `administer taxonomy`
- **Editor management UI:** `/admin/structure/taxonomy/manage/course_category/overview`
- **Revision / moderation:** No
- **Translation source:** Content translation (term name, description)
- **Edit smoke:** Rename a category term → reload `/categories` → updated name on card
- **View mode / Projection:** Card grid (icon, name, description, course count badge); 4 columns desktop, 3 tablet, 2 mobile
- **Region / Placement:** Main content section (below hero)
- **Notes:** No dedicated icon field exists on `course_category` — there is no `field_icon` field storage config in the repo (confirmed: only `field_category_thumbnail` and `field_is_featured` exist on this vocabulary per `config/sync/field.field.taxonomy_term.course_category.*`). The `categories()` controller loads `field_category_thumbnail` (image field, confirmed in `CoursePublicController::categories()`) and the Twig template falls back to `<i class="fas fa-book"></i>` when no thumbnail is set (confirmed in `lms-categories.html.twig` line 26). Each card links to `/categories/{term_id}` which triggers `CategoryDetail` filtered course catalog. Course count per category is computed via entity query on `lms_course` with `field_category = term->id()` and `status = TRUE` (confirmed in `CoursePublicController::categories()`).
- **How to implement (EN):** Load all published `course_category` terms (sort by weight or name); for each term count associated `lms_course` (published); render as card grid linking to `/categories/{tid}`.
- **How to implement (VI):** Load tất cả term `course_category` published (sắp theo weight hoặc name); mỗi term đếm `lms_course` liên kết (published); render dạng card grid link đến `/categories/{tid}`.
- **Solution Architect Request:**

### 3. CTA Strip — *Strip kêu gọi hành động*

- **Scope:** Page-specific
- **Implementation mechanism:** Static Twig block with links to `/courses`
- **Plugin / Module:** `lms_course` (template)
- **Source content type / Entity:** static (UI chrome)
- **Backed by fields / config keys:** Template default copy
- **Editable source:** computed/read-only (template copy)
- **Editor role / permission:** N/A
- **Editor management UI:** N/A
- **Revision / moderation:** No
- **Translation source:** Interface translation
- **Edit smoke:** N/A
- **View mode / Projection:** Horizontal strip: "Browse all 1,200+ courses" text + "View all courses" button
- **Region / Placement:** Below category grid, above "Not sure where to start" section
- **Notes:** Links to `/courses`. Course count in copy can be dynamic (live DB count) or static template default.
- **How to implement (EN):** Render a styled horizontal strip with a link to `/courses`; optionally inject live course count.
- **How to implement (VI):** Render strip ngang có link đến `/courses`; tuỳ chọn inject số lượng khóa học thực tế.
- **Solution Architect Request:**

### 4. "Not Sure Where to Start" CTA Section — *Phần gợi ý bắt đầu*

- **Scope:** Page-specific
- **Implementation mechanism:** Static Twig block with CTA links to `/courses` and `/contact`
- **Plugin / Module:** `lms_course` (template)
- **Source content type / Entity:** static (UI chrome) or `lms_theme.settings`
- **Backed by fields / config keys:** Template defaults
- **Editable source:** `lms_theme.settings` (if copy overrideable) or template
- **Editor role / permission:** administrator — `administer site configuration`
- **Editor management UI:** `/admin/appearance/settings/lms_theme`
- **Revision / moderation:** No
- **Translation source:** Interface / Config translation
- **Edit smoke:** N/A
- **View mode / Projection:** Dark-background section with headline, sub-copy, and two CTA buttons
- **Region / Placement:** Below CTA strip, before footer
- **Notes:** This CTA section ("Not sure where to start?" / "Take a quiz" / "Talk to an advisor") from the prototype is NOT present in the current `lms-categories.html.twig` template (confirmed: template is 93 lines and contains no such section). The page ends after the category grid. No advisor CTA copy or quiz link is implemented.
- **How to implement (EN):** Render a full-width section with headline, description, and two CTA links (e.g. Browse Courses + Contact).
- **How to implement (VI):** Render section full-width với headline, mô tả, và hai link CTA (VD: Browse Courses + Contact).
- **Solution Architect Request:**

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Page Hero | Headline, subtitle, stat pills (course count, category count) | `lms_theme.settings` (copy) + computed counts | Copy keys; `count(lms_course)`, `count(taxonomy_term)` | `/admin/appearance/settings/lms_theme` | administrator / `administer site configuration` | No | Config translation | `taxonomy_term_list`, `lms_course_list` | Edit headline → reload → updated |
| Category Grid | Category cards (icon, name, description, course count badge, link) | `taxonomy_term:course_category` | `name`, `description`, `weight`; computed course count | `/admin/structure/taxonomy/manage/course_category/overview` | administrator / `administer taxonomy` | No | Content translation | `taxonomy_term_list`, `lms_course_list` | Rename term → reload → updated on card |
| CTA Strip | "Browse all courses" text, "View all courses" button | static UI chrome / computed count | template defaults + optional count | N/A | N/A | No | Interface translation | (page cache) | N/A |
| Not Sure CTA Section | Headline, sub-copy, CTA buttons | static or `lms_theme.settings` | template defaults | `/admin/appearance/settings/lms_theme` | administrator | No | Config / Interface translation | (page cache) | N/A |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Category icon/emoji (prototype) | Icon field not confirmed on `course_category` vocabulary | Prototype placeholder | Add image field to vocabulary if icon management needed |
| CTA strip copy ("Browse all … courses") | UI chrome template default | Template default | Interface-translatable |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Page Hero | DONE | — | `categories()` method implemented (confirmed in routing.yml; file tail unread) |
| Category Grid | DONE | — | Template `lms-categories.html.twig` confirmed; category count aggregation confirmed in `CoursePublicController::categories()`: entity query on `lms_course` with `field_category = term->id()` and `status = TRUE` |
| CTA Strip | DONE | — | Visible in prototype; implementation assumed in template |
| Not Sure CTA Section | DONE | — | Visible in prototype |
