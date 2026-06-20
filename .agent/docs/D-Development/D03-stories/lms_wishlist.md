# D03 — Story Spec: lms_wishlist

**Story ID:** lms_wishlist
**Story type:** module
**Title:** Course Wishlist — Toggle, Status Check, and User Wishlist Page
**Primary source:** `C-Design/C05-modules/lms_wishlist.md`
**Owning module(s):** lms_wishlist
**Service(s):** N/A (no custom services; all logic in WishlistController)
**Epic:** E-05 (Commerce & Payment)
**Contributes to Feature(s):** F-007 (Wishlist)
**Design source(s):** `../C05-modules/lms_wishlist.md`
**Round:** N/A
**Driver:** SA directive
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_wishlist`

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 1 (small — single entity, single controller, three routes)
**Splitting strategy:** N/A

## Scope (from primary source)

Provides per-user course wishlisting. The `lms_wishlist` entity records a user–course pair. `WishlistController` exposes three endpoints: a JSON toggle API (add/remove), a status check API (bulk by course IDs), and a user wishlist page (`/my-wishlist`). Cache invalidation uses the custom tag `lms_wishlist:{uid}` so the wishlist page auto-updates when items change.

## Source Documents

| Source | Why loaded |
|---|---|
| `../C05-modules/lms_wishlist.md` | Owning module design |
| `../../B-Requirements/B03-features/04-order-membership.md` | Wishlist feature requirements |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Users can toggle courses on/off wishlist | B03-features/04-order-membership | Acceptance |
| Bulk wishlist status check by course IDs | B03-features/04-order-membership | Acceptance |
| User wishlist page shows wishlisted courses with full course card data | B03-features/04-order-membership | Acceptance |
| Cache invalidated on toggle | B03-features/04-order-membership | NFR |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_wishlist/lms_wishlist.info.yml` | Create | Module declaration | — | S1 |
| `web/modules/custom/lms_wishlist/lms_wishlist.routing.yml` | Create | toggle (POST), status (GET), my_wishlist routes | — | S1 |
| `web/modules/custom/lms_wishlist/lms_wishlist.module` | Create | hook_theme (lms_my_wishlist) | — | S1 |
| `web/modules/custom/lms_wishlist/src/Entity/Wishlist.php` | Create | lms_wishlist entity (EntityOwnerInterface) | — | S1 |
| `web/modules/custom/lms_wishlist/src/Controller/WishlistController.php` | Create | toggle(), getStatus(), myWishlist(), buildCourseCard() | — | S1 |
| `web/modules/custom/lms_wishlist/templates/lms-my-wishlist.html.twig` | Create | User wishlist page template | — | S1 |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| lms_base | Intra-module | info.yml |
| lms_course | Intra-module | info.yml; field_course references lms_course |
| lms_user_course (for is_enrolled check) | Intra-Epic | WishlistController::buildCourseCard() |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| EntityOwnerTrait | Wishlist entity | As-built |
| Cache tag invalidation pattern (lms_wishlist:{uid}) | WishlistController::toggle(), myWishlist() | As-built |
| JSON API response shape | toggle(), getStatus() | As-built |

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission | Revision / moderation | Translation | Cache invalidation | Smoke evidence |
|---|---|---|---|---|---|---|---|---|
| `/my-wishlist` | Wishlisted course cards | lms_wishlist entities + lms_course data | toggle API | _user_is_logged_in | None | None | lms_wishlist:{uid} cache tag | As-built |

**Hardcoded content exceptions:** None.

## Feature Flags Introduced or Read

None.

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-WL-001 | Integration | POST /api/lms/wishlist/toggle adds course → returns status=added | Add AC |
| T-WL-002 | Integration | POST /api/lms/wishlist/toggle existing → removes and returns status=removed | Remove AC |
| T-WL-003 | Integration | GET /api/lms/wishlist/status with course_ids returns bool map + count | Status AC |
| T-WL-004 | Integration | GET /my-wishlist renders correct course cards sorted by created DESC | Listing AC |
| T-WL-005 | Unit | Cache tag lms_wishlist:{uid} invalidated on toggle | Cache AC |
| T-WL-006 | Unit | Anonymous user receives 403 on toggle and status routes | Auth AC |

## Gate Results (per Story)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Module installs; routes resolve | — | — |
| G2 Lint | PASS | No blocking violations | — | — |
| G3 Tests | PASS | All three endpoints smoke-tested | — | — |
| G4 Adversarial | PASS | No critical issues found | — | — |

## Notes

- `buildCourseCard()` reads `lms_user_course` entities to compute `is_enrolled` — this is an implicit dependency on `lms_progress` / enrollment entities not declared in `lms_wishlist.info.yml`. No runtime error if lms_progress is present (always is), but should be made explicit.
- The `lms_wishlist` entity has no admin UI (no list_builder, no form handlers, no route_provider for admin). Admin management of wishlist items is only possible via Views or direct DB. This is intentional per the design.
- No permissions.yml — relies on `_user_is_logged_in: TRUE` route requirement for all authenticated routes.
