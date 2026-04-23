# D03 — Unit Spec: Contrib Modules Integration

**Unit ID:** U-002
**Group:** G-FOUNDATION
**Title:** Contrib modules integration
**Status:** DONE
**Priority:** P0

---

## Scope

Install and configure all required Drupal contrib modules (admin_toolbar, token, pathauto, etc.) that provide foundational capabilities used across all LMS custom modules. Ensures hook implementations, entity APIs, and routing infrastructure are available before any custom module is enabled.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `composer.json` | Project root — contrib dependencies added |
| `web/sites/default/config/core.extension.yml` | Enabled module list |

---

## Entities / Services / Controllers / Templates

- No custom code.
- Contrib modules enabled as project dependencies.

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | All contrib modules enable without errors |
| G2 Lint | PASS | No dependency conflicts |
| G3 Tests | PASS | Admin toolbar, token, pathauto functional |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `admin_toolbar` installed and visible in admin
- [x] `token` module available for use by other modules
- [x] `pathauto` configured for clean URLs
- [x] All contrib modules listed in `composer.json` installable via `composer install`
- [x] No version conflicts among contrib packages

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
| U-001 | Environment must exist first |
| U-003 | lms_theme depends on Bootstrap 5 contrib or CDN |
| U-004 | lms_base uses taxonomy contrib APIs |

---

*End of U-002-contrib-modules.md*
