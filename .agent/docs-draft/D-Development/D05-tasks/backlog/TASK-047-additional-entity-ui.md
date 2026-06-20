# Task: Wishlist, Badges, Notes & Bundles UI

**ID:** TASK-047
**Unit:** — (cross-unit: U-051, U-052, U-053, U-054)
**Group:** G-UI-POLISH (extension)
**Priority:** P2
**Estimate:** 14h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
UI pages cho additional entities có trong sitemap nhưng chỉ có entity scaffold. Cần public-facing pages cho: wishlist, badges, notes, course bundles.

## Context
- Sitemap C07: `/wishlist`, `/badges`, `/notes`
- Entities DONE: U-051 (Wishlist), U-052 (CourseBundle), U-053 (Note), U-054 (Badge)
- `lms_wishlist` đã có Controller + templates
- `lms_badge`, `lms_bundle`, `lms_note` chỉ có Entity

## Acceptance Criteria
- [ ] Wishlist page: course cards, remove button, move to cart
- [ ] Badges page: earned badges grid, progress toward next badge
- [ ] Badge detail: criteria, earned date, share
- [ ] Notes page: list by course/lesson, search, edit/delete
- [ ] Course bundle listing (public): bundles with pricing
- [ ] Bundle detail: included courses, price vs individual, buy CTA
- [ ] Responsive layout cho tất cả

## Technical Notes
- Modules: `lms_wishlist` (controller exists), `lms_badge` (need controller), `lms_note` (controller exists), `lms_bundle` (need controller)
- Routes cần thêm cho badge + bundle public pages
- Templates cần tạo mới cho badge + bundle

## Dependencies
- G-ADDITIONAL entities (DONE)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] All sitemap routes functional
- [ ] Mobile responsive verified
