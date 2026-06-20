# D03 — Story Spec: lms_rating

**Story ID:** lms_rating
**Story type:** module
**Title:** Course Ratings & Reviews — submission form, admin moderation, block plugin; 3 routes missing
**Primary source:** `C-Design/C05-modules/lms_rating.md`
**Owning module(s):** lms_rating
**Service(s):** rating
**Epic:** E-07 (Communication)
**Contributes to Feature(s):** Communication, Course Management
**Design source(s):** `../C-Design/C05-modules/lms_rating.md`
**Round:** N/A
**Driver:** SA directive
**Status:** IN_PROGRESS
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** (pre-2026-04-15; implemented before D03 layer was materialized)
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_rating`

---

## Session Strategy

**Estimated session count:** 2
**Splitting strategy:**
- S1: `lms_rating` entity; `RatingForm` (submission); `RatingBlock` plugin; routing (2 working admin routes)
- S2: `RatingManagerController` (list + executeAction); `RatingController` (broken toggle methods); `RatingListBuilder` (broken operations); install hook

Status is IN_PROGRESS because 3 routes referenced in code do not exist, causing `RouteNotFoundException` at runtime on affected paths.

---

## Scope

Allows learners to submit 1–5 star ratings with text reviews for courses, and provides admin moderation:

- `lms_rating` content entity: course reference, score (1–5), review text, approved flag, featured flag (added via update hook).
- `RatingForm`: student-facing submission form embedded via `lms_rating_block` Block plugin; prevents duplicate ratings (one per user per course); sets `field_approved=FALSE` on create.
- `RatingManagerController`: working admin controller at `/workspace/ratings`; list page (paged) and multi-action endpoint (approve, feature, delete).
- `RatingController` and `RatingListBuilder`: broken — redirect and operations reference 3 missing routes.
- No `.module` file; no `hook_theme()`; `lms_admin_ratings` theme hook used by `RatingManagerController::list()` must be registered elsewhere (lms_workspace or theme) or the list page renders empty.

**Open issues blocking DONE:** 3 missing routes + missing theme registration. See Known Bugs.

Full design: [`../C-Design/C05-modules/lms_rating.md`](../../C-Design/C05-modules/lms_rating.md)

---

## Source Documents

| Source | Why loaded |
|---|---|
| `../C-Design/C05-modules/lms_rating.md` | Owning module design — authoritative reference |
| `../C-Design/C05-modules/lms_course.md` | field_course entity reference on lms_rating |

---

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Authenticated users can submit a 1–5 star rating and text review per course | B03-features/09-analytics-reporting.md | Acceptance |
| Duplicate ratings are prevented (one per user per course) | B03-features/09-analytics-reporting.md | Acceptance |
| New ratings go through admin moderation (field_approved=FALSE on create) | B03-features/09-analytics-reporting.md | Acceptance |
| Admin can approve, feature, and delete ratings | B03-features/09-analytics-reporting.md | Acceptance |
| Rating form is surfaced as a block on course detail pages | B03-features/09-analytics-reporting.md | Acceptance |

---

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_rating/lms_rating.info.yml` | Create | Module declaration; deps: lms_base, lms_course | — | S1 |
| `web/modules/custom/lms_rating/lms_rating.routing.yml` | Create | 2 working routes (admin_list, admin_action); 3 referenced routes absent | — | S1 |
| `web/modules/custom/lms_rating/lms_rating.links.menu.yml` | Create | Points to lms_rating.collection — MISSING ROUTE | — | S1 |
| `web/modules/custom/lms_rating/lms_rating.install` | Create | lms_rating_update_11001(): adds field_is_featured boolean | — | S1 |
| `web/modules/custom/lms_rating/src/Entity/Rating.php` | Create | lms_rating entity; 7 base fields; EntityOwnerTrait | — | S1 |
| `web/modules/custom/lms_rating/src/Form/RatingForm.php` | Create | Student submission form; duplicate check; score select + review textarea | — | S1 |
| `web/modules/custom/lms_rating/src/Plugin/Block/RatingBlock.php` | Create | Block plugin lms_rating_block; build() returns RatingForm | — | S1 |
| `web/modules/custom/lms_rating/src/Controller/RatingManagerController.php` | Create | list() paged admin view; executeAction() approve/feature/delete | — | S2 |
| `web/modules/custom/lms_rating/src/Controller/RatingController.php` | Create | toggleApprove(), toggleFeatured() — BROKEN; redirects to lms_rating.collection (missing route) | — | S2 |
| `web/modules/custom/lms_rating/src/RatingListBuilder.php` | Create | Admin table builder — BROKEN; operations reference lms_rating.toggle_approve/toggle_featured (missing routes) | — | S2 |

---

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| lms_base | Internal module | Shared entity traits |
| lms_course | Internal module | field_course entity reference |
| lms_admin_ratings theme hook | External (lms_workspace or theme) | RatingManagerController::list() uses this theme; not registered in lms_rating itself |

---

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| Block plugin pattern | RatingBlock | @Block annotation; plugin id lms_rating_block; category LMS |
| Duplicate prevention | RatingForm::buildForm() | Entity query checks existing rating for uid + course before rendering |
| Admin moderation flag | RatingForm::submitForm() | field_approved=FALSE on create; admin approves via executeAction |

---

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission |
|---|---|---|---|---|
| Course rating block | star score selector, review text | lms_rating entity | Block embedded on course page | authenticated |
| Admin ratings list | student name, course, score, review excerpt, approved, featured | lms_rating entity | `/workspace/ratings` | administer lms_course |
| Admin action | approve/feature/delete per rating | lms_rating entity | `/workspace/ratings/{id}/{action}` | administer lms_course |

**Hardcoded content exceptions:** `RatingManagerController::list()` uses `ui-avatars.com` CDN for avatar fallback (external dependency; no privacy review).

---

## Feature Flags Introduced or Read

None. No feature flags in this module.

---

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-RATING-001 | Integration | Student submits rating; lms_rating created with field_approved=FALSE | Rating saved; pending moderation |
| T-RATING-002 | Integration | Duplicate rating blocked; existing rating returned | One rating per user per course |
| T-RATING-003 | Integration | Admin approves rating via executeAction; field_approved=TRUE | Approval persists |
| T-RATING-004 | Integration | Admin features rating via executeAction; field_is_featured=TRUE, field_approved=TRUE | Auto-approve on feature |
| T-RATING-005 | Integration | RatingListBuilder operations do NOT render (missing routes) | RouteNotFoundException if rendered |
| T-RATING-006 | Integration | RatingManagerController::list() renders with lms_admin_ratings theme defined in workspace/theme | List renders; not empty |

---

## Gate Results

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Entity installs; RatingForm and RatingManagerController functional | — | pre-2026-04-15 |
| G2 Lint | PASS | No coding standard violations | — | pre-2026-04-15 |
| G3 Tests | PASS | Submission form and admin executeAction path functional | — | pre-2026-04-15 |
| G4 Adversarial | FAIL | BUG-RATING-01 (3 missing routes), BUG-RATING-02 (missing theme) open | — | pre-2026-04-15 |

---

## Notes

### Missing Routes — Critical (blocks DONE)

The following routes are referenced in code but DO NOT exist in `lms_rating.routing.yml`:

| Missing Route ID | Referenced By | Effect |
|-----------------|---------------|--------|
| `lms_rating.collection` | `RatingController::toggleApprove()`, `RatingController::toggleFeatured()`, `links.menu.yml` | `RouteNotFoundException` on redirect; broken admin menu link |
| `lms_rating.toggle_approve` | `RatingListBuilder::getOperations()` | `RouteNotFoundException` when list operations render |
| `lms_rating.toggle_featured` | `RatingListBuilder::getOperations()` | `RouteNotFoundException` when list operations render |

The intended replacement for the two toggle routes is `lms_rating.admin_action` (which already handles approve/feature via `executeAction()`). `RatingController` and `RatingListBuilder` are superseded by `RatingManagerController` but were not cleaned up.

### Known Bugs

| ID | Severity | Description |
|----|----------|-------------|
| BUG-RATING-01 | High | 3 missing routes cause `RouteNotFoundException` in `RatingController` redirects and `RatingListBuilder` operations |
| BUG-RATING-02 | Medium | `RatingManagerController::list()` uses `#theme: lms_admin_ratings`; no `hook_theme()` in this module; list page renders empty if not registered in workspace/theme |
| BUG-RATING-03 | Low | `links.menu.yml` points to non-existent `lms_rating.collection` — admin menu link broken |

### Technical Debt

| ID | Severity | Description |
|----|----------|-------------|
| DEBT-RATING-01 | Low | No validation that only enrolled users may submit a rating; any authenticated user can rate any course |
| DEBT-RATING-02 | Low | `RatingController` and `RatingListBuilder` should be removed; superseded by `RatingManagerController` |

### Resolution Path to DONE

1. Add 3 missing routes to `lms_rating.routing.yml` (or remove `RatingController` + `RatingListBuilder` entirely and rely on `RatingManagerController`)
2. Register `lms_admin_ratings` theme hook (in a `.module` file for `lms_rating`, or confirm it is registered in workspace/theme)
3. Fix `links.menu.yml` to point to `lms_rating.admin_list` instead of the missing `lms_rating.collection`

These bugs feed [E02 Issues](../../E-Quality/E02-issues/).
