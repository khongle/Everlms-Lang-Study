---
module: lms_rating
service: rating
route-groups: [Manage]
status: IN_PROGRESS
---

# C05 — Module: LMS Rating

**Directory:** `web/modules/custom/lms_rating/`
**Responsibility:** Allows learners to submit 1–5 star ratings and text reviews for courses, and provides admin moderation (approve, feature, delete) via a workspace controller — but three runtime routes referenced by the list builder and legacy controller are missing, causing `RouteNotFoundException` errors.

> **Documentation mode:** as-built — partially implemented. Core rating submission and admin management controller work; `RatingController` (toggle approve/featured) and `RatingListBuilder` operations are broken due to 3 missing routes.

---

## Route Bindings

| Route Group | Prefix | Controller | Paths |
|-------------|--------|------------|-------|
| Manage | `/workspace` | `RatingManagerController` | `/workspace/ratings` [GET], `/workspace/ratings/{lms_rating}/{action}` [POST] |

No public-facing routes; the rating form is surfaced via the `lms_rating_block` Block plugin embedded on course detail pages.

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `RatingManagerController` | Controller | Admin list (working) + multi-action endpoint (working) |
| `RatingController` | Controller | Toggle approve / toggle featured — broken (redirects to missing route `lms_rating.collection`) |
| `RatingListBuilder` | List Builder | Entity list table — broken (operations reference missing routes `lms_rating.toggle_approve`, `lms_rating.toggle_featured`) |
| `RatingForm` | Form | Student-facing rating submission form (working) |
| `RatingBlock` | Block Plugin | Embeds `RatingForm`; plugin id `lms_rating_block` |
| `lms_rating` | Entity | Course rating record |

---

## Internal Structure

```
web/modules/custom/lms_rating/
+-- lms_rating.info.yml             # package: EverLMS; deps: lms_base, lms_course
+-- lms_rating.routing.yml          # 2 routes (admin_list, admin_action); 3 referenced routes MISSING
+-- lms_rating.links.menu.yml       # points to lms_rating.collection — MISSING ROUTE
+-- lms_rating.install              # lms_rating_update_11001() — adds field_is_featured
+-- src/
|   +-- Controller/
|   |   +-- RatingController.php            # toggleApprove(), toggleFeatured() — broken (missing route redirects)
|   |   +-- RatingManagerController.php     # list(), executeAction() — working
|   +-- Entity/
|   |   +-- Rating.php                      # lms_rating content entity
|   +-- Form/
|   |   +-- RatingForm.php                  # student submission form — working
|   +-- Plugin/
|   |   +-- Block/
|   |   |   +-- RatingBlock.php             # lms_rating_block — working
|   +-- RatingListBuilder.php               # broken (operations reference missing routes)
```

No `lms_rating.module` file (no hooks registered). No `lms_rating.services.yml`. No templates directory.

---

## Entity: `lms_rating` — Course Rating

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| id | auto | — | PK |
| uuid | auto | — | |
| uid | entity_reference (user) | — | via `EntityOwnerTrait` |
| field_course | entity_reference (lms_course) | TRUE | |
| field_score | integer | — | 1–5 stars; default 5 |
| field_review | text_long | — | |
| field_approved | boolean | — | default TRUE; display configurable |
| field_is_featured | boolean | — | default FALSE; added by `lms_rating_update_11001` |
| created | created | — | |

Table: `lms_rating`. Admin permission: `administer lms_course`. Access handler: core default `EntityAccessControlHandler`. List builder: `RatingListBuilder`.

---

## Controllers

### `RatingManagerController` — as-built (working)

Dependencies: `entity_type.manager`, `current_user`.

| Method | Route | Description |
|--------|-------|-------------|
| `list(Request)` | `lms_rating.admin_list` | Loads all ratings DESC with pager (15); builds items array with student name, avatar (ui-avatars.com fallback), course name, score, review excerpt, approved flag, featured flag; renders `#theme: lms_admin_ratings`. Note: `lms_admin_ratings` theme hook is NOT registered in this module (no `.module` file); must be registered in lms_workspace or the active theme — renders as empty/fallback if absent. |
| `executeAction(Request, $lms_rating, $action)` | `lms_rating.admin_action` | Actions: `approve` (toggle `field_approved`), `feature` (toggle `field_is_featured`; auto-approves when featuring), `delete`; redirects to `lms_rating.admin_list`. This is the working moderation path. |

### `RatingController` — as-built, BROKEN

| Method | Route | Description |
|--------|-------|-------------|
| `toggleApprove(Rating)` | (no route defined) | Toggles `field_approved`; redirects to `lms_rating.collection` — MISSING ROUTE; will throw `RouteNotFoundException`. |
| `toggleFeatured(Rating)` | (no route defined) | Toggles `field_is_featured`; guards against missing field; redirects to `lms_rating.collection` — MISSING ROUTE; will throw `RouteNotFoundException`. |

---

## Forms

### `RatingForm` — as-built (working)

Extends `FormBase`. Route parameter: `$course_id = NULL` (resolved from argument or current route param).

- Checks for existing rating to prevent duplicates (one rating per user per course).
- Renders: score select (1–5) + text review textarea.
- On submit: creates `lms_rating` entity with `field_approved=FALSE` and `field_is_featured=FALSE`; shows "pending moderation" message.

---

## Plugins

### `RatingBlock` (`src/Plugin/Block/RatingBlock.php`)

| Attribute | Value |
|-----------|-------|
| Plugin ID | `lms_rating_block` |
| Admin label | LMS Rating Block |
| Category | LMS |
| `build()` | Returns `RatingForm` via `\Drupal::formBuilder()->getForm()` |

No caching configuration specified — defaults to Drupal's standard block cache (per-route, per-permissions hash). Form itself is user-specific (checks existing rating), so effective cache is per-user per-route.

---

## Missing Routes (Critical)

The following routes are referenced in code but DO NOT exist in `lms_rating.routing.yml`:

| Missing Route ID | Referenced by | Effect |
|-----------------|---------------|--------|
| `lms_rating.collection` | `RatingController::toggleApprove()`, `RatingController::toggleFeatured()`, `links.menu.yml` | `RouteNotFoundException` on redirect; broken admin menu link |
| `lms_rating.toggle_approve` | `RatingListBuilder::getOperations()` | `RouteNotFoundException` when list builder operations render |
| `lms_rating.toggle_featured` | `RatingListBuilder::getOperations()` | `RouteNotFoundException` when list builder operations render |

The intended replacement for the two toggle routes is `lms_rating.admin_action` (which already handles approve/feature via `executeAction()`). The `RatingController` class and `RatingListBuilder` operations are superseded by `RatingManagerController` but were not cleaned up.

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_base` | Internal | Shared base traits |
| `lms_course` | Internal | `field_course` entity reference |

---

## Known Issues / Bugs

| ID | Severity | Description |
|----|----------|-------------|
| BUG-RATING-01 | High | 3 missing routes: `lms_rating.collection`, `lms_rating.toggle_approve`, `lms_rating.toggle_featured`. `RatingController` redirects and `RatingListBuilder` operations will throw `RouteNotFoundException`. See Missing Routes section above. |
| BUG-RATING-02 | Medium | `RatingManagerController::list()` uses `#theme: lms_admin_ratings` but no `hook_theme()` exists in this module (no `.module` file). If not registered elsewhere the list page renders empty. |
| BUG-RATING-03 | Low | `links.menu.yml` points to non-existent route `lms_rating.collection` — the admin menu link will be broken. |
| DEBT-RATING-01 | Low | No validation that only enrolled users may submit a rating. Any authenticated user with `access content` can rate any course. |
| DEBT-RATING-02 | Low | `RatingController` and `RatingListBuilder` are superseded by `RatingManagerController`; they should be removed to eliminate the missing-route risk. |

> These feed [E02 Issues](../../E-Quality/E02-issues/).

---

## Cross-Links

- Entity detail: [C04 Content Entities](../C04-entities/01-content-entities.md)
- Course context: [lms_course module](lms_course.md)
