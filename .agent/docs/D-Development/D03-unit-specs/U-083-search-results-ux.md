# D03 — Unit Spec: Search Results UX

**Unit ID:** U-083
**Group:** G-UI-POLISH
**Title:** Search results UX
**Status:** SPEC
**Priority:** P2
**Assigned:** unassigned

---

## Scope

Polish the search/catalog page UX: faceted filter sidebar (category, level, price range, rating), sort controls, active filter chips, grid/list view toggle, and "no results" empty state with suggestions.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `templates/catalog.html.twig` | Modify | lms_theme |
| `templates/catalog-filters.html.twig` | Create | lms_theme |
| `scss/pages/_catalog.scss` | Modify | lms_theme |
| `js/catalog-filters.js` | Create | lms_theme |
| `src/Controller/CatalogController.php` | Modify | lms_course |

---

## Acceptance Criteria

- [ ] Filter sidebar: category (multi-select), level (beginner/intermediate/advanced), price (free/paid/range slider), min rating (1-5 stars)
- [ ] Active filters shown as removable chips above results
- [ ] Sort: Newest, Most Popular, Highest Rated, Price Low-High, Price High-Low
- [ ] Grid view (3 col) / List view toggle, preference saved in localStorage
- [ ] Result count: "Showing 24 of 87 courses"
- [ ] Pagination: infinite scroll OR numbered pages (pick one, document)
- [ ] Empty state: "No courses found" illustration + "Clear all filters" + "Browse all" CTA
- [ ] URL reflects active filters (shareable/bookmarkable search state)

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Filter state in URL test |
| G4 Adversarial | PENDING | — |

---

## Dependencies

- U-009 (catalog page + search/filter — functional layer)
- G-HARDENING complete

**Feature Flag:** None

*End of U-083-search-results-ux.md*
