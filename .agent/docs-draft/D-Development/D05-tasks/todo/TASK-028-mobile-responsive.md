# Task: Mobile Responsive Audit

**ID:** TASK-028
**Unit:** U-079
**Group:** G-UI-POLISH
**Priority:** P1
**Estimate:** 10h
**Assigned:** unassigned
**Status:** TODO (60% done)

## Audit Notes — What Already Exists
- Bootstrap 5.3 breakpoints defined ($breakpoint-sm through $breakpoint-xxl) ✅
- ~35 media queries across 16 SCSS files ✅
- CSS Grid with auto-fill/minmax() for inherent responsiveness ✅
- KPI grid degrades 4→2→1 columns ✅
- Admin sidebar hides at md breakpoint ✅
- Homepage hero, featured, testimonials responsive ✅
- Learn page sidebar collapses at md ✅

## Remaining Work (Critical)
- [ ] **CRITICAL: No mobile navigation** — .lms-navbar__nav hides at ≤992px, toggle button exists but NO JS handler. Users lose all navigation on tablet/phone
- [ ] Add responsive table strategy (overflow-x: auto or card-stack)
- [ ] Add media queries to _categories.scss, _forum.scss (currently 0)
- [ ] Verify viewport meta tag in page template
- [ ] Audit touch-friendly sizing (44×44px minimum targets)
- [ ] Use $breakpoint-xxl (defined but never referenced)
- [ ] Add responsive rules to _forms.scss, _buttons.scss, _cards.scss

## Description
Audit và fix responsive issues trên tất cả pages cho mobile/tablet breakpoints.

## Acceptance Criteria
- [ ] Tất cả public pages responsive (xs, sm, md, lg, xl)
- [ ] Student portal pages responsive
- [ ] Management pages functional trên tablet+
- [ ] Navigation responsive (hamburger menu)
- [ ] Tables responsive (horizontal scroll hoặc card layout)
- [ ] Forms usable trên mobile
- [ ] Images responsive (srcset)

## Technical Notes
- Bootstrap 5.3 breakpoints
- SCSS media queries
- Test: Chrome DevTools device emulation

## Dependencies
- All UI tasks

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Manual test trên top 3 mobile resolutions
