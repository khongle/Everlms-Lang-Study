# Task: Recently Viewed Courses Tracking

**ID:** TASK-179
**Unit:** U-009 (course catalog)
**Group:** G-CONTENT
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Track recently viewed courses per visitor and surface them in two places: a "Recently viewed" rail on the homepage and a "You recently viewed" block on the course detail page. Anonymous users are tracked via a browser cookie (30-item ring buffer of course IDs, 90-day expiry). Authenticated users are tracked in a dedicated DB table (`lms_recently_viewed`) with the same 30-item limit enforced at write time. The feature lives in `lms_base` as a service + event subscriber.

## Acceptance Criteria
- [ ] Viewing a course detail page records that course for the current visitor (anon + auth)
- [ ] Anonymous tracking: cookie `lms_rv` stores JSON array of ≤30 course IDs (most-recent first); 90-day expiry
- [ ] Authenticated tracking: `lms_recently_viewed` table stores (`uid`, `course_id`, `viewed_at`); max 30 rows per user (oldest pruned on write)
- [ ] On login, cookie IDs merged into DB table (dedup, maintain recency order, cap at 30)
- [ ] Homepage "Recently viewed" rail shows ≤6 most-recent course cards (hidden if list empty)
- [ ] Course detail page shows "You recently viewed" block with ≤5 courses (excludes current course)
- [ ] Viewing the same course again moves it to top of list (no duplicate entries)
- [ ] Unit test: prune-on-write keeps exactly 30 items; duplicate view promotes to top

## Technical Notes
- Module: `web/modules/custom/lms_base/`
- `src/Service/RecentlyViewedService.php` — `record(int $courseId)`, `getForCurrentUser(int $limit): array`, `mergeAnonymous(array $cookieIds)`
- `src/EventSubscriber/CourseViewSubscriber.php` — subscribes to `KernelEvents::RESPONSE` after course node view; calls `RecentlyViewedService::record()`
- Cookie handling via `Symfony\Component\HttpFoundation\Cookie` set in subscriber response
- DB schema: `lms_recently_viewed` (`id` serial PK, `uid` int, `course_id` int, `viewed_at` timestamp). Unique index on `(uid, course_id)`. Defined in `lms_base.install` `hook_schema()`.
- Login merge: `hook_user_login()` in `lms_base.module` reads cookie, calls `mergeAnonymous()`
- Block: `src/Plugin/Block/RecentlyViewedBlock.php` — usable on homepage + course layout
- Template: `lms-recently-viewed-rail.html.twig`

## Dependencies
- None (standalone service; homepage rail integration is soft dependency on TASK-178 layout)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: service logic; Behat: rail appears after visiting 2+ courses)
- [ ] Cookie set with HttpOnly + SameSite=Lax flags (security review inline)
