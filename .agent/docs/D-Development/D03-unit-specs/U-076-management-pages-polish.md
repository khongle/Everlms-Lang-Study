# D03 — Unit Spec: Management Pages CRUD Polish

**Unit ID:** U-076
**Group:** G-UI-POLISH
**Title:** Management pages CRUD polish
**Status:** SPEC
**Priority:** P2
**Assigned:** unassigned

---

## Scope

Polish all `/manage/*` admin pages: consistent table layouts, bulk actions, inline status toggles, search/filter bars, breadcrumbs, and consistent action button styles across course, user, order, coupon, and certificate management pages.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `templates/manage/` directory | Modify all | lms_theme |
| `scss/pages/_management.scss` | Create | lms_theme |
| `js/manage-tables.js` | Create | lms_theme |

---

## Acceptance Criteria

- [ ] All `/manage/*` list pages have consistent column headers + sortable columns
- [ ] Bulk action checkboxes + "Apply to selected" dropdown on list pages
- [ ] Inline status toggle (active/inactive) without full page reload
- [ ] Search bar filters list client-side (for small sets) or via AJAX (large sets)
- [ ] Breadcrumb: `Home > Management > {section}` on all manage pages
- [ ] Action buttons: Create (primary blue), Edit (secondary), Delete (danger red) — consistent

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | — |
| G4 Adversarial | PENDING | Bulk action CSRF check |

---

## Dependencies

- U-034 (order management admin)
- U-032 (coupon admin)
- G-HARDENING complete (U-066 for CSRF on bulk actions)

**Feature Flag:** None

*End of U-076-management-pages-polish.md*
