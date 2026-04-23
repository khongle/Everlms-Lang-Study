# D03 — Unit Spec: Rating Entity (1-5 stars)

**Unit ID:** U-041
**Group:** G-COMMUNICATION
**Title:** Rating entity (1-5 stars)
**Status:** DONE
**Priority:** P1

---

## Scope

Implements the `lms_rating` custom content entity for 1-5 star course ratings with review text. Includes `RatingController`, `RatingManagerController` for admin, a `RatingBlock` plugin, and admin list builder. Ratings are displayed on the course detail page.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_rating/lms_rating.info.yml` | Module declaration |
| `web/modules/custom/lms_rating/lms_rating.install` | Schema install |
| `web/modules/custom/lms_rating/lms_rating.routing.yml` | Rating routes |
| `web/modules/custom/lms_rating/lms_rating.links.menu.yml` | Admin menu links |
| `web/modules/custom/lms_rating/src/Entity/Rating.php` | Rating entity class |
| `web/modules/custom/lms_rating/src/RatingListBuilder.php` | Admin list builder |
| `web/modules/custom/lms_rating/src/Controller/RatingController.php` | User rating controller |
| `web/modules/custom/lms_rating/src/Controller/RatingManagerController.php` | Admin rating manager |
| `web/modules/custom/lms_rating/src/Form/RatingForm.php` | Rating create/edit form |
| `web/modules/custom/lms_rating/src/Plugin/Block/RatingBlock.php` | Rating display block |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_rating` (`Rating extends ContentEntityBase`)
- Key fields: `uid`, `field_course` (ref: `lms_course`), `field_stars` (integer 1-5), `field_review` (text), `field_is_approved`, `created`

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Entity schema installs; rating submission works |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Rating create/list functional |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_rating` entity type registered
- [x] 1-5 star rating stored in `field_stars`
- [x] Review text stored in `field_review`
- [x] Ratings linked to a course via `field_course`
- [x] `RatingBlock` displays average stars and count on course detail
- [x] Admin can approve/reject ratings via `RatingManagerController`

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
| U-005 | Course entity referenced by Rating |
| U-011 | Course detail page displays rating summary |

---

*End of U-041-rating-entity.md*
