# ISSUE-010 — lms_rating: Three Runtime Routes Missing; RouteNotFoundException on RatingListBuilder Operations

| Field | Value |
|---|---|
| ID | `ISSUE-010` |
| Title | lms_rating.collection / toggle_approve / toggle_featured routes referenced but absent from routing.yml → RouteNotFoundException |
| Source | G4-Code-Quality / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `HIGH` |
| Affected page / route | `/workspace/ratings` (admin list), `lms_rating.collection` (menu link), `toggle_approve` and `toggle_featured` operations in `RatingListBuilder` |
| Owning Epic | E-04 (Communication) — |
| Owning Story | [`lms_rating`](../../../D-Development/D03-stories/lms_rating.md) |
| Owning module(s) | `lms_rating` |

## Summary

`lms_rating.routing.yml` defines only two admin routes (`lms_rating.admin_list` at `/workspace/ratings` and `lms_rating.admin_action` at `/workspace/ratings/{lms_rating}/{action}`). Three additional route IDs are referenced in PHP source and YAML configuration but do not exist in the routing file:

| Missing route ID | Referenced by | Consequence |
|---|---|---|
| `lms_rating.collection` | `lms_rating.links.menu.yml` → menu link; `RatingController::toggleApprove()`; `RatingController::toggleFeatured()` redirect calls | `RouteNotFoundException` thrown when Drupal builds the admin menu containing the rating link, and when either toggle method completes and calls `$this->redirect('lms_rating.collection')` |
| `lms_rating.toggle_approve` | `RatingListBuilder::getOperations()` — generates URL for the "toggle_approve" operation row | `RouteNotFoundException` thrown during list build at `/workspace/ratings` when `RatingListBuilder` is invoked |
| `lms_rating.toggle_featured` | `RatingListBuilder::getOperations()` — generates URL for the "toggle_featured" operation row | Same — `RouteNotFoundException` during list build |

The effect is that the admin rating list page (`/workspace/ratings`) is currently broken if it uses the `RatingListBuilder` handler (rather than the custom `RatingManagerController`). The approve/feature redirect in `RatingController` is also broken. Additionally, the admin menu link for ratings points to a non-existent route, causing a PHP exception when the menu is rebuilt.

## Reproduction

1. Log in as administrator.
2. Navigate to `/workspace/ratings` — if Drupal routes the request through `RatingListBuilder`, a `RouteNotFoundException` for `lms_rating.toggle_approve` is thrown during `getOperations()`.
3. Alternatively: run `drush router:rebuild` (or clear caches) — Drupal will throw on the `lms_rating.collection` menu link at menu build time.
4. Or: call `RatingController::toggleApprove($rating)` — it completes the toggle then throws `RouteNotFoundException` when redirecting to `lms_rating.collection`.

To confirm missing routes:
```
grep -n "lms_rating\." web/modules/custom/lms_rating/lms_rating.routing.yml
# Only shows: lms_rating.admin_list, lms_rating.admin_action
grep -rn "lms_rating\.collection\|lms_rating\.toggle" web/modules/custom/lms_rating/
# Shows references in links.menu.yml, RatingController.php, RatingListBuilder.php
```

## Root Cause

`RatingController` and `RatingListBuilder` were written to use a standard Drupal entity list-builder pattern (`lms_rating.collection` as the canonical listing route, plus per-entity toggle routes). The `lms_rating.routing.yml` file was either partially implemented or diverged from the controller/list-builder code: the custom `RatingManagerController` at `/workspace/ratings` and `/workspace/ratings/{id}/{action}` was added as an alternative admin UI, but the route IDs expected by `RatingController` and `RatingListBuilder` were never added.

The `lms_rating` module also has no `.module` file, which means no `hook_theme()` is registered for the `lms_admin_ratings` theme hook used by `RatingManagerController::list()` — a separate (MEDIUM) rendering issue.

Source files:
- `web/modules/custom/lms_rating/lms_rating.routing.yml` — missing 3 route definitions
- `web/modules/custom/lms_rating/lms_rating.links.menu.yml` — references `lms_rating.collection` (missing)
- `web/modules/custom/lms_rating/src/Controller/RatingController.php` — redirects to `lms_rating.collection`
- `web/modules/custom/lms_rating/src/RatingListBuilder.php` — generates URLs for `lms_rating.toggle_approve` and `lms_rating.toggle_featured`

## Resolution (filled at RESOLVED)

Two valid resolution paths:

**Option A — Add missing routes:**
1. Add `lms_rating.collection` route (alias for `lms_rating.admin_list`, or redirect).
2. Add `lms_rating.toggle_approve` route pointing to `RatingController::toggleApprove`.
3. Add `lms_rating.toggle_featured` route pointing to `RatingController::toggleFeatured`.
4. Update `lms_rating.links.menu.yml` to use `lms_rating.admin_list` (or newly added `collection` route).

**Option B — Remove dead references:**
1. Delete `RatingController.php` and `RatingListBuilder.php` (since `RatingManagerController` provides full admin functionality).
2. Update `lms_rating.links.menu.yml` to point to `lms_rating.admin_list`.

Option B is simpler given that `RatingManagerController` already covers the listing and approve/feature/delete actions via `lms_rating.admin_action`.

## Trace links

- **Story:** `D-Development/D03-stories/lms_rating.md` §"Known Bugs"
- **C05 module spec:** `C-Design/C05-modules/lms_rating.md`
- **Facts base:** `modules-progress-comms.md` §lms_rating §"Missing routes — IMPORTANT"
- **K04:** Reusable rule — when a module defines controller methods that redirect to named routes, those route IDs must exist in `routing.yml`; verify with `drush router:rebuild` and `drush route:match` during development.
