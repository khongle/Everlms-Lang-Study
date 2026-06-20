# Task: Search Autocomplete + Suggestions

**ID:** TASK-180
**Unit:** — (extends TASK-060 search)
**Group:** G-INTEGRATION
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Enhance the global search bar (built in TASK-060) with real-time autocomplete. As the user types, an AJAX endpoint performs prefix-match against course titles and returns up to 8 suggestions in a styled dropdown. Popular searches are logged to a DB table and weighted into results alongside title matches. The endpoint is debounced (300 ms) on the frontend. Extends the existing `lms_course` search infrastructure — no new module.

## Acceptance Criteria
- [ ] Typing ≥2 characters in the search bar triggers AJAX request to `/api/v1/search/suggest?q=...`
- [ ] Endpoint returns JSON: `{suggestions: [{label, type, url}]}` with ≤8 items; response in ≤200 ms (DB backend, warm cache)
- [ ] Suggestions blend: course title prefix matches (ranked by enrollment count desc) + top popular searches matching prefix
- [ ] Popular searches logged: each submitted search query (`/search?q=...`) inserts/increments row in `lms_search_query_log` (`query`, `count`, `last_searched`)
- [ ] Dropdown keyboard-navigable (↑ ↓ Enter Escape); accessible `role="listbox"` markup
- [ ] Selecting a suggestion navigates to `/search?q={label}` or direct course URL if type=course
- [ ] Anonymous and authenticated users both get suggestions (no auth required on endpoint)
- [ ] Endpoint responses cached 60 s in Drupal page cache (vary on `q` parameter)
- [ ] Unit test: suggest controller returns correct ranked order for fixture data

## Technical Notes
- Extend: `web/modules/custom/lms_course/src/Controller/SearchController.php` — add `suggest()` method
- Route: `lms_course.routing.yml` → `lms_course.search_suggest` (`/api/v1/search/suggest`, no_cache: false, `_access: 'TRUE'`)
- DB schema: `lms_search_query_log` (`query` varchar(255) PK, `count` int, `last_searched` timestamp). Add to `lms_course.install`.
- Hook: `lms_course_form_search_block_form_alter()` or custom JS to log on form submit
- JS: `web/modules/custom/lms_course/js/search-autocomplete.js` — fetch with 300 ms debounce, render dropdown
- SCSS: `_search-autocomplete.scss`
- Prefix match query: `SELECT title, nid, field_enrollment_count FROM node WHERE type='lms_course' AND title LIKE :prefix ORDER BY field_enrollment_count DESC LIMIT 8` (extend with search_api index for Solr compat)

## Dependencies
- TASK-060 (search engine + results page — SearchController already exists)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: suggest endpoint; JS: keyboard nav via Nightwatch or manual checklist)
- [ ] Accessible markup validated (axe-core or manual screen-reader check)
