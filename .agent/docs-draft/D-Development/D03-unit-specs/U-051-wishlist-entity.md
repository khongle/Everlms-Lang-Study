# D03 — Unit Spec: Wishlist Entity

**Unit ID:** U-051
**Group:** G-ADDITIONAL
**Title:** Wishlist entity
**Status:** DONE
**Priority:** P2

---

## Scope

Implements the `lms_wishlist` custom content entity allowing users to save courses to a personal wishlist. Includes `WishlistController` with AJAX toggle/status endpoints and a "My Wishlist" listing page with template.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_wishlist/lms_wishlist.info.yml` | Module declaration |
| `web/modules/custom/lms_wishlist/lms_wishlist.module` | Hook implementations |
| `web/modules/custom/lms_wishlist/lms_wishlist.routing.yml` | Wishlist routes |
| `web/modules/custom/lms_wishlist/src/Entity/Wishlist.php` | Wishlist entity class |
| `web/modules/custom/lms_wishlist/src/Controller/WishlistController.php` | Wishlist controller |
| `web/modules/custom/lms_wishlist/templates/lms-my-wishlist.html.twig` | My wishlist template |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_wishlist` (`Wishlist extends ContentEntityBase implements EntityOwnerInterface`)
- Key fields: `uid`, `field_course` (ref: `lms_course`), `created`

**WishlistController methods:**
- `toggle(Request $request): JsonResponse` — add/remove course from wishlist
- `getStatus(Request $request): JsonResponse` — check if course is wishlisted
- `myWishlist(): array` — user's wishlist listing page

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Entity schema installs; AJAX endpoints work |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Wishlist toggle and listing functional |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_wishlist` entity type registered
- [x] AJAX toggle adds/removes course from wishlist
- [x] `getStatus()` returns correct wishlisted state for course
- [x] `/my-wishlist` page lists saved courses with cards
- [x] Wishlist scoped to authenticated user

---

## Known Debt / Issues

None identified.

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-005 | Course entity referenced in wishlist |
| U-011 | Course detail page shows wishlist toggle button |

---

*End of U-051-wishlist-entity.md*
