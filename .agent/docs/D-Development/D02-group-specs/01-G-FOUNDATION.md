# D02 — Group Spec: G-FOUNDATION

**Group ID:** G-FOUNDATION
**Group Name:** Foundation & Theme
**Order:** 1
**Units in Scope:** U-001, U-002, U-003, U-004
**Dependencies:** None
**Estimated Sprints:** 1
**Status:** DONE

---

## Purpose

Thiết lập môi trường phát triển, cài đặt Drupal 11 core, contrib modules, custom theme Bootstrap 5.3, và base module chứa taxonomies + helper utilities. Tất cả các group khác phụ thuộc vào G-FOUNDATION.

---

## Units

| Unit ID | Name | Status | Gate | Priority |
|---------|------|--------|------|----------|
| U-001 | DDEV + Drupal 11.3 setup | DONE | G4 pass | P0 |
| U-002 | Contrib modules integration | DONE | G4 pass | P0 |
| U-003 | lms_theme (Bootstrap 5.3 dark premium) | DONE | G4 pass | P0 |
| U-004 | lms_base (taxonomies + LmsHelper) | DONE | G4 pass | P0 |

---

## Build Order Within Group

1. U-001 — DDEV + Drupal 11.3 setup (no dependencies)
2. U-002 — Contrib modules integration (depends on U-001)
3. U-003 — lms_theme (depends on U-001, U-002)
4. U-004 — lms_base (depends on U-001, U-002)

---

## Exit Criteria

- [x] Drupal 11.3 running in DDEV
- [x] All required contrib modules enabled
- [x] lms_theme active with Bootstrap 5.3 dark premium
- [x] lms_base providing taxonomy vocabularies and LmsHelper
- [x] All G1-G4 gates passed for all 4 units

---

## Key Risks

- None active — group fully DONE.
- Retrospective: PHP 8.3 compatibility with contrib modules required patch on initial setup.

---

*End of 01-G-FOUNDATION.md*
