# D03 — Unit Spec: DDEV + Drupal 11.3 Setup

**Unit ID:** U-001
**Group:** G-FOUNDATION
**Title:** DDEV + Drupal 11.3 setup
**Status:** DONE
**Priority:** P0

---

## Scope

Provision the local development environment using DDEV with Drupal 11.3, MariaDB 10.11, and PHP 8.3. Includes project scaffold, Composer dependencies, and baseline configuration so all subsequent units have a running Drupal instance to build against.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `.ddev/config.yaml` | Project root — DDEV config |
| `composer.json` | Project root — dependency manifest |
| `composer.lock` | Project root — locked deps |
| `web/sites/default/settings.php` | Drupal settings |
| `web/sites/default/settings.local.php` | Local DB credentials |

---

## Entities / Services / Controllers / Templates

- No custom entities.
- No custom services.
- Drupal core install with contrib modules enabled (see U-002).

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | `ddev start` succeeds; Drupal installs cleanly |
| G2 Lint | PASS | No PHP errors on bootstrap |
| G3 Tests | PASS | Site reachable at configured URL |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `ddev start` launches without errors
- [x] Drupal 11.3 installs with MariaDB 10.11 backend
- [x] PHP 8.3 used
- [x] Admin user accessible
- [x] `composer install` reproduces environment from lock file

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
| U-002 | Depends on this environment |
| U-003 | Depends on this environment |
| U-004 | Depends on this environment |

---

*End of U-001-ddev-drupal-setup.md*
