# D03 — Unit Spec: Homepage Controller + Template

**Unit ID:** U-012
**Group:** G-CONTENT
**Title:** Homepage controller + template
**Status:** DONE
**Priority:** P0

---

## Scope

Public homepage at `/homepage` serving as the marketing landing page. Renders featured courses, categories, testimonials, and platform statistics. Implemented in `CoursePublicController::homepage()`. Also includes static pages: `/about`, `/contact`, `/pricing`, `/glossary`.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_course/src/Controller/CoursePublicController.php` | `homepage()`, `glossary()` methods |
| `web/modules/custom/lms_course/src/Controller/PageController.php` | Static page handlers |
| `web/modules/custom/lms_course/lms_course.routing.yml` | `/homepage`, `/about`, `/contact`, `/pricing`, `/glossary` routes |
| `web/modules/custom/lms_course/templates/lms-homepage.html.twig` | Homepage template |
| `web/modules/custom/lms_course/templates/lms-about.html.twig` | About page template |
| `web/modules/custom/lms_course/templates/lms-contact.html.twig` | Contact page template |
| `web/modules/custom/lms_course/templates/lms-pricing.html.twig` | Pricing page template |
| `web/modules/custom/lms_course/templates/lms-glossary.html.twig` | Glossary template |
| `web/modules/custom/lms_course/templates/lms-profile.html.twig` | Public profile template |

---

## Entities / Services / Controllers / Templates

**CoursePublicController methods (relevant):**
- `homepage(): array` — featured courses, stats, categories
- `glossary(): array` — glossary term listing

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | `/homepage` resolves; template renders |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Featured courses and categories displayed |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `/homepage` renders without errors
- [x] Featured courses section shows course cards
- [x] Category highlights linked to catalog filter
- [x] Platform stats (enrolled users, courses, etc.) displayed
- [x] Static pages `/about`, `/contact`, `/pricing` render
- [x] `/glossary` renders term list

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
| U-005 | Course entity for featured courses |
| U-004 | Category taxonomy for highlights |
| U-009 | Catalog linked from homepage CTA |
| U-026 | Pricing page references MembershipPlan |

---

*End of U-012-homepage-controller.md*
