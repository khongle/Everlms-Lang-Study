---
page: homepage
mode: as-built
owner_module: lms_course
route_group: public
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Homepage

**Canonical URL:** `/homepage` *(per [`../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_course.homepage`
**Solution:** Custom controller (`CoursePublicController::homepage`) aggregates multi-entity data and renders via Twig template; one of 15 switchable homepage style partials is conditionally included based on the `homepage_style` theme setting.
**Owner module:** [`lms_course`](../../C05-modules/lms_course.md)
**Primary source:** Multiple — `lms_course`, `taxonomy_term` (course_category), `lms_rating`, `node:blog`, `lms_membership_plan`, `lms_faq` (global scope), `node:introduction`
**Editable source:** `lms_base` theme settings (hero copy, section toggles, style switcher); `lms_course` entities (featured, bestseller flags); taxonomy terms (course_category); `lms_rating` entities (testimonials); `node:blog` (news); `lms_membership_plan` entities (membership section); `lms_faq` entities (global scope); `node:introduction` (introductions section)
**Editor role:** administrator, manager, instructor (course cards); administrator (theme settings, section toggles)
**Editor management UI:** `/admin/appearance/settings/lms_theme` (style + section toggles); `/workspace/courses` (course cards); `/workspace/content/blog` (news); `/workspace/content/faqs` (FAQs); `/workspace/pricing` (membership plans)
**Revision / moderation:** N/A (aggregated controller output; individual entities vary)
**Forms:** — (read-only public page)
**Theme template:** `web/modules/custom/lms_course/templates/lms-homepage.html.twig` → conditionally includes `web/themes/custom/lms_theme/templates/home/styles/style-{key}.html.twig` *(per [`../../C09-skins.md`](../../C09-skins.md))*
**Access:** Public (anonymous + authenticated); `_permission: 'access content'`
**Cache:** Anonymous-cacheable — invalidated by `lms_course_list`, `taxonomy_term_list`, `lms_rating_list`, `lms_membership_plan_list`, `lms_faq_list` cache tags
**Translation:** Monolingual EN (theme settings copy can be updated per deployment)
**Related entities:** [`lms_course`](../../C04-entities/01-content-entities.md), [`lms_rating`](../../C04-entities/07-communication-entities.md), [`lms_membership_plan`](../../C04-entities/04-commerce-entities.md), [`lms_faq`](../../C04-entities/01-content-entities.md)
**Prototype reference:** [`../../C10-prototypes/html/public/homepage.html`](../../C10-prototypes/html/public/homepage.html)
**Notes:** The `homepage_style` setting (default: `glassmorphism`) controls which of the 15 style-variant Twig partials is included. `CoursePublicController::homepage` reads settings via `getHomepageSettings()` and per-section toggle booleans control which sections are rendered. Section toggles stored in `lms_theme` theme settings. The front page is configured via Drupal's site configuration to use `/homepage` — not `/`.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined ONCE in [`../_common.md`](../_common.md) and NOT repeated here.

### 1. Hero Section — *Phần banner chính*

- **Scope:** Page-specific
- **Implementation mechanism:** Twig template region inside style-variant partial (e.g. `style-1-glass.html.twig`); hero copy injected as Twig variables from `getHomepageSettings()`
- **Plugin / Module:** `CoursePublicController::homepage` → `lms_course`
- **Source content type / Entity:** `lms_base` theme settings config + live stats from `getHomepageStats()`
- **Backed by fields / config keys:** `lms_theme.settings` keys — `hero_title`, `hero_subtitle`, `hero_cta_text`, `hero_cta_url`, `hero_enabled`; computed: course count, certificate count
- **Editable source:** `lms_theme.settings` (Drupal theme settings config)
- **Editor role / permission:** administrator — `administer site configuration`
- **Editor management UI:** `/admin/appearance/settings/lms_theme`
- **Revision / moderation:** No (config, not entity)
- **Translation source:** Config translation
- **Edit smoke:** Edit hero_title in theme settings → save → reload `/homepage` → verify heading text changed
- **View mode / Projection:** Full page hero section
- **Region / Placement:** Above the fold, full-width
- **Notes:** Stats (course count, cert count) are live DB queries in `getHomepageStats()`; string overrides available via theme settings. Style-variant determines the visual layout (glassmorphism, corporate, nature, etc.).
- **How to implement (EN):** 1. Add config keys to `lms_theme.settings` schema. 2. Read them in `CoursePublicController::getHomepageSettings()`. 3. Pass to template. 4. Each style partial renders hero using these variables.
- **How to implement (VI):** 1. Thêm config key vào `lms_theme.settings`. 2. Đọc trong `getHomepageSettings()`. 3. Truyền vào template. 4. Partial style hiển thị hero từ các biến này.
- **Solution Architect Request:**

### 2. Featured Courses Section — *Phần khóa học nổi bật*

- **Scope:** Page-specific
- **Implementation mechanism:** Twig include of `lms-course-card.html.twig` sub-component; data built by `buildCourseCards()` private helper
- **Plugin / Module:** `CoursePublicController::homepage` → `lms_course`
- **Source content type / Entity:** `lms_course` entities (filtered by `field_featured = TRUE` or `field_bestseller = TRUE`)
- **Backed by fields / config keys:** `lms_course.field_thumbnail`, `lms_course.name`, `lms_course.field_price`, `lms_course.field_price_type`, `lms_course.field_rating`, `lms_course.field_category`, `lms_course.field_level`, `lms_course.uid` (instructor)
- **Editable source:** `lms_course` entity — `field_featured`, `field_bestseller` boolean flags
- **Editor role / permission:** manager, administrator — `administer lms_course`
- **Editor management UI:** `/workspace/courses/{id}/edit` (toggle featured/bestseller flag)
- **Revision / moderation:** No (lms_course is not revisionable)
- **Translation source:** Content translation (course name is translatable)
- **Edit smoke:** Set `field_featured=TRUE` on a course → reload `/homepage` → card appears in featured grid
- **View mode / Projection:** `lms_course_card` sub-template (card view)
- **Region / Placement:** First content section below hero
- **Notes:** `homepage_featured_enabled` toggle in theme settings controls section visibility. Section toggles from `getHomepageSettings()`.
- **How to implement (EN):** Query `lms_course` with `field_featured=TRUE`; build card arrays via `buildCourseCards()`; render through `lms-course-card.html.twig`.
- **How to implement (VI):** Query `lms_course` với `field_featured=TRUE`; tạo mảng card qua `buildCourseCards()`; render qua `lms-course-card.html.twig`.
- **Solution Architect Request:**

### 3. Popular Categories Section — *Phần danh mục phổ biến*

- **Scope:** Page-specific
- **Implementation mechanism:** Twig loop over taxonomy terms; thumbnail loaded from term entity
- **Plugin / Module:** `CoursePublicController::homepage` → `lms_course`
- **Source content type / Entity:** `taxonomy_term` vocabulary `course_category`
- **Backed by fields / config keys:** Term name, term description, term thumbnail image field (if configured), course count per category (computed)
- **Editable source:** Taxonomy terms at `/admin/structure/taxonomy/manage/course_category/overview`
- **Editor role / permission:** administrator — `administer taxonomy`
- **Editor management UI:** `/admin/structure/taxonomy/manage/course_category/overview`
- **Revision / moderation:** No
- **Translation source:** Content translation (taxonomy term labels)
- **Edit smoke:** Add/rename a category term → reload `/homepage` → updated category card appears
- **View mode / Projection:** Category card (icon + name + count)
- **Region / Placement:** Second content section
- **Notes:** `homepage_categories_enabled` toggle controls visibility. Category thumbnails require a custom field on the `course_category` vocabulary (field presence «unverified»).
- **How to implement (EN):** Load published `course_category` terms; for each, count associated `lms_course` entities; render as card grid.
- **How to implement (VI):** Tải term `course_category`; đếm `lms_course` liên kết; render dạng card grid.
- **Solution Architect Request:**

### 4. How It Works Section — *Phần hướng dẫn sử dụng*

- **Scope:** Page-specific
- **Implementation mechanism:** Static Twig content (step cards with icons); copy from theme settings
- **Plugin / Module:** `lms_course` (template), `lms_theme` (settings)
- **Source content type / Entity:** `lms_theme.settings` (copy) or hardcoded defaults
- **Backed by fields / config keys:** `lms_theme.settings` training_process copy keys (or static defaults in template)
- **Editable source:** `lms_theme.settings` or template defaults
- **Editor role / permission:** administrator — `administer site configuration`
- **Editor management UI:** `/admin/appearance/settings/lms_theme`
- **Revision / moderation:** No
- **Translation source:** Config translation
- **Edit smoke:** Edit step copy in theme settings → save → reload `/homepage` → updated step text visible
- **View mode / Projection:** 4-step card grid
- **Region / Placement:** Third content section
- **Notes:** `homepage_training_process_enabled` toggle. Steps are sequential: Browse → Enroll → Learn → Certify.
- **How to implement (EN):** Read step copy from theme settings with fallback defaults; render as 4-column card grid.
- **How to implement (VI):** Đọc nội dung bước từ theme settings với giá trị mặc định; render dạng lưới 4 cột.
- **Solution Architect Request:**

### 5. Testimonials Section — *Phần đánh giá học viên*

- **Scope:** Page-specific
- **Implementation mechanism:** Query `lms_rating` entities (approved, featured) via controller; render as testimonial cards
- **Plugin / Module:** `CoursePublicController::homepage` → `lms_course` / `lms_rating`
- **Source content type / Entity:** `lms_rating` entities
- **Backed by fields / config keys:** `lms_rating.field_rating` (score), `lms_rating.field_review` (text), `lms_rating.uid` (reviewer), `lms_rating.entity_id` (course)
- **Editable source:** `lms_rating` — approve/feature actions at `/workspace/ratings`
- **Editor role / permission:** administrator — `administer lms_course`
- **Editor management UI:** `/workspace/ratings`
- **Revision / moderation:** No
- **Translation source:** N/A (user-generated content)
- **Edit smoke:** Feature a rating at `/workspace/ratings` → reload `/homepage` → testimonial card appears
- **View mode / Projection:** Testimonial card (avatar initial, quote, author name, course name)
- **Region / Placement:** Fourth content section
- **Notes:** `homepage_testimonials_enabled` toggle. Only approved+featured ratings appear. `lms_rating` `RatingManagerController` provides the management UI.
- **How to implement (EN):** Query `lms_rating` where `field_approved=1` and `field_featured=1`; render as quote cards.
- **How to implement (VI):** Query `lms_rating` với `field_approved=1` và `field_featured=1`; render dạng quote card.
- **Solution Architect Request:**

### 6. Membership / Pricing Section — *Phần gói học phí*

- **Scope:** Page-specific
- **Implementation mechanism:** Load `lms_membership_plan` entities sorted by price; render as plan cards
- **Plugin / Module:** `CoursePublicController::getHomepageMemberships()` → `lms_course`
- **Source content type / Entity:** `lms_membership_plan` entities
- **Backed by fields / config keys:** `lms_membership_plan.name`, `lms_membership_plan.field_price`, `lms_membership_plan.field_duration`, `lms_membership_plan.field_features`
- **Editable source:** `lms_membership_plan` entity CRUD at `/workspace/pricing`
- **Editor role / permission:** administrator — `administer lms_course`
- **Editor management UI:** `/workspace/pricing`
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Add/edit a plan at `/workspace/pricing` → reload `/homepage` → plan card reflects changes
- **View mode / Projection:** Pricing plan card
- **Region / Placement:** Fifth content section (if `homepage_membership_enabled`)
- **Notes:** `homepage_membership_enabled` toggle. Links to `/pricing` for full plans page. Anonymous users see pricing; authenticated users see their active plan status.
- **How to implement (EN):** Load `lms_membership_plan` entities (published, sorted by price); render as plan comparison cards; CTA links to `/checkout/membership/{id}`.
- **How to implement (VI):** Load entity `lms_membership_plan` (published, sắp xếp theo giá); render thành card so sánh; CTA link đến `/checkout/membership/{id}`.
- **Solution Architect Request:**

### 7. Latest News Section — *Phần tin tức mới nhất*

- **Scope:** Page-specific
- **Implementation mechanism:** Query `node:blog` entities (type=blog, status=1, sort DESC, limit); render as news cards
- **Plugin / Module:** `CoursePublicController::homepage` → `lms_course`
- **Source content type / Entity:** `node:blog` (Drupal core node, blog bundle)
- **Backed by fields / config keys:** `node.title`, `node.field_thumb` (image), `node.created`, `node.body`
- **Editable source:** `node:blog` — managed via `/workspace/content/blog`
- **Editor role / permission:** administrator, manager — `administer lms_course`
- **Editor management UI:** `/workspace/content/blog`
- **Revision / moderation:** Core node revisions (if enabled)
- **Translation source:** Content translation
- **Edit smoke:** Publish a new `node:blog` → reload `/homepage` → appears in news section
- **View mode / Projection:** News card (thumbnail, title, date excerpt)
- **Region / Placement:** Sixth content section (if `homepage_news_enabled`)
- **Notes:** `homepage_news_enabled` toggle. Uses `node:blog`, NOT `lms_article`. This is the dual-track blog architecture; `lms_article` serves admin CRUD at `/manage/blog` only.
- **How to implement (EN):** Query node storage for `type=blog, status=1`; sort by created DESC; limit to 3–4 items; render as news card row.
- **How to implement (VI):** Query node storage với `type=blog, status=1`; sort created DESC; giới hạn 3–4 item; render thành hàng news card.
- **Solution Architect Request:**

### 8. FAQ Section — *Phần câu hỏi thường gặp*

- **Scope:** Page-specific
- **Implementation mechanism:** Query `lms_faq` entities with `field_scope=global`; render as accordion
- **Plugin / Module:** `CoursePublicController::homepage` → `lms_course`
- **Source content type / Entity:** `lms_faq` entities (`field_scope = global`)
- **Backed by fields / config keys:** `lms_faq.question`, `lms_faq.field_answer`, `lms_faq.weight`
- **Editable source:** `lms_faq` CRUD at `/workspace/content/faqs`
- **Editor role / permission:** administrator, manager — `administer lms_course`
- **Editor management UI:** `/workspace/content/faqs`
- **Revision / moderation:** No
- **Translation source:** Content translation (question and answer are translatable)
- **Edit smoke:** Create a FAQ with `field_scope=global` → reload `/homepage` → FAQ accordion item appears
- **View mode / Projection:** Accordion list (question → expanded answer)
- **Region / Placement:** Seventh content section (if `homepage_faqs_enabled`)
- **Notes:** `homepage_faqs_enabled` toggle. Sorted by `weight`. FAQs with `field_scope=course` appear on course detail page instead.
- **How to implement (EN):** Query `lms_faq` where `field_scope='global'`, order by weight; render as Bootstrap accordion.
- **How to implement (VI):** Query `lms_faq` với `field_scope='global'`, order by weight; render thành Bootstrap accordion.
- **Solution Architect Request:**

### 9. CTA Banner (Guest) — *Banner kêu gọi hành động*

- **Scope:** Page-specific
- **Implementation mechanism:** Conditional Twig block (shown when user is anonymous); CTA copy from theme settings
- **Plugin / Module:** `lms_course` (template), `lms_theme` (settings)
- **Source content type / Entity:** `lms_theme.settings` (CTA copy)
- **Backed by fields / config keys:** `lms_theme.settings` cta_guest copy keys
- **Editable source:** `lms_theme.settings`
- **Editor role / permission:** administrator — `administer site configuration`
- **Editor management UI:** `/admin/appearance/settings/lms_theme`
- **Revision / moderation:** No
- **Translation source:** Config translation
- **Edit smoke:** Edit CTA copy → save → reload `/homepage` as anonymous → verify CTA text updated
- **View mode / Projection:** Full-width banner with headline + two CTAs (Register / Browse Courses)
- **Region / Placement:** Last section before footer
- **Notes:** `homepage_cta_guest_enabled` toggle. Hidden for authenticated users.
- **How to implement (EN):** Check `current_user.isAnonymous()`; if true, render CTA banner with links to `/user/register` and `/courses`.
- **How to implement (VI):** Kiểm tra `current_user.isAnonymous()`; nếu đúng, render banner CTA với link đến `/user/register` và `/courses`.
- **Solution Architect Request:**

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Hero Section | Headline, subtitle, CTA button, live stats (course count, cert count) | `lms_theme.settings` + computed DB counts | `hero_title`, `hero_subtitle`, `hero_cta_text`; `getHomepageStats()` | `/admin/appearance/settings/lms_theme` | administrator / `administer site configuration` | No | Config translation | `lms_course_list`, `lms_user_certificate_list` | Edit hero_title → reload → heading changes |
| Featured Courses | Course card grid (thumbnail, name, price, rating, level, instructor) | `lms_course` entity | `name`, `field_thumbnail`, `field_price`, `field_price_type`, `field_rating`, `field_level`, `field_instructor` | `/workspace/courses/{id}/edit` | manager / `administer lms_course` | No | Content translation | `lms_course_list`, per-entity tags | Set `field_featured=TRUE` → reload → card appears |
| Popular Categories | Category card grid (icon, name, course count) | `taxonomy_term:course_category` | Term name, thumbnail (if configured), computed count | `/admin/structure/taxonomy/manage/course_category/overview` | administrator / `administer taxonomy` | No | Content translation | `taxonomy_term_list` | Add category term → reload → card appears |
| How It Works | 4-step process cards (icon, title, description) | `lms_theme.settings` or template defaults | `training_process_*` keys | `/admin/appearance/settings/lms_theme` | administrator / `administer site configuration` | No | Config translation | (page cache) | Edit step copy → reload → text changes |
| Testimonials | Quote cards (text, reviewer name, avatar initial, course name, rating) | `lms_rating` entity | `field_review`, `field_rating`, `uid`, `entity_id` | `/workspace/ratings` | administrator / `administer lms_course` | No | N/A | `lms_rating_list` | Feature a rating → reload → testimonial appears |
| Membership Plans | Plan comparison cards (name, price, duration, features, CTA) | `lms_membership_plan` entity | `name`, `field_price`, `field_duration`, `field_features` | `/workspace/pricing` | administrator / `administer lms_course` | No | N/A | `lms_membership_plan_list` | Add plan → reload → plan card appears |
| Latest News | News cards (thumbnail, title, date, excerpt) | `node:blog` | `title`, `field_thumb`, `created`, `body` (excerpt) | `/workspace/content/blog` | manager / `administer lms_course` | Core node revisions | Content translation | `node_list`, per-node tags | Publish blog node → reload → card appears |
| FAQ Accordion | Q&A accordion (question, answer, weight order) | `lms_faq` entity (`field_scope=global`) | `question`, `field_answer`, `weight` | `/workspace/content/faqs` | manager / `administer lms_course` | No | Content translation | `lms_faq_list` | Create global FAQ → reload → accordion item appears |
| CTA Banner | Headline, sub-copy, Register CTA, Browse Courses CTA | `lms_theme.settings` | `cta_guest_*` keys | `/admin/appearance/settings/lms_theme` | administrator / `administer site configuration` | No | Config translation | (page cache) | Edit CTA copy → reload as anonymous → text changes |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Step icons (emoji/SVG) in How It Works | Developer-owned UI chrome; not business copy | Template default — no ADR | Revisit if owner wants icon management |
| "How EverLMS Works" section title (fallback) | Default in `getHomepageSettings()` — overridable via theme settings | Code default | Already overridable; no exception needed |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Hero Section | DONE | — | 7 style variants implemented; copy read from `getHomepageSettings()` |
| Featured Courses | DONE | — | `field_featured` + `field_bestseller` filters implemented in `homepage()` |
| Popular Categories | DONE | — | Category grid with counts implemented |
| How It Works | DONE | — | Step cards rendered; copy from theme settings with defaults |
| Testimonials | DONE | — | Loads featured+approved `lms_rating` entities |
| Membership Plans | DONE | — | `getHomepageMemberships()` loads `lms_membership_plan` sorted by price |
| Latest News | DONE | — | Reads `node:blog` (NOT `lms_article`); see dual-track blog note |
| FAQ Accordion | DONE | — | Loads `lms_faq` with `field_scope=global` |
| CTA Banner | DONE | — | Conditional on `current_user.isAnonymous()`; `homepage_cta_guest_enabled` toggle |
