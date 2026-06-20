# Task: Leaderboard

**ID:** TASK-141
**Unit:** — (new module `lms_leaderboard`)
**Group:** G-COMMUNICATION (extension)
**Priority:** P3
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build a leaderboard system with two scopes: course-specific (top learners ranked by completion percentage) and global (most courses completed, highest XP points). Users can opt out for privacy; opted-out users are hidden from all rankings. Module may be a new `lms_leaderboard` or extend `lms_progress`.

## Context
- `lms_progress` tracks lesson + course completion per user.
- TASK-143 (XP system) will supply XP totals; leaderboard should gracefully handle XP being unavailable until TASK-143 ships.
- Privacy is a first-class concern: default is opted-in, users can toggle at any time.

## Acceptance Criteria
- [ ] Global leaderboard at `/leaderboard` — top 50 users by (a) total courses completed and (b) total XP (tab switcher)
- [ ] Course leaderboard at `/courses/{slug}/leaderboard` — top 50 enrolled users by completion percentage
- [ ] Each row shows: rank, avatar, display name (or "Anonymous" if opted-out), score
- [ ] Opted-out users are fully hidden from the list (not shown as "Anonymous")
- [ ] Privacy toggle at `/user/{uid}/settings` — "Show me on leaderboards" ON by default
- [ ] Leaderboard data cached (Drupal cache tags); invalidated on new course completion or XP change
- [ ] Responsive table with Bootstrap 5.3 dark theme; highlight current user's own row if present
- [ ] XP column shows "—" if TASK-143 not yet deployed (feature flag check)

## Technical Notes
- New module: `web/modules/custom/lms_leaderboard/`
- No new entity needed; queries aggregate `lms_progress` completion data + (optionally) `lms_xp` points table
- Service: `LeaderboardService::getGlobal(scope: completions|xp, limit: 50)` and `getCourse(course_id, limit: 50)`
- Privacy: `field_leaderboard_opt_out` (boolean) on User entity; added via `lms_leaderboard.install`
- Cache: `#cache` tags `leaderboard_global`, `leaderboard_course:{id}`; invalidated by `lms_progress` event subscriber
- Routes: `lms_leaderboard.routing.yml`
- Controller: `LeaderboardController::globalPage()`, `LeaderboardController::coursePage()`
- SCSS: `_leaderboard.scss`

## Dependencies
- TASK-143 (XP system — optional dependency; leaderboard works without it)
- None required to ship MVP

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests: `LeaderboardServiceTest` (ranking order, opt-out exclusion, cache invalidation)
- [ ] Privacy toggle verified: opted-out user does not appear on any leaderboard
- [ ] No new debt introduced
