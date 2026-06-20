# Task: Error & Empty State Pages

**ID:** TASK-043
**Unit:** U-084
**Group:** G-UI-POLISH
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Thiết kế và implement các error pages và empty states cho toàn bộ hệ thống: 403, 404, 500, empty lists, no results.

## Acceptance Criteria
- [ ] 403 Forbidden page (themed, helpful message)
- [ ] 404 Not Found page (themed, search suggestion, popular courses)
- [ ] 500 Server Error page (themed, friendly message)
- [ ] Empty state: no enrolled courses (CTA to browse)
- [ ] Empty state: no quiz results
- [ ] Empty state: no certificates
- [ ] Empty state: no notifications
- [ ] Empty state: empty wishlist
- [ ] Empty state: no forum topics
- [ ] Consistent illustration/icon style across all states

## Technical Notes
- Theme: `lms_theme` (error templates)
- Templates: `page--403.html.twig`, `page--404.html.twig`, etc.
- Empty states: partial templates included in listing pages
- SCSS: `_error-states.scss`

## Dependencies
- None

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] All error pages themed consistently
- [ ] Mobile responsive verified
