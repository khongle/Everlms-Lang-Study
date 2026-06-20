---
page: manage-ratings
mode: as-built
owner_module: lms_rating
route_group: manage
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Manage Ratings (Reviews)

**Canonical URL:** `/workspace/ratings` *(per [`../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_rating.admin_list`
**Solution:** Custom controller (`RatingManagerController::list`) renders `lms_admin_ratings` Twig template (theme hook registered in `lms_course` module, not in `lms_rating`). Action execution via `RatingManagerController::executeAction` at `/workspace/ratings/{id}/{action}`.
**Owner module:** [`lms_rating`](../../C05-modules/lms_rating.md) (entity + controller); theme hook registered in `lms_course`
**Primary source:** `entity:lms_rating`
**Editable source:** `lms_rating` entity — fields: `field_score`, `field_review`, `field_approved`, `field_is_featured`; managed via action routes (approve/feature/delete), not a standard edit form
**Editor role:** Administrator
**Editor management UI:** `/workspace/ratings` (list + inline action links)
**Revision / moderation:** Not revisionable; no workflow.
**Forms:** No dedicated form — actions are GET requests to `executeAction` which toggles boolean fields and redirects back.
**Theme template:** `lms_course/templates/lms-admin-ratings.html.twig` *(theme hook `lms_admin_ratings` defined in `lms_course.module`'s `hook_theme()`, not in `lms_rating`)*
**Access:** Role-gated: `administer lms_course`
**Cache:** Per-user; no anonymous caching. Invalidated on `lms_rating` entity save/delete.
**Translation:** Monolingual EN
**Related entities:** [`lms_rating`](../../C04-entities/01-content-entities.md), [`lms_course`](../../C04-entities/01-content-entities.md)
**Prototype reference:** None — no dedicated C10 prototype exists for this page.
**Notes:** **KNOWN BUG (3 missing routes).** `RatingListBuilder` references routes `lms_rating.toggle_approve` and `lms_rating.toggle_featured` which are NOT defined in `lms_rating.routing.yml`. `RatingController::toggleApprove/toggleFeatured` redirect to `lms_rating.collection` which is also NOT defined in routing.yml (only a menu link entry pointing to a non-existent route). These missing routes cause `RouteNotFoundException` at runtime when the `RatingListBuilder`-generated operation links are rendered. The workspace list at `/workspace/ratings` (via `RatingManagerController`) uses its OWN action links pointing to `lms_rating.admin_action` (which DOES exist) and is functional. The `RatingController` and `RatingListBuilder` are effectively broken. See also: C07-sitemap.md note on missing rating routes.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined ONCE in [`../_common.md`](../_common.md) and NOT repeated here.

### 1. Workspace Sidebar Navigation

- **Scope:** Global chrome (workspace)
- **Implementation mechanism:** Twig include — `lms-admin-sidebar.html.twig`
- **Plugin / Module:** `lms_course`
- **Source content type / Entity:** Static nav links
- **Backed by fields / config keys:** Static template markup
- **Editable source:** Static — developer-owned
- **Editor role / permission:** N/A
- **Editor management UI:** `«static»`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** N/A
- **Region / Placement:** Left sidebar
- **Notes:** Shared across all `/workspace/*` pages

### 2. Reviews List Table

- **Scope:** Page-specific
- **Implementation mechanism:** `lms-admin-ratings.html.twig` theme rendered by `RatingManagerController::list`; pager(15); supports `?search=` and `?status=` query params
- **Plugin / Module:** `lms_rating`
- **Source content type / Entity:** `lms_rating` entity
- **Backed by fields / config keys:** `uid` (student — resolved to name, avatar via `ui-avatars.com` fallback), `field_course` (course name), `field_score` (1–5 stars), `field_review` (excerpt truncated at 50 chars), `field_approved`, `field_is_featured`, `created`
- **Editable source:** `lms_rating` entity via action links (approve/feature/delete)
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/ratings/{id}/{action}` (inline action GET links)
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Click Approve → `field_approved` toggled → row reflects new state on reload
- **View mode / Projection:** Table row with inline action links
- **Region / Placement:** Main content area
- **Notes:** Pager: 15 per page. `RatingManagerController::list` passes `items` array built from entity queries (not via `RatingListBuilder`). Uses `ui-avatars.com` fallback for user avatars — external dependency.

### 3. Inline Action Links (Approve / Feature / Delete)

- **Scope:** Page-specific
- **Implementation mechanism:** GET action links per row → `lms_rating.admin_action` route → `RatingManagerController::executeAction($lms_rating, $action)`
- **Plugin / Module:** `lms_rating`
- **Source content type / Entity:** `lms_rating` (upcasted from route param)
- **Backed by fields / config keys:** `field_approved` (approve/reject action), `field_is_featured` (feature action, auto-approves on feature); entity deleted on `delete` action
- **Editable source:** `lms_rating` entity (mutated by action method)
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/ratings/{id}/{action}` (approve | feature | delete)
- **Revision / moderation:** No
- **Translation source:** N/A
- **Edit smoke:** Click Delete → rating disappears from list; click Approve → `field_approved` changes
- **View mode / Projection:** Action link (GET, no form/confirm)
- **Region / Placement:** Per-row action column
- **Notes:** Delete has no confirmation dialog — immediate DELETE on GET. `feature` action auto-sets `field_approved=TRUE`. Redirects back to `lms_rating.admin_list` after action.

### 4. Search + Filter Bar

- **Scope:** Page-specific
- **Implementation mechanism:** `lms-admin-ratings.html.twig` template GET form; `?search=` and `?status=` (approved/all) query params processed by `RatingManagerController::list`
- **Plugin / Module:** `lms_rating`
- **Source content type / Entity:** N/A (filter UI only)
- **Backed by fields / config keys:** `field_approved` (status filter), `field_review`/course name (search)
- **Editable source:** N/A
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** `/workspace/ratings`
- **Revision / moderation:** N/A
- **Translation source:** N/A
- **Edit smoke:** Filter by status=approved → only approved reviews shown
- **View mode / Projection:** Filter form (GET)
- **Region / Placement:** Above table
- **Notes:** Search is performed client-side in query params; controller re-queries with filters.

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Sidebar Nav | Workspace nav links | Static template | Static | Developer only | N/A | N/A | N/A | None | N/A |
| Reviews Table | Student avatar, name, course name, star score, review excerpt, approved badge, featured badge, date, action links | `entity:lms_rating` | `uid`, `field_course`, `field_score`, `field_review`, `field_approved`, `field_is_featured`, `created` | `/workspace/ratings/{id}/{action}` | `administer lms_course` | No | EN | Per-request | Approve → badge updates |
| Action Links | Approve/Reject/Feature/Delete links | `entity:lms_rating` | `field_approved`, `field_is_featured`, entity delete | `/workspace/ratings/{id}/{action}` | `administer lms_course` | N/A | N/A | Per-request | Delete → row removed |
| Search/Filter | Search input, status dropdown | N/A (filter parameters) | `?search=`, `?status=` | `/workspace/ratings` | `administer lms_course` | N/A | N/A | Per-request | Filter → list narrows |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Star icon rendering (1–5) | CSS/SVG in `lms-admin-ratings.html.twig` | Developer-owned UI chrome | N/A |
| `ui-avatars.com` fallback URL | Hardcoded external dependency in `RatingManagerController` | Code constant | Security review needed — external CDN for admin UI |

## Known Bugs

| Bug | Severity | Location | Impact |
|---|---|---|---|
| `lms_rating.collection` route missing | HIGH | `lms_rating.routing.yml` (absent), `links.menu.yml` (references it) | Menu link to `/admin/content/ratings` is broken — `RouteNotFoundException` |
| `lms_rating.toggle_approve` route missing | HIGH | `lms_rating.routing.yml` (absent), `RatingListBuilder::getOperations()` | `RatingListBuilder` operation links throw `RouteNotFoundException` |
| `lms_rating.toggle_featured` route missing | HIGH | `lms_rating.routing.yml` (absent), `RatingListBuilder::getOperations()` | Same as above |
| `RatingController` redirects to missing route | HIGH | `RatingController::toggleApprove/toggleFeatured` | Both methods redirect to `lms_rating.collection` (missing) — `RouteNotFoundException` |
| `lms_admin_ratings` theme not in `lms_rating.module` | MEDIUM | No `lms_rating.module` file | Theme hook must be defined elsewhere (confirmed in `lms_course.module`); if registry not rebuilt, renders empty |

*Note: The `/workspace/ratings` surface (via `RatingManagerController` + `lms_rating.admin_list` route) is functional. Only the `RatingListBuilder`/`RatingController` combo (used for entity-list-builder-generated pages) is broken.*

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Reviews List Table (`/workspace/ratings`) | DONE | — | `RatingManagerController::list` is functional |
| Inline action links (`/workspace/ratings/{id}/{action}`) | DONE | — | `executeAction` works for approve/feature/delete |
| Search/Filter | DONE | — | GET params processed by `RatingManagerController::list` |
| `RatingListBuilder` operation links | BUG | — | References 2 missing routes — `RouteNotFoundException` at render |
| `RatingController` (toggle methods) | BUG | — | Redirects to missing `lms_rating.collection` route |
| Menu link (`lms_rating.collection`) | BUG | — | Points to non-existent route in routing.yml |
| `field_is_featured` | DONE | — | Added via `lms_rating_update_11001`; functional in `executeAction` |
