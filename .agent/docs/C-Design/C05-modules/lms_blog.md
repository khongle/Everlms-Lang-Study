---
module: lms_blog
service: blog
route-groups: [Manage, Public]
status: DONE
documentation-mode: as-built
---

# C05 — Module: LMS Blog

**Directory:** `web/modules/custom/lms_blog/`
**Responsibility:** Dual-track blog/news system — the `lms_article` custom entity backs the `/manage/blog` admin CRUD interface, while the public `/news` frontend routes read `node:blog` (Drupal core node) entities; also provides an AI-assisted article writing widget via optional `lms_ai` integration.

---

## Route Bindings

| Route Group | Prefix | Controller / Form | Paths |
|-------------|--------|-------------------|-------|
| Manage | `/manage/blog` | `_entity_list: lms_article` | `/manage/blog` — list (requires `administer lms_blog`) |
| Manage | `/manage/blog/` | `_entity_form: lms_article.*` | `/manage/blog/add`, `/manage/blog/{lms_article}/edit`, `/manage/blog/{lms_article}/delete` |
| Public | `/news` | `LmsBlogController::index` | `/news` — public blog listing (`_access: TRUE`) |
| Public | `/news/{node}` | `LmsBlogController::view` | `/news/{node}` — single article view; param type: `entity:node` |

**ARCHITECTURAL NOTE — Dual Track:** The frontend controller (`LmsBlogController`) queries `node` storage for `type=blog`, NOT `lms_article` entities. The public blog reads `node:blog` content. The `lms_article` entity is only used for the admin-side management UI at `/manage/blog`. Both tracks coexist simultaneously.

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `lms_article` | ContentEntity | Admin-side article entity; managed at `/manage/blog` |
| `LmsArticleAccessControlHandler` | Access Handler | Fine-grained own/any edit/delete + publish permissions |
| `LmsArticleForm` | Form | AI writer widget (optional, requires lms_ai + openai provider config) |

---

## Internal Structure

```
web/modules/custom/lms_blog/
+-- lms_blog.info.yml                # dependencies: lms_base, lms_ai
+-- lms_blog.routing.yml             # 6 routes: 4 Manage (entity CRUD), 2 Public (/news, /news/{node})
+-- lms_blog.permissions.yml         # 7 permissions
+-- lms_blog.links.menu.yml          # Blog Articles → system.admin_content
+-- lms_blog.module                  # hook_theme: lms_blog_list, lms_blog_single
+-- src/
|   +-- Entity/
|   |   +-- LmsArticle.php           # lms_article ContentEntity; 8 base fields
|   +-- Controller/
|   |   +-- LmsBlogController.php    # index() + view() — queries node:blog, not lms_article
|   +-- Form/
|   |   +-- LmsArticleForm.php       # ContentEntityForm + AI writer widget (AJAX generateAiContent)
|   +-- Access/
|   |   +-- LmsArticleAccessControlHandler.php
|   +-- LmsArticleListBuilder.php    # Columns: ID, Title (link), Status, Created
+-- templates/
|   +-- lms-blog-list.html.twig      # lms_blog_list — public news list at /news
|   +-- lms-blog-single.html.twig    # lms_blog_single — single article at /news/{node}
```

No `.services.yml`, `.install`, or `config/` directories.

---

## Entity: lms_article

| Property | Value |
|----------|-------|
| Type | ContentEntityBase (no EntityOwnerTrait — no uid/author field in baseFieldDefinitions) |
| Base table | `lms_article` |
| Data table | `lms_article_field_data` |
| Translatable | TRUE |
| Admin permission | `administer lms_blog` |
| Entity keys | id, uuid, label=title, langcode, published=status |
| Canonical URL | `/manage/blog/{lms_article}` |
| Collection | `/manage/blog` |

**Base Fields (8):**

| Field | Type | Notes |
|-------|------|-------|
| title | string | required, max_length=255, translatable |
| content | text_long | translatable; main article body |
| thumbnail | file | `blog/thumbnails`; extensions: png jpg jpeg webp |
| seo_title | string | max_length=60 |
| seo_description | string | max_length=160 |
| status | boolean | default=TRUE |
| created / changed | timestamps | — |

**Fields NOT present in lms_article:** No `uid`/author, no category, no tags. The field name is `thumbnail` (NOT `field_thumb`). The public `CoursePublicController::homepage()` reads `node:blog` articles using `field_thumb` — a different field on the node content type.

---

## Controllers

### LmsBlogController

| Method | Route | Data Source | Notes |
|--------|-------|-------------|-------|
| `index()` | `lms_blog.frontend.collection` (`/news`) | `node` storage, `type=blog`, `status=1`, sort DESC, limit 12 | Renders `lms_blog_list` template |
| `view()` | `lms_blog.frontend.canonical` (`/news/{node}`) | `node` entity (param type: entity:node) | Renders `lms_blog_single`; `is_teaser_mode=TRUE` for anonymous |
| `viewTitle()` | — | — | Title callback; returns node label |

The frontend explicitly reads **node:blog** nodes, not lms_article entities. The `{node}` param in `/news/{node}` is upcasted as `entity:node`.

### LmsArticleForm (AI Writer)

The admin form adds an AI content generation widget when:
1. Current user has `administer lms_blog` permission, AND
2. `lms_ai` provider manager service is available.

| Method | Behavior |
|--------|----------|
| `buildForm()` | Adds AI writer widget: topic input, keywords, style selector, generate button |
| `generateAiContent()` | AJAX callback: calls lms_ai openai provider; receives JSON with title, HTML content, seo_title, seo_description; fills form fields via jQuery AJAX commands |
| `save()` | Saves entity; adds status message; redirects to collection |

---

## Permissions

| Machine Name | Title |
|-------------|-------|
| `administer lms_blog` | Administer LMS Blog (restrict access: true) |
| `create lms_article` | Create Blog Articles |
| `edit own lms_article` | Edit own Blog Articles |
| `edit any lms_article` | Edit any Blog Articles |
| `delete own lms_article` | Delete own Blog Articles |
| `delete any lms_article` | Delete any Blog Articles |
| `publish lms_article` | Publish Blog Articles |

### Access Control (LmsArticleAccessControlHandler)

| Operation | Permission |
|-----------|------------|
| view (published) | open to all |
| view (unpublished) | `edit any lms_article` |
| update | `edit any lms_article` OR (`edit own lms_article` AND is owner) |
| delete | `delete any lms_article` OR (`delete own lms_article` AND is owner) |
| create | `create lms_article` OR `administer lms_blog` |
| full override | `administer lms_blog` grants all operations |

---

## Hook Implementations

| Hook | Behavior |
|------|----------|
| `hook_theme()` | Registers `lms_blog_list` (variables: articles) and `lms_blog_single` (variables: article, is_teaser_mode) |

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_base` | Internal | Base utilities |
| `lms_ai` | Internal | Required in info.yml; AI writer in LmsArticleForm checks for provider manager availability at runtime |
| `drupal:node` | Core (implicit) | `LmsBlogController` queries node storage for `type=blog`; frontend routes read node entities |

---

## Known Issues / Debt

- **Architectural split (as-built behavior, not a bug):** The `lms_article` entity and the public `/news` frontend use completely different data sources. `node:blog` powers the public display; `lms_article` powers admin CRUD. Content created in `/manage/blog` is NOT visible at `/news` unless equivalent `node:blog` content exists. This is the intentional current design, but it creates a confusing dual-maintenance burden.
- **Hard dependency on lms_ai:** `lms_ai` is a required dependency in `lms_blog.info.yml`, but the AI writer feature is optional at runtime. This forces lms_ai to be installed even for minimal blog usage. Should be a soft/optional dependency.
- **No author field on lms_article:** `lms_article` has no `uid` field, so article authorship is not tracked.
- **No category or tag fields on lms_article:** Unlike `node:blog`, the `lms_article` entity has no taxonomy references — no filtering or tagging on the admin side.

---

## Cross-Links

- AI generation dependency: `lms_ai` module
- Homepage news section: [lms_course](./lms_course.md) `CoursePublicController::homepage()` reads `node:blog` via `field_thumb`
- Entity detail: [lms_article entity](../C04-entities/01-content-entities.md)
