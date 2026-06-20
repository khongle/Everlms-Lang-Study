---
page: news-blog
mode: as-built
owner_module: lms_blog
route_group: public
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: News / Blog

**Canonical URL:** `/news` (listing) · `/news/{node}` (single article)
**Route name:** `lms_blog.frontend.collection` · `lms_blog.frontend.canonical`
**Solution:** Custom controller (`LmsBlogController`) reads `node:blog` entities; renders Twig templates `lms-blog-list.html.twig` / `lms-blog-single.html.twig`. The admin-side `lms_article` entity backs `/manage/blog` CRUD only — **not** the frontend.
**Owner module:** `lms_blog` (`web/modules/custom/lms_blog/`)
**Primary source:** `node:blog` (Drupal core node, bundle `blog`)
**Editable source:** `node:blog` nodes — field_title, body, field_thumb, field_tags (vocab `blog_tags`); managed at `/manage/blog` via `lms_article` form (AI-writer enabled) OR via `/workspace/content/blog` (WorkspaceContentController)
**Editor role:** `manager`, `administrator`
**Editor management UI:** `/manage/blog` (lms_article list) · `/workspace/content/blog` (WorkspaceContentController)
**Revision / moderation:** Standard node revision; no workflow module configured
**Forms:** `LmsArticleForm` (create/edit at `/manage/blog`) — includes optional AI Blog Writer widget (requires `administer lms_blog` + lms_ai available)
**Theme template:** `lms_blog/templates/lms-blog-list.html.twig` (listing) · `lms_blog/templates/lms-blog-single.html.twig` (single)
**Access:** Public (anonymous) — `_access: TRUE`
**Cache:** Anonymous-cacheable — invalidated by `node_list` tag (listing) / `node:{nid}` (single)
**Translation:** Monolingual EN (node translatable but no active translation workflow)
**Related entities:** `node:blog`, `lms_article` (admin-side only)
**Prototype reference:** [`../../C10-prototypes/html/public/blog.html`](../../C10-prototypes/html/public/blog.html)
**Notes:** Dual-track architecture — `LmsBlogController::index()` queries `node` storage (`type=blog, status=1`, sort DESC, limit 12). The `lms_article` entity used at `/manage/blog` is the editorial interface; published `node:blog` entities are the display source. These are **not the same records** — editors create `lms_article` entities; a separate import/sync step or manual `node:blog` creation is required to populate the frontend. `LmsBlogController::view()` renders the `node` entity directly as `lms_blog_single` with `is_teaser_mode=TRUE` for anonymous users.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined in [`../_common.md`](../_common.md) and not repeated here.

### 1. Hero / Featured Article Banner

- **Scope:** Page-specific
- **Implementation mechanism:** Twig include in `lms-blog-list.html.twig`; first node in result set promoted as featured
- **Plugin / Module:** `lms_blog` — `LmsBlogController::index()`
- **Source content type / Entity:** `node:blog` (first result, sorted by created DESC)
- **Backed by fields / config keys:** `title`, `body`, `field_thumb` (image), `field_tags` (taxonomy_term:blog_tags)
- **Editable source:** `node:blog` node — editable at node edit form
- **Editor role / permission:** `manager` / `administer lms_course`; `administrator`
- **Editor management UI:** `/workspace/content/blog/{node}/edit`
- **Revision / moderation:** Node revision; no workflow
- **Translation source:** Content translation (node entity)
- **Edit smoke:** Edit node:blog title → save → reload `/news` → verify hero headline changes
- **View mode / Projection:** Featured card (full-width hero layout, teaser)
- **Region / Placement:** Top of content area, full-width
- **Notes:** Prototype shows hero with badge, headline, author, date, read-time, CTA button. `is_teaser_mode` flag passed to single template; anonymous users see teaser
- **How to implement (EN):** Query node storage for `type=blog, status=1`, take first result, pass as `featured_article` to template. Template renders title, field_thumb image, field_tags badges, author name, created date
- **How to implement (VI):** Truy vấn node storage với `type=blog, status=1`, lấy kết quả đầu tiên, truyền vào template dưới dạng `featured_article`. Template render tiêu đề, ảnh thumbnail, tag, tên tác giả, ngày tạo
- **Solution Architect Request:** —

### 2. Article Grid (Latest Articles)

- **Scope:** Page-specific
- **Implementation mechanism:** Twig loop in `lms-blog-list.html.twig` over `articles` variable (max 12)
- **Plugin / Module:** `lms_blog` — `LmsBlogController::index()`
- **Source content type / Entity:** `node:blog` (up to 12 nodes, sorted by created DESC)
- **Backed by fields / config keys:** `title`, `body` (teaser), `field_thumb`, `field_tags`, `created`, `uid`
- **Editable source:** `node:blog` nodes
- **Editor role / permission:** `manager` / `administer lms_course`
- **Editor management UI:** `/workspace/content/blog` · `/manage/blog`
- **Revision / moderation:** Node revision
- **Translation source:** Content translation
- **Edit smoke:** Create `node:blog` → publish → reload `/news` → article appears in grid
- **View mode / Projection:** Card (teaser) — 2-column grid per prototype
- **Region / Placement:** Main content area below hero
- **Notes:** Prototype shows tag filter chips (All / Tutorials / Career / News) — filter logic not confirmed in controller; may be front-end JS only or not yet implemented
- **How to implement (EN):** Pass `articles` array from controller; loop in template; render card per article with thumbnail, tags, title, read-time estimate, author
- **How to implement (VI):** Truyền mảng `articles` từ controller; vòng lặp trong template; render thẻ bài viết với ảnh, tag, tiêu đề, thời gian đọc, tác giả
- **Solution Architect Request:** —

### 3. Sidebar — Popular / Category (listing page)

- **Scope:** Page-specific
- **Implementation mechanism:** Twig aside block in `lms-blog-list.html.twig`
- **Plugin / Module:** `lms_blog`
- **Source content type / Entity:** Likely static or from `blog_tags` taxonomy
- **Backed by fields / config keys:** `blog_tags` vocabulary terms (taxonomy)
- **Editable source:** `taxonomy_term:blog_tags` — editable at `/admin/structure/taxonomy/manage/blog_tags/overview`
- **Editor role / permission:** `administrator`
- **Editor management UI:** `/admin/structure/taxonomy/manage/blog_tags/overview`
- **Revision / moderation:** N/A (taxonomy)
- **Translation source:** N/A
- **Edit smoke:** Add blog_tags term → reload `/news` sidebar → term appears
- **View mode / Projection:** Tag list / chip strip
- **Region / Placement:** Right sidebar (per prototype)
- **Notes:** Prototype shows "Popular Questions" and "Still need help?" panels in FAQ sidebar. Blog sidebar likely shows popular tags or recent posts. Implementation detail not confirmed in controller code — may be static placeholder in template
- **How to implement (EN):** Load blog_tags terms; render as sidebar chip list linking to tag-filtered view (if implemented)
- **How to implement (VI):** Tải các term từ taxonomy blog_tags; render dưới dạng sidebar chip list
- **Solution Architect Request:** —

### 4. Single Article Body

- **Scope:** Page-specific (single article page only)
- **Implementation mechanism:** `lms-blog-single.html.twig` — renders node entity fields
- **Plugin / Module:** `lms_blog` — `LmsBlogController::view()`
- **Source content type / Entity:** `node:blog` (single node by NID, from route param `{node}`)
- **Backed by fields / config keys:** `title`, `body` (full HTML), `field_thumb`, `field_tags`, `created`, `uid` (author), `changed`
- **Editable source:** `node:blog` node
- **Editor role / permission:** `manager` / `administer lms_course`
- **Editor management UI:** `/workspace/content/blog/{node}/edit`
- **Revision / moderation:** Node revision
- **Translation source:** Content translation
- **Edit smoke:** Edit node:blog body → save → reload `/news/{node}` → body updated
- **View mode / Projection:** Full / default
- **Region / Placement:** Main content area
- **Notes:** `is_teaser_mode=TRUE` for anonymous — template may hide certain fields or show truncated body for guests
- **How to implement (EN):** Route param `{node}` upcasts to node entity; controller passes to template. Template renders full body, author info, tags, published date
- **How to implement (VI):** Tham số route `{node}` được upcast thành node entity; controller truyền vào template. Template render body đầy đủ, thông tin tác giả, tags, ngày đăng
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Hero Banner | Headline, thumbnail, tags, author, date, CTA | `node:blog` (first result) | title, field_thumb, field_tags, uid, created | `/workspace/content/blog/{node}/edit` | manager / `administer lms_course` | Node revision | EN | `node_list`, `node:{nid}` | Edit title → reload `/news` |
| Article Grid | Article cards (title, thumb, tags, teaser, author) | `node:blog` (up to 12) | title, body, field_thumb, field_tags, created, uid | `/workspace/content/blog` | manager / `administer lms_course` | Node revision | EN | `node_list` | Create node:blog → appears in grid |
| Sidebar | Tag chips / popular tags | `taxonomy_term:blog_tags` | name | `/admin/structure/taxonomy/manage/blog_tags/overview` | administrator | N/A | EN | `taxonomy_term_list` | Add tag → appears in sidebar |
| Single Article Body | Full body, title, thumbnail, author, date, tags | `node:blog` | title, body, field_thumb, field_tags, uid, created | `/workspace/content/blog/{node}/edit` | manager / `administer lms_course` | Node revision | EN | `node:{nid}` | Edit body → reload `/news/{node}` |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| `is_teaser_mode` logic (teaser vs full body for anon) | Template-level UX decision, not content | `LmsBlogController::view()` | Revisit if paywall/teaser gates are required |
| Limit of 12 articles | Controller hardcode | `LmsBlogController::index()` | Add pagination if volume grows |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Hero Banner | DONE | — | Rendered via lms-blog-list.html.twig |
| Article Grid | DONE | — | Rendered via lms-blog-list.html.twig; tag filter chips may be front-end JS only |
| Sidebar | DONE | — | Template-level; tag source not confirmed in controller |
| Single Article Body | DONE | — | Rendered via lms-blog-single.html.twig |
