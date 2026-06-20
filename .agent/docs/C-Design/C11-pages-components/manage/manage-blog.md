---
page: manage-blog
mode: as-built
owner_module: lms_blog
route_group: manage
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Manage Blog Articles (List, Create, Edit, Delete)

Blog article management in the workspace. Backed by the `lms_article` custom entity defined in `lms_blog`. Routes are provided directly by `lms_blog.routing.yml` using Drupal core `_entity_list` and `_entity_form` handlers — no custom controller is involved for these manage pages.

**Critical architectural note (dual-track blog):** The public blog frontend at `/news` and `/news/{node}` reads `node:blog` core node entities, NOT `lms_article`. The `lms_article` entity exists solely to back the `/manage/blog` admin CRUD. Both tracks coexist; `LmsBlogController::index()` queries node storage for `type=blog`. This is a known architectural split — see [`../../C05-modules/lms_blog.md`](../../C05-modules/lms_blog.md) and cross-reference [`../../C07-sitemap.md`](../../C07-sitemap.md).

**AI integration:** `LmsArticleForm` (the create/edit form) includes an optional AI Blog Writer widget powered by `lms_ai` (OpenAI provider). The widget appears for users with `administer lms_blog` permission when the `lms_ai` provider manager is available.

---

## Page A: Blog List — `/manage/blog`

**Canonical URL:** `/manage/blog` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `entity.lms_article.collection`
**Solution:** Drupal core `_entity_list: lms_article` handler using `LmsArticleListBuilder`. Renders the standard Drupal entity list builder table within the admin theme (Claro) or lms_theme, depending on the `_admin_route` flag (not set on this route — uses lms_theme).
**Owner module:** `lms_blog` — `src/LmsArticleListBuilder.php`
**Primary source:** `lms_article` entity storage
**Editable source:** `lms_article` entities; edit at `/manage/blog/{id}/edit`
**Editor role:** `administer lms_blog`
**Editor management UI:** `/manage/blog/{lms_article}/edit` (row action); `/manage/blog/add` (add button)
**Theme template:** Default Drupal entity list table (`entity-list` core pattern); no custom Twig template in `lms_blog`
**Access:** Role-gated: `administer lms_blog`
**Cache:** No-cache (entity list builder result; no explicit cache configured)
**Related entities:** [`lms_article`](../../C04-entities/)
**Notes:** The list builder columns are: ID, Title (link to edit-form), Status (boolean), Created (date). This is a minimal Drupal-default list — not a styled workspace page like `/workspace/courses`. The `/manage/blog` route is also linked from the Drupal admin content menu (`system.admin_content`, weight=10) via `lms_blog.links.menu.yml`.

---

## Page B: Add Blog Article — `/manage/blog/add`

**Canonical URL:** `/manage/blog/add` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `entity.lms_article.add_form`
**Solution:** Drupal core `_entity_form: lms_article.add` handler using `LmsArticleForm`. Form includes optional AI Blog Writer widget.
**Owner module:** `lms_blog` — `src/Form/LmsArticleForm.php`
**Primary source:** New `lms_article` entity (unsaved)
**Editable source:** `lms_article` fields — `title`, `content`, `thumbnail`, `seo_title`, `seo_description`, `status`
**Editor role:** `administer lms_blog` or `create lms_article`
**Editor management UI:** `/manage/blog/add` (this page)
**Theme template:** Core entity form rendering; `LmsArticleForm` adds AI widget inline via `buildForm()` override
**Access:** `_entity_create_access: lms_article`
**Cache:** No-cache
**Related entities:** [`lms_article`](../../C04-entities/)

---

## Page C: Edit Blog Article — `/manage/blog/{lms_article}/edit`

**Canonical URL:** `/manage/blog/{lms_article}/edit` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `entity.lms_article.edit_form`
**Solution:** Drupal core `_entity_form: lms_article.edit` handler using `LmsArticleForm`. Same form class as add; AI widget available.
**Owner module:** `lms_blog` — `src/Form/LmsArticleForm.php`
**Primary source:** `lms_article` entity (existing)
**Editable source:** All `lms_article` base fields
**Editor role:** `administer lms_blog`, `edit any lms_article`, or (`edit own lms_article` AND is owner)
**Editor management UI:** `/manage/blog/{lms_article}/edit` (this page)
**Theme template:** Core entity form rendering with AI widget overlay
**Access:** `_entity_access: lms_article.update`
**Cache:** No-cache
**Related entities:** [`lms_article`](../../C04-entities/)

---

## Page D: Delete Blog Article — `/manage/blog/{lms_article}/delete`

**Canonical URL:** `/manage/blog/{lms_article}/delete` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `entity.lms_article.delete_form`
**Solution:** Drupal core `ContentEntityDeleteForm` — standard confirmation form.
**Owner module:** `lms_blog`
**Primary source:** `lms_article` entity (to be deleted)
**Editable source:** N/A (delete operation)
**Editor role:** `administer lms_blog`, `delete any lms_article`, or (`delete own lms_article` AND is owner)
**Editor management UI:** `/manage/blog/{lms_article}/delete` (confirmation page)
**Theme template:** Core delete confirmation form
**Access:** `_entity_access: lms_article.delete`
**Cache:** No-cache

---

## Components (visual order, top-to-bottom)

> Global chrome is defined ONCE in [`../_common.md`](../_common.md) and NOT repeated here.

### 1. Article List Table (Page A)

- **Scope:** Page A (blog list)
- **Implementation mechanism:** Drupal `EntityListBuilder` → `LmsArticleListBuilder`; rendered by core entity list pattern
- **Plugin / Module:** `lms_blog`
- **Source content type / Entity:** `lms_article` entity storage
- **Backed by fields / config keys:** `lms_article.id`, `lms_article.title`, `lms_article.status`, `lms_article.created`
- **Editable source:** `lms_article` entities; row actions link to `/manage/blog/{id}/edit` and `/manage/blog/{id}/delete`
- **Editor role / permission:** `administer lms_blog`
- **Editor management UI:** `/manage/blog/{lms_article}/edit`
- **Revision / moderation:** No
- **Translation source:** Content translation (title translatable)
- **Edit smoke:** Create article → verify it appears in list with correct title and status
- **View mode / Projection:** Entity list table (4 columns: ID, Title, Status, Created)
- **Region / Placement:** Main page content
- **Notes:** This is a plain Drupal entity list — no workspace shell, no sidebar, no AJAX, no bulk actions, no search/filter. Significantly less styled than the `/workspace/courses` list. The list is accessible from both `/manage/blog` and via the Drupal admin content menu.
- **How to implement (EN):** Define `LmsArticleListBuilder` extending `EntityListBuilder`; override `buildHeader()` to declare columns (ID, Title, Status, Created); override `buildRow()` to populate values; declare it as `list_builder: LmsArticleListBuilder` in entity annotation handlers.
- **How to implement (VI):** Định nghĩa `LmsArticleListBuilder` kế thừa `EntityListBuilder`; khai báo các cột tiêu đề và dòng dữ liệu; đăng ký trong annotation handler `list_builder`.
- **Solution Architect Request:** —

### 2. AI Blog Writer Widget (Pages B and C)

- **Scope:** Page B (add) and Page C (edit) — conditional
- **Implementation mechanism:** `LmsArticleForm::buildForm()` injects the AI widget inline into the Drupal form when: (a) current user has `administer lms_blog`, AND (b) `lms_ai` plugin manager service is available. Widget uses an AJAX callback `generateAiContent()`.
- **Plugin / Module:** `lms_blog` (widget UI) + `lms_ai` (content generation service)
- **Source content type / Entity:** External — OpenAI API (via `lms_ai` openai provider)
- **Backed by fields / config keys:** Widget inputs: topic (string), keywords (string), style (select); Output fills: `lms_article.title`, `lms_article.content`, `lms_article.seo_title`, `lms_article.seo_description`
- **Editable source:** AI-generated values fill entity fields; user can edit before saving
- **Editor role / permission:** `administer lms_blog` (widget visible only to this role)
- **Editor management UI:** Inline in article create/edit form
- **Revision / moderation:** No
- **Translation source:** N/A (AI generates content in the configured language)
- **Edit smoke:** Enter topic + keywords → click Generate → verify title, content, SEO fields are populated via AJAX
- **View mode / Projection:** Collapsible sidebar panel or inline form section (above/beside main fields)
- **Region / Placement:** Above or alongside article form fields
- **Notes:** `generateAiContent()` is an AJAX Drupal Form API callback. It calls the OpenAI provider via `lms_ai`'s plugin manager with prompt parameters (title, HTML content, seo_title, seo_description). Returns `AjaxResponse` with `InvokeCommand` jQuery calls to fill form fields. **Depends on `lms_ai` being enabled and an OpenAI API key configured** (key stored in plaintext config YAML — «secret in config — see security risk»). If `lms_ai` is unavailable, the widget is silently omitted.
- **How to implement (EN):** In `buildForm()`, check `\Drupal::hasService('plugin.manager.lms_ai_provider')` and user permission. If true, add form elements for topic, keywords, style, and a "Generate" AJAX button. In `generateAiContent()`, call the lms_ai openai provider; parse the JSON response; return `AjaxResponse` with jQuery commands to fill form fields.
- **How to implement (VI):** Trong `buildForm()`, kiểm tra service `lms_ai` và quyền người dùng; nếu có, thêm widget AI gồm ô nhập topic, keywords, nút Generate AJAX. Callback tạo nội dung từ OpenAI và điền vào các trường form qua AJAX response.
- **Solution Architect Request:** —

### 3. Article Create/Edit Form (Pages B and C)

- **Scope:** Pages B and C
- **Implementation mechanism:** Drupal `ContentEntityForm` subclass (`LmsArticleForm`); core form rendering
- **Plugin / Module:** `lms_blog`
- **Source content type / Entity:** `lms_article` entity (new or existing)
- **Backed by fields / config keys:**
  - `lms_article.title` (string, max 255, required) — article headline
  - `lms_article.content` (text_long) — full rich text body; rendered with available text format
  - `lms_article.thumbnail` (file: png/jpg/jpeg/webp) — article cover image; directory `blog/thumbnails`
  - `lms_article.seo_title` (string, max 60) — meta title; AI-populated when widget used
  - `lms_article.seo_description` (string, max 160) — meta description; AI-populated
  - `lms_article.status` (boolean) — published/draft state
- **Editable source:** `lms_article` entity fields as listed
- **Editor role / permission:** `administer lms_blog` (full access); `create lms_article` (add only); `edit any lms_article` or `edit own lms_article` (edit)
- **Editor management UI:** `/manage/blog/add` (create) or `/manage/blog/{id}/edit` (edit)
- **Revision / moderation:** No (lms_article is not revisionable)
- **Translation source:** Content translation (title translatable; content translatable)
- **Edit smoke (create):** Fill title + body → save → verify article appears in list and at its canonical URL `/manage/blog/{id}`
- **Edit smoke (edit):** Edit body text → save → verify updated on public `/news` listing (if article has a matching `node:blog` counterpart — see dual-track note)
- **View mode / Projection:** Standard Drupal entity form
- **Region / Placement:** Main page content
- **Notes:**
  - `lms_article` has NO `uid` (author) field in `baseFieldDefinitions()`. Author tracking is not natively supported.
  - `lms_article` has NO `field_category` or `field_tags` fields — no taxonomy classification available at entity level.
  - `thumbnail` field name differs from `node:blog` which uses `field_thumb` — these are separate entities.
  - `LmsArticleForm::save()` adds a status message and redirects to the collection (`/manage/blog`).
  - The form does NOT include `field_category` or author assignment — both are gaps relative to a full blogging system.
- **How to implement (EN):** `LmsArticleForm` extends `ContentEntityForm`; override `buildForm()` to inject the AI widget conditionally; `save()` redirects to collection. Register as `form: LmsArticleForm (default, add, edit)` in entity annotation handlers.
- **How to implement (VI):** `LmsArticleForm` kế thừa `ContentEntityForm`; nhúng widget AI có điều kiện; `save()` chuyển về danh sách. Đăng ký trong handler `form` của annotation entity.
- **Solution Architect Request:** —

### 4. Delete Confirmation Form (Page D)

- **Scope:** Page D (delete)
- **Implementation mechanism:** Drupal core `ContentEntityDeleteForm`
- **Plugin / Module:** `lms_blog`
- **Source content type / Entity:** `lms_article` entity (to be deleted)
- **Backed by fields / config keys:** `lms_article.title` (displayed in confirmation message)
- **Editable source:** N/A (destructive operation)
- **Editor role / permission:** `administer lms_blog`, `delete any lms_article`, `delete own lms_article`
- **Editor management UI:** `/manage/blog/{lms_article}/delete`
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Delete article → verify removed from list
- **View mode / Projection:** Standard Drupal confirmation dialog form
- **Region / Placement:** Main page content
- **Notes:** Standard core delete form — no custom template needed. After deletion, redirects to `/manage/blog` collection.
- **How to implement (EN):** Declare `ContentEntityDeleteForm` (delete) in entity annotation form handlers. The `delete-form` link (`/manage/blog/{lms_article}/delete`) is declared in entity annotation `links`.
- **How to implement (VI):** Khai báo `ContentEntityDeleteForm` trong handler form của entity annotation; link xóa đăng ký trong `links` annotation.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Article List Table | ID, title (link), status, created date, row actions | `lms_article` entity | id, title, status, created | `/manage/blog/{id}/edit` | `administer lms_blog` | No | Yes | No-cache | Create article → verify in list |
| AI Writer Widget | Topic input, keywords, style selector, Generate button, generated output preview | External (OpenAI via lms_ai) | N/A (fills article fields) | Inline in form | `administer lms_blog` | No | No | No-cache (AJAX) | Enter topic → Generate → verify fields filled |
| Article Form | Title, body (rich text), thumbnail, SEO title, SEO description, status | `lms_article` entity | title, content, thumbnail, seo_title, seo_description, status | `/manage/blog/add` or `/{id}/edit` | `administer lms_blog` / `create lms_article` / `edit * lms_article` | No | Yes (title, content) | No-cache | Edit body → save → verify at canonical URL |
| Delete Confirmation | "Are you sure you want to delete {title}?" | `lms_article` entity | title | `/manage/blog/{id}/delete` | `administer lms_blog` / `delete * lms_article` | No | No | No-cache | Delete → verify removed from list |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| List builder column labels (ID, Title, Status, Created) | Defined in `LmsArticleListBuilder::buildHeader()` PHP code | Developer convention | Update code if column set changes |
| No author/uid field | `lms_article` base fields do not include uid/author — by entity design | Known gap in entity design | Add `uid` field and `EntityOwnerTrait` if author tracking is needed |
| No category/tag taxonomy | `lms_article` has no category or tag fields | Known gap relative to full blog feature | Add taxonomy reference fields if filtering/categorization needed |
| AI provider dependency | Widget silently hidden if `lms_ai` not enabled | Architecture: `lms_blog` depends on `lms_ai` but degrades gracefully | Document availability in deployment runbook |

## Hardcoded Content Exceptions — Dual-Track Blog Architecture

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Public `/news` reads `node:blog` not `lms_article` | Architectural split — lms_article is admin-only; node:blog is the frontend entity | Existing code pattern in `LmsBlogController::index()` | Reconcile if a unified blog entity is desired |
| `node:blog` field_thumb vs `lms_article` thumbnail | Two different entities with different field names for the same concept | Dual-track design | Unify if the blog entities are merged |

## Implementation status

| Component | Status | Notes |
|---|---|---|
| Blog list route | DONE | `entity.lms_article.collection` → `_entity_list: lms_article` |
| LmsArticleListBuilder | DONE | 4 columns; ID, Title, Status, Created |
| Blog add route + form | DONE | `entity.lms_article.add_form` → `LmsArticleForm` |
| Blog edit route + form | DONE | `entity.lms_article.edit_form` → `LmsArticleForm` |
| Blog delete route + form | DONE | `entity.lms_article.delete_form` → `ContentEntityDeleteForm` |
| AI Blog Writer widget | DONE (conditional) | Available to `administer lms_blog` users when `lms_ai` OpenAI provider is configured; silently omitted otherwise |
| AI content generation AJAX | DONE | `generateAiContent()` AJAX callback fills title, content, seo_title, seo_description |
| Author field (uid) | NOT_IMPLEMENTED | No uid field on lms_article |
| Category/tag taxonomy | NOT_IMPLEMENTED | No category or tag fields on lms_article |
| Workspace shell (sidebar) wrapping | NOT_IMPLEMENTED | `/manage/blog` uses standard Drupal entity list builder, not workspace shell template |
| Dual-track reconciliation | NOT_IMPLEMENTED | Public /news uses node:blog; /manage/blog uses lms_article — intentional split, no unified entity |
