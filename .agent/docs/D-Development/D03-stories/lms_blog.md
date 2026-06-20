# D03 — Story Spec: LMS Blog

**Story ID:** lms_blog
**Story type:** module
**Title:** Dual-track blog system — lms_article admin entity + node:blog public frontend + AI writer widget
**Primary source:** `../../C-Design/C05-modules/lms_blog.md`
**Owning module(s):** lms_blog
**Service(s):** _(none — no services.yml)_
**Epic:** E-02 (Content Layer)
**Contributes to Feature(s):** F-001 (Course Management — news/blog content), F-014 (AI Features — AI blog writer)
**Design source(s):** `../../C-Design/C05-modules/lms_blog.md`
**Round:** N/A
**Driver:** SA directive — blog/news content module with AI generation
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** pre-2026-04-15 (pre-framework)
**Tasks:** see `../D04-tasks/` filtered by `Story ID: lms_blog`

---

## Session Strategy

**Estimated session count:** 1–2 (medium — dual-track architecture requires careful understanding; AI widget adds complexity)
**Splitting strategy:**
- S1: `LmsArticle` entity + `LmsArticleAccessControlHandler` + `LmsArticleListBuilder` + permissions + hooks
- S2: `LmsBlogController` (frontend reading node:blog) + `LmsArticleForm` (AI writer widget) + templates + routing

---

## Scope (from primary source)

Dual-track blog/news system with a significant architectural split:

**Track A — Admin-side (lms_article entity):**
1. **`lms_article` entity** — ContentEntityBase; 8 base fields (title, content, thumbnail, seo_title, seo_description, status, created, changed); NO uid/author, NO category, NO tags; translatable.
2. **`LmsArticleAccessControlHandler`** — fine-grained own/any edit/delete + publish permissions (7 permissions total).
3. **`LmsArticleForm`** — ContentEntityForm with optional AI Blog Writer widget when `administer lms_blog` + lms_ai provider available; `generateAiContent()` AJAX callback calls lms_ai openai provider.
4. **`LmsArticleListBuilder`** — columns: ID, Title (link to edit-form), Status, Created.
5. **Admin routes (4)** — entity CRUD at `/manage/blog`, `/manage/blog/add`, `/manage/blog/{id}/edit`, `/manage/blog/{id}/delete`.
6. **Menu link** — Blog Articles → `system.admin_content`.

**Track B — Public-side (node:blog):**
7. **`LmsBlogController`** — `index()` queries `node` storage for `type=blog`, status=1, sort DESC, limit 12; `view()` renders node entity; `viewTitle()` returns node label.
8. **Public routes (2)** — `/news` (listing) and `/news/{node}` (single; param type: `entity:node`).
9. **Templates (2)** — `lms-blog-list.html.twig` (lms_blog_list) and `lms-blog-single.html.twig` (lms_blog_single).

**ARCHITECTURAL NOTE:** The frontend (`/news`) reads `node:blog` nodes (Drupal core content type), NOT `lms_article` entities. Content created at `/manage/blog` (lms_article) is NOT visible at `/news` unless equivalent `node:blog` content also exists. Both tracks coexist simultaneously.

---

## Source Documents

| Source | Why loaded |
|--------|-----------|
| `../../C-Design/C05-modules/lms_blog.md` | Owning module design (as-built) |
| `../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md` | Blog/news content AC |
| `../../B-Requirements/B05-acceptance-criteria/AC-14-ai-features.md` | AI blog writer AC |

---

## Requirements

| Requirement | Source | Type |
|-------------|--------|------|
| Public /news listing shows published node:blog articles | AC-01-course-management.md | Acceptance |
| Single article view renders full node:blog content | AC-01-course-management.md | Acceptance |
| Admin can create/edit lms_article entities via /manage/blog | AC-01-course-management.md | Acceptance |
| AI writer widget available to administer lms_blog users when lms_ai enabled | AC-14-ai-features.md | Acceptance |
| 7 fine-grained lms_article permissions enforced | Internal design | Acceptance |

---

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_blog/lms_blog.info.yml` | Create | deps: lms_base, lms_ai | U-056 | pre-2026-04-15 |
| `web/modules/custom/lms_blog/lms_blog.routing.yml` | Create | 6 routes: 4 Manage (entity CRUD) + 2 Public (/news) | U-056 | pre-2026-04-15 |
| `web/modules/custom/lms_blog/lms_blog.permissions.yml` | Create | 7 permissions | U-056 | pre-2026-04-15 |
| `web/modules/custom/lms_blog/lms_blog.links.menu.yml` | Create | Blog Articles → system.admin_content | U-056 | pre-2026-04-15 |
| `web/modules/custom/lms_blog/lms_blog.module` | Create | hook_theme: lms_blog_list, lms_blog_single | U-056 | pre-2026-04-15 |
| `web/modules/custom/lms_blog/src/Entity/LmsArticle.php` | Create | lms_article ContentEntity; 8 base fields | U-056 | pre-2026-04-15 |
| `web/modules/custom/lms_blog/src/Access/LmsArticleAccessControlHandler.php` | Create | Fine-grained own/any + publish access control | U-056 | pre-2026-04-15 |
| `web/modules/custom/lms_blog/src/LmsArticleListBuilder.php` | Create | ID/Title/Status/Created columns | U-056 | pre-2026-04-15 |
| `web/modules/custom/lms_blog/src/Controller/LmsBlogController.php` | Create | index() + view() + viewTitle() — queries node:blog | U-056 | pre-2026-04-15 |
| `web/modules/custom/lms_blog/src/Form/LmsArticleForm.php` | Create | ContentEntityForm + AI writer AJAX widget | U-056 | pre-2026-04-15 |
| `web/modules/custom/lms_blog/templates/lms-blog-list.html.twig` | Create | Public news listing template | U-056 | pre-2026-04-15 |
| `web/modules/custom/lms_blog/templates/lms-blog-single.html.twig` | Create | Single article template (is_teaser_mode for anonymous) | U-056 | pre-2026-04-15 |

---

## Dependencies

| Dependency | Type | Source |
|------------|------|--------|
| `lms_base` | Intra-Epic | Base utilities; declared in info.yml |
| `lms_ai` | Cross-Epic (required in info.yml) | AI writer in LmsArticleForm; hard dep but feature is runtime-optional |
| `drupal:node` | External (Drupal core, implicit) | LmsBlogController queries node storage for type=blog |

---

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|------------|-----------|---------|
| _access: TRUE for public blog routes | /news, /news/{node} | Confirmed in routing.yml |
| entity:node param upcasting | /news/{node} | Confirmed — LmsBlogController::view() receives NodeInterface |
| is_teaser_mode flag for anonymous users | lms-blog-single.html.twig | Confirmed in LmsBlogController::view() |

---

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission |
|--------------------|----------------|-----------------|-----------|-------------------|
| `/news` — blog listing | Published articles (title, thumbnail, excerpt) | `node:blog` entities (Drupal core node) | `/node/add/blog` (core) OR `lms_content_tools` import | administer lms content tools / node author |
| `/news/{node}` — single article | Full article content | `node:blog` entity | `/node/{id}/edit` | node author / administrator |
| `/manage/blog` — admin listing | lms_article entities | `lms_article` entities | `/manage/blog/add`, `/manage/blog/{id}/edit` | administer lms_blog |

**IMPORTANT:** Content in `/manage/blog` (lms_article) is NOT automatically mirrored to `/news` (node:blog). These are two separate data sources.

**Hardcoded content exceptions:** None in templates. AI writer widget produces dynamic content.

---

## Feature Flags Introduced or Read

None. AI writer availability is checked at runtime via service container (`\Drupal::hasService('lms_ai.provider_manager')`), not via a feature flag.

---

## What Was Built (from facts base)

All code is **as-built**. Status confirmed by `modules-content.md`:

- `LmsArticle.php` — 8 base fields verified; NO uid/author field, NO category, NO tags (these are absent vs. node:blog which has field_thumb).
- `LmsArticleAccessControlHandler.php` — full own/any/publish permission matrix verified.
- `LmsBlogController.php` — queries `node` storage (type=blog); `view()` accepts node entity (not lms_article); `is_teaser_mode=TRUE` for anonymous confirmed.
- `LmsArticleForm.php` — AI writer widget (topic, keywords, style, generate button) + AJAX `generateAiContent()` calling lms_ai openai provider verified.
- 2 Twig templates verified.

**Known issues / debt:**
- **Dual-track architectural split (as-built behavior, not a bug):** `/manage/blog` (lms_article) and `/news` (node:blog) use different data sources. Creating a blog article in the admin UI does NOT make it visible on the frontend. This creates a confusing dual-maintenance burden.
- **Hard dependency on lms_ai in info.yml:** Even if the AI writer is not needed, lms_ai must be installed. Should be a soft/optional dependency.
- **No uid/author field on lms_article:** Article authorship is not tracked.
- **No category/tag fields on lms_article:** Unlike node:blog, lms_article has no taxonomy references.
- **lms_content_tools importer writes to node:blog** (the `blogs/` import prefix), not lms_article. The importer bypasses the admin entity entirely for seeded content.

---

## Tasks (draft unit references)

| Task / Draft ID | Description | Status |
|----------------|-------------|--------|
| U-056 | Blog entity (lms_article + LmsBlogController + AI writer widget) | DONE |

Note: The draft U-056 described a simpler single-track model and missed the dual-track split. The as-built architecture is more complex — the public frontend reads node:blog, not lms_article.

---

## Acceptance Link

See [`../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md`](../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md) and [`../../B-Requirements/B05-acceptance-criteria/AC-14-ai-features.md`](../../B-Requirements/B05-acceptance-criteria/AC-14-ai-features.md).

---

## DONE Evidence

| Check | Result |
|-------|--------|
| /news listing renders published node:blog articles | PASS — LmsBlogController::index() verified |
| /news/{node} renders single article | PASS — LmsBlogController::view() verified |
| /manage/blog admin CRUD functional (lms_article) | PASS — entity CRUD routes + LmsArticleForm verified |
| AI writer widget generates content via lms_ai openai provider | PASS — generateAiContent() AJAX callback verified |
| 7 lms_article permissions enforced | PASS — LmsArticleAccessControlHandler verified |
| is_teaser_mode=TRUE for anonymous on single article | PASS — confirmed in controller |

---

## Gate Results (per Story, see A02 §3)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | All routes resolve; entity schema installs; templates render | 1/3 | pre-2026-04-15 |
| G2 Lint | PASS | No violations | 1/3 | pre-2026-04-15 |
| G3 Tests | PASS | Article create/list/view functional on both tracks | 1/3 | pre-2026-04-15 |
| G4 Adversarial | PASS | Dual-track split and hard lms_ai dep noted as architectural debt | 1/3 | pre-2026-04-15 |

---

## Notes

- **Dual-track** is the key fact for any future developer: the `lms_article` entity is admin-side-only; the public frontend reads `node:blog`. Any feature that needs to appear at `/news` must write to `node:blog`, not `lms_article`.
- The `lms_content_tools` importer's `blogs/` prefix writes to `node:blog` (consistent with the public frontend track), not lms_article.
- The homepage news section in `CoursePublicController::homepage()` also reads `node:blog` with `field_thumb` (note: lms_article uses `thumbnail`, not `field_thumb`).
- AI writer depends on `lms_ai` module and specifically the `openai` provider being configured with a valid API key. Key stored in config — see security risk in [`../../A-Project-Information/A03-project-context.md`](../../A-Project-Information/A03-project-context.md).
