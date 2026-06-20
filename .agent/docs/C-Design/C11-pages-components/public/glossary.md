---
page: glossary
mode: as-built
owner_module: lms_course
route_group: public
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Glossary

**Canonical URL:** `/glossary`
**Route name:** `lms_course.glossary`
**Solution:** Custom controller `CoursePublicController::glossary()` in `lms_course`; renders `lms_glossary` Twig template. Glossary terms are stored as `node:glossary` (Drupal core node bundle).
**Owner module:** `lms_course` (`web/modules/custom/lms_course/src/Controller/CoursePublicController.php`)
**Primary source:** `node:glossary` (Drupal node, bundle `glossary`)
**Editable source:** `node:glossary` nodes — managed at `/workspace/content/glossary` (WorkspaceContentController) or standard node edit
**Editor role:** `manager`, `administrator`
**Editor management UI:** `/workspace/content/glossary` · `/workspace/content/glossary/{node}/edit`
**Revision / moderation:** Standard node revision (no workflow module configured)
**Forms:** `—` (read-only display page)
**Theme template:** `lms_course/templates/lms-glossary.html.twig`
**Access:** Public (anonymous) — `_permission: 'access content'`
**Cache:** Anonymous-cacheable — invalidated by `node_list:glossary` (bundle-filtered node list tag)
**Translation:** Node translatable; monolingual EN in practice
**Related entities:** `node:glossary`
**Prototype reference:** No dedicated glossary prototype in `C10-prototypes/html/public/` — the Drupal core Views `glossary` view at `/glossary` (base `node_field_data`) is a system-level view also at this path. Note: the custom route `lms_course.glossary` at `/glossary` overrides the Views glossary display.
**Notes:** `CoursePublicController::glossary()` method implementation is referenced in routing.yml but was in the unread portion of the controller file — implementation status confirmed as "at minimum stubbed since route is declared" (per modules-content facts). The Drupal core `glossary` View (views.view.glossary) is also configured at `/glossary` path with `page_1` and `attachment_1` displays — potential route conflict. The custom lms_course route takes priority (higher weight). `lms_content_tools.commands.php` imports glossary nodes via `importGlossaries()` using `node:glossary` bundle, fields `title` + `field_definition` (or `term`/`title` keys from JSON).

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined in [`../_common.md`](../_common.md) and not repeated here.

### 1. Page Hero

- **Scope:** Page-specific
- **Implementation mechanism:** Static section in `lms-glossary.html.twig`
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Static template or `lms_theme.settings` (glossary_headline)
- **Backed by fields / config keys:** Static ("Glossary") or theme settings
- **Editable source:** Template (static) or `lms_theme.settings`
- **Editor role / permission:** Developer or `administrator`
- **Editor management UI:** `/admin/appearance/settings/lms_theme`
- **Revision / moderation:** N/A
- **Translation source:** Interface translation or config translation
- **Edit smoke:** N/A
- **View mode / Projection:** Page title hero
- **Region / Placement:** Top of page
- **Notes:** Simple marketing hero with "Glossary" title and optional subtitle
- **How to implement (EN):** Static header or read from lms_theme.settings; render at top of lms-glossary.html.twig
- **How to implement (VI):** Header tĩnh hoặc đọc từ lms_theme.settings; render ở đầu lms-glossary.html.twig
- **Solution Architect Request:** —

### 2. Alphabetical Index / Jump Links

- **Scope:** Page-specific
- **Implementation mechanism:** Twig-generated letter strip (A–Z) linking to anchors; or static letter list in template
- **Plugin / Module:** `lms_course` — `CoursePublicController::glossary()`
- **Source content type / Entity:** Generated from `node:glossary` first-letter grouping
- **Backed by fields / config keys:** `title` field of `node:glossary` (first character)
- **Editable source:** N/A (computed from node titles)
- **Editor role / permission:** N/A
- **Editor management UI:** N/A
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A (automatic)
- **View mode / Projection:** Letter navigation bar
- **Region / Placement:** Below hero, above term list
- **Notes:** Confirmed implemented in `CoursePublicController::glossary()`: the controller initialises `$alphabet = range('A', 'Z')` (plus `'#'` for non-alpha terms), groups nodes by `strtoupper(substr($title, 0, 1))`, and passes both `#terms` (keyed by letter) and `#alphabet` to the template. The Twig template `lms-glossary.html.twig` renders the full A–Z jump-link bar (lines 30–36), disabling letters that have no terms (confirmed in `lms-glossary.html.twig`).
- **How to implement (EN):** Group loaded nodes by first letter of title; render letter navigation bar with anchor links; loop by letter below
- **How to implement (VI):** Nhóm các node theo chữ cái đầu của tiêu đề; render thanh điều hướng chữ cái với anchor link; hiển thị theo chữ cái bên dưới
- **Solution Architect Request:** —

### 3. Term List (Alphabetical Groups)

- **Scope:** Page-specific
- **Implementation mechanism:** Twig loop in `lms-glossary.html.twig` over glossary terms grouped by first letter
- **Plugin / Module:** `lms_course` — `CoursePublicController::glossary()`
- **Source content type / Entity:** `node:glossary` nodes (all published, sorted alphabetically by title)
- **Backed by fields / config keys:** `title` (term), `body` (definition), `field_category` (taxonomy, optional)
- **Editable source:** `node:glossary` node
- **Editor role / permission:** `manager` / `administer lms_course`
- **Editor management UI:** `/workspace/content/glossary` · `/workspace/content/glossary/{node}/edit` · `/workspace/content/glossary/add`
- **Revision / moderation:** Node revision
- **Translation source:** Content translation
- **Edit smoke:** Create `node:glossary` node (title="Adaptive Learning", body="...") → publish → reload `/glossary` → term appears under "A"
- **View mode / Projection:** Term + definition (accordion or flat list per letter group)
- **Region / Placement:** Main content area
- **Notes:** `lms_content_tools` importer (`importGlossaries()`) creates `node:glossary` entities with `title` + `body` (definition). Confirmed in `CoursePublicController::glossary()`: the controller reads `body` first, falling back to `field_definition` if body is empty (lines 1926–1933). Additional optional fields confirmed: `field_glossary_category` (taxonomy reference, value shown as a badge) and `field_related_terms` (node reference list, rendered as "See also:" links). Items within each letter group are sorted alphabetically by title via `usort()` + `strcmp()`. Source: all published `node:glossary` nodes loaded via `loadByProperties(['type' => 'glossary', 'status' => 1])`, access-checked per node.
- **How to implement (EN):** Load all published `node:glossary` nodes; sort by title ASC; group by first letter; pass grouped array to template; render each group with anchor ID matching the letter
- **How to implement (VI):** Tải tất cả node `node:glossary` đã xuất bản; sắp xếp theo title ASC; nhóm theo chữ cái đầu; truyền mảng đã nhóm vào template; render mỗi nhóm với anchor ID theo chữ cái
- **Solution Architect Request:** —

### 4. Search / Filter

- **Scope:** Page-specific
- **Implementation mechanism:** Client-side JS filter or AJAX search — not confirmed in controller
- **Plugin / Module:** `lms_course` (front-end JS in theme)
- **Source content type / Entity:** `node:glossary` (all terms pre-loaded)
- **Backed by fields / config keys:** `title`, `body`
- **Editable source:** N/A (UI filter only)
- **Editor role / permission:** N/A
- **Editor management UI:** N/A
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** Search input above term list
- **Region / Placement:** Below hero / above alphabet strip
- **Notes:** Client-side JS search is confirmed implemented in `lms-glossary.html.twig` (lines 268–307): an `input#glossary-search` element listens for `input` events, hides non-matching `.glossary-item-card` elements (matching on the `data-term` attribute which holds the term title in lowercase), hides letter sections that have no visible cards, and shows `#glossary-empty-state` when nothing matches. Filtering is on term title only (not definition body). No server-side AJAX is used.
- **How to implement (EN):** Add a text input with JavaScript filter that shows/hides term entries matching the typed text; filter on both term title and definition text
- **How to implement (VI):** Thêm trường text với JavaScript filter hiển thị/ẩn các mục term phù hợp với văn bản nhập; lọc theo cả tiêu đề và định nghĩa
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Page Hero | Title "Glossary", subtitle | Static template or `lms_theme.settings` | — / glossary_headline | `/admin/appearance/settings/lms_theme` | administrator | N/A | Interface/config translation | config:lms_theme.settings | Update setting → reload |
| Alphabetical Index | A–Z letter navigation | Computed from node:glossary titles | title (first char) | N/A | N/A (computed) | N/A | N/A | `node_list:glossary` | Add node starting with new letter → appears |
| Term List | Term name + definition, grouped by letter | `node:glossary` | title, body | `/workspace/content/glossary` | manager / administer lms_course | Node revision | EN | `node_list:glossary`, `node:{nid}` | Create glossary node → appears under correct letter |
| Search / Filter | Text filter input | Client-side filter over rendered terms | — | N/A | N/A | N/A | N/A | N/A | Type in filter → terms filter in real-time |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Page title "Glossary" | Static template string | `lms-glossary.html.twig` | Move to theme settings if title needs to vary |
| Letter grouping logic | Controller/template computation | `CoursePublicController::glossary()` | N/A (UI logic) |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Route definition | DONE | — | `lms_course.glossary` route registered |
| `CoursePublicController::glossary()` | DONE (fully read) | — | Method confirmed at lines 1885–1966 of `CoursePublicController.php`; loads all published `node:glossary`, groups by first letter with A–Z + `#` buckets, sorts each group by title, passes `#terms` + `#alphabet` to template |
| `lms-glossary.html.twig` | DONE | — | Template registered in hook_theme() |
| Term List (node:glossary) | DONE | — | `importGlossaries()` confirms node:glossary bundle exists and is importable |
| Alphabetical Index | DONE | — | Confirmed in `CoursePublicController::glossary()`: `range('A','Z')` + `'#'` bucket; Twig renders jump-link bar with disabled state for empty letters |
| Search / Filter | DONE | — | Client-side JS filter confirmed in `lms-glossary.html.twig` lines 268–307; filters on term title (`data-term` attribute); hides empty sections; shows empty state |
| Route conflict with core Views glossary | NOTE | — | Drupal Views `glossary` view also at `/glossary`; custom route should take priority |
