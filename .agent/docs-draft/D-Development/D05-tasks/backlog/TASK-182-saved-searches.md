# Task: Saved Searches per User

**ID:** TASK-182
**Unit:** — (extends TASK-060 search + TASK-139 digest)
**Group:** G-INTEGRATION
**Priority:** P3
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Allow authenticated users to save a search query + active filters for later reuse and receive a weekly email digest of new courses matching their saved searches. Saved searches appear in a dropdown on the `/search` page and in the user account section. The weekly digest is integrated into the TASK-139 email digest system. The feature extends the existing search module and digest queue — no new module.

## Acceptance Criteria
- [ ] "Save this search" button appears on `/search` results page (authenticated only; hidden for anon)
- [ ] Clicking button saves current `q` + active facets to `lms_saved_search` table for current user
- [ ] User can name the saved search (inline text input, default = query string)
- [ ] Saved searches dropdown in search bar shows user's up to 10 most-recent saved searches
- [ ] `/user/{uid}/saved-searches` page lists all saved searches; user can delete individual entries
- [ ] Weekly digest: for each user's saved searches, query new courses (published in last 7 days) matching criteria; include in TASK-139 digest email section "New matches for your saved searches"
- [ ] Max 5 saved searches per user; UI shows error and blocks save if limit reached
- [ ] Unit test: digest builder correctly finds new courses matching saved search filter set

## Technical Notes
- Extend: `web/modules/custom/lms_course/src/Controller/SearchController.php` — add `saveSearch()` and `deleteSavedSearch()` endpoints
- DB schema: `lms_saved_search` (`id` serial PK, `uid` int, `name` varchar(128), `query` varchar(255), `filters` JSON, `created` timestamp). Unique index `(uid, query, filters)`. Add to `lms_course.install`.
- Route: `lms_course.routing.yml` → `POST /api/v1/search/save`, `DELETE /api/v1/search/save/{id}`
- User page: `src/Controller/SavedSearchController.php` + route `/user/{uid}/saved-searches`
- Digest integration: extend `lms_email_digest` queue worker (TASK-139) with new section builder `SavedSearchDigestSection`
- New course detection: `SELECT nid FROM node WHERE type='lms_course' AND status=1 AND created > :since` + apply saved filters via Search API programmatic query
- JS: `web/modules/custom/lms_course/js/saved-search.js` — save button handler, dropdown population

## Dependencies
- TASK-060 (search engine — SearchController and facets in place)
- TASK-139 (email digest — digest queue worker exists; extend with saved-search section)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: save/delete endpoints; digest section builder)
- [ ] 5-search limit enforced at DB level (CHECK constraint) and UI level
