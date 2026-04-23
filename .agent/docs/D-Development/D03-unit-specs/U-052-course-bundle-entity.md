# D03 — Unit Spec: CourseBundle Entity

**Unit ID:** U-052
**Group:** G-ADDITIONAL
**Title:** CourseBundle entity
**Status:** DONE (PARTIAL)
**Priority:** P2

---

## Scope

Implements the `lms_course_bundle` custom content entity for bundling multiple courses into a discounted package. The entity class is implemented but the module lacks routing, controller, forms, and templates — the entity is a scaffold-level data model only.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_bundle/lms_bundle.info.yml` | Module declaration |
| `web/modules/custom/lms_bundle/src/Entity/CourseBundle.php` | CourseBundle entity class |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_course_bundle` (`CourseBundle extends ContentEntityBase`)
- Key fields: `title`, `field_courses` (multi-value ref: `lms_course`), `field_price`, `field_discount_percentage`, `status`

No routing, controller, forms, or templates implemented yet.

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Entity schema installs |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PARTIAL | Entity creation works via code only; no UI |
| G4 Adversarial | PARTIAL | No admin UI or checkout integration |

---

## Acceptance Criteria

- [x] `lms_course_bundle` entity type registered
- [x] Bundle can reference multiple courses
- [ ] Admin CRUD form for bundles (PARTIAL — not implemented)
- [ ] Bundle purchasable via checkout (PARTIAL — not wired to lms_order)
- [ ] Bundle landing page (PARTIAL — no template)

---

## Known Debt / Issues

- No routing, controller, forms, or templates — entity is scaffold only. UI and commerce integration is unimplemented.

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-005 | Course entities bundled here |
| U-025 | Order entity would reference bundles |

---

*End of U-052-course-bundle-entity.md*
