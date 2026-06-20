# Task: Search Results UX

**ID:** TASK-042
**Unit:** U-083
**Group:** G-UI-POLISH
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Cải thiện search results page: filters, sorting, result cards, pagination, autocomplete, empty state.

## Acceptance Criteria
- [ ] Search input with autocomplete suggestions
- [ ] Filters: category, level, price range, rating, format
- [ ] Sorting: relevance, newest, price, rating, popularity
- [ ] Result cards: thumbnail, title, instructor, rating, price
- [ ] Pagination (infinite scroll hoặc numbered)
- [ ] Result count display
- [ ] Empty state khi không có kết quả
- [ ] URL params (shareable search links)
- [ ] Responsive layout

## Technical Notes
- Module: `lms_course` (CatalogController/search exists)
- Route: `/search`, `/courses` (catalog with filters)
- JS: autocomplete, AJAX filtering
- SCSS: `_search.scss`

## Dependencies
- None (existing scaffold)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Filters + sorting functional
- [ ] Mobile responsive verified
