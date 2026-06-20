---
page: faq
mode: as-built
owner_module: lms_faq
route_group: public
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: FAQ (Frequently Asked Questions)

**Canonical URL:** `/` — FAQs appear as a section on the **Homepage** (global scope) and on **Course Detail** (`/courses/{id}`, course scope). There is no dedicated `/faq` public page route.
**Route name:** FAQs rendered within `lms_course.homepage` and `lms_course.detail` (not a standalone route)
**Solution:** `lms_faq` entity (`field_scope`: global / course / lesson). `CoursePublicController::homepage()` loads global-scope FAQs. `CoursePublicController::detail()` loads course-scope FAQs filtered by `field_course`. No dedicated public `/faq` route exists; prototype at `public/faq.html` is a design mockup only.
**Owner module:** `lms_faq` (entity) · `lms_course` (controller that renders FAQs on public pages)
**Primary source:** `lms_faq` entity (base table `lms_faq`)
**Editable source:** `lms_faq` entity fields: `question`, `field_answer`, `field_scope`, `field_course`, `field_category`, `weight`
**Editor role:** `manager`, `administrator`
**Editor management UI:** `/workspace/content/faqs` (WorkspaceContentController list) · `/workspace/content/faqs/add` (create) · `/workspace/content/faqs/{id}/edit`
**Revision / moderation:** Not revisionable (no `status` field, no workflow)
**Forms:** Standard ContentEntityForm (no custom class); workspace forms via `WorkspaceContentController::faqCreateForm` / `faqEditForm`
**Theme template:** No dedicated FAQ Twig template — FAQ items rendered inline within `lms-homepage.html.twig` (homepage section) and `lms-course-detail.html.twig` (course detail FAQ section)
**Access:** Public (anonymous) — rendered within pages accessible via `access content`
**Cache:** Anonymous-cacheable — invalidated by `lms_faq_list` tag (entity list cache tag)
**Translation:** Translatable entity (`lms_faq` has `langcode` base key); monolingual EN in practice
**Related entities:** [`lms_faq`](../../C04-entities/), [`lms_course`](../../C04-entities/)
**Prototype reference:** [`../../C10-prototypes/html/public/faq.html`](../../C10-prototypes/html/public/faq.html) *(design mockup — no matching route in codebase)*
**Notes:** The `lms_faq` entity has 3 scope values: `global` (homepage), `course` (course detail), `lesson` (not yet surfaced on a public page). No `status` (published) field exists — all FAQs are always visible once saved. FAQ `field_category` references `taxonomy_term:faq_category` vocabulary but category-based filtering is not implemented in any public controller. The prototype shows a standalone `/faq` page with category sections (General, Courses, Payment, Certificates, Account) — this is target-state only; currently FAQs surface inside other pages.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined in [`../_common.md`](../_common.md) and not repeated here.

### 1. Page Hero / Search Bar

- **Scope:** Page-specific
- **Implementation mechanism:** Section within `lms-homepage.html.twig` or `lms-course-detail.html.twig`; prototype shows full-width hero with search input
- **Plugin / Module:** `lms_course` — `CoursePublicController`
- **Source content type / Entity:** Static copy (page title, subtitle) — no entity-backed hero for FAQ section
- **Backed by fields / config keys:** `lms_theme.settings` (via `getPublicPageSettings()`) or hardcoded in template
- **Editable source:** `lms_theme.settings` — theme settings form
- **Editor role / permission:** `administrator` / `administer themes`
- **Editor management UI:** `/admin/appearance/settings/lms_theme`
- **Revision / moderation:** N/A (config)
- **Translation source:** N/A
- **Edit smoke:** Update theme FAQ section heading in theme settings → reload page → heading updated
- **View mode / Projection:** Static marketing hero
- **Region / Placement:** Top of page / section header
- **Notes:** FAQ search is not implemented in the backend — no `/api/courses/search` equivalent for FAQs. Prototype shows search-as-you-type; this is target-state
- **How to implement (EN):** Add FAQ hero section to the homepage or a dedicated template; wire a client-side JS filter over rendered FAQ items as a quick implementation of search
- **How to implement (VI):** Thêm section hero FAQ vào template homepage; sử dụng JS filter phía client để lọc các FAQ item đã được render
- **Solution Architect Request:** —

### 2. FAQ Accordion by Category — Global Scope

- **Scope:** Page-specific
- **Implementation mechanism:** Twig loop within `lms-homepage.html.twig` over `faqs` variable; accordion UI in template
- **Plugin / Module:** `lms_course` — `CoursePublicController::homepage()` passes `faqs` (global scope)
- **Source content type / Entity:** `lms_faq` — `field_scope = 'global'`
- **Backed by fields / config keys:** `question`, `field_answer`, `field_category` (taxonomy_term:faq_category), `weight`
- **Editable source:** `lms_faq` entity; manage at `/workspace/content/faqs`
- **Editor role / permission:** `manager` / `administer lms_course`
- **Editor management UI:** `/workspace/content/faqs` · `/workspace/content/faqs/add`
- **Revision / moderation:** Not revisionable
- **Translation source:** `lms_faq` is translatable
- **Edit smoke:** Create lms_faq with `field_scope=global` → reload homepage FAQ section → question appears
- **View mode / Projection:** Accordion / expand-collapse
- **Region / Placement:** FAQ section on homepage
- **Notes:** Category grouping (General, Courses, Payment, etc.) in prototype groups items by `field_category` taxonomy. Prototype groups not confirmed as implemented in `CoursePublicController::homepage()`; may be template-side grouping. `weight` field used for sort order
- **How to implement (EN):** Query `lms_faq` where `field_scope=global`, sort by `weight ASC`; group by `field_category` in template; render each group as labeled accordion section
- **How to implement (VI):** Truy vấn `lms_faq` với `field_scope=global`, sắp xếp theo `weight ASC`; nhóm theo `field_category` trong template; render mỗi nhóm thành accordion có nhãn
- **Solution Architect Request:** —

### 3. FAQ Accordion — Course Scope

- **Scope:** Page-specific (course detail page)
- **Implementation mechanism:** Twig loop within `lms-course-detail.html.twig` over course-scope FAQs
- **Plugin / Module:** `lms_course` — `CoursePublicController::detail()` passes course FAQs filtered by `field_course`
- **Source content type / Entity:** `lms_faq` — `field_scope = 'course'`, `field_course = {course_id}`
- **Backed by fields / config keys:** `question`, `field_answer`, `weight`
- **Editable source:** `lms_faq` entity with `field_course` set
- **Editor role / permission:** `manager` / `administer lms_course`
- **Editor management UI:** `/workspace/content/faqs/add` (set field_course) · `/workspace/content/faqs/{id}/edit`
- **Revision / moderation:** Not revisionable
- **Translation source:** Translatable
- **Edit smoke:** Create lms_faq with `field_scope=course`, `field_course={id}` → reload `/courses/{id}` → FAQ appears in course FAQ section
- **View mode / Projection:** Accordion
- **Region / Placement:** FAQ section within course detail page
- **Notes:** Course-scope FAQs are course-specific; they appear on the course detail page, not the homepage
- **How to implement (EN):** Query `lms_faq` where `field_scope=course AND field_course={course_id}`; pass to course detail template
- **How to implement (VI):** Truy vấn `lms_faq` với `field_scope=course AND field_course={course_id}`; truyền vào template chi tiết khóa học
- **Solution Architect Request:** —

### 4. Sidebar — Popular Questions + Contact CTA

- **Scope:** Page-specific
- **Implementation mechanism:** Twig aside in FAQ page template (prototype only)
- **Plugin / Module:** `lms_course` (static) or future endpoint
- **Source content type / Entity:** Static hardcoded links in prototype; no entity-backed sidebar in current implementation
- **Backed by fields / config keys:** Static
- **Editable source:** Template-level static — not entity-managed
- **Editor role / permission:** Developer only
- **Editor management UI:** N/A (requires code change)
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** Sidebar card
- **Region / Placement:** Right sidebar
- **Notes:** Prototype shows "Popular Questions" quick-link list and "Still need help?" CTA linking to `/contact`. Not implemented in current codebase (no standalone `/faq` route). Target-state for a future `/faq` page
- **How to implement (EN):** When a `/faq` route is added, load top-N FAQ items by view count or weight for "Popular" list; render contact CTA from `lms_theme.settings`
- **How to implement (VI):** Khi thêm route `/faq`, tải N FAQ phổ biến nhất theo view count hoặc weight; render CTA liên hệ từ `lms_theme.settings`
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Page Hero | Title, subtitle | `lms_theme.settings` or static template | theme settings / hardcoded | `/admin/appearance/settings/lms_theme` | administrator | N/A | EN | config tags | Update setting → reload |
| Global FAQ Accordion | Question + answer pairs, category grouping | `lms_faq` (field_scope=global) | question, field_answer, field_category, weight | `/workspace/content/faqs` | manager | Not revisionable | EN | `lms_faq_list` | Add FAQ → appears on homepage |
| Course FAQ Accordion | Question + answer pairs | `lms_faq` (field_scope=course, filtered by course) | question, field_answer, weight | `/workspace/content/faqs/{id}/edit` | manager | Not revisionable | EN | `lms_faq_list`, `lms_faq:{id}` | Add course FAQ → appears on course detail |
| Sidebar Popular + Contact CTA | Quick-link list, contact button | Static (prototype only) | — | N/A | N/A | N/A | N/A | N/A | Not yet implemented |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| FAQ section heading ("Frequently Asked Questions") | Template static string | `lms-homepage.html.twig` / `lms-course-detail.html.twig` | Move to `lms_theme.settings` when i18n needed |
| Sidebar popular questions | No `/faq` route; rendered in prototype only | Prototype mockup | Implement when dedicated `/faq` route added |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Global FAQ Accordion (homepage) | DONE | — | `CoursePublicController::homepage()` loads global-scope FAQs |
| Course FAQ Accordion (course detail) | DONE | — | `CoursePublicController::detail()` loads course-scope FAQs |
| FAQ Hero / Search | PLANNED | — | No dedicated `/faq` route; search not implemented |
| Sidebar / Popular Questions | PLANNED | — | Requires dedicated `/faq` route |
| Standalone `/faq` page | PLANNED | — | Route not in codebase; prototype is target-state |
