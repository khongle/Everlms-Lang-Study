# D03 — Unit Spec: lms_theme

**Unit ID:** U-003
**Group:** G-FOUNDATION
**Title:** lms_theme (Bootstrap 5.3 dark premium)
**Status:** DONE
**Priority:** P0

---

## Scope

Custom Drupal theme providing the Bootstrap 5.3 dark premium visual design used across all LMS pages. Sets the global layout, colour variables, typography, and component styles consumed by every Twig template in the system.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/themes/custom/lms_theme/lms_theme.info.yml` | Theme declaration |
| `web/themes/custom/lms_theme/lms_theme.libraries.yml` | Asset libraries (Bootstrap 5.3, custom CSS/JS) |
| `web/themes/custom/lms_theme/templates/` | Base layout templates |
| `web/themes/custom/lms_theme/css/` | Custom CSS overrides |
| `web/themes/custom/lms_theme/js/` | Theme-level JS |

---

## Entities / Services / Controllers / Templates

- No custom entities or services.
- Provides Twig base templates: `html.html.twig`, `page.html.twig`, `block.html.twig`.

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Theme enables and renders without errors |
| G2 Lint | PASS | CSS/JS assets load correctly |
| G3 Tests | PASS | Dark mode renders on all core pages |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] Bootstrap 5.3 assets load on all pages
- [x] Dark premium colour scheme applied globally
- [x] Theme set as default frontend theme
- [x] Responsive breakpoints functional on mobile/tablet/desktop
- [x] No console JS errors on page load

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
| U-001 | Environment prerequisite |
| U-002 | Contrib assets may be included |
| All content/UI units | Consume this theme |

---

*End of U-003-lms-theme.md*
